<?php

return [
    'project_slug' => env('PAKASIR_PROJECT_SLUG', 'gegares'),
    'api_key' => env('PAKASIR_API_KEY'),

    /*
     | Timezone that Pakasir's `completed_at` timestamps are expressed in.
     |
     | The value arrives without an offset, so it has to be interpreted against
     | a known zone. Parsing it against the app timezone (Asia/Jakarta) treated a
     | UTC timestamp as local and reported payments 7 hours early in the
     | "Pembayaran Berhasil" email. Timestamps that DO carry an offset are
     | unaffected — Carbon honours the embedded zone and ignores this setting.
     */
    'timezone' => env('PAKASIR_TIMEZONE', 'UTC'),
];
