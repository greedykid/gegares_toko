<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCancelOrders extends Command
{
    protected $signature = 'orders:auto-cancel {--hours=24 : Hours after creation to cancel unpaid orders}';

    protected $description = 'Automatically cancel orders that are still unpaid after a specified period.';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);

        $cancelledCount = 0;

        // NOTE: deliberately does NOT return stock. Stock is only ever deducted
        // when a payment settles (PakasirService::markOrderPaid); these orders
        // are unpaid, so nothing was taken from stock in the first place.
        // Incrementing here used to invent stock that never existed.
        Order::where('status', 'pending')
            ->where('payment_status', 'unpaid')
            ->where('created_at', '<', $cutoff)
            ->chunkById(100, function ($orders) use (&$cancelledCount, $hours) {
                foreach ($orders as $order) {
                    $order->update([
                        'status' => 'cancelled',
                        'payment_status' => 'expired',
                        // System trail goes to admin_note; `notes` stays the customer's.
                        'admin_note' => ($order->admin_note ? $order->admin_note.' | ' : '')."Otomatis dibatalkan setelah {$hours} jam tanpa pembayaran.",
                    ]);

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
