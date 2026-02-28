<?php

namespace App\Notification\Channels;

use App\Notification\Contracts\NotificationChannel;
use App\Notification\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Log;

/**
 * SMS bildirim kanalı (Faz 3). Sağlayıcı container'dan çözülür (Twilio vb. değiştirilebilir).
 */
class SmsChannel implements NotificationChannel
{
    public function __construct(
        protected SmsProviderInterface $provider
    ) {}

    public function send(string $to, string $template, array $data = []): void
    {
        if (! config('notifications.sms.enabled', false)) {
            Log::debug('SMS channel disabled, skipping send.', ['to' => $to, 'template' => $template]);

            return;
        }

        $this->provider->send($to, $template, $data);
    }
}
