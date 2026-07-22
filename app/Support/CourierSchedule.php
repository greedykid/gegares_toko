<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * When a parcel can actually be handed to a courier.
 *
 * Two things have to line up, and both are easy to forget one of.
 *
 * The courier: same-day services are on demand — they collect now or not at
 * all. Asking Biteship to schedule one for the morning is refused ("Courier is
 * not available for scheduled delivery"), which used to leave a paid order in
 * "processing" with no booking and no way out.
 *
 * The shop: someone has to be there to give the parcel to the driver. Biteship
 * will accept an instant booking at 02:39 — production has the bookings to
 * prove it — but at 02:39 the shop is dark and no food changes hands. A
 * courier's willingness is not the same as a pickup.
 *
 * So the answer to "when can this ship?" is the first moment BOTH are open. An
 * order placed after hours is not booked late, it is booked later: the job
 * waits. The checkout notice, the confirmation modal and the booking job all
 * ask this class, so the customer is never promised a pickup that will not be
 * attempted.
 */
class CourierSchedule
{
    /**
     * Does this service only get collected inside a fixed daily window?
     */
    public static function hasPickupWindow(?string $courier, ?string $service): bool
    {
        return static::window($courier, $service) !== null;
    }

    /**
     * Can this parcel be collected right now? Requires the shop to be staffed
     * and, for on-demand services, the courier to be running.
     */
    public static function isOpenNow(?string $courier, ?string $service, ?Carbon $at = null): bool
    {
        return StoreSchedule::isOpenNow($at)
            && static::courierIsRunning($courier, $service, $at);
    }

    /**
     * The next moment a pickup is possible: the first time the shop and the
     * courier are open together.
     *
     * Null means either a pickup could happen right now, or — and callers must
     * handle this — the two never overlap at all. Opening 22:00–06:00 while a
     * courier only collects 09:00–14:00 has no answer, and pretending otherwise
     * would queue a booking that can never run.
     *
     * Stepping hour by hour rather than reasoning about which constraint binds:
     * either side can wrap past midnight, so the arithmetic has more cases than
     * it has value. Both windows sit on whole hours and the memo makes each
     * check free, so a week is 192 comparisons and no queries.
     */
    public static function nextOpening(?string $courier, ?string $service, ?Carbon $at = null): ?Carbon
    {
        if (static::isOpenNow($courier, $service, $at)) {
            return null;
        }

        $cursor = static::localTime($at)->startOfHour();

        for ($hour = 0; $hour < 24 * 8; $hour++) {
            $cursor->addHour();

            if (static::isOpenNow($courier, $service, $cursor)) {
                return $cursor->copy();
            }
        }

        return null;
    }

    /** Is the courier itself collecting at this moment? */
    protected static function courierIsRunning(?string $courier, ?string $service, ?Carbon $at = null): bool
    {
        $window = static::window($courier, $service);

        if ($window === null) {
            return true; // Runs round the clock; only the shop constrains it.
        }

        $hour = (int) static::localTime($at)->format('H');

        return $hour >= $window['opens_at'] && $hour < $window['closes_at'];
    }

    /**
     * @return array{opens_at:int, closes_at:int}|null
     */
    protected static function window(?string $courier, ?string $service): ?array
    {
        $courier = strtolower(trim((string) $courier));
        $service = strtolower(trim((string) $service));

        if (! in_array($service, config('biteship.pickup_window_services', []), true)) {
            return null;
        }

        $window = config('biteship.pickup_windows.'.$courier);

        return is_array($window) ? $window : null;
    }

    protected static function localTime(?Carbon $at = null): Carbon
    {
        return ($at ? $at->copy() : now())
            ->timezone(config('biteship.pickup_timezone', 'Asia/Jakarta'));
    }
}
