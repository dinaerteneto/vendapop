<?php

namespace Tests\Feature\Emails;

use App\Mail\TrialCaseStudyMail;
use App\Mail\TrialEndedMail;
use App\Mail\TrialReminderMail;
use App\Mail\TrialTipsMail;
use App\Mail\TrialUrgentMail;
use App\Mail\TrialWelcomeMail;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TrialEmailMailingTest extends TestCase
{
    use RefreshDatabase;

    private function createTenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Loja Teste',
            'slug' => 'loja-teste-' . uniqid(),
            'whatsapp_number' => '5511999999999',
            'email_contact' => 'loja@teste.com',
        ]);
    }

    public function test_trial_welcome_mail_envelope_subject(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialWelcomeMail($tenant);

        $envelope = $mail->envelope();

        $this->assertStringContainsString('Sua loja', $envelope->subject);
        $this->assertStringContainsString($tenant->name, $envelope->subject);
        $this->assertStringContainsString('🚀', $envelope->subject);
    }

    public function test_trial_welcome_mail_content_template(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialWelcomeMail($tenant);

        $content = $mail->content();

        $this->assertEquals('emails.trial.day-00-welcome', $content->markdown);
    }

    public function test_trial_welcome_mail_renders_with_tenant_name(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialWelcomeMail($tenant);

        $rendered = $mail->render();

        $this->assertStringContainsString($tenant->name, $rendered);
        $this->assertStringContainsString('utm_campaign=day_00', $rendered);
    }

    public function test_trial_welcome_mail_with_tenant_fluent(): void
    {
        $tenant1 = $this->createTenant();
        $tenant2 = $this->createTenant();
        $mail = new TrialWelcomeMail($tenant1);

        $result = $mail->withTenant($tenant2);

        $this->assertSame($mail, $result);
        $this->assertSame($tenant2, $mail->tenant);
    }

    public function test_trial_case_study_mail_envelope_subject(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialCaseStudyMail($tenant);

        $envelope = $mail->envelope();

        $this->assertStringContainsString('recebeu 8 pedidos', $envelope->subject);
        $this->assertStringContainsString($mail->caseStore, $envelope->subject);
    }

    public function test_trial_case_study_mail_content_template(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialCaseStudyMail($tenant);

        $content = $mail->content();

        $this->assertEquals('emails.trial.day-07-case-study', $content->markdown);
    }

    public function test_trial_case_study_mail_renders_with_case_store(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialCaseStudyMail($tenant);

        $rendered = $mail->render();

        $this->assertStringContainsString($mail->caseStore, $rendered);
        $this->assertStringContainsString($tenant->name, $rendered);
        $this->assertStringContainsString('utm_campaign=day_07', $rendered);
    }

    public function test_trial_tips_mail_envelope_subject(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialTipsMail($tenant);

        $envelope = $mail->envelope();

        $this->assertEquals('3 dicas pra divulgar sua loja no Instagram', $envelope->subject);
    }

    public function test_trial_tips_mail_content_template(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialTipsMail($tenant);

        $content = $mail->content();

        $this->assertEquals('emails.trial.day-15-tips', $content->markdown);
    }

    public function test_trial_tips_mail_renders_with_tenant_name(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialTipsMail($tenant);

        $rendered = $mail->render();

        $this->assertStringContainsString($tenant->name, $rendered);
        $this->assertStringContainsString('utm_campaign=day_15', $rendered);
    }

    public function test_trial_reminder_mail_envelope_subject(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialReminderMail($tenant);

        $envelope = $mail->envelope();

        $this->assertStringContainsString('Faltam 15 dias', $envelope->subject);
        $this->assertStringContainsString('Básico grátis', $envelope->subject);
    }

    public function test_trial_reminder_mail_content_template(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialReminderMail($tenant);

        $content = $mail->content();

        $this->assertEquals('emails.trial.day-30-reminder', $content->markdown);
    }

    public function test_trial_reminder_mail_renders_with_cta_utm(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialReminderMail($tenant);

        $rendered = $mail->render();

        $this->assertStringContainsString($tenant->name, $rendered);
        $this->assertStringContainsString('utm_campaign=day_30', $rendered);
        $this->assertStringContainsString('/admin/assinatura', $rendered);
    }

    public function test_trial_urgent_mail_envelope_subject(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialUrgentMail($tenant);

        $envelope = $mail->envelope();

        $this->assertStringContainsString('termina em 5 dias', $envelope->subject);
        $this->assertStringContainsString('⏰', $envelope->subject);
    }

    public function test_trial_urgent_mail_content_template(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialUrgentMail($tenant);

        $content = $mail->content();

        $this->assertEquals('emails.trial.day-40-urgent', $content->markdown);
    }

    public function test_trial_urgent_mail_renders_with_cta_utm(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialUrgentMail($tenant);

        $rendered = $mail->render();

        $this->assertStringContainsString($tenant->name, $rendered);
        $this->assertStringContainsString('utm_campaign=day_40', $rendered);
        $this->assertStringContainsString('Garantir Meu Plano', $rendered);
    }

    public function test_trial_ended_mail_envelope_subject(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialEndedMail($tenant);

        $envelope = $mail->envelope();

        $this->assertStringContainsString('plano Grátis', $envelope->subject);
        $this->assertStringContainsString('Continue vendendo', $envelope->subject);
    }

    public function test_trial_ended_mail_content_template(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialEndedMail($tenant);

        $content = $mail->content();

        $this->assertEquals('emails.trial.day-45-ended', $content->markdown);
    }

    public function test_trial_ended_mail_renders_with_tenant_name(): void
    {
        $tenant = $this->createTenant();
        $mail = new TrialEndedMail($tenant);

        $rendered = $mail->render();

        $this->assertStringContainsString($tenant->name, $rendered);
        $this->assertStringContainsString('utm_campaign=day_45', $rendered);
        $this->assertStringContainsString('plano Grátis', $rendered);
    }

    public function test_all_mailables_render_without_errors(): void
    {
        $tenant = $this->createTenant();

        $mailables = [
            new TrialWelcomeMail($tenant),
            new TrialCaseStudyMail($tenant),
            new TrialTipsMail($tenant),
            new TrialReminderMail($tenant),
            new TrialUrgentMail($tenant),
            new TrialEndedMail($tenant),
        ];

        foreach ($mailables as $mailable) {
            $rendered = $mailable->render();
            $this->assertStringContainsString($tenant->name, $rendered);
        }
    }

    public function test_all_templates_contain_unsubscribe_link(): void
    {
        $tenant = $this->createTenant();

        $mailables = [
            new TrialWelcomeMail($tenant),
            new TrialCaseStudyMail($tenant),
            new TrialTipsMail($tenant),
            new TrialReminderMail($tenant),
            new TrialUrgentMail($tenant),
            new TrialEndedMail($tenant),
        ];

        foreach ($mailables as $mailable) {
            $rendered = $mailable->render();
            $this->assertStringContainsString('descadastre-se', $rendered);
        }
    }

    public function test_all_mailables_send_with_mail_fake(): void
    {
        Mail::fake();

        $tenant = $this->createTenant();

        $mailables = [
            TrialWelcomeMail::class,
            TrialCaseStudyMail::class,
            TrialTipsMail::class,
            TrialReminderMail::class,
            TrialUrgentMail::class,
            TrialEndedMail::class,
        ];

        foreach ($mailables as $mailableClass) {
            Mail::to('test@example.com')->queue(new $mailableClass($tenant));
        }

        foreach ($mailables as $mailableClass) {
            Mail::assertQueued($mailableClass, function ($mail) use ($tenant) {
                return $mail->tenant->id === $tenant->id;
            });
        }
    }

    public function test_trial_welcome_mail_implements_should_queue(): void
    {
        $reflection = new \ReflectionClass(TrialWelcomeMail::class);
        $this->assertTrue($reflection->implementsInterface(\Illuminate\Contracts\Queue\ShouldQueue::class));
    }

    public function test_trial_case_study_mail_implements_should_queue(): void
    {
        $reflection = new \ReflectionClass(TrialCaseStudyMail::class);
        $this->assertTrue($reflection->implementsInterface(\Illuminate\Contracts\Queue\ShouldQueue::class));
    }

    public function test_trial_tips_mail_implements_should_queue(): void
    {
        $reflection = new \ReflectionClass(TrialTipsMail::class);
        $this->assertTrue($reflection->implementsInterface(\Illuminate\Contracts\Queue\ShouldQueue::class));
    }

    public function test_trial_reminder_mail_implements_should_queue(): void
    {
        $reflection = new \ReflectionClass(TrialReminderMail::class);
        $this->assertTrue($reflection->implementsInterface(\Illuminate\Contracts\Queue\ShouldQueue::class));
    }

    public function test_trial_urgent_mail_implements_should_queue(): void
    {
        $reflection = new \ReflectionClass(TrialUrgentMail::class);
        $this->assertTrue($reflection->implementsInterface(\Illuminate\Contracts\Queue\ShouldQueue::class));
    }

    public function test_trial_ended_mail_implements_should_queue(): void
    {
        $reflection = new \ReflectionClass(TrialEndedMail::class);
        $this->assertTrue($reflection->implementsInterface(\Illuminate\Contracts\Queue\ShouldQueue::class));
    }
}
