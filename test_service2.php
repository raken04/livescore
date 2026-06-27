<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

$date = '2026-06-21';
$timezone = 'Asia/Jakarta';

$response = Http::withoutVerifying()->get("https://worldcup26.ir/get/games");
$data = $response->json();
$games = $data['games'] ?? [];
$matches = [];

foreach ($games as $game) {
    try {
        $matchDateTime = Carbon::createFromFormat('m/d/Y H:i', $game['local_date'], '-05:00');
        $matchDateTime->setTimezone($timezone);
        $gameDate = $matchDateTime->format('Y-m-d');
    } catch (\Exception $e) {
        $gameDate = null;
    }
    
    if ($gameDate === $date) {
        $matches[] = $game['home_team_name_en'] . ' vs ' . $game['away_team_name_en'];
    }
}
echo "Found " . count($matches) . " matches.\n";
print_r($matches);
