<?php

namespace Tests\Feature;

use App\Models\Invite;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InviteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Invite Test Store',
            'slug' => 'invite-test',
            'whatsapp_number' => '5511999999999',
        ]);

        // Mark this tenant as a manual founder so it can generate invites
        Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'premium',
            'plan_status' => 'active',
            'invite_source' => 'manual',
            'started_at' => now(),
            'ends_at' => null,
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@invitetest.com',
            'password' => bcrypt('password'),
            'is_owner' => true,
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_generate_invite_as_authenticated_user(): void
    {
        $response = $this->postJson('/api/admin/invites');

        $response->assertStatus(201)
            ->assertJsonStructure(['code', 'url', 'expires_at', 'remaining'])
            ->assertJson(['remaining' => 1]);

        $this->assertDatabaseHas('invites', [
            'created_by_tenant_id' => $this->tenant->id,
            'type' => 'manual',
            'max_uses' => 1,
        ]);
    }

    public function test_list_invites_as_authenticated_user(): void
    {
        Invite::create([
            'code' => 'ABC12345',
            'type' => 'manual',
            'created_by_tenant_id' => $this->tenant->id,
            'max_uses' => 1,
            'current_uses' => 0,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->getJson('/api/admin/invites');

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_generate_third_invite_is_blocked(): void
    {
        $this->postJson('/api/admin/invites');
        $this->postJson('/api/admin/invites');

        $response = $this->postJson('/api/admin/invites');

        $response->assertStatus(403)
            ->assertJson(['message' => 'Você já usou todos os seus convites.']);
    }

    public function test_create_public_link_as_admin(): void
    {
        $response = $this->postJson('/api/admin/invites/public', [
            'max_uses' => 5,
            'expires_in_hours' => 48,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['code', 'url', 'max_uses', 'expires_at']);

        $this->assertDatabaseHas('invites', [
            'type' => 'public',
            'max_uses' => 5,
        ]);
    }

    public function test_validate_public_endpoint_for_valid_code(): void
    {
        $invite = Invite::create([
            'code' => 'VALID123',
            'type' => 'manual',
            'created_by_tenant_id' => $this->tenant->id,
            'max_uses' => 1,
            'current_uses' => 0,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->getJson('/api/admin/invites/validate/' . $invite->code);

        $response->assertStatus(200)
            ->assertJson(['valid' => true, 'code' => 'VALID123']);
    }

    public function test_validate_public_endpoint_for_expired_code(): void
    {
        $invite = Invite::create([
            'code' => 'OLDCODE1',
            'type' => 'manual',
            'created_by_tenant_id' => $this->tenant->id,
            'max_uses' => 1,
            'current_uses' => 0,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/admin/invites/validate/' . $invite->code);

        $response->assertStatus(422)
            ->assertJson(['valid' => false]);
    }

    public function test_get_remaining_invites(): void
    {
        $response = $this->getJson('/api/admin/invites/remaining');

        $response->assertStatus(200)
            ->assertJson(['remaining' => 2]);
    }

    public function test_generate_invite_without_auth_returns_401(): void
    {
        // Use a fresh app instance without Sanctum acting as
        $this->app->make(\Illuminate\Contracts\Auth\Factory::class)->guard('sanctum')->forgetUser();

        $response = $this->postJson('/api/admin/invites');

        $response->assertStatus(401);
    }
}
