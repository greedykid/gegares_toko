<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$biteship = app(\App\Services\BiteshipService::class);
$orderId = '6a3725092152e9761d5e19af';

$result = $biteship->getOrder($orderId);

echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
