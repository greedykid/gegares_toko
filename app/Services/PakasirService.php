<?php

namespace App\Services;

use App\Jobs\BookBiteshipOrder;
use App\Models\Order;
use App\Notifications\OrderPaidNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PakasirService
{
    /**
     * Create redirect payment URL for Pakasir.
     */
    public function createPaymentUrl(Order $order): ?string
    {
        try {
            $slug = config('pakasir.project_slug', 'gegares');
            $amount = (int) $order->total;
            $orderId = $this->getPakasirOrderId($order->order_number);

            // Redirect user back to our payment page when done
            $isChatbotOrder = str_contains($order->notes ?? '', 'Dipesan otomatis via AI Chatbot');
            $redirectUrl = route('orders.payment', $order).($isChatbotOrder ? '?chatbot_open=1' : '');

            // Construct the Pakasir hosted payment URL (restricted to QRIS channel only)
            $paymentUrl = "https://app.pakasir.com/pay/{$slug}/{$amount}?order_id={$orderId}&payment_channel=qris&redirect=".urlencode($redirectUrl);

            $order->update([
                'pakasir_link' => $paymentUrl,
                'pakasir_order_id' => $orderId,
            ]);

            return $paymentUrl;
        } catch (\Exception $e) {
            Log::error('Pakasir createPaymentUrl error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Handle incoming Pakasir Webhook notification.
     */
    public function handleNotification(array $payload): ?Order
    {
        try {
            $orderId = $payload['order_id'] ?? null;

            if (! $orderId) {
                Log::warning('Pakasir Webhook: Missing order_id in payload');

                return null;
            }

            // Case-insensitive lookup to ensure matching regardless of driver settings
            $order = Order::whereRaw('LOWER(order_number) = ?', [strtolower($orderId)])
                ->orWhereRaw('LOWER(pakasir_order_id) = ?', [strtolower($orderId)])
                ->first();

            if (! $order) {
                Log::warning("Pakasir Webhook: Order not found for ID {$orderId}");

                return null;
            }

            // Idempotency: already settled, nothing to do.
            if ($order->payment_status === 'paid') {
                return $order;
            }

            // SECURITY: Do NOT trust the webhook payload to decide payment status.
            // The endpoint is public, so a spoofed POST could otherwise mark an
            // order as paid. Re-confirm the transaction straight from Pakasir's API.
            $transaction = $this->fetchCompletedTransaction($order);

            if ($transaction) {
                if ((int) ($transaction['amount'] ?? 0) !== (int) $order->total) {
                    Log::warning("Pakasir Webhook: Amount mismatch for Order #{$orderId}. API amount: ".($transaction['amount'] ?? 'null').", Order total: {$order->total}");

                    return null;
                }

                $this->markOrderPaid($order, $transaction['payment_method'] ?? 'qris', $transaction['completed_at'] ?? null);
                Log::info("Pakasir Webhook: Order #{$orderId} confirmed paid via API.");

                return $order;
            }

            // Fallback only when no API key is configured (e.g. local development):
            // verify the payload amount at minimum so the flow still works offline.
            if (! config('pakasir.api_key')) {
                $status = $payload['status'] ?? null;
                $amount = $payload['amount'] ?? null;

                if ($status === 'completed' && (int) $amount === (int) $order->total) {
                    Log::warning("Pakasir Webhook: API key not configured, falling back to payload trust for Order #{$orderId}.");
                    $this->markOrderPaid($order, $payload['payment_method'] ?? 'qris', $payload['completed_at'] ?? null);
                }

                return $order;
            }

            Log::warning("Pakasir Webhook: Unable to confirm a completed transaction with Pakasir API for Order #{$orderId}. Ignoring payload.");

            return $order;
        } catch (\Exception $e) {
            Log::error('Pakasir handleNotification error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Sync order status directly with Pakasir API.
     */
    public function syncOrderWithPakasir(Order $order): ?Order
    {
        try {
            if ($order->payment_status === 'paid') {
                return $order;
            }

            if (! config('pakasir.api_key')) {
                Log::warning('Pakasir sync: API Key is not configured.');

                return $order;
            }

            // Non-blocking single attempt on the polling path (the client polls
            // again shortly, and the webhook remains the authoritative path).
            $transaction = $this->fetchCompletedTransaction($order, false);

            if ($transaction && (int) ($transaction['amount'] ?? 0) === (int) $order->total) {
                $this->markOrderPaid($order, $transaction['payment_method'] ?? 'qris', $transaction['completed_at'] ?? null);
                Log::info("Pakasir Sync: Order #{$order->order_number} synced and updated to paid.");
            }

            return $order->refresh();
        } catch (\Exception $e) {
            Log::error('Pakasir syncOrderWithPakasir error: '.$e->getMessage());

            return $order;
        }
    }

    /**
     * Query Pakasir's API for a completed transaction belonging to the order.
     * Uses the single canonical order id format (no casing brute-force).
     *
     * @return array|null The transaction array when status is 'completed', otherwise null.
     */
    protected function fetchCompletedTransaction(Order $order, bool $withRetry = true): ?array
    {
        $apiKey = config('pakasir.api_key');
        if (! $apiKey) {
            return null;
        }

        $slug = config('pakasir.project_slug', 'gegares');

        // The id we actually registered with Pakasir when the payment link was
        // created, so it is the one that should match on the first try.
        $canonicalOrderId = $order->pakasir_order_id ?: $this->getPakasirOrderId($order->order_number);

        // ── Interactive path: the payment page and its 2-second polling ──
        // One request, nothing more. The exhaustive sweep below fired up to
        // 15 sequential API calls per check, so a single poll could take
        // seconds; because PHP holds an exclusive session lock for the whole
        // request, the next poll queued behind it and the page kept showing
        // "menunggu pembayaran" long after the payment had actually settled.
        if (! $withRetry) {
            return $this->queryTransaction($slug, $canonicalOrderId, $apiKey);
        }

        // ── Webhook path: authoritative and runs without a user waiting on it ──
        // It can afford the full sweep: Pakasir has been inconsistent about the
        // casing it accepts, and QRIS settlement can lag by a second or two.
        $projectSlugStrategies = array_values(array_unique([
            $slug,
            ucfirst($slug),
            strtoupper($slug),
        ]));

        $casingStrategies = [$canonicalOrderId];
        $parts = explode('-', $order->order_number);
        if (count($parts) === 3) {
            $prefix = $parts[0].'-'.$parts[1];
            $suffix = $parts[2];
            $casingStrategies[] = strtoupper($prefix).'-'.strtoupper($suffix); // GGR-...-HEX
            $casingStrategies[] = strtoupper($prefix).'-'.strtolower($suffix); // GGR-...-hex
            $casingStrategies[] = strtolower($prefix).'-'.strtoupper($suffix); // ggr-...-HEX
            $casingStrategies[] = strtolower($prefix).'-'.strtolower($suffix); // ggr-...-hex
        } else {
            $casingStrategies[] = strtolower($order->order_number);
            $casingStrategies[] = strtoupper($order->order_number);
        }
        $casingStrategies = array_values(array_unique($casingStrategies));

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            foreach ($projectSlugStrategies as $projectSlug) {
                foreach ($casingStrategies as $orderIdToCheck) {
                    $transaction = $this->queryTransaction($projectSlug, $orderIdToCheck, $apiKey);

                    if ($transaction) {
                        return $transaction;
                    }
                }
            }

            // QRIS settlement can lag; retry once after a short delay.
            if ($attempt < 2) {
                sleep(2);
            }
        }

        return null;
    }

    /**
     * Ask Pakasir for one transaction. Returns it only when it is 'completed'.
     *
     * The timeout is deliberate: without one, a slow Pakasir response would hang
     * the request for the client's default (30s) while holding the session lock.
     */
    protected function queryTransaction(string $projectSlug, string $orderId, string $apiKey): ?array
    {
        $response = Http::timeout(8)->get('https://app.pakasir.com/api/transactiondetail', [
            'project' => $projectSlug,
            'amount' => 0, // Pass 0 to bypass Pakasir's amount validation; we verify amount ourselves.
            'order_id' => $orderId,
            'api_key' => $apiKey,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $transaction = $response->json('transaction');

        return $transaction && ($transaction['status'] ?? null) === 'completed' ? $transaction : null;
    }

    /**
     * Atomically mark an order as paid and deduct stock exactly once.
     * Wrapped in a transaction with a row lock so concurrent webhook/sync
     * calls cannot double-deduct stock or double-process the payment.
     */
    protected function markOrderPaid(Order $order, string $paymentMethod, ?string $completedAt): void
    {
        $didTransition = DB::transaction(function () use ($order, $paymentMethod, $completedAt) {
            // Re-fetch under a row lock to make the paid-transition idempotent.
            $locked = Order::whereKey($order->getKey())->lockForUpdate()->first();

            if (! $locked || $locked->payment_status === 'paid') {
                return false; // Already processed by a concurrent request.
            }

            // Deduct stock first (atomic SQL decrement) while still inside the lock.
            // The `stock >= quantity` guard means two orders racing for the last
            // unit can never drive stock negative: whichever loses the race matches
            // zero rows and is logged instead of silently overselling.
            $shortfalls = [];

            foreach ($locked->items as $item) {
                $relation = $item->product_variant_id ? $item->variant() : $item->product();

                $affected = $relation->where('stock', '>=', $item->quantity)
                    ->decrement('stock', $item->quantity);

                if ($affected === 0) {
                    Log::warning("Stock oversell prevented for Order #{$locked->order_number}: '{$item->product_name}' requested {$item->quantity} but insufficient stock remains.");
                    $shortfalls[] = $item->product_name.' x'.$item->quantity;
                }
            }

            $updates = [
                // Go straight to "processing": once payment is confirmed the order
                // is being fulfilled, so the customer sees "Diproses" immediately.
                // Courier booking runs in the background (BookBiteshipOrder) and
                // only fills in tracking IDs — it no longer drives the visible
                // status, so the status is deterministic the moment payment lands.
                'status' => 'processing',
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod,
                // Pakasir sends `completed_at` without an offset, so it must be read
                // against its own zone and then shifted into the app timezone.
                // The shift is not optional: Eloquent stores whatever wall-clock the
                // Carbon carries, so a UTC 05:00 would be written as "05:00" and read
                // back as 05:00 WIB — the payment would look 7 hours early.
                'paid_at' => $completedAt
                    ? Carbon::parse($completedAt, config('pakasir.timezone', 'UTC'))
                        ->setTimezone(config('app.timezone'))
                    : now(),
            ];

            // Flag the shortfall on the order itself, not just in the log: the
            // customer has paid for goods the shop may not physically have, and
            // the admin needs to see that when opening the order.
            if ($shortfalls !== []) {
                $warning = 'PERLU DICEK: stok tidak mencukupi saat pembayaran ('.implode(', ', $shortfalls).').';
                $updates['notes'] = ($locked->notes ? $locked->notes.' | ' : '').$warning;
            }

            $locked->update($updates);

            return true;
        });

        $order->refresh();

        if ($didTransition) {
            // Notify the customer their payment succeeded (queued, non-blocking).
            // Only fires for the call that actually performed the paid transition,
            // so concurrent webhook/sync requests never send a duplicate email.
            if ($order->user) {
                try {
                    $order->user->notify(new OrderPaidNotification($order));
                } catch (\Throwable $e) {
                    Log::error('OrderPaid notification failed: '.$e->getMessage());
                }
            }

            // Book the courier in the background, outside this transaction/lock.
            // The test guard keeps the suite from hitting the real Biteship API
            // unless a mock is bound (queue is sync under tests).
            if (empty($order->biteship_order_id)
                && ! (app()->runningUnitTests() && ! app()->bound(BiteshipService::class))) {
                BookBiteshipOrder::dispatch($order->id);
            }
        }
    }

    /**
     * Get consistent order ID format for Pakasir (uppercase prefix GGR-YYYYMMDD, lowercase suffix).
     */
    public function getPakasirOrderId(string $orderNumber): string
    {
        $parts = explode('-', $orderNumber);
        if (count($parts) === 3) {
            return strtoupper($parts[0].'-'.$parts[1]).'-'.strtolower($parts[2]);
        }

        return strtoupper($orderNumber);
    }
}
