<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'logo' => [
        'endpoint' => env('LOGO_API_ENDPOINT'),
        'api_key' => env('LOGO_API_KEY'),
    ],

    'python' => [
        'endpoint' => env('PYTHON_BRIDGE_ENDPOINT', 'http://localhost:8001/api/process'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Geocoding (Adres -> Enlem/Boylam)
    |--------------------------------------------------------------------------
    | provider: nominatim (OpenStreetMap, ücretsiz) veya google (API key gerekir)
    | Nominatim: rate limit 1 istek/saniye; user_agent ve email önerilir
    */
    'geocoding' => [
        'provider' => env('GEOCODING_PROVIDER', 'nominatim'),
        'nominatim_url' => env('GEOCODING_NOMINATIM_URL', 'https://nominatim.openstreetmap.org/search'),
        'nominatim_reverse_url' => env('GEOCODING_NOMINATIM_REVERSE_URL', 'https://nominatim.openstreetmap.org/reverse'),
        'nominatim_email' => env('GEOCODING_NOMINATIM_EMAIL', ''),
        'google_api_key' => env('GOOGLE_MAPS_GEOCODING_API_KEY'),
        'user_agent' => env('GEOCODING_USER_AGENT', env('APP_NAME', 'Laravel').'/1.0'),
    ],

];
