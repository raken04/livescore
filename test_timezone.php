<?php
$stadiums = [
    ["id" => "1", "region" => "Central", "country_en" => "Mexico"],
    ["id" => "4", "region" => "Central", "country_en" => "United States"],
    ["id" => "7", "region" => "Eastern", "country_en" => "United States"],
    ["id" => "13", "region" => "Western", "country_en" => "Canada"],
];

foreach ($stadiums as $s) {
    $offset = '-05:00'; // default fallback
    if ($s['region'] === 'Western') {
        $offset = '-07:00';
    } elseif ($s['region'] === 'Eastern') {
        $offset = '-04:00';
    } elseif ($s['region'] === 'Central') {
        if ($s['country_en'] === 'Mexico') {
            $offset = '-06:00';
        } else {
            $offset = '-05:00';
        }
    }
    echo "Stadium {$s['id']} -> {$offset}\n";
}
