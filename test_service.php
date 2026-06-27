<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ApiFootballService;

$service = app(ApiFootballService::class);
$fixtures = $service->getFixturesByDate('2026-06-21', 'Asia/Jakarta');

echo "Total fixtures for 2026-06-21: " . count($fixtures) . "\n";
print_r($fixtures);
