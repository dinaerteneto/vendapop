<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BelongsToTenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_assigns_tenant_id_when_tenant_service_has_tenant(): void
    {
        $tenant = Tenant::create([
            'name' => 'Test Store',
            'slug' => 'test-store-001',
            'whatsapp_number' => '5511999999999',
        ]);

        app(TenantService::class)->setTenant($tenant);

        $user = User::create([
            'name' => 'Regular User',
            'email' => 'regular@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertNotNull($user->tenant_id);
        $this->assertEquals($tenant->id, $user->tenant_id);
    }

    public function test_does_not_auto_assign_when_tenant_id_explicitly_null(): void
    {
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'is_super_admin' => true,
            'tenant_id' => null,
        ]);

        $this->assertNull($user->tenant_id);
        $this->assertTrue($user->is_super_admin);
    }

    public function test_still_auto_assigns_when_no_tenant_id_in_attributes(): void
    {
        $tenant = Tenant::create([
            'name' => 'Second Store',
            'slug' => 'second-store-002',
            'whatsapp_number' => '5511888888888',
        ]);

        app(TenantService::class)->setTenant($tenant);

        $user = User::create([
            'name' => 'Another User',
            'email' => 'another@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertNotNull($user->tenant_id);
        $this->assertEquals($tenant->id, $user->tenant_id);
    }

    public function test_explicit_tenant_id_still_respected(): void
    {
        $tenant = Tenant::create([
            'name' => 'Cross Store',
            'slug' => 'cross-store-003',
            'whatsapp_number' => '5511777777777',
        ]);

        $user = User::create([
            'name' => 'Cross Tenant',
            'email' => 'cross@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $tenant->id,
        ]);

        $this->assertNotNull($user->tenant_id);
        $this->assertEquals($tenant->id, $user->tenant_id);
    }
}
