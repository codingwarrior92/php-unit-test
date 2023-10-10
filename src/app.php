<?php

require_once '../vendor/autoload.php';
use App\CommissionCalculator;

if ($argc !== 2) {
    echo "Usage: php app.php sample.txt\n";
    exit(1);
}
$currencyRatesUrl = 'https://v6.exchangerate-api.com/v6/169e94830a6aa1481e0fa2bf/latest/EUR';
$binLookupUrl = 'https://lookup.binlist.net/';
$filename = $argv[1];
$calculator = new CommissionCalculator($filename, $currencyRatesUrl, $binLookupUrl);
$calculator->calculateCommissionsFromFile();