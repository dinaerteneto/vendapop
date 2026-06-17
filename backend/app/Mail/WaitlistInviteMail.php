<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitlistInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $inviteCode,
        public string $inviteLink,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Você foi convidado para o PopVenda!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.waitlist-invite',
            with: [
                'inviteCode' => $this->inviteCode,
                'inviteLink' => $this->inviteLink,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
