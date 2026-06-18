<?php

namespace Tests\Feature\Migrations;

use App\Models\Tenant;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OnboardingMigrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenants_table_has_onboarding_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('tenants', 'onboarding_completed'));
        $this->assertTrue(Schema::hasColumn('tenants', 'onboarding_step'));
    }

    public function test_products_table_has_is_demo_column(): void
    {
        $this->assertTrue(Schema::hasColumn('products', 'is_demo'));
    }

    public function test_categories_table_has_is_demo_column(): void
    {
        $this->assertTrue(Schema::hasColumn('categories', 'is_demo'));
    }

    private function createTestTenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Test Store',
            'slug' => 'test-store-' . uniqid(),
            'whatsapp_number' => '5511999999999',
        ]);
    }

    public function test_tenant_onboarding_completed_defaults_to_false(): void
    {
        $tenant = $this->createTestTenant();
        $tenant->refresh();

        $this->assertFalse($tenant->onboarding_completed);
        $this->assertEquals(0, $tenant->onboarding_step);
    }

    public function test_product_is_demo_defaults_to_false(): void
    {
        $tenant = $this->createTestTenant();

        $category = Category::create([
            'name' => 'Test Category',
            'tenant_id' => $tenant->id,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'price' => 99.90,
        ]);
        $product->refresh();

        $this->assertFalse($product->is_demo);
    }
}
