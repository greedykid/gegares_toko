<?php

return [
    'api_key' => env('BITESHIP_API_KEY'),
    // Shared secret to authenticate incoming Biteship webhooks. When set, requests
    // must present it via the `X-Webhook-Token` header or a `token` query param.
    'webhook_token' => env('BITESHIP_WEBHOOK_TOKEN'),
    'base_url' => 'https://api.biteship.com/v1',
    'origin_area_id' => env('BITESHIP_ORIGIN_AREA_ID', 'IDNP6IDNC147IDND829'),
    'timeout' => 30,
];
