<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\BiteshipService;
use App\Services\OrderService;
use Illuminate\Console\Command;

class SyncBiteshipOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'biteship:sync';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Sync active orders and tracking details from Biteship API';

    /**
     * Execute the console command.
     */
    public function handle(BiteshipService $biteship, OrderService $orders)
    {
        // Only orders still in flight. The old filter also caught cancelled and
        // completed orders whose courier_tracking_id happened to be empty, and
        // since it wrote statuses directly it could bring them back to life.
        $pending = Order::whereNotNull('biteship_order_id')
            ->where('biteship_order_id', '!=', '')
            ->whereIn('status', ['processing', 'shipped'])
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No orders require syncing.');

            return 0;
        }

        foreach ($pending as $order) {
            $this->info("Syncing Order #{$order->order_number}...");
            $result = $biteship->getOrder($order->biteship_order_id);

            if (! $result || ! isset($result['courier'])) {
                $this->error("Failed to sync Order #{$order->order_number}.");

                continue;
            }

            $trackingId = $result['courier']['tracking_id'] ?? null;
            $waybillId = $result['courier']['waybill_id'] ?? null;
            $status = $result['status'] ?? null;

            $order->update([
                'courier_tracking_id' => $trackingId ?? $order->courier_tracking_id,
                'tracking_number' => $waybillId ?? $order->tracking_number,
            ]);

            // This used to carry its own copy of the status map and write the
            // result straight onto the order — no transition rules, and a
            // cancellation that never returned the stock or the coupon slot.
            $orders->applyCourierStatus($order, $status, 'Biteship Sync');

            $this->info("Successfully synced Order #{$order->order_number}. Tracking ID: ".($trackingId ?? 'null').', Status: '.($status ?? 'null'));
        }

        \Illuminate\Support\Facades\Artisan::call('orders:auto-complete', ['--hours' => 24]);

        return 0;
    }
}
