<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RepresentativeOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $name,
        public string $type = 'registration'
    ) {}

    public function envelope(): Envelope
    {
        $subject = match($this->type) {
            'registration' => __('emails.otp_subject_registration'),
            'login' => __('emails.otp_subject_login'),
            'password_reset' => __('emails.otp_subject_password_reset'),
            default => __('emails.otp_subject_default'),
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.representative-otp');
    }
}
