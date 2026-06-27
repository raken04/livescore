<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiService = app(\App\Services\ApiFootballService::class);
$date = '2026-06-20';
$timezone = 'Asia/Jakarta';

$matches = $apiService->getFixturesByDate($date, $timezone);
echo "Matches count: " . count($matches) . "\n";
if (count($matches) > 0) {
    echo "First match league ID: " . $matches[0]['league']['id'] . "\n";
}
