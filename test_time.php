<?php
require __DIR__.'/vendor/autoload.php';

use Carbon\Carbon;

$games = [
    ["local_date" => "06/20/2026 12:00", "desc" => "Netherlands vs Sweden"],
    ["local_date" => "06/20/2026 22:00", "desc" => "Tunisia vs Japan"],
];

$timezone = 'Asia/Jakarta';

foreach($games as $game) {
    try {
        $matchDateTime = Carbon::createFromFormat('m/d/Y H:i', $game['local_date'], '-05:00');
        $matchDateTime->setTimezone($timezone);
        $gameDate = $matchDateTime->format('Y-m-d');
        echo "Match: {$game['desc']}\n";
        echo "Local Date: {$game['local_date']}\n";
        echo "Converted to Jakarta: " . $matchDateTime->toDateTimeString() . "\n";
        echo "Resulting Y-m-d: {$gameDate}\n\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
