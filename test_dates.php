<?php
require __DIR__.'/vendor/autoload.php';
$tz = 'Asia/Jakarta';
$today = \Carbon\Carbon::now($tz)->format('Y-m-d');
$yesterday = \Carbon\Carbon::now($tz)->subDay()->format('Y-m-d');
$tomorrow = \Carbon\Carbon::now($tz)->addDay()->format('Y-m-d');
echo "Today: $today\nYesterday: $yesterday\nTomorrow: $tomorrow\n";
