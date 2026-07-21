<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('orders:auto-cancel --hours=24')->hourly();

// Safety net: the Biteship webhook is the primary way shipping status reaches
// us, but a missed/failed delivery would otherwise leave an order stuck. Pull
// tracking for in-flight orders periodically. withoutOverlapping() keeps a slow
// run from stacking on the next tick.
Schedule::command('biteship:sync')->everyTenMinutes()->withoutOverlapping();
