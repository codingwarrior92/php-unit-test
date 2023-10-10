<?php
namespace App;

class CommissionCalculator
{
    private $currencyRatesUrl;
    private $binLookupUrl;
    private $filename;
    // https://v6.exchangerate-api.com/v6/169e94830a6aa1481e0fa2bf/latest/EUR
    // https://api.exchangeratesapi.io/latest
    public function __construct($filename='sample.txt', $currencyRatesUrl = 'https://v6.exchangerate-api.com/v6/169e94830a6aa1481e0fa2bf/latest/EUR', $binLookupUrl = 'https://lookup.binlist.net/')
    {
        $this->filename = $filename;
        $this->currencyRatesUrl = $currencyRatesUrl;
        $this->binLookupUrl = $binLookupUrl;
    }

    public function calculateCommissionsFromFile()
    {
        $lines = file($this->filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $transaction = json_decode($line, true);

            if (!$transaction) {
                continue;
            }

            $bin = $transaction['bin'];
            $amount = $transaction['amount'];
            $currency = $transaction['currency'];

            $countryCode = $this->getCountryCode($bin);
            $isEu = $this->isEuCountry($countryCode);
            $exchangeRate = $this->getExchangeRate($currency);

            $commission = $this->calculateCommission($amount, $isEu, $exchangeRate);

            echo number_format(ceil($commission * 100) / 100, 2) . "\n";
        }
    }

    public function getCountryCode($bin)
    {
        $binResults = @file_get_contents($this->binLookupUrl . $bin);
        if (!$binResults) {
            throw new \Exception('Error fetching BIN information.');
        }
        $data = json_decode($binResults);

        return $data->country->alpha2;
    }

    private function isEuCountry($countryCode)
    {
        $euCountries = [
            'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI',
            'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT',
            'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK',
        ];

        return in_array($countryCode, $euCountries);
    }

    public function getExchangeRate($currency)
    {
        $exchangeRates = @json_decode(file_get_contents($this->currencyRatesUrl), true);
        // var_dump($exchangeRates);
        if (!$exchangeRates || !isset($exchangeRates['conversion_rates'][$currency]) || $exchangeRates['conversion_rates'][$currency] == 0) {
            return 1; // Default to 1 if there is an error or rate is 0
        }

        return $exchangeRates['conversion_rates'][$currency];
    }

    public function calculateCommission($amount, $isEu, $exchangeRate)
    {
        $commissionRate = $isEu ? 0.01 : 0.02;
        $amntFixed = ($exchangeRate == 1 || $exchangeRate == 0) ? $amount : $amount / $exchangeRate;

        return $amntFixed * $commissionRate;
    }
}
