<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bildirim kanalları (Faz 3)
    |--------------------------------------------------------------------------
    | Senaryo bazında hangi kanalların kullanılacağı.
    */
    'channels' => [
        'document_expiry' => ['mail', 'sms'],
        'payment_due' => ['mail', 'sms', 'whatsapp'],
        'ai_critical' => ['mail', 'sms'],
        'fleet_maintenance' => ['mail'],
    ],

    'sms' => [
        'enabled' => env('SMS_ENABLED', false),
        'provider' => env('SMS_PROVIDER', 'twilio'),
        'from' => env('SMS_FROM'),
        'admin_phone' => env('NOTIFICATION_ADMIN_PHONE'),
    ],

    'whatsapp' => [
        'enabled' => env('WHATSAPP_ENABLED', false),
        'provider' => env('WHATSAPP_PROVIDER', 'twilio'),
        'from' => env('WHATSAPP_FROM'),
        'admin_phone' => env('NOTIFICATION_WHATSAPP_PHONE', env('NOTIFICATION_ADMIN_PHONE')),
    ],

];
