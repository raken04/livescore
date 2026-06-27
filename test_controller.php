<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/livescore', 'GET', ['date' => '2026-06-21'])
);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content contains 'Netherlands vs Sweden': " . (strpos($response->getContent(), 'Netherlands') !== false ? 'Yes' : 'No') . "\n";
