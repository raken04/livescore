<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiService = app(\App\Services\ApiFootballService::class);
$date = '2026-06-20';
$timezone = 'Asia/Jakarta';

// Simulate what getFixturesByDate does inside
$todayDate = \Carbon\Carbon::now($timezone)->format('Y-m-d');
$isToday = $date === $todayDate;
echo "Date: $date\nTodayDate: $todayDate\nIsToday: ".($isToday ? 'true' : 'false')."\n";
