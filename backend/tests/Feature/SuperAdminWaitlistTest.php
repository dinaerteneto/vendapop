<?php

namespace Tests\Feature;

use App\Models\Invite;
use App\Models\User;
use App\Models\WaitlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuperAdminWaitlistTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'is_super_admin' => true,
            'tenant_id' => null,
        ]);

        Sanctum::actingAs($this->superAdmin);
    }

    public function test_lists_waitlist_entries(): void
    {
        WaitlistEntry::create(['email' => 'a@test.com']);
        WaitlistEntry::create(['email' => 'b@test.com']);

        $response = $this->getJson('/api/superadmin/waitlist');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    public function test_filters_by_status(): void
    {
        WaitlistEntry::create(['email' => 'pending@test.com', 'status' => 'pending']);
        WaitlistEntry::create(['email' => 'approved@test.com', 'status' => 'approved']);

        $response = $this->getJson('/api/superadmin/waitlist?status=approved');

        $response->assertStatus(200);
        foreach ($response->json('data') as $entry) {
            $this->assertEquals('approved', $entry['status']);
        }
    }

    public function test_filters_by_date_range(): void
    {
        $old = WaitlistEntry::create([
            'email' => 'old@test.com',
            'created_at' => now()->subDays(10),
        ]);
        $new = WaitlistEntry::create([
            'email' => 'new@test.com',
            'created_at' => now()->subDays(1),
        ]);

        $response = $this->getJson('/api/superadmin/waitlist?date_from=' . now()->subDays(3)->toDateString());

        $response->assertStatus(200);
        foreach ($response->json('data') as $entry) {
            $this->assertGreaterThanOrEqual(now()->subDays(3)->timestamp, strtotime($entry['created_at']));
        }
    }

    public function test_approves_entry_and_generates_invite(): void
    {
        $entry = WaitlistEntry::create(['email' => 'approve@test.com']);

        $response = $this->putJson("/api/superadmin/waitlist/{$entry->id}", [
            'status' => 'approved',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['entry', 'invite_code', 'invite_link', 'email_sent']);
        $this->assertEquals('approved', $response->json('entry.status'));
        $this->assertNotNull($response->json('invite_code'));
        $this->assertTrue($response->json('email_sent'));
        $this->assertStringContainsString('/convite/', $response->json('invite_link'));

        $this->assertDatabaseHas('invites', ['code' => $response->json('invite_code')]);
        $this->assertDatabaseHas('waitlist_entries', [
            'id' => $entry->id,
            'status' => 'approved',
        ]);
    }

    public function test_rejects_entry_with_reason(): void
    {
        $entry = WaitlistEntry::create(['email' => 'reject@test.com']);

        $response = $this->putJson("/api/superadmin/waitlist/{$entry->id}", [
            'status' => 'rejected',
            'rejection_reason' => 'Not our target audience',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('rejected', $response->json('status'));
        $this->assertEquals('Not our target audience', $response->json('rejection_reason'));
    }

    public function test_batch_approves_entries(): void
    {
        $e1 = WaitlistEntry::create(['email' => 'batch1@test.com']);
        $e2 = WaitlistEntry::create(['email' => 'batch2@test.com']);

        $response = $this->postJson('/api/superadmin/waitlist/batch', [
            'ids' => [$e1->id, $e2->id],
        ]);

        $response->assertStatus(200);
        $this->assertCount(2, $response->json());

        foreach ($response->json() as $result) {
            $this->assertEquals('approved', $result['entry']['status']);
            $this->assertNotNull($result['invite_code']);
        }
    }

    public function test_non_superadmin_gets_403(): void
    {
        $tenant = \App\Models\Tenant::create([
            'name' => 'T', 'slug' => 't-'.uniqid(),
            'whatsapp_number' => '5511',
        ]);

        $tenantUser = User::create([
            'name' => 'Tenant',
            'email' => 'tenant@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $tenant->id,
        ]);

        Sanctum::actingAs($tenantUser);

        $response = $this->getJson('/api/superadmin/waitlist');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_gets_401(): void
    {
        auth()->forgetGuards();

        $response = $this->getJson('/api/superadmin/waitlist');
        $response->assertStatus(401);
    }
}
