<?php

namespace App\Mail;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    public Announcement $announcement;
    public string $recipientName;

    public function __construct(Announcement $announcement, string $recipientName)
    {
        $this->announcement = $announcement;
        $this->recipientName = $recipientName;
    }

    public function envelope(): Envelope
    {
        $prefix = match($this->announcement->priority) {
            'urgent' => '[عاجل] ',
            'important' => '[مهم] ',
            default => '',
        };

        return new Envelope(
            subject: $prefix . 'تعميم: ' . $this->announcement->title . ' - وزارة الصحة',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.announcement',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
