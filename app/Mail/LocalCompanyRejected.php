<?php

namespace App\Mail;

use App\Models\LocalCompany;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LocalCompanyRejected extends Mailable
{
    use SerializesModels;

    public function __construct(
        public LocalCompany $company
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'بخصوص طلب تسجيل شركتكم - وزارة الصحة',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.local-company-rejected',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
