<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCancelOrders extends Command
{
    protected $signature = 'orders:auto-cancel {--hours=24 : Hours after creation to cancel unpaid orders}';

    protected $description = 'Automatically cancel orders that are still unpaid after a specified period.';

    public function handle(OrderService $orders): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);

        $cancelledCount = 0;

        // This is the release valve for the reservation made at checkout: stock
        // and the coupon slot were claimed when the order was written, so an
        // order abandoned without payment has to give both back — otherwise a
        // few unpaid carts could hold the whole shelf hostage. cancelAndRelease
        // owns that logic for every cancellation path.
        Order::where('status', 'pending')
            ->where('payment_status', 'unpaid')
            ->where('created_at', '<', $cutoff)
            ->chunkById(100, function ($chunk) use (&$cancelledCount, $hours, $orders) {
                foreach ($chunk as $order) {
                    $cancelled = $orders->cancelAndRelease($order, [
                        'payment_status' => 'expired',
                        // System trail goes to admin_note; `notes` stays the customer's.
                        'admin_note' => ($order->admin_note ? $order->admin_note.' | ' : '')."Otomatis dibatalkan setelah {$hours} jam tanpa pembayaran.",
                    ]);

                    if ($cancelled) {
                        $cancelledCount++;
                        Log::info("Auto-cancelled order #{$order->order_number} (ID: {$order->id})");
                    }
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
