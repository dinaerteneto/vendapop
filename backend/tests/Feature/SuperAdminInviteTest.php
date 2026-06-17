<?php

namespace Tests\Feature;

use App\Models\Invite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuperAdminInviteTest extends TestCase
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

    public function test_lists_all_invites(): void
    {
        Invite::create([
            'code' => 'CODE001', 'type' => 'manual', 'max_uses' => 1,
            'current_uses' => 0, 'expires_at' => now()->addDays(7),
        ]);
        Invite::create([
            'code' => 'CODE002', 'type' => 'public', 'max_uses' => 5,
            'current_uses' => 0, 'expires_at' => now()->addDays(7),
        ]);

        $response = $this->getJson('/api/superadmin/invites');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    public function test_filters_by_type(): void
    {
        Invite::create([
            'code' => 'MANUAL01', 'type' => 'manual', 'max_uses' => 1,
            'current_uses' => 0, 'expires_at' => now()->addDays(7),
        ]);
        Invite::create([
            'code' => 'PUBLIC01', 'type' => 'public', 'max_uses' => 5,
            'current_uses' => 0, 'expires_at' => now()->addDays(7),
        ]);

        $response = $this->getJson('/api/superadmin/invites?type=public');

        $response->assertStatus(200);
        foreach ($response->json('data') as $item) {
            $this->assertEquals('public', $item['type']);
        }
    }

    public function test_generates_invite_codes(): void
    {
        $response = $this->postJson('/api/superadmin/invites', ['count' => 3]);

        $response->assertStatus(201);
        $this->assertCount(3, $response->json());
        foreach ($response->json() as $invite) {
            $this->assertEquals(8, strlen($invite['code']));
            $this->assertEquals('manual', $invite['type']);
        }
    }

    public function test_non_superadmin_gets_403(): void
    {
        $tenant = \App\Models\Tenant::create([
            'name' => 'T', 'slug' => 't-'.uniqid(),
            'whatsapp_number' => '5511',
        ]);

        $tenantUser = User::create([
            'name' => 'Tenant', 'email' => 'tenant@test.com',
            'password' => bcrypt('password'), 'tenant_id' => $tenant->id,
        ]);

        Sanctum::actingAs($tenantUser);

        $response = $this->getJson('/api/superadmin/invites');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_gets_401(): void
    {
        auth()->forgetGuards();

        $response = $this->getJson('/api/superadmin/invites');
        $response->assertStatus(401);
    }
}
