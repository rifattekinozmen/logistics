<?php

namespace App\Notification\Providers;

use App\Notification\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Log;

/**
 * SMS için log-only sağlayıcı (gerçek gönderim yapmaz; Twilio vb. eklenene kadar).
 */
class LogOnlySmsProvider implements SmsProviderInterface
{
    public function send(string $to, string $template, array $data = []): void
    {
        Log::info('SMS would be sent.', [
            'to' => $to,
            'template' => $template,
            'data' => $data,
        ]);
    }
}
