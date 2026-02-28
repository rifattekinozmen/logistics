<?php

namespace App\Notification\Contracts;

/**
 * WhatsApp sağlayıcı arayüzü (Twilio, WhatsApp Business API vb. için).
 */
interface WhatsappProviderInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function send(string $to, string $template, array $data = []): void;
}
