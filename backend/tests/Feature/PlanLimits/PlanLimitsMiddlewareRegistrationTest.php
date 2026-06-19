<?php

namespace Tests\Feature\PlanLimits;

use App\Http\Middleware\CheckPlanLimits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PlanLimitsMiddlewareRegistrationTest extends TestCase
{
    public function test_middleware_alias_is_registered(): void
    {
        $middleware = $this->app['router']->getMiddleware();

        $this->assertArrayHasKey('check.plan.limits', $middleware);
    }

    public function test_middleware_alias_resolves_to_correct_class(): void
    {
        $middleware = $this->app['router']->getMiddleware();

        $this->assertEquals(CheckPlanLimits::class, $middleware['check.plan.limits']);
    }

    public function test_product_store_route_has_check_plan_limits_middleware(): void
    {
        $routes = Route::getRoutes()->getRoutes();

        $found = false;
        foreach ($routes as $route) {
            if ($route->uri() === 'api/admin/products' && in_array('POST', $route->methods())) {
                $found = true;
                $this->assertContains(
                    'check.plan.limits:products',
                    $route->middleware(),
                    'POST /api/admin/products should have check.plan.limits:products middleware'
                );
            }
        }

        $this->assertTrue($found, 'POST /api/admin/products route not found');
    }

    public function test_product_update_route_has_check_plan_limits_middleware(): void
    {
        $routes = Route::getRoutes()->getRoutes();

        $found = false;
        foreach ($routes as $route) {
            if (str_contains($route->uri(), 'api/admin/products') && in_array('PUT', $route->methods())) {
                $found = true;
                $this->assertContains(
                    'check.plan.limits:products',
                    $route->middleware(),
                    'PUT /api/admin/products/{product} should have check.plan.limits:products middleware'
                );
            }
        }

        $this->assertTrue($found, 'PUT /api/admin/products/{product} route not found');
    }

    public function test_product_index_route_does_not_have_check_plan_limits_middleware(): void
    {
        $routes = Route::getRoutes()->getRoutes();

        foreach ($routes as $route) {
            if ($route->uri() === 'api/admin/products' && in_array('GET', $route->methods())) {
                $this->assertNotContains(
                    'check.plan.limits',
                    $route->middleware(),
                    'GET /api/admin/products should NOT have check.plan.limits middleware'
                );
                $this->assertNotContains(
                    'check.plan.limits:products',
                    $route->middleware(),
                    'GET /api/admin/products should NOT have check.plan.limits:products middleware'
                );
            }
        }
    }

    public function test_product_destroy_route_does_not_have_check_plan_limits_middleware(): void
    {
        $routes = Route::getRoutes()->getRoutes();

        foreach ($routes as $route) {
            if (str_contains($route->uri(), 'api/admin/products') && in_array('DELETE', $route->methods())) {
                $this->assertNotContains(
                    'check.plan.limits',
                    $route->middleware(),
                    'DELETE /api/admin/products/{product} should NOT have check.plan.limits middleware'
                );
            }
        }
    }

    public function test_category_index_route_does_not_have_check_plan_limits_middleware(): void
    {
        $routes = Route::getRoutes()->getRoutes();

        foreach ($routes as $route) {
            if ($route->uri() === 'api/admin/categories' && in_array('GET', $route->methods())) {
                $this->assertNotContains(
                    'check.plan.limits',
                    $route->middleware(),
                    'GET /api/admin/categories should NOT have check.plan.limits middleware'
                );
            }
        }
    }

    public function test_category_store_route_has_check_plan_limits_middleware(): void
    {
        $routes = Route::getRoutes()->getRoutes();

        $found = false;
        foreach ($routes as $route) {
            if ($route->uri() === 'api/admin/categories' && in_array('POST', $route->methods())) {
                $found = true;
                $this->assertContains(
                    'check.plan.limits:categories',
                    $route->middleware(),
                    'POST /api/admin/categories should have check.plan.limits:categories middleware'
                );
            }
        }

        $this->assertTrue($found, 'POST /api/admin/categories route not found');
    }

    public function test_non_product_write_routes_are_not_affected(): void
    {
        $routes = Route::getRoutes()->getRoutes();

        foreach ($routes as $route) {
            if (in_array('POST', $route->methods()) || in_array('PUT', $route->methods())) {
                $hasLimitMiddleware = in_array('check.plan.limits', $route->middleware())
                    || in_array('check.plan.limits:products', $route->middleware())
                    || in_array('check.plan.limits:categories', $route->middleware());

                if ($hasLimitMiddleware) {
                    $uri = $route->uri();
                    $isProductOrCategory = str_contains($uri, 'admin/products')
                        || $uri === 'api/admin/categories';

                    $this->assertTrue(
                        $isProductOrCategory,
                        "Route $uri should not have check.plan.limits middleware"
                    );
                }
            }
        }
    }

    public function test_middleware_class_exists(): void
    {
        $this->assertTrue(class_exists(CheckPlanLimits::class));
    }
}
