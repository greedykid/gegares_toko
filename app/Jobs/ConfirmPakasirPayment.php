<?php

namespace App\Jobs;

use App\Services\PakasirService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Confirm a Pakasir payment off the request path.
 *
 * The webhook used to run the whole API re-confirmation inline — a sweep of up
 * to two attempts across several project-slug and order-id casings with a
 * two-second sleep between rounds. Pakasir was left holding the connection for
 * that entire time and could time the notification out. Moving it here lets the
 * webhook acknowledge immediately; the authoritative confirmation still happens,
 * just without a caller waiting on it. The payment page's own 2-second polling
 * (OrderController::checkStatus) remains a second, independent path to paid.
 *
 * Only the non-sensitive fields the confirmation actually reads are carried, so
 * nothing extra from the payment notification is persisted in the jobs table.
 */
class ConfirmPakasirPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** QRIS settlement can lag; give the confirmation a few chances. */
    public int $tries = 3;

    public int $backoff = 15;

    /** @param  array<string, mixed>  $payload */
    public function __construct(protected array $payload) {}

    public function handle(PakasirService $pakasir): void
    {
        $pakasir->handleNotification($this->payload);
    }
}
