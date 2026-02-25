<?php

return [
    'default_provider' => env('PAYMENT_PROVIDER', 'generic'),

    'providers' => [
        'generic' => [
            'secret' => env('PAYMENT_GENERIC_SECRET'),
        ],
    ],
];
