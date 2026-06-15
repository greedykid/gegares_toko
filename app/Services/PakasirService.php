<?php

namespace App\Services;

use App\Models\Order;
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
            $redirectUrl = route('orders.payment', $order);
            
            // Construct the Pakasir hosted payment URL
            $paymentUrl = "https://app.pakasir.com/pay/{$slug}/{$amount}?order_id={$orderId}&redirect=" . urlencode($redirectUrl);

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
            $status = $payload['status'] ?? null;
            $amount = $payload['amount'] ?? null;
            $paymentMethod = $payload['payment_method'] ?? 'qris';
            $completedAt = $payload['completed_at'] ?? null;

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

            // Verify transaction amount matches
            if ((int) $amount !== (int) $order->total) {
                Log::warning("Pakasir Webhook: Amount mismatch for Order #{$orderId}. Payload amount: {$amount}, Order total: {$order->total}");
                return null;
            }

            if ($status === 'completed') {
                if ($order->payment_status !== 'paid') {
                    $order->update([
                        'status' => 'paid',
                        'payment_status' => 'paid',
                        'payment_method' => $paymentMethod,
                        'paid_at' => $completedAt ? \Illuminate\Support\Carbon::parse($completedAt) : now(),
                    ]);

                    // Deduct stock for each purchased item
                    foreach ($order->items as $item) {
                        if ($item->product_variant_id) {
                            $item->variant()->decrement('stock', $item->quantity);
                        } else {
                            $item->product()->decrement('stock', $item->quantity);
                        }
                    }

                    Log::info("Pakasir Webhook: Order #{$orderId} has been successfully paid.");
                }
            }

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
            $slug = config('pakasir.project_slug', 'gegares');
            // Try different project slug casing strategies due to inconsistent casing requirements in Pakasir
            $projectSlugStrategies = array_values(array_unique([
                $slug,
                ucfirst($slug),
                strtoupper($slug)
            ]));
            $amount = (int) $order->total;
            $apiKey = config('pakasir.api_key');

            if (!$apiKey) {
                Log::warning('Pakasir sync: API Key is not configured.');
                return $order;
            }

            // Generate casing strategies for order_id to query due to inconsistent Pakasir API case matching
            $orderId = $order->order_number;
            $parts = explode('-', $orderId);
            $casingStrategies = [];
            if (count($parts) === 3) {
                $prefix = $parts[0] . '-' . $parts[1];
                $suffix = $parts[2];
                // Try all 4 combinations of prefix and suffix casing:
                $casingStrategies[] = strtoupper($prefix) . '-' . strtoupper($suffix); // GGR-...-HEX
                $casingStrategies[] = strtoupper($prefix) . '-' . strtolower($suffix); // GGR-...-hex
                $casingStrategies[] = strtolower($prefix) . '-' . strtoupper($suffix); // ggr-...-HEX
                $casingStrategies[] = strtolower($prefix) . '-' . strtolower($suffix); // ggr-...-hex
            } else {
                $casingStrategies[] = $orderId;
                $casingStrategies[] = strtolower($orderId);
                $casingStrategies[] = strtoupper($orderId);
            }

            // Add the stored pakasir_order_id as the very first check if it exists
            if ($order->pakasir_order_id) {
                array_unshift($casingStrategies, $order->pakasir_order_id);
            }
            $casingStrategies = array_values(array_unique($casingStrategies));

            $response = null;
            $transaction = null;

            foreach ($projectSlugStrategies as $projectSlug) {
                foreach ($casingStrategies as $orderIdToCheck) {
                    $response = Http::get('https://app.pakasir.com/api/transactiondetail', [
                        'project' => $projectSlug,
                        'amount' => 0, // Pass 0 to bypass Pakasir's buggy amount validation logic
                        'order_id' => $orderIdToCheck,
                        'api_key' => $apiKey,
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $transaction = $data['transaction'] ?? null;
                        if ($transaction) {
                            break 2; // Found transaction!
                        }
                    }
                }
            }

            if ($transaction && ($transaction['status'] ?? null) === 'completed') {
                // Verify transaction amount matches order total in database to maintain security
                if ((int) ($transaction['amount'] ?? 0) === (int) $order->total) {
                    if ($order->payment_status !== 'paid') {
                        $order->update([
                            'status' => 'paid',
                            'payment_status' => 'paid',
                            'payment_method' => $transaction['payment_method'] ?? 'qris',
                            'paid_at' => isset($transaction['completed_at']) ? \Illuminate\Support\Carbon::parse($transaction['completed_at']) : now(),
                        ]);

                        // Deduct stock for each purchased item
                        foreach ($order->items as $item) {
                            if ($item->product_variant_id) {
                                $item->variant()->decrement('stock', $item->quantity);
                            } else {
                                $item->product()->decrement('stock', $item->quantity);
                            }
                        }
                        
                        Log::info("Pakasir Sync: Order #{$order->order_number} synced and updated to paid.");
                    }
                }
            }

            return $order->refresh();
        } catch (\Exception $e) {
            Log::error('Pakasir syncOrderWithPakasir error: ' . $e->getMessage());
            return $order;
        }
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
