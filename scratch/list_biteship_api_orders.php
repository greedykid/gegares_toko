<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = config('biteship.api_key');
$baseUrl = config('biteship.base_url');

$response = \Illuminate\Support\Facades\Http::withToken($apiKey)
    ->get("{$baseUrl}/orders", [
        'page' => 1,
        'limit' => 20
    ]);

echo "Status: " . $response->status() . "\n";
echo "Response: " . $response->body() . "\n";
