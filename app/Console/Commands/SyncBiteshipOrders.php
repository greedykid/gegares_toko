<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\BiteshipService;

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
    public function handle(BiteshipService $biteship)
    {
        $orders = Order::whereNotNull('biteship_order_id')
            ->where('biteship_order_id', '!=', '')
            ->where(function ($query) {
                $query->whereNull('courier_tracking_id')
                      ->orWhereIn('status', ['processing', 'shipped']);
            })
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No orders require syncing.');
            return 0;
        }

        foreach ($orders as $order) {
            $this->info("Syncing Order #{$order->order_number}...");
            $result = $biteship->getOrder($order->biteship_order_id);

            if ($result && isset($result['courier'])) {
                $trackingId = $result['courier']['tracking_id'] ?? null;
                $waybillId = $result['courier']['waybill_id'] ?? null;
                $status = $result['status'] ?? null;

                $newStatus = match($status) {
                    'allocated', 'picking_up', 'pickingUp' => 'processing',
                    'picked_up', 'picked', 'dropping_off', 'droppingOff', 'out_for_delivery', 'on_the_way', 'in_transit', 'dispatched', 'return_in_transit', 'returnInTransit' => 'shipped',
                    'delivered' => 'completed',
                    'cancelled', 'canceled', 'returned' => 'cancelled',
                    default => $order->status
                };

                $order->update([
                    'courier_tracking_id' => $trackingId ?? $order->courier_tracking_id,
                    'tracking_number' => $waybillId ?? $order->tracking_number,
                    'status' => $newStatus,
                ]);

                $this->info("Successfully synced Order #{$order->order_number}. Tracking ID: " . ($trackingId ?? 'null') . ", Status: " . $status);
            } else {
                $this->error("Failed to sync Order #{$order->order_number}.");
            }
        }

        return 0;
    }
}
