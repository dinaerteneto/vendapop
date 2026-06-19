<?php

namespace Tests\Feature;

use App\Models\PlanLimit;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PlanLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuperAdminPlanLimitTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        PlanLimit::query()->delete();

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'is_super_admin' => true,
            'tenant_id' => null,
        ]);

        Sanctum::actingAs($this->superAdmin);
    }

    public function test_lists_all_plan_limits(): void
    {
        PlanLimit::create(['plan_type' => 'free', 'max_products' => 5]);
        PlanLimit::create(['plan_type' => 'basic', 'max_products' => 20]);
        PlanLimit::create(['plan_type' => 'professional', 'max_products' => 100]);
        PlanLimit::create(['plan_type' => 'premium', 'max_products' => 0]);

        $response = $this->getJson('/api/superadmin/plan-limits');

        $response->assertStatus(200);
        $response->assertJsonCount(4);
        $response->assertJsonStructure([
            '*' => ['plan_type', 'max_products'],
        ]);
    }

    public function test_non_superadmin_gets_403(): void
    {
        $tenant = Tenant::create([
            'name' => 'Test Store',
            'slug' => 'test-store-' . uniqid(),
            'whatsapp_number' => '5511999999999',
        ]);

        $tenantUser = User::create([
            'name' => 'Tenant',
            'email' => 'tenant@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $tenant->id,
        ]);

        Sanctum::actingAs($tenantUser);

        $response = $this->getJson('/api/superadmin/plan-limits');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_gets_401(): void
    {
        auth()->forgetGuards();

        $response = $this->getJson('/api/superadmin/plan-limits');
        $response->assertStatus(401);
    }

    public function test_updates_max_products(): void
    {
        PlanLimit::create(['plan_type' => 'free', 'max_products' => 5]);

        $response = $this->putJson('/api/superadmin/plan-limits/free', [
            'max_products' => 10,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(10, $response->json('max_products'));
        $this->assertDatabaseHas('plan_limits', [
            'plan_type' => 'free',
            'max_products' => 10,
        ]);
    }

    public function test_sets_unlimited_with_null(): void
    {
        PlanLimit::create(['plan_type' => 'premium', 'max_products' => 100]);

        $response = $this->putJson('/api/superadmin/plan-limits/premium', [
            'max_products' => null,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('max_products'));
        $this->assertDatabaseHas('plan_limits', [
            'plan_type' => 'premium',
            'max_products' => 0,
        ]);
    }

    public function test_cache_is_invalidated_after_update(): void
    {
        PlanLimit::create(['plan_type' => 'free', 'max_products' => 5]);

        $service = app(PlanLimitService::class);
        $this->assertEquals(5, $service->getLimit('free'));

        $this->putJson('/api/superadmin/plan-limits/free', [
            'max_products' => 10,
        ]);

        $this->assertEquals(10, $service->getLimit('free'));
    }

    public function test_invalid_plan_type_returns_422(): void
    {
        PlanLimit::create(['plan_type' => 'free', 'max_products' => 5]);

        $response = $this->putJson('/api/superadmin/plan-limits/invalid', [
            'max_products' => 10,
        ]);

        $response->assertStatus(422);
    }

    public function test_rejects_negative_max_products(): void
    {
        PlanLimit::create(['plan_type' => 'free', 'max_products' => 5]);

        $response = $this->putJson('/api/superadmin/plan-limits/free', [
            'max_products' => -1,
        ]);

        $response->assertStatus(422);
    }

    public function test_rejects_string_max_products(): void
    {
        PlanLimit::create(['plan_type' => 'free', 'max_products' => 5]);

        $response = $this->putJson('/api/superadmin/plan-limits/free', [
            'max_products' => 'not-a-number',
        ]);

        $response->assertStatus(422);
    }

    public function test_plan_limit_not_found_returns_404(): void
    {
        $response = $this->putJson('/api/superadmin/plan-limits/free', [
            'max_products' => 10,
        ]);

        $response->assertStatus(404);
    }
}
