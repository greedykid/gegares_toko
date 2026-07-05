<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCancelOrders extends Command
{
    protected $signature = 'orders:auto-cancel {--hours=24 : Hours after creation to cancel unpaid orders}';
    protected $description = 'Automatically cancel orders that are unpaid after a specified period and release stock.';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);

        $cancelledCount = 0;

        // Stream overdue orders in batches (with items + relations eager loaded)
        // instead of loading everything into memory and lazy-loading per item.
        Order::where('status', 'pending')
            ->where('payment_status', 'unpaid')
            ->where('created_at', '<', $cutoff)
            ->with(['items.product', 'items.variant'])
            ->chunkById(100, function ($orders) use (&$cancelledCount, $hours) {
                foreach ($orders as $order) {
                    DB::transaction(function () use ($order, $hours) {
                        // Release stock back to products/variants
                        foreach ($order->items as $item) {
                            if ($item->product_variant_id) {
                                $item->variant?->increment('stock', $item->quantity);
                            } else {
                                $item->product?->increment('stock', $item->quantity);
                            }
                        }

                        $order->update([
                            'status' => 'cancelled',
                            'payment_status' => 'expired',
                            'notes' => ($order->notes ? $order->notes . ' | ' : '') . "Otomatis dibatalkan setelah {$hours} jam tanpa pembayaran.",
                        ]);
                    });

                    $cancelledCount++;
                    Log::info("Auto-cancelled order #{$order->order_number} (ID: {$order->id})");
                }
            });

        if ($cancelledCount === 0) {
            $this->info('No overdue orders found.');
            return self::SUCCESS;
        }

        $this->info("Successfully cancelled {$cancelledCount} overdue order(s).");
        return self::SUCCESS;
    }
}
