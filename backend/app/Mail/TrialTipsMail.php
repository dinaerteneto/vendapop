<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialTipsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant
    ) {}

    public function withTenant(Tenant $tenant): static
    {
        $this->tenant = $tenant;

        return $this;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '3 dicas pra divulgar sua loja no Instagram',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.trial.day-15-tips',
            with: [
                'tenant' => $this->tenant,
            ],
        );
    }
}
