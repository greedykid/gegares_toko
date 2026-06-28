<?php
// deploy.php

// Token rahasia untuk validasi keamanan
$secret = 'GegaresAutoDeployToken2026'; 

// Validasi request dari GitHub
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';
if (!$signature) {
    header('HTTP/1.1 403 Forbidden');
    die('Akses Ditolak');
}

list($algo, $hash) = explode('=', $signature, 2);
$payload = file_get_contents('php://input');
$payloadHash = hash_hmac($algo, $payload, $secret);

if (!hash_equals($payloadHash, $hash)) {
    header('HTTP/1.1 403 Forbidden');
    die('Signature tidak valid');
}

// Jalankan git pull (menggunakan Deploy Key yang sudah ada di cPanel)
echo "Memulai otomatisasi deploy...\n";
$output = shell_exec('cd /home/gegaress/gegares && git pull 2>&1');
echo $output;

// Jalankan migrasi dan bersihkan cache
$migrate = shell_exec('cd /home/gegaress/gegares && php artisan migrate --force 2>&1');
echo $migrate;

$cache = shell_exec('cd /home/gegaress/gegares && php artisan config:cache 2>&1');
echo $cache;
