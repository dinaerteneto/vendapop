<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $tenantUser;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::create([
            'name' => 'Test Store',
            'slug' => 'test-store',
            'whatsapp_number' => '5511999999999',
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'is_super_admin' => true,
            'tenant_id' => null,
        ]);

        $this->tenantUser = User::create([
            'name' => 'Tenant User',
            'email' => 'tenant@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_superadmin_login_returns_token_with_is_super_admin(): void
    {
        $response = $this->postJson('/api/superadmin/login', [
            'email' => 'superadmin@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'user']);
        $response->assertJsonPath('user.is_super_admin', true);
        $response->assertJsonPath('user.email', 'superadmin@test.com');
    }

    public function test_tenant_user_login_to_superadmin_returns_401(): void
    {
        $response = $this->postJson('/api/superadmin/login', [
            'email' => 'tenant@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401);
    }

    public function test_invalid_credentials_returns_401(): void
    {
        $response = $this->postJson('/api/superadmin/login', [
            'email' => 'superadmin@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_updates_last_login_at(): void
    {
        $this->postJson('/api/superadmin/login', [
            'email' => 'superadmin@test.com',
            'password' => 'password',
        ]);

        $user = User::find($this->superAdmin->id);
        $this->assertNotNull($user->last_login_at);
    }

    public function test_logout_deletes_token(): void
    {
        $token = $this->superAdmin->createToken('test')->plainTextToken;

        $this->assertEquals(1, $this->superAdmin->tokens()->count());

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/superadmin/logout')
            ->assertStatus(200);

        $this->assertEquals(0, $this->superAdmin->fresh()->tokens()->count());
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->postJson('/api/superadmin/logout');

        $response->assertStatus(401);
    }

    public function test_validation_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/superadmin/login', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }
}
