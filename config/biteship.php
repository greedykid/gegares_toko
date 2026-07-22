<?php

return [
    'api_key' => env('BITESHIP_API_KEY'),
    // Shared secret to authenticate incoming Biteship webhooks. When set, requests
    // must present it via the `X-Webhook-Token` header or a `token` query param.
    'webhook_token' => env('BITESHIP_WEBHOOK_TOKEN'),
    'base_url' => 'https://api.biteship.com/v1',
    'origin_area_id' => env('BITESHIP_ORIGIN_AREA_ID', 'IDNP6IDNC147IDND829'),
    'timeout' => 30,

    /*
    | When each same-day courier will actually collect a parcel.
    |
    | These are the courier's hours, not the shop's: they are what decides
    | whether a booking can be made at all, so both the checkout notice and the
    | booking job read them from here. They used to be hardcoded inside
    | BiteshipService, where nothing else could see them.
    |
    | Couriers not listed here are treated as bookable at any hour.
    */
    'pickup_timezone' => 'Asia/Jakarta',

    'pickup_windows' => [
        'grab' => ['opens_at' => 9, 'closes_at' => 14],
        'gojek' => ['opens_at' => 9, 'closes_at' => 15],
    ],

    // Only these service types depend on the window above.
    //
    // 'instant' is deliberately absent, and production data says to keep it that
    // way: of ten instant orders, the nine that were paid all booked -- at 02:23,
    // 02:39, 05:09, 06:32, 08:26 and 20:30 WIB among others. Biteship accepts an
    // instant booking round the clock. Same-day in the same period never booked
    // once outside 09:00-15:00. Adding 'instant' here would show customers a
    // false warning and, worse, hold their order until morning while a courier
    // was available immediately.
    'pickup_window_services' => ['same_day', 'sameday'],
];
