<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\PakasirService;
use Illuminate\Console\Command;

/**
 * Close the one gap where a real payment can slip through.
 *
 * The webhook (now a queued ConfirmPakasirPayment job) is the primary path to
 * "paid", and the payment page's polling is a second one — but that polling only
 * runs while the customer keeps the page open. If the webhook job exhausts its
 * retries (Pakasir's API briefly down) AND the customer has closed the tab, a
 * genuinely settled order would sit unpaid until orders:auto-cancel retires it
 * at 24h — cancelling an order the shop was actually paid for.
 *
 * This sweeps still-unpaid orders that already have a payment link and re-confirms
 * them against Pakasir's API, using the same authoritative path the webhook uses.
 * It only looks at orders old enough that the live polling has moved on and young
 * enough that auto-cancel has not yet acted, so it neither fights the payment page
 * nor wakes long-dead orders.
 */
class ReconcilePendingPayments extends Command
{
    protected $signature = 'orders:reconcile-payments
        {--minutes=10 : Only re-check orders at least this many minutes old}
        {--hours=24 : Ignore orders older than this (auto-cancel owns them)}';

    protected $description = 'Re-confirm still-unpaid orders against Pakasir so a settled payment whose webhook was lost is not auto-cancelled.';

    public function handle(PakasirService $pakasir): int
    {
        if (! config('pakasir.api_key')) {
            $this->warn('Pakasir API key not configured; nothing to reconcile.');

            return self::SUCCESS;
        }

        $youngerThan = now()->subMinutes((int) $this->option('minutes'));
        $olderThan = now()->subHours((int) $this->option('hours'));

        $settled = 0;
        $checked = 0;

        Order::where('status', 'pending')
            ->where('payment_status', 'unpaid')
            ->whereNotNull('pakasir_order_id')
            ->where('created_at', '<=', $youngerThan)
            ->where('created_at', '>', $olderThan)
            ->chunkById(100, function ($orders) use ($pakasir, &$settled, &$checked) {
                foreach ($orders as $order) {
                    $checked++;

                    // Authoritative sweep — same confirmation the webhook runs.
                    // markOrderPaid (inside) is idempotent and handles notifying
                    // the customer and booking the courier when it settles.
                    $pakasir->syncOrderWithPakasir($order, true);

                    if ($order->fresh()->payment_status === 'paid') {
                        $settled++;
                        $this->info("Reconciled paid: Order #{$order->order_number}");
                    }
                }
            });

        $this->info("Payment reconciliation done. Checked {$checked}, settled {$settled}.");

        return self::SUCCESS;
    }
}
