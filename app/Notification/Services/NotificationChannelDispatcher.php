<?php

namespace App\Notification\Services;

use App\Notification\Channels\SmsChannel;
use App\Notification\Channels\WhatsappChannel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Senaryo bazında config'teki kanallara (sms, whatsapp) gönderim yapar (Faz 3).
 * Aynı alıcı+şablon için günde en fazla 1 kez gönderim (spam önleme).
 */
class NotificationChannelDispatcher
{
    private const RATE_LIMIT_TTL_SECONDS = 86400; // 24 saat

    public function __construct(
        protected SmsChannel $smsChannel,
        protected WhatsappChannel $whatsappChannel
    ) {}

    /**
     * Belirtilen senaryo için config'te tanımlı sms/whatsapp kanallarını tetikler.
     * Aynı (kanal, alıcı, şablon) için günde 1 kez gönderilir.
     *
     * @param  array<string, mixed>  $data  Şablon verisi (days, count, summary vb.)
     */
    public function sendForScenario(string $scenario, string $template, array $data = []): void
    {
        $channels = config("notifications.channels.{$scenario}", []);

        if (in_array('sms', $channels, true)) {
            $to = config('notifications.sms.admin_phone');
            if ($to && $this->shouldSend('sms', $to, $template)) {
                try {
                    $this->smsChannel->send($to, $template, $data);
                    $this->markSent('sms', $to, $template);
                } catch (\Throwable $e) {
                    Log::warning('SMS send failed for scenario.', [
                        'scenario' => $scenario,
                        'template' => $template,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        if (in_array('whatsapp', $channels, true)) {
            $to = config('notifications.whatsapp.admin_phone');
            if ($to && $this->shouldSend('whatsapp', $to, $template)) {
                try {
                    $this->whatsappChannel->send($to, $template, $data);
                    $this->markSent('whatsapp', $to, $template);
                } catch (\Throwable $e) {
                    Log::warning('WhatsApp send failed for scenario.', [
                        'scenario' => $scenario,
                        'template' => $template,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Aynı (kanal, alıcı, şablon) bugün zaten gönderildiyse false.
     */
    protected function shouldSend(string $channel, string $to, string $template): bool
    {
        $key = $this->rateLimitKey($channel, $to, $template);

        return ! Cache::has($key);
    }

    /**
     * Gönderimi kaydet; 24 saat tekrar gönderme.
     */
    protected function markSent(string $channel, string $to, string $template): void
    {
        $key = $this->rateLimitKey($channel, $to, $template);
        Cache::put($key, true, self::RATE_LIMIT_TTL_SECONDS);
    }

    protected function rateLimitKey(string $channel, string $to, string $template): string
    {
        $normalized = preg_replace('/\D/', '', $to) ?: $to;

        return sprintf('notification_sent:%s:%s:%s:%s', $channel, $normalized, $template, now()->toDateString());
    }
}
