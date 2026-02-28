<?php

namespace App\Notification\Providers;

use App\Notification\Contracts\WhatsappProviderInterface;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp için log-only sağlayıcı (gerçek gönderim yapmaz; Twilio vb. eklenene kadar).
 */
class LogOnlyWhatsappProvider implements WhatsappProviderInterface
{
    public function send(string $to, string $template, array $data = []): void
    {
        Log::info('WhatsApp would be sent.', [
            'to' => $to,
            'template' => $template,
            'data' => $data,
        ]);
    }
}
