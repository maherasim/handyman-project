<?php

namespace App\Currency;

use App\Models\Setting;

use App\Models\Country;

class CurrencyChange
{
    public $CurrencyId;

    public $CurrencyPosition;

    public $defaultCurrency;

    public $afterdecimalpoint;

    public function __construct()
    {
        $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
        $sitesetupdata = $sitesetup ? json_decode($sitesetup->value, true) : null;

        // Always use EUR currency regardless of country settings
        $this->CurrencyId = null; // Not needed since we're forcing EUR
        $this->CurrencyPosition = $sitesetupdata['currency_position'] ?? null;
        $this->afterdecimalpoint = $sitesetupdata['digitafter_decimal_point'] ?? null;
        
        // Create EUR currency object
        $this->defaultCurrency = (object) [
            'id' => 1,
            'name' => 'Euro',
            'currency_code' => 'EUR',
            'symbol' => '€',
            'country_code' => 'EU'
        ];
    }

    public function getDefaultCurrency($array = false)
    {
        $data = [
            'defaultCurrency' => $this->defaultCurrency ?? null,
            'defaultPosition' => $this->CurrencyPosition ?? null,
            // 'defaultPosition' => $this->CurrencyPosition->value ?? null,
        ];

        if ($array) {
            return $data;
        }

        return response()->json($data);
    }
    public function defaultSymbol()
    {
        return $this->defaultCurrency->symbol ?? '';
    }

    public function defaultPosition()
    {
        return $this->CurrencyPosition ?? '';
    }

    public function format($amount)
    {
        $noOfDecimal = $this->afterdecimalpoint;
        $currencyPosition =   $this->CurrencyPosition;

        $currencySymbol = $this->defaultCurrency->symbol;

        return formatCurrency($amount, $noOfDecimal, $currencyPosition, $currencySymbol);
    }
}
