<?php

namespace Tests\Feature\PlanLimits;

use App\Http\Middleware\CheckPlanLimits;
use App\Models\PlanLimit;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PlanLimitService;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CheckPlanLimitsMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private PlanLimitService $planLimitService;

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

        $this->planLimitService = app(PlanLimitService::class);
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

    private function createRequest(string $method, string $path): Request
    {
        $request = Request::create($path, $method);

        $request->setRouteResolver(function () use ($method, $path) {
            return new \Illuminate\Routing\Route($method, $path, []);
        });

        return $request;
    }

    private function makeMiddleware(): CheckPlanLimits
    {
        return new CheckPlanLimits($this->planLimitService);
    }

    // ─── Unit tests (mocked PlanLimitService) ───

    public function test_passes_through_when_check_limit_returns_null(): void
    {
        $service = $this->createMock(PlanLimitService::class);
        $service->method('checkLimit')->willReturn(null);

        app(TenantService::class)->setTenant($this->tenant);

        $middleware = new CheckPlanLimits($service);
        $request = $this->createRequest('POST', '/api/admin/products');

        $response = $middleware->handle($request, fn ($req) => response('ok', 200));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_returns_402_when_check_limit_returns_int(): void
    {
        $service = $this->createMock(PlanLimitService::class);
        $service->method('checkLimit')->willReturn(6);

        app(TenantService::class)->setTenant($this->tenant);

        $middleware = new CheckPlanLimits($service);
        $request = $this->createRequest('POST', '/api/admin/products');

        $response = $middleware->handle($request, fn ($req) => response('ok', 200));

        $this->assertEquals(Response::HTTP_PAYMENT_REQUIRED, $response->getStatusCode());
    }

    public function test_402_response_contains_all_required_keys(): void
    {
        $service = $this->createMock(PlanLimitService::class);
        $service->method('checkLimit')->willReturn(6);

        app(TenantService::class)->setTenant($this->tenant);

        $middleware = new CheckPlanLimits($service);
        $request = $this->createRequest('POST', '/api/admin/products');

        $response = $middleware->handle($request, fn ($req) => response('ok', 200));
        $data = $response->getData(true);

        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('upgrade_url', $data);
        $this->assertArrayHasKey('current', $data);
        $this->assertArrayHasKey('limit', $data);
    }

    public function test_402_message_is_in_portuguese_and_mentions_limit(): void
    {
        $service = $this->createMock(PlanLimitService::class);
        $service->method('checkLimit')->willReturn(6);

        app(TenantService::class)->setTenant($this->tenant);

        $middleware = new CheckPlanLimits($service);
        $request = $this->createRequest('POST', '/api/admin/products');

        $response = $middleware->handle($request, fn ($req) => response('ok', 200));
        $data = $response->getData(true);

        $this->assertStringContainsString('6', $data['message']);
        $this->assertStringContainsString('produtos', $data['message']);
    }

    public function test_upgrade_url_is_admin_planos(): void
    {
        $service = $this->createMock(PlanLimitService::class);
        $service->method('checkLimit')->willReturn(6);

        app(TenantService::class)->setTenant($this->tenant);

        $middleware = new CheckPlanLimits($service);
        $request = $this->createRequest('POST', '/api/admin/products');

        $response = $middleware->handle($request, fn ($req) => response('ok', 200));
        $data = $response->getData(true);

        $this->assertEquals('/admin/planos', $data['upgrade_url']);
    }

    public function test_passes_through_when_get_tenant_returns_null(): void
    {
        app(TenantService::class)->clearTenant();

        $middleware = $this->makeMiddleware();
        $request = $this->createRequest('POST', '/api/admin/products');

        $response = $middleware->handle($request, fn ($req) => response('ok', 200));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_middleware_calls_check_limit_with_products_resource_type(): void
    {
        $service = $this->createMock(PlanLimitService::class);
        $service->expects($this->once())
            ->method('checkLimit')
            ->with($this->tenant, 'products')
            ->willReturn(null);

        app(TenantService::class)->setTenant($this->tenant);

        $middleware = new CheckPlanLimits($service);
        $request = $this->createRequest('POST', '/api/admin/products');

        $middleware->handle($request, fn ($req) => response('ok', 200));
    }

    public function test_passes_through_when_no_matching_route_param_or_path(): void
    {
        $service = $this->createMock(PlanLimitService::class);
        $service->expects($this->never())->method('checkLimit');

        app(TenantService::class)->setTenant($this->tenant);

        $middleware = new CheckPlanLimits($service);
        $request = $this->createRequest('GET', '/api/admin/dashboard');

        $response = $middleware->handle($request, fn ($req) => response('ok', 200));

        $this->assertEquals(200, $response->getStatusCode());
    }

    // ─── Integration tests (real PlanLimitService, real DB) ───

    public function test_integration_passes_when_below_product_limit(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6]);
        $this->createSubscription(['plan_type' => 'free', 'plan_status' => 'active']);

        for ($i = 0; $i < 5; $i++) {
            $this->createProduct();
        }

        app(TenantService::class)->setTenant($this->tenant);

        $middleware = $this->makeMiddleware();
        $request = $this->createRequest('POST', '/api/admin/products');

        $response = $middleware->handle($request, fn ($req) => response('ok', 200));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_integration_returns_402_when_at_product_limit(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6]);
        $this->createSubscription(['plan_type' => 'free', 'plan_status' => 'active']);

        for ($i = 0; $i < 6; $i++) {
            $this->createProduct();
        }

        app(TenantService::class)->setTenant($this->tenant);

        $middleware = $this->makeMiddleware();
        $request = $this->createRequest('POST', '/api/admin/products');

        $response = $middleware->handle($request, fn ($req) => response('ok', 200));

        $this->assertEquals(Response::HTTP_PAYMENT_REQUIRED, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals(6, $data['limit']);
        $this->assertEquals(6, $data['current']);
    }

    public function test_integration_returns_402_when_over_product_limit(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6]);
        $this->createSubscription(['plan_type' => 'free', 'plan_status' => 'active']);

        for ($i = 0; $i < 10; $i++) {
            $this->createProduct();
        }

        app(TenantService::class)->setTenant($this->tenant);

        $middleware = $this->makeMiddleware();
        $request = $this->createRequest('POST', '/api/admin/products');

        $response = $middleware->handle($request, fn ($req) => response('ok', 200));

        $this->assertEquals(Response::HTTP_PAYMENT_REQUIRED, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertEquals(6, $data['limit']);
    }

    public function test_integration_passes_for_premium_unlimited(): void
    {
        $this->createPlanLimit('premium', ['max_products' => 0]);
        $this->createSubscription(['plan_type' => 'premium', 'plan_status' => 'active']);

        for ($i = 0; $i < 100; $i++) {
            $this->createProduct();
        }

        app(TenantService::class)->setTenant($this->tenant);

        $middleware = $this->makeMiddleware();
        $request = $this->createRequest('POST', '/api/admin/products');

        $response = $middleware->handle($request, fn ($req) => response('ok', 200));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_integration_passes_when_no_subscription_uses_free_plan(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6]);

        for ($i = 0; $i < 5; $i++) {
            $this->createProduct();
        }

        app(TenantService::class)->setTenant($this->tenant);

        $middleware = $this->makeMiddleware();
        $request = $this->createRequest('POST', '/api/admin/products');

        $response = $middleware->handle($request, fn ($req) => response('ok', 200));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_integration_blocks_when_no_subscription_and_at_free_limit(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6]);

        for ($i = 0; $i < 6; $i++) {
            $this->createProduct();
        }

        app(TenantService::class)->setTenant($this->tenant);

        $middleware = $this->makeMiddleware();
        $request = $this->createRequest('POST', '/api/admin/products');

        $response = $middleware->handle($request, fn ($req) => response('ok', 200));

        $this->assertEquals(Response::HTTP_PAYMENT_REQUIRED, $response->getStatusCode());
    }

    public function test_integration_inactive_products_do_not_count_toward_limit(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6]);
        $this->createSubscription(['plan_type' => 'free', 'plan_status' => 'active']);

        for ($i = 0; $i < 6; $i++) {
            $this->createProduct(['is_active' => false]);
        }

        app(TenantService::class)->setTenant($this->tenant);

        $middleware = $this->makeMiddleware();
        $request = $this->createRequest('POST', '/api/admin/products');

        $response = $middleware->handle($request, fn ($req) => response('ok', 200));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_integration_resolves_resource_type_from_route_param(): void
    {
        $this->createPlanLimit('free', ['max_products' => 6]);
        $this->createSubscription(['plan_type' => 'free', 'plan_status' => 'active']);

        for ($i = 0; $i < 6; $i++) {
            $this->createProduct();
        }

        app(TenantService::class)->setTenant($this->tenant);

        $middleware = $this->makeMiddleware();
        $request = Request::create('/api/admin/products/some-uuid', 'PUT');
        $request->setRouteResolver(function () use ($request) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/api/admin/products/{product}', []);
            $route->bind($request);
            $route->setParameter('product', 'some-uuid');
            return $route;
        });

        $response = $middleware->handle($request, fn ($req) => response('ok', 200));

        $this->assertEquals(Response::HTTP_PAYMENT_REQUIRED, $response->getStatusCode());
    }
}
