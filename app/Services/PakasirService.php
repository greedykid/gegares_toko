<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PakasirService
{
    /**
     * Create redirect payment URL for Pakasir.
     *
     * @param Order $order
     * @return string|null
     */
    public function createPaymentUrl(Order $order): ?string
    {
        try {
            $slug = config('pakasir.project_slug', 'gegares');
            $amount = (int) $order->total;
            $orderId = $this->getPakasirOrderId($order->order_number);
            
            // Redirect user back to our payment page when done
            $redirectUrl = route('orders.payment', $order) . '?chatbot_open=1';
            
            // Construct the Pakasir hosted payment URL (restricted to QRIS channel only)
            $paymentUrl = "https://app.pakasir.com/pay/{$slug}/{$amount}?order_id={$orderId}&payment_channel=qris&redirect=" . urlencode($redirectUrl);

            $order->update([
                'pakasir_link' => $paymentUrl,
                'pakasir_order_id' => $orderId,
            ]);

            return $paymentUrl;
        } catch (\Exception $e) {
            Log::error('Pakasir createPaymentUrl error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Handle incoming Pakasir Webhook notification.
     *
     * @param array $payload
     * @return Order|null
     */
    public function handleNotification(array $payload): ?Order
    {
        try {
            $orderId = $payload['order_id'] ?? null;

            if (!$orderId) {
                Log::warning('Pakasir Webhook: Missing order_id in payload');
                return null;
            }

            // Case-insensitive lookup to ensure matching regardless of driver settings
            $order = Order::whereRaw('LOWER(order_number) = ?', [strtolower($orderId)])
                ->orWhereRaw('LOWER(pakasir_order_id) = ?', [strtolower($orderId)])
                ->first();

            if (!$order) {
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
                    Log::warning("Pakasir Webhook: Amount mismatch for Order #{$orderId}. API amount: " . ($transaction['amount'] ?? 'null') . ", Order total: {$order->total}");
                    return null;
                }

                $this->markOrderPaid($order, $transaction['payment_method'] ?? 'qris', $transaction['completed_at'] ?? null);
                Log::info("Pakasir Webhook: Order #{$orderId} confirmed paid via API.");
                return $order;
            }

            // Fallback only when no API key is configured (e.g. local development):
            // verify the payload amount at minimum so the flow still works offline.
            if (!config('pakasir.api_key')) {
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
            Log::error('Pakasir handleNotification error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Sync order status directly with Pakasir API.
     *
     * @param Order $order
     * @return Order|null
     */
    public function syncOrderWithPakasir(Order $order): ?Order
    {
        try {
            if ($order->payment_status === 'paid') {
                return $order;
            }

            if (!config('pakasir.api_key')) {
                Log::warning('Pakasir sync: API Key is not configured.');
                return $order;
            }

            $transaction = $this->fetchCompletedTransaction($order);

            if ($transaction && (int) ($transaction['amount'] ?? 0) === (int) $order->total) {
                $this->markOrderPaid($order, $transaction['payment_method'] ?? 'qris', $transaction['completed_at'] ?? null);
                Log::info("Pakasir Sync: Order #{$order->order_number} synced and updated to paid.");
            }

            return $order->refresh();
        } catch (\Exception $e) {
            Log::error('Pakasir syncOrderWithPakasir error: ' . $e->getMessage());
            return $order;
        }
    }

    /**
     * Query Pakasir's API for a completed transaction belonging to the order.
     * Uses the single canonical order id format (no casing brute-force).
     *
     * @param Order $order
     * @return array|null The transaction array when status is 'completed', otherwise null.
     */
    protected function fetchCompletedTransaction(Order $order): ?array
    {
        $apiKey = config('pakasir.api_key');
        if (!$apiKey) {
            return null;
        }

        $slug = config('pakasir.project_slug', 'gegares');
        $orderId = $order->pakasir_order_id ?: $this->getPakasirOrderId($order->order_number);
        $maxAttempts = 2;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $response = Http::get('https://app.pakasir.com/api/transactiondetail', [
                'project' => $slug,
                'amount' => 0, // Pass 0 to bypass Pakasir's amount validation; we verify amount ourselves.
                'order_id' => $orderId,
                'api_key' => $apiKey,
            ]);

            if ($response->successful()) {
                $transaction = $response->json('transaction');
                if ($transaction && ($transaction['status'] ?? null) === 'completed') {
                    return $transaction;
                }
            }

            // QRIS settlement can lag; retry once after a short delay.
            if ($attempt < $maxAttempts) {
                sleep(2);
            }
        }

        return null;
    }

    /**
     * Atomically mark an order as paid and deduct stock exactly once.
     * Wrapped in a transaction with a row lock so concurrent webhook/sync
     * calls cannot double-deduct stock or double-process the payment.
     *
     * @param Order $order
     * @param string $paymentMethod
     * @param string|null $completedAt
     * @return void
     */
    protected function markOrderPaid(Order $order, string $paymentMethod, ?string $completedAt): void
    {
        DB::transaction(function () use ($order, $paymentMethod, $completedAt) {
            // Re-fetch under a row lock to make the paid-transition idempotent.
            $locked = Order::whereKey($order->getKey())->lockForUpdate()->first();

            if (!$locked || $locked->payment_status === 'paid') {
                return; // Already processed by a concurrent request.
            }

            // Deduct stock first (atomic SQL decrement) while still inside the lock.
            foreach ($locked->items as $item) {
                if ($item->product_variant_id) {
                    $item->variant()->decrement('stock', $item->quantity);
                } else {
                    $item->product()->decrement('stock', $item->quantity);
                }
            }

            $locked->update([
                'status' => 'paid',
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod,
                'paid_at' => $completedAt ? Carbon::parse($completedAt) : now(),
            ]);
        });

        $order->refresh();
    }

    /**
     * Get consistent order ID format for Pakasir (uppercase prefix GGR-YYYYMMDD, lowercase suffix).
     *
     * @param string $orderNumber
     * @return string
     */
    public function getPakasirOrderId(string $orderNumber): string
    {
        $parts = explode('-', $orderNumber);
        if (count($parts) === 3) {
            return strtoupper($parts[0] . '-' . $parts[1]) . '-' . strtolower($parts[2]);
        }
        return strtoupper($orderNumber);
    }
}
