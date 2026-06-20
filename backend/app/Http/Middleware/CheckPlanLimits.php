<?php

namespace App\Http\Middleware;

use App\Services\PlanLimitService;
use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimits
{
    public function __construct(
        private PlanLimitService $planLimitService
    ) {}

    public function handle(Request $request, Closure $next, ?string $resourceType = null): Response
    {
        $tenant = app(TenantService::class)->getTenant()
            ?? $request->user()?->tenant;

        if (!$tenant) {
            return $next($request);
        }

        if ($resourceType === null) {
            $resourceType = $this->resolveResourceType($request);
        }

        if (!$resourceType) {
            return $next($request);
        }

        $limit = $this->planLimitService->checkLimit($tenant, $resourceType);

        if ($limit !== null) {
            $labels = [
                'products' => 'produtos',
                'categories' => 'categorias',
                'orders' => 'pedidos',
            ];

            $label = $labels[$resourceType] ?? $resourceType;

            return response()->json([
                'message' => "Você atingiu o limite de {$limit} {$label} do seu plano.",
                'upgrade_url' => '/admin/planos',
                'current' => $limit,
                'limit' => $limit,
            ], Response::HTTP_PAYMENT_REQUIRED);
        }

        return $next($request);
    }

    private function resolveResourceType(Request $request): ?string
    {
        $map = [
            'product' => 'products',
            'category' => 'categories',
            'order' => 'orders',
        ];

        try {
            foreach ($map as $param => $type) {
                if ($request->route($param)) {
                    return $type;
                }
            }
        } catch (\LogicException) {
            // Route not bound — fall through to path matching
        }

        $path = $request->path();
        foreach ($map as $param => $type) {
            if (str_contains($path, $type)) {
                return $type;
            }
        }

        return null;
    }
}
