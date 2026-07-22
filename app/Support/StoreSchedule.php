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

    /** Memo for the request; store hours are one rarely-changing row. */
    protected static ?array $cachedHours = null;

    public static function isOpenNow(?Carbon $at = null): bool
    {
        [$opens, $closes] = static::hours();

        $hour = (int) static::localTime($at)->format('H');

        // Equal times mean round the clock, not "shut forever" — a shop can
        // legitimately never close, and reading it the other way would silently
        // stop every order.
        if ($opens === $closes) {
            return true;
        }

        // Hours that wrap past midnight (22:00–06:00) are normal for a kitchen
        // that produces overnight, which is exactly what Gegares does. Treating
        // them as a broken range left the shop closed forever.
        if ($opens < $closes) {
            return $hour >= $opens && $hour < $closes;
        }

        return $hour >= $opens || $hour < $closes;
    }

    /** The next moment the shop is open, or null if it is already. */
    public static function nextOpening(?Carbon $at = null): ?Carbon
    {
        if (static::isOpenNow($at)) {
            return null;
        }

        [$opens] = static::hours();
        $now = static::localTime($at);

        $opening = $now->copy()->setTime($opens, 0);

        if ($opening->lessThanOrEqualTo($now)) {
            $opening->addDay();
        }

        return $opening;
    }

    /** Drop the memo. Called whenever the settings row is saved. */
    public static function forgetCachedHours(): void
    {
        static::$cachedHours = null;
    }

    /** Opening hours as whole hours, falling back to the documented defaults. */
    protected static function hours(): array
    {
        // Without this the row was read on every call — and since the checkout
        // page asks per shipping option, and nextOpening() asks repeatedly while
        // it searches, one page cost 21 queries for a single row.
        return static::$cachedHours ??= static::loadHours();
    }

    protected static function loadHours(): array
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
