<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\BiteshipService;
use App\Support\CourierSchedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Books a paid order with Biteship outside the request lifecycle.
 *
 * This used to run synchronously inside Order's `updated` model event, which
 * fired inside PakasirService::markOrderPaid()'s DB transaction while a
 * `lockForUpdate` row lock was still held. A slow Biteship response therefore
 * kept the order row locked and the payment transaction open for the whole
 * network round-trip, and a Biteship timeout could roll back an already-settled
 * payment. Dispatching a queued job moves the network call to a worker that
 * runs after the transaction has committed and the lock is released.
 */
class BookBiteshipOrder implements ShouldQueue
{
    use Queueable;

    /**
     * Retry a failed booking a few times — Biteship can be briefly down.
     *
     * Five rather than three because waiting for a courier's pickup window also
     * costs an attempt, and an order placed at night must not arrive at the
     * morning with its budget already half spent.
     */
    public $tries = 5;

    /** Seconds to wait before the 2nd and 3rd attempt. */
    public function backoff(): array
    {
        return [30, 120];
    }

    public function __construct(public int $orderId) {}

    public function handle(BiteshipService $biteship): void
    {
        $order = Order::find($this->orderId);

        // Defensive re-checks: the job can run after the paid transition was
        // rolled back, after a concurrent worker already booked the order, or
        // after an admin booked it manually. Only auto-book a still-unbooked,
        // already-paid order (status is "processing" by this point).
        if (! $order || $order->payment_status !== 'paid' || ! empty($order->biteship_order_id)) {
            return;
        }

        // Cap automated re-allocation attempts so a courier that keeps rejecting
        // cannot loop forever (the Biteship webhook resets status back to paid).
        $retryKey = 'biteship_reallocation_retries_'.$order->id;
        if (Cache::get($retryKey, 0) >= 2) {
            Log::warning("Biteship Auto-Process: Order #{$order->order_number} has exceeded re-allocation retry limit. Skipping auto-booking.");

            return;
        }

        // A same-day courier collects on demand, so out of hours there is
        // nothing to ask for yet. Wait for the window rather than spending the
        // retries on a rejection that cannot change for hours — which is what
        // left after-hours orders paid, unbooked and unrecoverable.
        //
        // release() re-queues THIS job for the moment the window opens. A fresh
        // dispatch()->delay() would preserve the retry budget but is unsafe: the
        // sync driver ignores delays, so the new job would run at once, find the
        // window still shut, dispatch again — forever. release() is a no-op
        // under sync, so there the job simply stops and the admin_note explains
        // what happened.
        if (! CourierSchedule::isOpenNow($order->shipping_courier, $order->shipping_service)) {
            $opensAt = CourierSchedule::nextOpening($order->shipping_courier, $order->shipping_service);

            Log::info("Biteship Auto-Process: Order #{$order->order_number} is outside {$order->shipping_courier} pickup hours. Booking deferred to {$opensAt}.");

            $this->noteDeferral($order, $opensAt);

            $this->release($opensAt ?? now()->addHour());

            return;
        }

        try {
            $result = $biteship->createOrder($order);

            if ($result && ($result['success'] ?? false)) {
                Order::withoutEvents(function () use ($order, $result) {
                    $order->syncOriginal();
                    $updates = [
                        'biteship_order_id' => $result['id'] ?? $order->biteship_order_id,
                        'courier_tracking_id' => $result['courier']['tracking_id'] ?? $result['courier_tracking_id'] ?? $order->courier_tracking_id,
                        'tracking_number' => $result['courier']['waybill_id'] ?? $order->tracking_number,
                    ];

                    // The order is already "processing" by this point (payment set
                    // it there); this only guards against a more-advanced order
                    // being dragged back if the job runs late.
                    if ($order->status === 'processing') {
                        $updates['status'] = 'processing';
                    }

                    $order->update($updates);
                });
                Log::info("Biteship Auto-Process: Order #{$order->order_number} successfully processed to Biteship.");
            } else {
                $err = $result['error'] ?? 'Unknown error';
                Log::warning("Biteship Auto-Process Failed for Order #{$order->order_number}: ".$err);

                // Hand the job back to the queue so a transient Biteship outage
                // gets another go (see $tries/backoff). release() is a no-op when
                // handle() is invoked directly, so this never throws into the
                // payment flow.
                $this->release($this->retryDelay());
            }
        } catch (\Throwable $e) {
            Log::error("Biteship Auto-Process Error for Order #{$order->order_number}: ".$e->getMessage());

            $this->release($this->retryDelay());
        }
    }

    /** Delay for the next attempt, mirroring backoff(). */
    protected function retryDelay(): int
    {
        return $this->attempts() >= 2 ? 120 : 30;
    }

    /**
     * Leave one line on the order saying why no courier has been booked yet, so
     * an admin opening it sees a plan rather than something broken. Written once:
     * the job re-dispatches itself and must not stack the same sentence nightly.
     */
    protected function noteDeferral(Order $order, ?\Illuminate\Support\Carbon $opensAt): void
    {
        $marker = 'MENUNGGU JAM KURIR';

        if (str_contains($order->admin_note ?? '', $marker)) {
            return;
        }

        $note = $marker.': di luar jam operasional '.strtoupper((string) $order->shipping_courier)
            .', booking otomatis dijadwalkan '.$opensAt?->translatedFormat('d M H:i').' WIB.';

        // admin_note, never `notes` — that column belongs to the customer.
        $order->update([
            'admin_note' => ($order->admin_note ? $order->admin_note.' | ' : '').$note,
        ]);
    }

    /**
     * All attempts exhausted: leave a clear trail. The order stays "processing"
     * without a Biteship booking, which is exactly the state the admin's
     * "Booking Ulang ke Biteship" button is there to resolve.
     */
    public function failed(?\Throwable $e = null): void
    {
        Log::error("Biteship Auto-Process: giving up on order #{$this->orderId} after {$this->tries} attempts. ".($e?->getMessage() ?? ''));
    }
}
