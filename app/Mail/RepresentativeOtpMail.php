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
            'registration' => 'رمز التحقق لتسجيل حسابك - وزارة الصحة',
            'login' => 'رمز التحقق لتسجيل الدخول - وزارة الصحة',
            'password_reset' => 'رمز التحقق لإعادة تعيين كلمة المرور - وزارة الصحة',
            default => 'رمز التحقق - وزارة الصحة'
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.representative-otp');
    }
}
