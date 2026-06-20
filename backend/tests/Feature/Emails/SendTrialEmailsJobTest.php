<?php

namespace Tests\Feature\Emails;

use App\Jobs\SendTrialEmailsJob;
use App\Mail\TrialCaseStudyMail;
use App\Mail\TrialEndedMail;
use App\Mail\TrialReminderMail;
use App\Mail\TrialTipsMail;
use App\Mail\TrialUrgentMail;
use App\Mail\TrialWelcomeMail;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TrialEmailsSent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendTrialEmailsJobTest extends TestCase
{
    use RefreshDatabase;

    private function createTenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'name' => 'Loja Teste',
            'slug' => 'loja-teste-' . uniqid(),
            'whatsapp_number' => '5511999999999',
            'email_contact' => 'loja@teste.com',
        ], $overrides));
    }

    public function test_resolve_mailable_returns_welcome_for_day_0(): void
    {
        $this->assertEquals(TrialWelcomeMail::class, SendTrialEmailsJob::resolveMailable(0));
    }

    public function test_resolve_mailable_returns_case_study_for_day_7(): void
    {
        $this->assertEquals(TrialCaseStudyMail::class, SendTrialEmailsJob::resolveMailable(7));
    }

    public function test_resolve_mailable_returns_tips_for_day_15(): void
    {
        $this->assertEquals(TrialTipsMail::class, SendTrialEmailsJob::resolveMailable(15));
    }

    public function test_resolve_mailable_returns_reminder_for_day_30(): void
    {
        $this->assertEquals(TrialReminderMail::class, SendTrialEmailsJob::resolveMailable(30));
    }

    public function test_resolve_mailable_returns_urgent_for_day_40(): void
    {
        $this->assertEquals(TrialUrgentMail::class, SendTrialEmailsJob::resolveMailable(40));
    }

    public function test_resolve_mailable_returns_ended_for_day_45(): void
    {
        $this->assertEquals(TrialEndedMail::class, SendTrialEmailsJob::resolveMailable(45));
    }

    public function test_resolve_mailable_returns_null_for_non_matching_day(): void
    {
        $this->assertNull(SendTrialEmailsJob::resolveMailable(1));
        $this->assertNull(SendTrialEmailsJob::resolveMailable(5));
        $this->assertNull(SendTrialEmailsJob::resolveMailable(10));
        $this->assertNull(SendTrialEmailsJob::resolveMailable(20));
        $this->assertNull(SendTrialEmailsJob::resolveMailable(50));
    }

    public function test_skips_non_trial_subscriptions(): void
    {
        Mail::fake();

        $tenant = $this->createTenant();
        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'basic',
            'plan_status' => 'active',
            'started_at' => now(),
            'ends_at' => now()->addYear(),
        ]);

        (new SendTrialEmailsJob())->handle();

        Mail::assertNothingQueued();
        Mail::assertNothingSent();
    }

    public function test_skips_expired_subscriptions(): void
    {
        Mail::fake();

        $tenant = $this->createTenant();
        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'basic',
            'plan_status' => 'trial',
            'started_at' => now()->subDays(50),
            'ends_at' => now()->subDays(5),
        ]);

        (new SendTrialEmailsJob())->handle();

        Mail::assertNothingQueued();
    }

    public function test_skips_tenant_without_email_contact(): void
    {
        Mail::fake();

        $tenant = $this->createTenant(['email_contact' => null]);
        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'basic',
            'plan_status' => 'trial',
            'started_at' => now(),
            'ends_at' => now()->addDays(45),
        ]);

        (new SendTrialEmailsJob())->handle();

        Mail::assertNothingQueued();
    }

    public function test_skips_when_already_sent(): void
    {
        Mail::fake();

        $tenant = $this->createTenant();
        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'basic',
            'plan_status' => 'trial',
            'started_at' => now(),
            'ends_at' => now()->addDays(45),
        ]);

        TrialEmailsSent::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'email_day' => 0,
            'sent_at' => now(),
        ]);

        (new SendTrialEmailsJob())->handle();

        Mail::assertNothingQueued();
    }

    public function test_creates_trial_emails_sent_record_after_send(): void
    {
        Mail::fake();

        $tenant = $this->createTenant();
        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'basic',
            'plan_status' => 'trial',
            'started_at' => now(),
            'ends_at' => now()->addDays(45),
        ]);

        (new SendTrialEmailsJob())->handle();

        $this->assertDatabaseHas('trial_emails_sent', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'email_day' => 0,
        ]);

        Mail::assertQueued(TrialWelcomeMail::class, 1);
    }

    public function test_full_flow_integration(): void
    {
        Mail::fake();

        $makeSub = function (Tenant $tenant, int $daysAgo) {
            return Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_type' => 'basic',
                'plan_status' => 'trial',
                'started_at' => now()->subDays($daysAgo),
                'ends_at' => now()->addDays(46 - $daysAgo),
            ]);
        };

        $tenantDay0 = $this->createTenant(['name' => 'Day 0']);
        $makeSub($tenantDay0, 0);

        $tenantDay7 = $this->createTenant(['name' => 'Day 7']);
        $makeSub($tenantDay7, 7);

        $tenantDay15 = $this->createTenant(['name' => 'Day 15']);
        $makeSub($tenantDay15, 15);

        $tenantDay30 = $this->createTenant(['name' => 'Day 30']);
        $makeSub($tenantDay30, 30);

        $tenantDay40 = $this->createTenant(['name' => 'Day 40']);
        $makeSub($tenantDay40, 40);

        $tenantDay45 = $this->createTenant(['name' => 'Day 45']);
        $makeSub($tenantDay45, 45);

        $tenantNoEmail = $this->createTenant(['name' => 'No Email', 'email_contact' => null]);
        $makeSub($tenantNoEmail, 0);

        $tenantExpired = $this->createTenant(['name' => 'Expired']);
        Subscription::create([
            'tenant_id' => $tenantExpired->id,
            'plan_type' => 'basic',
            'plan_status' => 'trial',
            'started_at' => now()->subDays(50),
            'ends_at' => now()->subDays(5),
        ]);

        $tenantNonTrial = $this->createTenant(['name' => 'Non Trial']);
        Subscription::create([
            'tenant_id' => $tenantNonTrial->id,
            'plan_type' => 'basic',
            'plan_status' => 'active',
            'started_at' => now(),
            'ends_at' => now()->addYear(),
        ]);

        (new SendTrialEmailsJob())->handle();

        Mail::assertQueued(TrialWelcomeMail::class, 1);
        Mail::assertQueued(TrialCaseStudyMail::class, 1);
        Mail::assertQueued(TrialTipsMail::class, 1);
        Mail::assertQueued(TrialReminderMail::class, 1);
        Mail::assertQueued(TrialUrgentMail::class, 1);
        Mail::assertQueued(TrialEndedMail::class, 1);

        $this->assertDatabaseHas('trial_emails_sent', ['tenant_id' => $tenantDay0->id, 'email_day' => 0]);
        $this->assertDatabaseHas('trial_emails_sent', ['tenant_id' => $tenantDay7->id, 'email_day' => 7]);
        $this->assertDatabaseHas('trial_emails_sent', ['tenant_id' => $tenantDay15->id, 'email_day' => 15]);
        $this->assertDatabaseHas('trial_emails_sent', ['tenant_id' => $tenantDay30->id, 'email_day' => 30]);
        $this->assertDatabaseHas('trial_emails_sent', ['tenant_id' => $tenantDay40->id, 'email_day' => 40]);
        $this->assertDatabaseHas('trial_emails_sent', ['tenant_id' => $tenantDay45->id, 'email_day' => 45]);

        $this->assertDatabaseMissing('trial_emails_sent', ['tenant_id' => $tenantNoEmail->id]);
        $this->assertDatabaseMissing('trial_emails_sent', ['tenant_id' => $tenantExpired->id]);
        $this->assertDatabaseMissing('trial_emails_sent', ['tenant_id' => $tenantNonTrial->id]);
    }
}
