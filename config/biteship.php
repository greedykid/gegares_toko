<?php

return [
    'api_key' => env('BITESHIP_API_KEY'),
    'base_url' => 'https://api.biteship.com/v1',
    'origin_area_id' => env('BITESHIP_ORIGIN_AREA_ID', 'IDNP6IDNC147IDND829'),
    'timeout' => 30,
];
