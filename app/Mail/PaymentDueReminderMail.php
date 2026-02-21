<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class PaymentDueReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Collection $payments,
        public int $daysUntil,
        public bool $isOverdue = false
    ) {}

    public function envelope(): Envelope
    {
        $subject = match (true) {
            $this->isOverdue => 'ACİL: Gecikmiş Ödemeler',
            $this->daysUntil === 0 => 'ACİL: Bugün Vadesi Gelen Ödemeler',
            $this->daysUntil === 1 => 'DİKKAT: Yarın Vadesi Gelecek Ödemeler',
            default => "Hatırlatma: {$this->daysUntil} Gün Sonra Vadesi Gelecek Ödemeler",
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-due-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
