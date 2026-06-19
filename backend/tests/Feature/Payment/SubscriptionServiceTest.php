<?php

namespace Tests\Feature\Payment;

use App\Models\Invite;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
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

    private function createSubscription(Tenant $tenant, array $overrides = []): Subscription
    {
        $invite = Invite::create([
            'code' => strtoupper(\Illuminate\Support\Str::random(8)),
            'type' => 'public',
            'created_by_tenant_id' => null,
            'max_uses' => 1,
            'current_uses' => 0,
            'expires_at' => now()->addDays(7),
        ]);

        return $this->subscriptionService->createFromInvite($tenant, $invite);
    }

    public function test_upgrade_to_sets_active_status(): void
    {
        $tenant = $this->createTenant('upgrade-status');
        $subscription = $this->createSubscription($tenant);

        $result = $this->subscriptionService->upgradeTo($subscription, 'professional');

        $this->assertEquals('active', $result->plan_status);
        $this->assertEquals('professional', $result->plan_type);
        $this->assertFalse($result->is_pending);
    }

    public function test_upgrade_to_clears_is_pending(): void
    {
        $tenant = $this->createTenant('upgrade-clear');
        $subscription = $this->createSubscription($tenant);
        $subscription->update(['is_pending' => true]);

        $result = $this->subscriptionService->upgradeTo($subscription, 'basic');

        $this->assertFalse($result->is_pending);
    }

    public function test_upgrade_to_sets_payment_transaction_id(): void
    {
        $tenant = $this->createTenant('upgrade-txn');
        $subscription = $this->createSubscription($tenant);

        $result = $this->subscriptionService->upgradeTo($subscription, 'premium', 'txn_abc123');

        $this->assertEquals('txn_abc123', $result->payment_transaction_id);
    }

    public function test_upgrade_to_returns_fresh_instance(): void
    {
        $tenant = $this->createTenant('upgrade-fresh');
        $subscription = $this->createSubscription($tenant);

        $result = $this->subscriptionService->upgradeTo($subscription, 'professional');

        $this->assertNotSame($subscription, $result);
    }

    public function test_cancel_sets_cancelled_status(): void
    {
        $tenant = $this->createTenant('cancel-status');
        $subscription = $this->createSubscription($tenant);

        $result = $this->subscriptionService->cancel($subscription);

        $this->assertEquals('cancelled', $result->plan_status);
    }

    public function test_cancel_works_on_active_subscription(): void
    {
        $tenant = $this->createTenant('cancel-active');
        $subscription = $this->createSubscription($tenant);
        $this->subscriptionService->upgradeTo($subscription, 'professional');

        $result = $this->subscriptionService->cancel($subscription->fresh());

        $this->assertEquals('cancelled', $result->plan_status);
    }

    public function test_cancel_does_not_alter_plan_type(): void
    {
        $tenant = $this->createTenant('cancel-plantype');
        $subscription = $this->createSubscription($tenant);

        $result = $this->subscriptionService->cancel($subscription);

        $this->assertEquals($subscription->plan_type, $result->plan_type);
    }

    public function test_mark_pending_sets_is_pending_true(): void
    {
        $tenant = $this->createTenant('pending-set');
        $subscription = $this->createSubscription($tenant);

        $result = $this->subscriptionService->markPending($subscription);

        $this->assertTrue($result->is_pending);
    }

    public function test_mark_pending_does_not_alter_plan_status(): void
    {
        $tenant = $this->createTenant('pending-status');
        $subscription = $this->createSubscription($tenant);

        $result = $this->subscriptionService->markPending($subscription);

        $this->assertEquals($subscription->plan_status, $result->plan_status);
    }

    public function test_is_pending_returns_true(): void
    {
        $tenant = $this->createTenant('ispending-true');
        $subscription = $this->createSubscription($tenant);
        $subscription->update(['is_pending' => true]);

        $this->assertTrue($this->subscriptionService->isPending($subscription->fresh()));
    }

    public function test_is_pending_returns_false(): void
    {
        $tenant = $this->createTenant('ispending-false');
        $subscription = $this->createSubscription($tenant);

        $this->assertFalse($this->subscriptionService->isPending($subscription));
    }

    public function test_existing_methods_still_work(): void
    {
        $tenant = $this->createTenant('existing');
        $subscription = $this->createSubscription($tenant);

        $this->assertTrue($subscription->isTrial());
        $this->assertTrue($this->subscriptionService->isActive($tenant));
        $this->assertNotNull($this->subscriptionService->expiresInDays($tenant));
        $this->assertSame($subscription->id, $this->subscriptionService->getActive($tenant)->id);
    }

    public function test_full_cycle_create_upgrade(): void
    {
        $tenant = $this->createTenant('cycle-upgrade');
        $subscription = $this->createSubscription($tenant);

        $this->subscriptionService->markPending($subscription);
        $this->assertTrue($subscription->fresh()->is_pending);

        $result = $this->subscriptionService->upgradeTo($subscription->fresh(), 'professional', 'txn_cycle_001');

        $this->assertEquals('active', $result->plan_status);
        $this->assertEquals('professional', $result->plan_type);
        $this->assertFalse($result->is_pending);
        $this->assertEquals('txn_cycle_001', $result->payment_transaction_id);
    }

    public function test_full_cycle_create_cancel(): void
    {
        $tenant = $this->createTenant('cycle-cancel');
        $subscription = $this->createSubscription($tenant);

        $this->subscriptionService->markPending($subscription);
        $this->assertTrue($subscription->fresh()->is_pending);

        $result = $this->subscriptionService->cancel($subscription->fresh());

        $this->assertEquals('cancelled', $result->plan_status);
    }
}
