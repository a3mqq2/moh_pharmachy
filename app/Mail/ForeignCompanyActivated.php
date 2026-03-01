<?php

namespace App\Mail;

use App\Models\ForeignCompany;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ForeignCompanyActivated extends Mailable
{
    use Queueable, SerializesModels;

    public ForeignCompany $company;

    /**
     * Create a new message instance.
     */
    public function __construct(ForeignCompany $company)
    {
        $this->company = $company;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'تم تفعيل الشركة الأجنبية - وزارة الصحة',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.foreign-company-activated',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
