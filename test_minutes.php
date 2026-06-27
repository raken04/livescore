<?php
require __DIR__.'/vendor/autoload.php';
use Carbon\Carbon;

$matchDateTime = Carbon::now()->subMinutes(8); // Started 8 minutes ago
$diff = $matchDateTime->diffInMinutes(Carbon::now());
echo "Diff: " . $diff . " minutes\n";

$matchDateTime2 = Carbon::now()->subMinutes(55); // Started 55 minutes ago (should be 55 - 15 = 40 in game time)
$diff2 = $matchDateTime2->diffInMinutes(Carbon::now());
if ($diff2 > 45 && $diff2 < 60) {
    echo "Halftime (HT)\n";
} elseif ($diff2 >= 60) {
    echo "Diff2: " . ($diff2 - 15) . " minutes\n";
} else {
    echo "Diff2: " . $diff2 . " minutes\n";
}
