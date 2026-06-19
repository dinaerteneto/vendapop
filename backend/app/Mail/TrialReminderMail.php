<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialReminderMail extends Mailable implements ShouldQueue
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
            subject: 'Faltam 15 dias de Básico grátis — aproveite',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.trial.day-30-reminder',
            with: [
                'tenant' => $this->tenant,
            ],
        );
    }
}
