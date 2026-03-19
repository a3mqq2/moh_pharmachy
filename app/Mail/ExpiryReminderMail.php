<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpiryReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $expiringItems;
    public string $recipientName;

    public function __construct(array $expiringItems, string $recipientName)
    {
        $this->expiringItems = $expiringItems;
        $this->recipientName = $recipientName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'تنبيه بقرب انتهاء الصلاحية - وزارة الصحة',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.expiry-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
