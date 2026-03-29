<?php

namespace App\Mail;

use App\Models\LocalCompany;
use App\Models\LocalCompanyInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReceiptRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public LocalCompany $company;
    public LocalCompanyInvoice $invoice;
    public string $rejectionReason;

    /**
     * Create a new message instance.
     */
    public function __construct(LocalCompany $company, LocalCompanyInvoice $invoice, string $rejectionReason)
    {
        $this->company = $company;
        $this->invoice = $invoice;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.receipt_rejected_subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.receipt-rejected',
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
