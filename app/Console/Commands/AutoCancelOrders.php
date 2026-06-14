<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCancelOrders extends Command
{
    protected $signature = 'orders:auto-cancel {--hours=24 : Hours after creation to cancel unpaid orders}';
    protected $description = 'Automatically cancel orders that are unpaid after a specified period and release stock.';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);

        $orders = Order::where('status', 'pending')
            ->where('payment_status', 'unpaid')
            ->where('created_at', '<', $cutoff)
            ->with('items')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No overdue orders found.');
            return self::SUCCESS;
        }

        $cancelledCount = 0;

        foreach ($orders as $order) {
            // Release stock back to products/variants
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $variant = ProductVariant::find($item->product_variant_id);
                    if ($variant) {
                        $variant->increment('stock', $item->quantity);
                    }
                } else {
                    $product = $item->product;
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }
            }

            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'expired',
                'notes' => ($order->notes ? $order->notes . ' | ' : '') . "Otomatis dibatalkan setelah {$hours} jam tanpa pembayaran.",
            ]);

            $cancelledCount++;
            Log::info("Auto-cancelled order #{$order->order_number} (ID: {$order->id})");
        }

        $this->info("Successfully cancelled {$cancelledCount} overdue order(s).");
        return self::SUCCESS;
    }
}
