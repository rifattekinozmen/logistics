<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FuelPriceWeeklyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $filePath,
        public array $summary
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Haftalık Motorin Fiyat Raporu - '.now()->format('d.m.Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.fuel-price-weekly-report',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)
                ->as('motorin_fiyat_raporu_'.now()->format('Y_m_d').'.xlsx')
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}
