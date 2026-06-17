<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuperAdminTenantTest extends TestCase
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

    protected function createTenant(string $slug, string $planType = 'free', string $planStatus = 'active'): Tenant
    {
        $tenant = Tenant::create([
            'name' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'whatsapp_number' => '5511999999999',
        ]);

        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_type' => $planType,
            'plan_status' => $planStatus,
            'started_at' => now()->subMonth(),
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Owner',
            'email' => "owner@{$slug}.com",
            'password' => bcrypt('password'),
        ]);

        return $tenant;
    }

    public function test_lists_all_tenants(): void
    {
        $this->createTenant('store-a');
        $this->createTenant('store-b');

        $response = $this->getJson('/api/superadmin/tenants');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    public function test_search_filters_by_name(): void
    {
        $this->createTenant('alpha-store');
        $this->createTenant('beta-store');

        $response = $this->getJson('/api/superadmin/tenants?search=alpha');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('alpha-store', $response->json('data.0.slug'));
    }

    public function test_search_filters_by_slug(): void
    {
        $this->createTenant('alpha-store');
        $this->createTenant('beta-store');

        $response = $this->getJson('/api/superadmin/tenants?search=beta');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('beta-store', $response->json('data.0.slug'));
    }

    public function test_sort_by_created_at_descending(): void
    {
        $a = $this->createTenant('store-a');
        $b = $this->createTenant('store-b');

        $response = $this->getJson('/api/superadmin/tenants?sort_by=created_at&sort_direction=desc');

        $response->assertStatus(200);
        $this->assertEquals('store-b', $response->json('data.0.slug'));
    }

    public function test_sort_by_name_ascending(): void
    {
        $this->createTenant('zulu-store');
        $this->createTenant('alpha-store');

        $response = $this->getJson('/api/superadmin/tenants?sort_by=name&sort_direction=asc');

        $response->assertStatus(200);
        $this->assertEquals('alpha-store', $response->json('data.0.slug'));
    }

    public function test_filter_by_plan_type(): void
    {
        $this->createTenant('free-store', 'free');
        $this->createTenant('premium-store', 'premium');

        $response = $this->getJson('/api/superadmin/tenants?plan_type=premium');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('premium-store', $response->json('data.0.slug'));
    }

    public function test_filter_by_plan_status(): void
    {
        $this->createTenant('active-store', 'free', 'active');
        $this->createTenant('trial-store', 'free', 'trial');

        $response = $this->getJson('/api/superadmin/tenants?plan_status=trial');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('trial-store', $response->json('data.0.slug'));
    }

    public function test_pagination_respects_per_page(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->createTenant("store-{$i}");
        }

        $response = $this->getJson('/api/superadmin/tenants?per_page=2');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    public function test_tenant_detail_includes_subscription_history(): void
    {
        $tenant = $this->createTenant('detail-store', 'premium', 'active');

        $response = $this->getJson("/api/superadmin/tenants/{$tenant->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('slug', 'detail-store');
        $this->assertNotEmpty($response->json('subscriptions'));
    }

    public function test_tenant_detail_includes_user_list(): void
    {
        $tenant = $this->createTenant('userlist-store');

        $response = $this->getJson("/api/superadmin/tenants/{$tenant->id}");

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('users'));
    }

    public function test_non_superadmin_gets_403(): void
    {
        $tenantUser = User::create([
            'name' => 'Tenant',
            'email' => 'tenant@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->createTenant('tenant-store')->id,
        ]);

        Sanctum::actingAs($tenantUser);

        $response = $this->getJson('/api/superadmin/tenants');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_gets_401(): void
    {
        auth()->forgetGuards();

        $response = $this->getJson('/api/superadmin/tenants');
        $response->assertStatus(401);
    }
}
