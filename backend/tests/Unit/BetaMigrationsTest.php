<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BetaMigrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_invites_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('invites'));
        $this->assertTrue(Schema::hasColumns('invites', [
            'id', 'code', 'type', 'created_by_tenant_id',
            'max_uses', 'current_uses', 'expires_at', 'created_at', 'updated_at',
        ]));
    }

    public function test_invites_table_has_unique_code(): void
    {
        $columns = Schema::getIndexes('invites');
        $uniqueCodes = array_filter($columns, fn($idx) => str_contains($idx['name'], 'code') && str_contains($idx['name'], 'unique'));
        $this->assertNotEmpty($uniqueCodes, 'invites.code should have a unique index');
    }

    public function test_invites_table_has_type_index(): void
    {
        $columns = Schema::getIndexes('invites');
        $typeIndex = array_filter($columns, fn($idx) => str_contains($idx['name'], 'type') && !str_contains($idx['name'], 'unique'));
        $this->assertNotEmpty($typeIndex, 'invites.type should have an index');
    }

    public function test_invites_table_foreign_key_on_created_by_tenant_id(): void
    {
        $foreignKeys = Schema::getForeignKeys('invites');
        $this->assertNotEmpty(array_filter(
            $foreignKeys,
            fn($fk) => in_array('created_by_tenant_id', $fk['columns'])
        ), 'invites.created_by_tenant_id should have a foreign key');
    }

    public function test_invites_table_enum_type_accepts_manual_and_public(): void
    {
        \Illuminate\Support\Facades\DB::table('invites')->delete();

        // Create a dummy tenant first for the FK
        $tenantId = \App\Models\Tenant::create([
            'name' => 'Test',
            'slug' => 'test-invite-enum',
            'whatsapp_number' => '5511999999999',
        ])->id;

        \Illuminate\Support\Facades\DB::table('invites')->insert([
            'code' => 'ABC12345',
            'type' => 'manual',
            'max_uses' => 1,
            'expires_at' => now()->addDays(7),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::table('invites')->insert([
            'code' => 'XYZ98765',
            'type' => 'public',
            'created_by_tenant_id' => $tenantId,
            'max_uses' => 5,
            'expires_at' => now()->addHours(48),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('invites', ['code' => 'ABC12345', 'type' => 'manual']);
        $this->assertDatabaseHas('invites', ['code' => 'XYZ98765', 'type' => 'public']);
    }

    public function test_subscriptions_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('subscriptions'));
        $this->assertTrue(Schema::hasColumns('subscriptions', [
            'id', 'tenant_id', 'plan_type', 'plan_status', 'invite_id',
            'invite_source', 'started_at', 'ends_at', 'cancelled_at',
            'created_at', 'updated_at',
        ]));
    }

    public function test_subscriptions_table_foreign_key_on_tenant_id(): void
    {
        $foreignKeys = Schema::getForeignKeys('subscriptions');
        $this->assertNotEmpty(array_filter(
            $foreignKeys,
            fn($fk) => in_array('tenant_id', $fk['columns'])
        ), 'subscriptions.tenant_id should have a foreign key referencing tenants');
    }

    public function test_subscriptions_table_has_tenant_plan_status_index(): void
    {
        $indexes = Schema::getIndexes('subscriptions');
        $found = array_filter($indexes, fn($idx) =>
            in_array('tenant_id', $idx['columns'] ?? []) && in_array('plan_status', $idx['columns'] ?? [])
        );
        $this->assertNotEmpty($found, 'subscriptions should have composite index on tenant_id + plan_status');
    }

    public function test_subscriptions_table_has_ends_at_index(): void
    {
        $indexes = Schema::getIndexes('subscriptions');
        $found = array_filter($indexes, fn($idx) =>
            in_array('ends_at', $idx['columns'] ?? [])
        );
        $this->assertNotEmpty($found, 'subscriptions should have an index on ends_at');
    }

    public function test_subscriptions_table_enum_plan_types(): void
    {
        $tenantId = \App\Models\Tenant::create([
            'name' => 'SubTest',
            'slug' => 'test-sub-enum',
            'whatsapp_number' => '5511888888888',
        ])->id;

        $types = ['free', 'basic', 'professional', 'premium'];
        foreach ($types as $type) {
            \Illuminate\Support\Facades\DB::table('subscriptions')->insert([
                'tenant_id' => $tenantId,
                'plan_type' => $type,
                'plan_status' => 'active',
                'invite_source' => 'manual',
                'started_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // Delete after inserting to avoid duplicate for next iteration
            \Illuminate\Support\Facades\DB::table('subscriptions')->where('plan_type', $type)->delete();
        }

        $this->assertTrue(true); // No exception = enum values accepted
    }

    public function test_tenant_trackings_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('tenant_trackings'));
        $this->assertTrue(Schema::hasColumns('tenant_trackings', [
            'id', 'tenant_id', 'provider', 'tracking_code', 'created_at', 'updated_at',
        ]));
    }

    public function test_tenant_trackings_table_has_unique_composite_index(): void
    {
        $indexes = Schema::getIndexes('tenant_trackings');
        $uniqueIndexes = array_filter($indexes, fn($idx) => $idx['unique'] ?? false);
        $found = array_filter($uniqueIndexes, fn($idx) =>
            in_array('tenant_id', $idx['columns'] ?? []) && in_array('provider', $idx['columns'] ?? [])
        );
        $this->assertNotEmpty($found, 'tenant_trackings should have unique composite index on tenant_id + provider');
    }

    public function test_waitlist_entries_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('waitlist_entries'));
        $this->assertTrue(Schema::hasColumns('waitlist_entries', [
            'id', 'email', 'created_at', 'updated_at',
        ]));
    }

    public function test_waitlist_entries_table_has_unique_email(): void
    {
        $indexes = Schema::getIndexes('waitlist_entries');
        $uniqueIndexes = array_filter($indexes, fn($idx) => $idx['unique'] ?? false);
        $found = array_filter($uniqueIndexes, fn($idx) =>
            in_array('email', $idx['columns'] ?? [])
        );
        $this->assertNotEmpty($found, 'waitlist_entries.email should have a unique index');
    }

    public function test_plan_expiry_banner_dismissed_at_column_exists_on_tenants(): void
    {
        $this->assertTrue(Schema::hasColumn('tenants', 'plan_expiry_banner_dismissed_at'));
    }

    public function test_plan_expiry_banner_dismissed_at_is_nullable(): void
    {
        $tenant = \App\Models\Tenant::create([
            'name' => 'BannerTest',
            'slug' => 'test-banner',
            'whatsapp_number' => '5511777777777',
        ]);

        $this->assertNull($tenant->fresh()->plan_expiry_banner_dismissed_at);
    }
}
