<?php

namespace App\Notification\Contracts;

/**
 * SMS / WhatsApp gibi kanallar için soyut arayüz (Faz 3).
 * Sağlayıcıya (Twilio vb.) sıkı bağlılığı önlemek için kullanılır.
 */
interface NotificationChannel
{
    /**
     * Şablon tabanlı mesaj gönder.
     *
     * @param  string  $to  Alıcı (telefon numarası, WhatsApp ID vb.)
     * @param  string  $template  Mesaj şablon kimliği (örn. 'payment_due', 'document_expiry')
     * @param  array<string, mixed>  $data  Şablonda kullanılacak değişkenler
     */
    public function send(string $to, string $template, array $data = []): void;
}
