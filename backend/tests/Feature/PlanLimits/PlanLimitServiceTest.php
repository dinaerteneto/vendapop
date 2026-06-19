<?php

namespace Tests\Feature\PlanLimits;

use App\Models\PlanLimit;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\PlanLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PlanLimitServiceTest extends TestCase
{
    use RefreshDatabase;

    private PlanLimitService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PlanLimitService();
        Cache::flush();
        DB::table('plan_limits')->delete();
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

    private function createTenant(array $overrides = []): Tenant
    {
        $defaults = [
            'name' => 'Test Store',
            'slug' => 'test-store-' . uniqid(),
            'whatsapp_number' => '5511999999999',
        ];

        return Tenant::create(array_merge($defaults, $overrides));
    }

    private function createProduct(Tenant $tenant, array $overrides = []): Product
    {
        $defaults = [
            'tenant_id' => $tenant->id,
            'name' => 'Test Product ' . uniqid(),
            'price' => 10.00,
            'is_active' => true,
        ];

        return Product::create(array_merge($defaults, $overrides));
    }

    private function createSubscription(Tenant $tenant, array $overrides = []): Subscription
    {
        $defaults = [
            'tenant_id' => $tenant->id,
            'plan_type' => 'free',
            'plan_status' => 'active',
            'invite_source' => 'manual',
            'started_at' => now(),
        ];

        return Subscription::create(array_merge($defaults, $overrides));
    }

    // getLimits

    public function test_get_limits_returns_plan_limit_for_existing_type(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6]);

        $result = $this->service->getLimits('free');

        $this->assertInstanceOf(PlanLimit::class, $result);
        $this->assertEquals(6, $result->max_products);
    }

    public function test_get_limits_returns_null_for_nonexistent_type(): void
    {
        $this->assertNull($this->service->getLimits('nonexistent'));
    }

    // getLimit

    public function test_get_limit_returns_6_for_free(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6]);

        $this->assertEquals(6, $this->service->getLimit('free'));
    }

    public function test_get_limit_returns_null_for_premium_unlimited(): void
    {
        $this->createPlanLimit('premium', ['max_products' => 0]);

        $this->assertNull($this->service->getLimit('premium'));
    }

    public function test_get_limit_returns_null_for_nonexistent_type(): void
    {
        $this->assertNull($this->service->getLimit('nonexistent'));
    }

    public function test_get_limit_uses_cache(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6]);

        $this->assertEquals(6, $this->service->getLimit('free'));

        PlanLimit::byPlanType('free')->delete();

        $this->assertEquals(6, $this->service->getLimit('free'));
    }

    // canAddProducts

    public function test_can_add_products_returns_true_when_below_limit(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6]);

        $this->assertTrue($this->service->canAddProducts('free', 5));
    }

    public function test_can_add_products_returns_false_when_at_limit(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6]);

        $this->assertFalse($this->service->canAddProducts('free', 6));
    }

    public function test_can_add_products_returns_false_when_exceeds_limit(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6]);

        $this->assertFalse($this->service->canAddProducts('free', 10));
    }

    public function test_can_add_products_returns_true_when_max_is_zero_unlimited(): void
    {
        $this->createPlanLimit('premium', ['max_products' => 0]);

        $this->assertTrue($this->service->canAddProducts('premium', 999));
    }

    public function test_can_add_products_returns_true_for_nonexistent_plan(): void
    {
        $this->assertTrue($this->service->canAddProducts('nonexistent', 5));
    }

    // canAddCategories

    public function test_can_add_categories_returns_true_when_below_limit(): void
    {
        $this->createPlanLimit('free', ['max_categories' => 3]);

        $this->assertTrue($this->service->canAddCategories('free', 2));
    }

    public function test_can_add_categories_returns_false_when_at_limit(): void
    {
        $this->createPlanLimit('free', ['max_categories' => 3]);

        $this->assertFalse($this->service->canAddCategories('free', 3));
    }

    public function test_can_add_categories_returns_true_when_null_is_unlimited(): void
    {
        $this->createPlanLimit('basic', ['max_categories' => null]);

        $this->assertTrue($this->service->canAddCategories('basic', 999));
    }

    public function test_can_add_categories_returns_true_for_nonexistent_plan(): void
    {
        $this->assertTrue($this->service->canAddCategories('nonexistent', 5));
    }

    // canUseFeature

    public function test_can_use_feature_returns_true_when_feature_enabled(): void
    {
        $this->createPlanLimit('basic', ['allow_checkout_pix' => true]);

        $this->assertTrue($this->service->canUseFeature('basic', 'allow_checkout_pix'));
    }

    public function test_can_use_feature_returns_false_when_feature_disabled(): void
    {
        $this->createPlanLimit('free', ['allow_checkout_pix' => false]);

        $this->assertFalse($this->service->canUseFeature('free', 'allow_checkout_pix'));
    }

    public function test_can_use_feature_returns_false_for_nonexistent_plan(): void
    {
        $this->assertFalse($this->service->canUseFeature('nonexistent', 'allow_checkout_pix'));
    }

    public function test_can_use_feature_checks_custom_domain(): void
    {
        $this->createPlanLimit('basic', ['allow_custom_domain' => true]);

        $this->assertTrue($this->service->canUseFeature('basic', 'allow_custom_domain'));
        $this->assertFalse($this->service->canUseFeature('free', 'allow_custom_domain'));
    }

    // countActiveProducts

    public function test_count_active_products_counts_only_active_products(): void
    {
        $tenant = $this->createTenant();
        $this->createProduct($tenant, ['is_active' => true]);
        $this->createProduct($tenant, ['is_active' => true]);
        $this->createProduct($tenant, ['is_active' => true]);
        $this->createProduct($tenant, ['is_active' => false]);
        $this->createProduct($tenant, ['is_active' => false]);

        $this->assertEquals(3, $this->service->countActiveProducts($tenant));
    }

    public function test_count_active_products_returns_zero_for_tenant_with_no_products(): void
    {
        $tenant = $this->createTenant();

        $this->assertEquals(0, $this->service->countActiveProducts($tenant));
    }

    // resolvePlanType

    public function test_resolve_plan_type_returns_free_when_no_subscription(): void
    {
        $tenant = $this->createTenant();

        $this->assertEquals('free', $this->service->resolvePlanType($tenant));
    }

    public function test_resolve_plan_type_returns_basic_when_active_subscription_exists(): void
    {
        $tenant = $this->createTenant();
        $this->createSubscription($tenant, [
            'plan_type' => 'basic',
            'plan_status' => 'active',
        ]);

        $this->assertEquals('basic', $this->service->resolvePlanType($tenant));
    }

    public function test_resolve_plan_type_returns_trial_plan_type_when_only_trial_exists(): void
    {
        $tenant = $this->createTenant();
        $this->createSubscription($tenant, [
            'plan_type' => 'premium',
            'plan_status' => 'trial',
        ]);

        $this->assertEquals('premium', $this->service->resolvePlanType($tenant));
    }

    public function test_resolve_plan_type_prefers_active_over_trial(): void
    {
        $tenant = $this->createTenant();
        $this->createSubscription($tenant, [
            'plan_type' => 'premium',
            'plan_status' => 'trial',
        ]);
        $this->createSubscription($tenant, [
            'plan_type' => 'basic',
            'plan_status' => 'active',
        ]);

        $this->assertEquals('basic', $this->service->resolvePlanType($tenant));
    }

    // checkLimit

    public function test_check_limit_returns_null_for_premium_unlimited(): void
    {
        $tenant = $this->createTenant();
        $this->createPlanLimit('premium', ['max_products' => 0]);
        $this->createSubscription($tenant, [
            'plan_type' => 'premium',
            'plan_status' => 'active',
        ]);
        for ($i = 0; $i < 100; $i++) {
            $this->createProduct($tenant, ['is_active' => true]);
        }

        $this->assertNull($this->service->checkLimit($tenant));
    }

    public function test_check_limit_returns_null_when_under_limit(): void
    {
        $tenant = $this->createTenant();
        $this->createPlanLimit('free', ['max_products' => 6]);
        $this->createSubscription($tenant, [
            'plan_type' => 'free',
            'plan_status' => 'active',
        ]);
        for ($i = 0; $i < 5; $i++) {
            $this->createProduct($tenant, ['is_active' => true]);
        }

        $this->assertNull($this->service->checkLimit($tenant));
    }

    public function test_check_limit_returns_6_when_at_limit(): void
    {
        $tenant = $this->createTenant();
        $this->createPlanLimit('free', ['max_products' => 6]);
        $this->createSubscription($tenant, [
            'plan_type' => 'free',
            'plan_status' => 'active',
        ]);
        for ($i = 0; $i < 6; $i++) {
            $this->createProduct($tenant, ['is_active' => true]);
        }

        $this->assertEquals(6, $this->service->checkLimit($tenant));
    }

    public function test_check_limit_returns_6_when_over_limit(): void
    {
        $tenant = $this->createTenant();
        $this->createPlanLimit('free', ['max_products' => 6]);
        $this->createSubscription($tenant, [
            'plan_type' => 'free',
            'plan_status' => 'active',
        ]);
        for ($i = 0; $i < 10; $i++) {
            $this->createProduct($tenant, ['is_active' => true]);
        }

        $this->assertEquals(6, $this->service->checkLimit($tenant));
    }

    // clearCache

    public function test_clear_cache_flushes_cache_key(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6]);

        $this->assertEquals(6, $this->service->getLimit('free'));

        $this->service->clearCache('free');

        PlanLimit::byPlanType('free')->delete();

        $this->assertNull($this->service->getLimit('free'));
    }

    // integration: real cache behavior

    public function test_cache_hits_after_first_query(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6]);

        $this->assertEquals(6, $this->service->getLimit('free'));

        PlanLimit::byPlanType('free')->update(['max_products' => 999]);

        $this->assertEquals(6, $this->service->getLimit('free'));
    }

    public function test_clear_cache_then_get_requeries_db(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6]);

        $this->assertEquals(6, $this->service->getLimit('free'));

        $this->service->clearCache('free');

        PlanLimit::byPlanType('free')->update(['max_products' => 10]);

        $this->assertEquals(10, $this->service->getLimit('free'));
    }

    public function test_service_integration_with_seeded_data(): void
    {
        $tenant = $this->createTenant();
        $this->createPlanLimit('free', ['max_products' => 6]);
        $this->createSubscription($tenant, [
            'plan_type' => 'free',
            'plan_status' => 'active',
        ]);

        $this->assertNull($this->service->checkLimit($tenant));

        for ($i = 0; $i < 6; $i++) {
            $this->createProduct($tenant, ['is_active' => true]);
        }

        $this->assertEquals(6, $this->service->checkLimit($tenant));

        $this->assertFalse($this->service->canAddProducts('free', 6));
    }
}
