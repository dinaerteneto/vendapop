<?php

namespace Tests\Feature\Payment;

use App\Domain\Payment\CheckoutResponse;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\UseCases\Payment\CancelSubscriptionUseCase;
use App\UseCases\Payment\CreateCheckoutUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentSubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected Subscription $subscription;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Payment Test Store',
            'slug' => 'payment-test-' . strtolower(str()->random(6)),
            'whatsapp_number' => '5511999999999',
        ]);

        $this->subscription = Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'basic',
            'plan_status' => 'active',
            'invite_source' => 'manual',
            'started_at' => now(),
            'ends_at' => null,
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Payment Admin',
            'email' => 'payment-admin-' . strtolower(str()->random(6)) . '@test.com',
            'password' => bcrypt('password'),
            'is_owner' => true,
        ]);
    }

    public function test_create_checkout_with_valid_data_returns_201(): void
    {
        $this->mock(CreateCheckoutUseCase::class, function ($mock) {
            $mock->shouldReceive('execute')
                ->once()
                ->andReturn(new CheckoutResponse(
                    checkout_url: 'https://mercadopago.com/checkout/test',
                    transaction_id: 'txn_mock_001',
                ));
        });

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/admin/subscription/create', [
            'plan_type' => 'professional',
            'billing_cycle' => 'monthly',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'checkout_url',
                'subscription_id',
                'gateway',
                'gateway_payment_id',
                'status',
            ])
            ->assertJsonPath('checkout_url', 'https://mercadopago.com/checkout/test')
            ->assertJsonPath('gateway_payment_id', 'txn_mock_001')
            ->assertJsonPath('status', 'pending');
    }

    public function test_create_checkout_with_invalid_plan_type_returns_422(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/admin/subscription/create', [
            'plan_type' => 'invalid_plan',
            'billing_cycle' => 'monthly',
        ]);

        $response->assertStatus(422);
    }

    public function test_create_checkout_with_invalid_billing_cycle_returns_422(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/admin/subscription/create', [
            'plan_type' => 'professional',
            'billing_cycle' => 'invalid_cycle',
        ]);

        $response->assertStatus(422);
    }

    public function test_create_checkout_when_pending_exists_returns_409(): void
    {
        Sanctum::actingAs($this->user);

        $this->subscription->update(['is_pending' => true]);

        $response = $this->postJson('/api/admin/subscription/create', [
            'plan_type' => 'professional',
            'billing_cycle' => 'monthly',
        ]);

        $response->assertStatus(409);
    }

    public function test_create_checkout_when_downgrade_returns_400(): void
    {
        $this->subscription->update(['plan_type' => 'premium']);

        $this->mock(CreateCheckoutUseCase::class, function ($mock) {
            $mock->shouldReceive('execute')
                ->never();
        });

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/admin/subscription/create', [
            'plan_type' => 'basic',
            'billing_cycle' => 'monthly',
        ]);

        $response->assertStatus(400);
    }

    public function test_create_checkout_without_auth_returns_401(): void
    {
        $response = $this->postJson('/api/admin/subscription/create', [
            'plan_type' => 'professional',
            'billing_cycle' => 'monthly',
        ]);

        $response->assertStatus(401);
    }

    public function test_cancel_returns_200(): void
    {
        $this->mock(CancelSubscriptionUseCase::class, function ($mock) {
            $mock->shouldReceive('execute')
                ->once()
                ->andReturn($this->subscription->fresh());
        });

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/admin/subscription/cancel');

        $response->assertStatus(200)
            ->assertJson(['status' => 'cancelled']);
    }

    public function test_cancel_when_no_subscription_returns_400(): void
    {
        $otherTenant = Tenant::create([
            'name' => 'Other Store',
            'slug' => 'other-store-' . strtolower(str()->random(6)),
            'whatsapp_number' => '5511999999999',
        ]);

        $otherUser = User::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Admin',
            'email' => 'other-admin-' . strtolower(str()->random(6)) . '@test.com',
            'password' => bcrypt('password'),
            'is_owner' => true,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->postJson('/api/admin/subscription/cancel');

        $response->assertStatus(400);
    }

    public function test_cancel_without_auth_returns_401(): void
    {
        $response = $this->postJson('/api/admin/subscription/cancel');

        $response->assertStatus(401);
    }

    public function test_show_subscription_includes_is_pending(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/admin/subscription');

        $response->assertStatus(200)
            ->assertJsonPath('is_pending', false);
    }

    public function test_show_subscription_includes_gateway_status(): void
    {
        Sanctum::actingAs($this->user);

        PaymentTransaction::create([
            'tenant_id' => $this->tenant->id,
            'subscription_id' => $this->subscription->id,
            'transaction_id' => 'txn_test_' . strtolower(str()->random(8)),
            'plan_type' => 'professional',
            'amount' => 29.90,
            'status' => 'pending',
            'gateway' => 'mercadopago',
        ]);

        $response = $this->getJson('/api/admin/subscription');

        $response->assertStatus(200)
            ->assertJsonPath('gateway_status', 'pending');
    }

    public function test_show_subscription_includes_next_billing_date(): void
    {
        Sanctum::actingAs($this->user);

        $this->subscription->update(['ends_at' => now()->addMonth()]);

        $response = $this->getJson('/api/admin/subscription');

        $response->assertStatus(200);
        $response->assertJsonStructure(['next_billing_date']);
    }

    public function test_show_subscription_includes_days_remaining(): void
    {
        Sanctum::actingAs($this->user);

        $endsAt = now()->addDays(15);
        $this->subscription->update(['ends_at' => $endsAt]);

        $response = $this->getJson('/api/admin/subscription');

        $response->assertStatus(200);
        $daysRemaining = $response->json('days_remaining');
        $this->assertIsInt($daysRemaining);
        $this->assertGreaterThan(0, $daysRemaining);
    }

    public function test_show_subscription_current_transaction_null_when_no_transactions(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/admin/subscription');

        $response->assertStatus(200)
            ->assertJsonPath('current_transaction', null);
    }

    public function test_show_subscription_includes_current_transaction_when_exists(): void
    {
        Sanctum::actingAs($this->user);

        $transaction = PaymentTransaction::create([
            'tenant_id' => $this->tenant->id,
            'subscription_id' => $this->subscription->id,
            'transaction_id' => 'txn_show_' . strtolower(str()->random(8)),
            'plan_type' => 'professional',
            'amount' => 29.90,
            'status' => 'approved',
            'gateway' => 'mercadopago',
            'paid_at' => now(),
        ]);

        $response = $this->getJson('/api/admin/subscription');

        $response->assertStatus(200)
            ->assertJsonPath('current_transaction.id', $transaction->id)
            ->assertJsonPath('current_transaction.transaction_id', $transaction->transaction_id)
            ->assertJsonPath('current_transaction.status', 'approved');
    }

    public function test_show_subscription_existing_fields_preserved(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/admin/subscription');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'plan_type',
                'plan_status',
                'invite_source',
                'started_at',
                'ends_at',
                'days_remaining',
                'is_active',
                'limits',
            ]);
    }

    public function test_show_subscription_returns_free_when_no_subscription(): void
    {
        $this->subscription->delete();

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/admin/subscription');

        $response->assertStatus(200)
            ->assertJsonPath('plan_type', 'free')
            ->assertJsonPath('is_pending', false)
            ->assertJsonPath('gateway_status', null)
            ->assertJsonPath('next_billing_date', null)
            ->assertJsonPath('current_transaction', null);
    }
}
