<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialUrgentMail extends Mailable implements ShouldQueue
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
            subject: 'Seu Básico grátis termina em 5 dias ⏰',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.trial.day-40-urgent',
            with: [
                'tenant' => $this->tenant,
            ],
        );
    }
}
