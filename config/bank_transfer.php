<?php

return [
    'recipient'    => env('BANK_TRANSFER_RECIPIENT', 'Ben Ghezaiel'),
    'iban'         => env('BANK_TRANSFER_IBAN', 'DE02 1001 0178 1361 6331 79'),
    'bic'          => env('BANK_TRANSFER_BIC', 'REVODEB2'),
    'bank_name'    => env('BANK_TRANSFER_BANK_NAME', 'Revolut Bank UAB'),
    'bank_address' => env('BANK_TRANSFER_BANK_ADDRESS', "Zweigniederlassung Deutschland\nFORA Linden Palais, Unter den\nLinden 40\n10117, Berlin, Germany"),
    'sender_bic'   => env('BANK_TRANSFER_SENDER_BIC', 'CHASDEFX'),
    'email'        => env('BANK_TRANSFER_EMAIL', 'billing@frobster.com'),
];
