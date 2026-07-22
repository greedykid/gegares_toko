<?php

namespace App\Support;

use App\Models\StoreSetting;
use Illuminate\Support\Carbon;

/**
 * When the shop is staffed.
 *
 * Separate from the courier's hours on purpose: they answer different
 * questions. Gojek Instant will take a booking at 02:39, but nobody is at the
 * shop to hand the driver a box of klepon. A pickup needs both.
 *
 * Read from store_settings so the owner can change opening hours without a
 * deploy; the defaults match the contact page's existing 06:00–17:00 text.
 */
class StoreSchedule
{
    public const DEFAULT_OPENS_AT = 6;

    public const DEFAULT_CLOSES_AT = 17;

    public static function isOpenNow(?Carbon $at = null): bool
    {
        [$opens, $closes] = static::hours();

        $hour = (int) static::localTime($at)->format('H');

        return $hour >= $opens && $hour < $closes;
    }

    /**
     * When the shop opens on the given day, or null if it is shut all day.
     * Returns the moment itself so callers can compare it against a courier's.
     */
    public static function openingOn(Carbon $day): ?Carbon
    {
        [$opens, $closes] = static::hours();

        if ($opens >= $closes) {
            return null; // Misconfigured, or closed. Better than a bad promise.
        }

        return static::localTime($day)->setTime($opens, 0);
    }

    /** The next moment the shop is open, or null if it is already. */
    public static function nextOpening(?Carbon $at = null): ?Carbon
    {
        if (static::isOpenNow($at)) {
            return null;
        }

        [$opens, $closes] = static::hours();
        $now = static::localTime($at);

        $opening = $now->copy()->setTime($opens, 0);

        // Past closing, so the next shift is tomorrow's.
        if ($now->hour >= $closes) {
            $opening->addDay();
        }

        return $opening;
    }

    /** Opening hours as whole hours, falling back to the documented defaults. */
    protected static function hours(): array
    {
        $setting = StoreSetting::first();

        $opens = $setting?->opens_at
            ? (int) Carbon::parse($setting->opens_at)->format('H')
            : self::DEFAULT_OPENS_AT;

        $closes = $setting?->closes_at
            ? (int) Carbon::parse($setting->closes_at)->format('H')
            : self::DEFAULT_CLOSES_AT;

        return [$opens, $closes];
    }

    protected static function localTime(?Carbon $at = null): Carbon
    {
        return ($at ? $at->copy() : now())
            ->timezone(config('biteship.pickup_timezone', 'Asia/Jakarta'));
    }
}
