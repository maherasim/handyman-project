<?php

// Read from bank_transfer_settings DB table (managed via admin panel).
// Falls back to ENV values if table doesn't exist or has no record yet.
try {
    $locale   = app()->getLocale();
    $setting  = \App\Models\BankTransferSetting::where('language', $locale)->where('is_active', 1)->first()
             ?? \App\Models\BankTransferSetting::where('language', 'en')->where('is_active', 1)->first();
} catch (\Throwable $e) {
    $setting = null;
}

return [
    'recipient'    => $setting->recipient    ?? env('BANK_TRANSFER_RECIPIENT',     'Frobster Marketplace'),
    'iban'         => $setting->iban         ?? env('BANK_TRANSFER_IBAN',          'DE02 1001 0178 1361 6331 79'),
    'bic'          => $setting->bic          ?? env('BANK_TRANSFER_BIC',           'REVODEB2'),
    'bank_name'    => $setting->bank_name    ?? env('BANK_TRANSFER_BANK_NAME',     'Revolut Bank UAB'),
    'bank_address' => $setting->bank_address ?? env('BANK_TRANSFER_BANK_ADDRESS',  ''),
    'sender_bic'   => env('BANK_TRANSFER_SENDER_BIC', 'CHASDEFX'),
    'email'        => $setting->email        ?? env('BANK_TRANSFER_EMAIL',         'billing@frobster.com'),
];
