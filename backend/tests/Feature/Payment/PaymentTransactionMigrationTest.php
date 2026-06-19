<?php

namespace Tests\Feature\Payment;

use App\Models\PaymentTransaction;
use App\Models\Tenant;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaymentTransactionMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_transactions_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('payment_transactions'));
        $this->assertTrue(Schema::hasColumn('payment_transactions', 'id'));
        $this->assertTrue(Schema::hasColumn('payment_transactions', 'tenant_id'));
        $this->assertTrue(Schema::hasColumn('payment_transactions', 'subscription_id'));
        $this->assertTrue(Schema::hasColumn('payment_transactions', 'transaction_id'));
        $this->assertTrue(Schema::hasColumn('payment_transactions', 'plan_type'));
        $this->assertTrue(Schema::hasColumn('payment_transactions', 'amount'));
        $this->assertTrue(Schema::hasColumn('payment_transactions', 'status'));
        $this->assertTrue(Schema::hasColumn('payment_transactions', 'gateway'));
        $this->assertTrue(Schema::hasColumn('payment_transactions', 'payload'));
        $this->assertTrue(Schema::hasColumn('payment_transactions', 'paid_at'));
        $this->assertTrue(Schema::hasColumn('payment_transactions', 'created_at'));
        $this->assertTrue(Schema::hasColumn('payment_transactions', 'updated_at'));
    }

    public function test_unique_index_on_transaction_id(): void
    {
        [$tenant, $subscription] = $this->createTestData();

        PaymentTransaction::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'transaction_id' => 'tx_unique_001',
            'plan_type' => 'basic',
            'amount' => 99.90,
            'status' => 'approved',
            'gateway' => 'mercadopago',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        PaymentTransaction::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'transaction_id' => 'tx_unique_001',
            'plan_type' => 'basic',
            'amount' => 99.90,
            'status' => 'approved',
            'gateway' => 'mercadopago',
        ]);
    }

    public function test_foreign_key_cascade_on_tenant_delete(): void
    {
        [$tenant, $subscription] = $this->createTestData();

        PaymentTransaction::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'transaction_id' => 'tx_cascade_001',
            'plan_type' => 'basic',
            'amount' => 99.90,
            'status' => 'approved',
            'gateway' => 'mercadopago',
        ]);

        $tenant->delete();

        $this->assertEquals(0, PaymentTransaction::count());
    }

    public function test_model_can_create_a_record(): void
    {
        [$tenant, $subscription] = $this->createTestData();

        $transaction = PaymentTransaction::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'transaction_id' => 'tx_create_001',
            'plan_type' => 'basic',
            'amount' => 99.90,
            'status' => 'approved',
            'gateway' => 'mercadopago',
            'payload' => ['payment_id' => '12345', 'status' => 'approved'],
            'paid_at' => now(),
        ]);

        $this->assertNotNull($transaction);
        $this->assertEquals('tx_create_001', $transaction->transaction_id);
        $this->assertEquals(99.90, (float) $transaction->amount);
        $this->assertEquals(['payment_id' => '12345', 'status' => 'approved'], $transaction->payload);
    }

    public function test_is_pending_column_exists_on_subscriptions_table(): void
    {
        $this->assertTrue(Schema::hasColumn('subscriptions', 'is_pending'));
    }

    public function test_is_pending_defaults_to_false(): void
    {
        [$tenant] = $this->createTestData();

        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'basic',
            'plan_status' => 'active',
            'invite_source' => 'manual',
            'started_at' => now(),
        ]);

        $subscription->refresh();

        $this->assertFalse($subscription->is_pending);
    }

    public function test_belongs_to_relationships(): void
    {
        [$tenant, $subscription] = $this->createTestData();

        $transaction = PaymentTransaction::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'transaction_id' => 'tx_rel_001',
            'plan_type' => 'basic',
            'amount' => 99.90,
            'status' => 'approved',
            'gateway' => 'mercadopago',
        ]);

        $this->assertEquals($tenant->id, $transaction->tenant->id);
        $this->assertEquals($subscription->id, $transaction->subscription->id);
    }

    private function createTestData(): array
    {
        $tenant = Tenant::create([
            'name' => 'Test Store',
            'slug' => 'test-store-' . uniqid(),
            'whatsapp_number' => '5511999999999',
        ]);

        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => 'basic',
            'plan_status' => 'active',
            'invite_source' => 'manual',
            'started_at' => now(),
        ]);

        return [$tenant, $subscription];
    }
}
