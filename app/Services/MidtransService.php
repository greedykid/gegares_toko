<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\Order;

class MidtransService
{
    public function __construct()
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');
    }

    public function createSnapToken(Order $order): ?string
    {
        try {
            $items = $order->items->map(function ($item) {
                return [
                    'id' => $item->product_id,
                    'price' => (int) $item->product_price,
                    'quantity' => $item->quantity,
                    'name' => substr($item->product_name, 0, 50),
                ];
            })->toArray();

            if ($order->discount_amount > 0) {
                $items[] = [
                    'id' => 'DISCOUNT',
                    'price' => -(int) $order->discount_amount,
                    'quantity' => 1,
                    'name' => 'Potongan Diskon',
                ];
            }

            if ($order->shipping_cost > 0) {
                $items[] = [
                    'id' => 'SHIPPING',
                    'price' => (int) $order->shipping_cost,
                    'quantity' => 1,
                    'name' => 'Ongkos Kirim',
                ];
            }

            $params = [
                'transaction_details' => [
                    'order_id' => $order->midtrans_order_id ?? $order->order_number,
                    'gross_amount' => (int) $order->total,
                ],
                'item_details' => $items,
                'customer_details' => [
                    'first_name' => $order->user->name,
                    'email' => $order->user->email,
                    'phone' => $order->user->phone ?? '',
                ],
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            $order->update([
                'snap_token' => $snapToken,
                'midtrans_order_id' => $params['transaction_details']['order_id'],
            ]);

            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans createSnapToken error: ' . $e->getMessage());
            return null;
        }
    }

    public function handleNotification(array $payload): ?Order
    {
        try {
            $notification = new \Midtrans\Notification();

            $orderId = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status ?? null;
            $paymentType = $notification->payment_type ?? null;

            $order = Order::where('midtrans_order_id', $orderId)
                ->orWhere('order_number', $orderId)
                ->first();

            if (!$order) {
                Log::warning("Midtrans webhook: Order not found for ID {$orderId}");
                return null;
            }

            if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
                if ($fraudStatus === 'accept' || $fraudStatus === null) {
                    $order->update([
                        'status' => 'paid',
                        'payment_status' => 'paid',
                        'payment_method' => $paymentType,
                        'paid_at' => now(),
                    ]);

                    // Reduce stock
                    foreach ($order->items as $item) {
                        if ($item->product_variant_id) {
                            $item->variant()->decrement('stock', $item->quantity);
                        } else {
                            $item->product()->decrement('stock', $item->quantity);
                        }
                    }
                }
            } elseif ($transactionStatus === 'pending') {
                $order->update([
                    'status' => 'awaiting_payment',
                    'payment_status' => 'pending',
                    'payment_method' => $paymentType,
                ]);
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => $transactionStatus === 'expire' ? 'expired' : 'failed',
                ]);
            }

            return $order;
        } catch (\Exception $e) {
            Log::error('Midtrans handleNotification error: ' . $e->getMessage());
            return null;
        }
    }

    public function syncOrderWithMidtrans(Order $order): ?Order
    {
        try {
            $orderId = $order->midtrans_order_id ?? $order->order_number;
            /** @var object $status */
            $status = \Midtrans\Transaction::status($orderId);
            
            $transactionStatus = $status->transaction_status;
            $fraudStatus = $status->fraud_status ?? null;
            $paymentType = $status->payment_type ?? null;

            if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
                if ($fraudStatus === 'accept' || $fraudStatus === null) {
                    if ($order->payment_status !== 'paid') {
                        $order->update([
                            'status' => 'paid',
                            'payment_status' => 'paid',
                            'payment_method' => $paymentType,
                            'paid_at' => now(),
                        ]);

                        // Reduce stock
                        foreach ($order->items as $item) {
                            if ($item->product_variant_id) {
                                $item->variant()->decrement('stock', $item->quantity);
                            } else {
                                $item->product()->decrement('stock', $item->quantity);
                            }
                        }
                    }
                }
            } elseif ($transactionStatus === 'pending') {
                $order->update([
                    'status' => 'awaiting_payment',
                    'payment_status' => 'pending',
                    'payment_method' => $paymentType,
                ]);
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => $transactionStatus === 'expire' ? 'expired' : 'failed',
                ]);
            }

            return $order->refresh();
        } catch (\Exception $e) {
            // Transaction might not exist yet if just opened
            return $order;
        }
    }

    public function getTransactionStatus(string $orderId): ?\stdClass
    {
        try {
            /** @var \stdClass|array $status */
            $status = \Midtrans\Transaction::status($orderId);
            return is_array($status) ? (object)$status : $status;
        } catch (\Exception $e) {
            Log::error('Midtrans getTransactionStatus error: ' . $e->getMessage());
            return null;
        }
    }
}
