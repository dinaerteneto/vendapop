<?php

namespace Tests\Feature\Trial;

use App\Models\Invite;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrialDurationTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionService $subscriptionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subscriptionService = new SubscriptionService();
    }

    private function createTenant(string $slug = 'test-store'): Tenant
    {
        return Tenant::create([
            'name' => 'Test Store',
            'slug' => $slug,
            'whatsapp_number' => '5511999999999',
        ]);
    }

    private function createInvite(string $type, ?Tenant $creator = null): Invite
    {
        return Invite::create([
            'code' => strtoupper(\Illuminate\Support\Str::random(8)),
            'type' => $type,
            'created_by_tenant_id' => $creator?->id,
            'max_uses' => 1,
            'current_uses' => 0,
            'expires_at' => now()->addDays(7),
        ]);
    }

    public function test_config_returns_correct_default(): void
    {
        $this->assertEquals(45, config('trial.duration_days'));
    }

    public function test_subscription_isTrial_works_correctly(): void
    {
        $tenant = $this->createTenant('trial-check');

        $subscription = $this->subscriptionService->createFromInvite(
            $tenant,
            $this->createInvite('public')
        );

        $this->assertTrue($subscription->isTrial());
        $this->assertEquals('trial', $subscription->plan_status);
    }

    public function test_public_link_invite_creates_subscription_with_45_day_trial(): void
    {
        $tenant = $this->createTenant('public-link');
        $invite = $this->createInvite('public');

        $subscription = $this->subscriptionService->createFromInvite($tenant, $invite);

        $expectedEnd = $subscription->started_at->copy()->addDays(config('trial.duration_days'));
        $this->assertEquals(
            $expectedEnd->toDateString(),
            $subscription->ends_at->toDateString()
        );
        $this->assertEquals('public_link', $subscription->invite_source);
    }

    public function test_founder_invite_creates_subscription_with_45_day_trial(): void
    {
        $creator = $this->createTenant('creator');
        $tenant = $this->createTenant('founder');

        $invite = $this->createInvite('manual', $creator);

        $subscription = $this->subscriptionService->createFromInvite($tenant, $invite);

        $expectedEnd = $subscription->started_at->copy()->addDays(config('trial.duration_days'));
        $this->assertEquals(
            $expectedEnd->toDateString(),
            $subscription->ends_at->toDateString()
        );
        $this->assertEquals('founder', $subscription->invite_source);
    }

    public function test_lifetime_subscription_has_no_end_date(): void
    {
        $tenant = $this->createTenant('lifetime');
        $invite = $this->createInvite('manual');
        $invite->update(['created_by_tenant_id' => null]);

        $invite->refresh();

        $subscription = $this->subscriptionService->createFromInvite($tenant, $invite);

        $this->assertNull($subscription->ends_at);
        $this->assertEquals('active', $subscription->plan_status);
        $this->assertEquals('manual', $subscription->invite_source);
    }
}
