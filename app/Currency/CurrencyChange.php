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

        // Resolve currency dynamically from Site Setup -> default_currency (Country)
        $this->CurrencyId = $sitesetupdata['default_currency'] ?? null;
        $this->CurrencyPosition = $sitesetupdata['currency_position'] ?? 'left';
        $this->afterdecimalpoint = $sitesetupdata['digitafter_decimal_point'] ?? 2;

        $country = $this->CurrencyId ? Country::find($this->CurrencyId) : null;
        $code = $country->currency_code ?? 'EUR';
        $symbol = $country->symbol ?? '€';
        $name = $country->name ?? 'Euro';
        $countryCode = $country->short_code ?? 'EU';

        $this->defaultCurrency = (object) [
            'id' => (int)($this->CurrencyId ?? 0),
            'name' => $name,
            'currency_code' => strtoupper($code),
            'symbol' => $symbol,
            'country_code' => strtoupper($countryCode),
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
