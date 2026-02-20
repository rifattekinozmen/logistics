<?php

return [
    /*
    |--------------------------------------------------------------------------
    | E-Invoice Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for Turkish E-Invoice (GIB) integration.
    |
    */

    'enabled' => env('EINVOICE_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | GIB Connection
    |--------------------------------------------------------------------------
    |
    | GIB (Gelir İdaresi Başkanlığı) web service endpoints.
    |
    */

    'gib_url' => env('EINVOICE_GIB_URL', env('APP_ENV') === 'production'
        ? 'https://efaturatest.gbonline.com.tr/services'
        : 'https://efaturatest.gbonline.com.tr/services'),

    'gib_username' => env('EINVOICE_GIB_USERNAME'),

    'gib_password' => env('EINVOICE_GIB_PASSWORD'),

    'timeout' => env('EINVOICE_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Company Certificates
    |--------------------------------------------------------------------------
    |
    | E-Invoice requires company digital certificates.
    |
    */

    'certificates' => [
        'path' => env('EINVOICE_CERT_PATH', storage_path('certificates')),
        'password' => env('EINVOICE_CERT_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Profiles
    |--------------------------------------------------------------------------
    |
    | UBL-TR invoice profiles.
    |
    */

    'profiles' => [
        'commercial' => 'TICARIFATURA',   // Commercial invoice
        'basic' => 'TEMELFATURA',         // Basic invoice
        'export' => 'IHRACAT',            // Export invoice
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Settings
    |--------------------------------------------------------------------------
    |
    | Automatic e-invoice generation settings.
    |
    */

    'auto_generate' => env('EINVOICE_AUTO_GENERATE', false),

    'auto_send' => env('EINVOICE_AUTO_SEND', false),

    /*
    |--------------------------------------------------------------------------
    | E-Arşiv Settings
    |--------------------------------------------------------------------------
    |
    | E-Archive invoice settings for retail customers.
    |
    */

    'e_archive' => [
        'enabled' => env('EINVOICE_E_ARCHIVE_ENABLED', false),
        'threshold_amount' => env('EINVOICE_E_ARCHIVE_THRESHOLD', 5000),
    ],
];
