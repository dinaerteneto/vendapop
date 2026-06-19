<?php

namespace Tests\Feature\Emails;

use App\Models\TrialEmailsSent;
use App\Models\Tenant;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TrialEmailsSentMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_trial_emails_sent_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('trial_emails_sent'));
        $this->assertTrue(Schema::hasColumn('trial_emails_sent', 'tenant_id'));
        $this->assertTrue(Schema::hasColumn('trial_emails_sent', 'subscription_id'));
        $this->assertTrue(Schema::hasColumn('trial_emails_sent', 'email_day'));
        $this->assertTrue(Schema::hasColumn('trial_emails_sent', 'sent_at'));
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
            'plan_status' => 'trial',
            'invite_source' => 'manual',
            'started_at' => now(),
            'ends_at' => now()->addDays(45),
        ]);

        return [$tenant, $subscription];
    }

    public function test_unique_index_prevents_duplicate(): void
    {
        [$tenant, $subscription] = $this->createTestData();

        TrialEmailsSent::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'email_day' => 0,
            'sent_at' => now(),
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        TrialEmailsSent::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'email_day' => 0,
            'sent_at' => now(),
        ]);
    }

    public function test_foreign_key_cascade_on_tenant_delete(): void
    {
        [$tenant, $subscription] = $this->createTestData();

        TrialEmailsSent::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'email_day' => 7,
            'sent_at' => now(),
        ]);

        $tenant->delete();

        $this->assertEquals(0, TrialEmailsSent::count());
    }

    public function test_model_has_fillable_attributes(): void
    {
        [$tenant, $subscription] = $this->createTestData();

        $record = TrialEmailsSent::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'email_day' => 15,
            'sent_at' => now(),
        ]);

        $this->assertNotNull($record);
        $this->assertEquals($tenant->id, $record->tenant_id);
        $this->assertEquals($subscription->id, $record->subscription_id);
        $this->assertEquals(15, $record->email_day);
    }

    public function test_belongs_to_relationships(): void
    {
        [$tenant, $subscription] = $this->createTestData();

        $record = TrialEmailsSent::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'email_day' => 0,
            'sent_at' => now(),
        ]);

        $this->assertEquals($tenant->id, $record->tenant->id);
        $this->assertEquals($subscription->id, $record->subscription->id);
    }
}
