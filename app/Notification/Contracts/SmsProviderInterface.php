<?php

namespace App\Notification\Contracts;

/**
 * SMS sağlayıcı arayüzü (Twilio, Netgsm vb. entegrasyonu için).
 */
interface SmsProviderInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function send(string $to, string $template, array $data = []): void;
}
