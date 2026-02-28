<?php

namespace App\Notification\Channels;

use App\Notification\Contracts\NotificationChannel;
use App\Notification\Contracts\WhatsappProviderInterface;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp bildirim kanalı (Faz 3). Sağlayıcı container'dan çözülür (Twilio vb. değiştirilebilir).
 */
class WhatsappChannel implements NotificationChannel
{
    public function __construct(
        protected WhatsappProviderInterface $provider
    ) {}

    public function send(string $to, string $template, array $data = []): void
    {
        if (! config('notifications.whatsapp.enabled', false)) {
            Log::debug('WhatsApp channel disabled, skipping send.', ['to' => $to, 'template' => $template]);

            return;
        }

        $this->provider->send($to, $template, $data);
    }
}
