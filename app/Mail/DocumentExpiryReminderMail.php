<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DocumentExpiryReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Collection $documents,
        public int $daysUntil
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->daysUntil) {
            0 => 'ACİL: Bugün Süresi Dolan Belgeler',
            1 => 'DİKKAT: Yarın Süresi Dolacak Belgeler',
            default => "Hatırlatma: {$this->daysUntil} Gün Sonra Süresi Dolacak Belgeler",
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.document-expiry-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
