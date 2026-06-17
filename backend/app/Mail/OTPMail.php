<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OTPMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $email;
    public string $otpCode;
    public string $magicLinkUrl;

    public function __construct(string $email, string $otpCode, string $magicLinkUrl)
    {
        $this->email = $email;
        $this->otpCode = $otpCode;
        $this->magicLinkUrl = $magicLinkUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Seu código de acesso - PopVenda',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.otp',
            with: [
                'email' => $this->email,
                'otpCode' => $this->otpCode,
                'magicLinkUrl' => $this->magicLinkUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
