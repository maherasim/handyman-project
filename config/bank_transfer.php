<?php

// Prefer calling getAdminMailFromAddress()-style helper getBankTransferDisplayConfig()
// directly wherever possible: config() values get frozen by `php artisan config:cache`
// (including whatever locale/DB state was active at cache-build time), so this file's
// live DB lookup can silently go stale in production. Kept here only for any caller
// still reading config('bank_transfer') in an environment where config isn't cached.
return function_exists('getBankTransferDisplayConfig')
    ? getBankTransferDisplayConfig()
    : [
        'recipient'    => env('BANK_TRANSFER_RECIPIENT',    'Frobster Marketplace'),
        'iban'         => env('BANK_TRANSFER_IBAN',         'DE02 1001 0178 1361 6331 79'),
        'bic'          => env('BANK_TRANSFER_BIC',          'REVODEB2'),
        'bank_name'    => env('BANK_TRANSFER_BANK_NAME',    'Revolut Bank UAB'),
        'bank_address' => env('BANK_TRANSFER_BANK_ADDRESS', ''),
        'sender_bic'   => env('BANK_TRANSFER_SENDER_BIC',   'CHASDEFX'),
        'email'        => env('BANK_TRANSFER_EMAIL',        'billing@frobster.com'),
    ];
