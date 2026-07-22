<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * When a courier will actually come and collect a parcel.
 *
 * Same-day couriers are on demand: they pick up now or not at all. Asking
 * Biteship to schedule one for the morning is refused ("Courier is not
 * available for scheduled delivery"), which used to leave a paid order sitting
 * in "processing" with no booking and no way to recover — the retry budget was
 * spent within three minutes and the admin's re-book button hit the same wall.
 *
 * So an order placed after hours is not booked late, it is booked later: the
 * job waits for the window to open. The checkout notice and the booking job
 * both ask this class, so the customer is never promised a pickup the system
 * will not attempt.
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
     * Can this service be booked right now?
     *
     * True for anything without a window — those are handed over as normal
     * shipments and do not wait for anybody.
     */
    public static function isOpenNow(?string $courier, ?string $service, ?Carbon $at = null): bool
    {
        $window = static::window($courier, $service);

        if ($window === null) {
            return true;
        }

        $hour = (int) static::localTime($at)->format('H');

        return $hour >= $window['opens_at'] && $hour < $window['closes_at'];
    }

    /**
     * The next moment this service can be booked — today if the window has not
     * opened yet, otherwise tomorrow morning. Null when there is nothing to
     * wait for.
     */
    public static function nextOpening(?string $courier, ?string $service, ?Carbon $at = null): ?Carbon
    {
        $window = static::window($courier, $service);

        if ($window === null || static::isOpenNow($courier, $service, $at)) {
            return null;
        }

        $now = static::localTime($at);

        $opening = $now->copy()->setTime($window['opens_at'], 0);

        // Past today's window: the next one is tomorrow.
        if ($now->hour >= $window['closes_at']) {
            $opening->addDay();
        }

        return $opening;
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
