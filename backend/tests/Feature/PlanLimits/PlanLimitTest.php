<?php

namespace Tests\Feature\PlanLimits;

use App\Models\PlanLimit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlanLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('plan_limits')->delete();
    }

    public function test_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('plan_limits'));
        $this->assertTrue(Schema::hasColumns('plan_limits', [
            'id', 'plan_type', 'max_products', 'max_categories',
            'allow_custom_domain', 'allow_checkout_pix', 'allow_checkout_credit_card',
            'allow_analytics', 'max_staff_accounts', 'max_orders_per_month',
            'created_at', 'updated_at',
        ]));
    }

    public function test_by_plan_type_scope_returns_correct_record(): void
    {
        PlanLimit::create(['plan_type' => 'free', 'max_products' => 6]);
        PlanLimit::create(['plan_type' => 'basic', 'max_products' => 30]);

        $free = PlanLimit::byPlanType('free')->first();
        $basic = PlanLimit::byPlanType('basic')->first();

        $this->assertEquals(6, $free->max_products);
        $this->assertEquals(30, $basic->max_products);
    }

    public function test_can_add_more_products_returns_true_when_below_limit(): void
    {
        $plan = PlanLimit::create(['plan_type' => 'free', 'max_products' => 6]);

        $this->assertTrue($plan->canAddMoreProducts(0));
        $this->assertTrue($plan->canAddMoreProducts(5));
    }

    public function test_can_add_more_products_returns_false_when_at_or_exceeds_limit(): void
    {
        $plan = PlanLimit::create(['plan_type' => 'free', 'max_products' => 6]);

        $this->assertFalse($plan->canAddMoreProducts(6));
        $this->assertFalse($plan->canAddMoreProducts(10));
    }

    public function test_can_add_more_products_returns_true_when_max_is_zero_unlimited(): void
    {
        $plan = PlanLimit::create(['plan_type' => 'pro', 'max_products' => 0]);

        $this->assertTrue($plan->canAddMoreProducts(0));
        $this->assertTrue($plan->canAddMoreProducts(999));
    }

    public function test_can_add_more_categories_returns_true_when_below_limit(): void
    {
        $plan = PlanLimit::create(['plan_type' => 'free', 'max_categories' => 3]);

        $this->assertTrue($plan->canAddMoreCategories(0));
        $this->assertTrue($plan->canAddMoreCategories(2));
    }

    public function test_can_add_more_categories_returns_false_when_at_or_exceeds_limit(): void
    {
        $plan = PlanLimit::create(['plan_type' => 'free', 'max_categories' => 3]);

        $this->assertFalse($plan->canAddMoreCategories(3));
        $this->assertFalse($plan->canAddMoreCategories(5));
    }

    public function test_can_add_more_categories_returns_true_when_null_is_unlimited(): void
    {
        $plan = PlanLimit::create(['plan_type' => 'basic', 'max_categories' => null]);

        $this->assertTrue($plan->canAddMoreCategories(0));
        $this->assertTrue($plan->canAddMoreCategories(999));
    }
}
