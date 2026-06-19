<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransferSetting extends Model
{
    protected $table = 'bank_transfer_settings';

    protected $fillable = [
        'language', 'recipient', 'iban', 'bic', 'bank_name', 'bank_address', 'email', 'is_active',
    ];
}
