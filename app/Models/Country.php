<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    protected $table = "countries";
    protected $primaryKey = "id";

    protected $casts = [
        'dial_code' => 'integer',
    ];

    /**
     * GeoDB / RapidAPI-style columns: currency, currency_symbol, iso2.
     * Legacy app columns: currency_code, symbol, short_code / code.
     */
    protected function symbol(): Attribute
    {
        return Attribute::get(function (): ?string {
            return $this->attributes['currency_symbol'] ?? $this->attributes['symbol'] ?? null;
        });
    }

    protected function currencyCode(): Attribute
    {
        return Attribute::get(function (): ?string {
            return $this->attributes['currency'] ?? $this->attributes['currency_code'] ?? null;
        });
    }

    protected function shortCode(): Attribute
    {
        return Attribute::get(function (): ?string {
            return $this->attributes['iso2'] ?? $this->attributes['short_code'] ?? $this->attributes['code'] ?? null;
        });
    }
    
    public function states()
    {
        return $this->hasMany(State::class, 'country_id','id');
    }
    public function taxes()
{
    return $this->hasMany(Tax::class);
}
}
