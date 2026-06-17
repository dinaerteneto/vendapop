<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSuperAdmin;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckSuperAdminTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $superAdmin;
    protected User $tenantUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
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
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_superadmin_passes_middleware(): void
    {
        $middleware = app(CheckSuperAdmin::class);

        $request = Request::create('/api/superadmin/tenants');
        $request->setUserResolver(fn () => $this->superAdmin);

        $response = $middleware->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_tenant_user_gets_403(): void
    {
        $middleware = app(CheckSuperAdmin::class);

        $request = Request::create('/api/superadmin/tenants');
        $request->setUserResolver(fn () => $this->tenantUser);

        $response = $middleware->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_unauthenticated_gets_403(): void
    {
        $middleware = app(CheckSuperAdmin::class);

        $request = Request::create('/api/superadmin/tenants');
        $request->setUserResolver(fn () => null);

        $response = $middleware->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_tenant_service_cleared_after_middleware(): void
    {
        $tenantService = app(TenantService::class);
        $tenantService->setTenant($this->tenant);

        $this->assertEquals($this->tenant->id, $tenantService->getTenantId());

        $middleware = app(CheckSuperAdmin::class);

        $request = Request::create('/api/superadmin/tenants');
        $request->setUserResolver(fn () => $this->superAdmin);

        $middleware->handle($request, fn ($req) => response()->json(['ok' => true]));

        $this->assertNull($tenantService->getTenantId());
    }
}
