<?php

namespace Tests\Feature\PlanLimits;

use App\Models\Category;
use App\Models\PlanLimit;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SubscriptionLimitInfoTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        DB::table('plan_limits')->delete();

        $this->tenant = Tenant::create([
            'name' => 'Test Store',
            'slug' => 'test-store-' . uniqid(),
            'whatsapp_number' => '5511999999999',
        ]);

        $this->user = User::create([
            'name' => 'Tenant User',
            'email' => 'tenant@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
        ]);
    }

    private function authHeaders(): array
    {
        $token = $this->user->createToken('test')->plainTextToken;
        return ['Authorization' => 'Bearer ' . $token];
    }

    private function createPlanLimit(string $planType, array $overrides = []): PlanLimit
    {
        $defaults = [
            'plan_type' => $planType,
            'max_products' => 6,
            'max_categories' => 3,
            'allow_custom_domain' => false,
            'allow_checkout_pix' => false,
            'allow_checkout_credit_card' => false,
            'allow_analytics' => false,
            'max_staff_accounts' => 1,
            'max_orders_per_month' => 10,
        ];

        return PlanLimit::create(array_merge($defaults, $overrides));
    }

    private function createProduct(array $overrides = []): Product
    {
        $defaults = [
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Product ' . uniqid(),
            'price' => 10.00,
            'is_active' => true,
        ];

        return Product::create(array_merge($defaults, $overrides));
    }

    private function createCategory(array $overrides = []): Category
    {
        $defaults = [
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Category ' . uniqid(),
            'is_active' => true,
        ];

        return Category::create(array_merge($defaults, $overrides));
    }

    private function createSubscription(array $overrides = []): Subscription
    {
        $defaults = [
            'tenant_id' => $this->tenant->id,
            'plan_type' => 'free',
            'plan_status' => 'active',
            'invite_source' => 'manual',
            'started_at' => now(),
        ];

        return Subscription::create(array_merge($defaults, $overrides));
    }

    public function test_returns_limits_structure_when_no_subscription(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6, 'max_categories' => 3]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/subscription');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'plan_type',
            'plan_status',
            'days_remaining',
            'is_active',
            'limits' => [
                'max_products',
                'max_categories',
                'allow_checkout_pix',
                'allow_checkout_credit_card',
                'allow_analytics',
                'max_staff_accounts',
                'max_orders_per_month',
                'current_products',
                'current_categories',
                'can_add_product',
                'can_add_category',
            ],
        ]);

        $response->assertJsonPath('plan_type', 'free');
        $response->assertJsonPath('plan_status', null);
        $response->assertJsonPath('is_active', false);
    }

    public function test_returns_correct_products_used_count(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6, 'max_categories' => 3]);
        $this->createProduct();
        $this->createProduct();
        $this->createProduct();
        $this->createProduct(['is_active' => false]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/subscription');

        $response->assertStatus(200);
        $response->assertJsonPath('limits.current_products', 3);
        $response->assertJsonPath('limits.can_add_product', true);
    }

    public function test_products_remaining_edge_with_at_limit(): void
    {
        $this->createPlanLimit('free', ['max_products' => 3, 'max_categories' => 3]);
        for ($i = 0; $i < 3; $i++) {
            $this->createProduct();
        }

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/subscription');

        $response->assertStatus(200);
        $response->assertJsonPath('limits.current_products', 3);
        $response->assertJsonPath('limits.can_add_product', false);
    }

    public function test_can_add_product_true_when_one_away_from_limit(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6, 'max_categories' => 3]);
        for ($i = 0; $i < 5; $i++) {
            $this->createProduct();
        }

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/subscription');

        $response->assertStatus(200);
        $response->assertJsonPath('limits.current_products', 5);
        $response->assertJsonPath('limits.can_add_product', true);
    }

    public function test_premium_plan_has_null_max_products(): void
    {
        $this->createPlanLimit('premium', ['max_products' => 0, 'max_categories' => null]);
        $this->createSubscription(['plan_type' => 'premium', 'plan_status' => 'active']);

        for ($i = 0; $i < 100; $i++) {
            $this->createProduct();
        }

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/subscription');

        $response->assertStatus(200);
        $response->assertJsonPath('limits.max_products', null);
        $response->assertJsonPath('limits.current_products', 100);
        $response->assertJsonPath('limits.can_add_product', true);
    }

    public function test_all_original_fields_remain_unchanged_with_subscription(): void
    {
        $this->createPlanLimit('basic', ['max_products' => 30, 'max_categories' => 10]);
        $this->createSubscription([
            'plan_type' => 'basic',
            'plan_status' => 'active',
            'started_at' => now()->subDays(5),
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/subscription');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'plan_type',
            'plan_status',
            'invite_source',
            'started_at',
            'ends_at',
            'days_remaining',
            'is_active',
            'limits',
        ]);
        $response->assertJsonPath('plan_type', 'basic');
        $response->assertJsonPath('plan_status', 'active');
    }

    public function test_can_add_product_is_false_when_over_limit(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6, 'max_categories' => 10]);
        for ($i = 0; $i < 7; $i++) {
            $this->createProduct();
        }

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/subscription');

        $response->assertStatus(200);
        $response->assertJsonPath('limits.current_products', 7);
        $response->assertJsonPath('limits.can_add_product', false);
    }

    public function test_can_add_category_is_false_when_at_limit(): void
    {
        $this->createPlanLimit('free', ['max_products' => 10, 'max_categories' => 3]);
        for ($i = 0; $i < 3; $i++) {
            $this->createCategory();
        }

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/subscription');

        $response->assertStatus(200);
        $response->assertJsonPath('limits.current_categories', 3);
        $response->assertJsonPath('limits.can_add_category', false);
    }

    public function test_can_add_category_true_when_below_limit(): void
    {
        $this->createPlanLimit('free', ['max_products' => 10, 'max_categories' => 5]);
        $this->createCategory();
        $this->createCategory();

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/subscription');

        $response->assertStatus(200);
        $response->assertJsonPath('limits.current_categories', 2);
        $response->assertJsonPath('limits.can_add_category', true);
    }

    public function test_no_subscription_still_returns_all_original_fields(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6, 'max_categories' => 3]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/subscription');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'plan_type',
            'plan_status',
            'days_remaining',
            'is_active',
            'limits',
        ]);
        $response->assertJsonPath('plan_type', 'free');
        $response->assertJsonPath('is_active', false);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/admin/subscription');

        $response->assertStatus(401);
    }

    public function test_inactive_products_do_not_count_toward_limit(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6, 'max_categories' => 3]);
        $this->createProduct(['is_active' => true]);
        $this->createProduct(['is_active' => false]);
        $this->createProduct(['is_active' => false]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/subscription');

        $response->assertStatus(200);
        $response->assertJsonPath('limits.current_products', 1);
        $response->assertJsonPath('limits.can_add_product', true);
    }

    public function test_inactive_categories_do_not_count_toward_limit(): void
    {
        $this->createPlanLimit('free', ['max_products' => 10, 'max_categories' => 3]);
        $this->createCategory(['is_active' => true]);
        $this->createCategory(['is_active' => false]);
        $this->createCategory(['is_active' => false]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/subscription');

        $response->assertStatus(200);
        $response->assertJsonPath('limits.current_categories', 1);
        $response->assertJsonPath('limits.can_add_category', true);
    }
}
