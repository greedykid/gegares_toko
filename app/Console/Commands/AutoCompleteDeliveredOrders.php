<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCompleteDeliveredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:auto-complete {--hours=24 : Number of hours after delivery to auto complete}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Auto complete orders that were delivered more than 24 hours (1 day) ago if customer has not confirmed receipt';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $threshold = now()->subHours($hours);

        $orders = Order::where('status', 'shipped')
            ->whereNotNull('delivered_at')
            ->where('delivered_at', '<=', $threshold)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No delivered orders ready for auto-completion.');

            return 0;
        }

        $count = 0;
        foreach ($orders as $order) {
            $order->update([
                'status' => 'completed',
            ]);
            Log::info("AutoCompleteDeliveredOrders: Order #{$order->order_number} auto-completed after {$hours} hours from delivery.");
            $count++;
        }

        $this->info("Successfully auto-completed {$count} order(s).");

        return 0;
    }
}
