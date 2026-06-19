<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialCaseStudyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $caseStore;

    public function __construct(
        public Tenant $tenant
    ) {
        $this->caseStore = 'Loja do João';
    }

    public function withTenant(Tenant $tenant): static
    {
        $this->tenant = $tenant;

        return $this;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Como a ' . $this->caseStore . ' recebeu 8 pedidos na primeira semana',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.trial.day-07-case-study',
            with: [
                'tenant' => $this->tenant,
                'caseStore' => $this->caseStore,
            ],
        );
    }
}
