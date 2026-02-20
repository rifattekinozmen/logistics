<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LOGO ERP Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for LOGO ERP system integration.
    |
    */

    'enabled' => env('LOGO_INTEGRATION_ENABLED', false),

    'api_url' => env('LOGO_API_URL', 'https://logo-api.example.com/api'),

    'api_token' => env('LOGO_API_TOKEN'),

    'timeout' => env('LOGO_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Sync Settings
    |--------------------------------------------------------------------------
    |
    | Control automatic synchronization behavior.
    |
    */

    'auto_sync' => [
        'customers' => env('LOGO_AUTO_SYNC_CUSTOMERS', false),
        'invoices' => env('LOGO_AUTO_SYNC_INVOICES', false),
        'orders' => env('LOGO_AUTO_SYNC_ORDERS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Company Mappings
    |--------------------------------------------------------------------------
    |
    | Map local company IDs to LOGO firm codes.
    |
    */

    'company_mappings' => [
        // 1 => 'FIRM001',
        // 2 => 'FIRM002',
    ],
];
