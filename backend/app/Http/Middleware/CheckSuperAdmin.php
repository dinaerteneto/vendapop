<?php

namespace App\Http\Middleware;

use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    public function __construct(
        private TenantService $tenantService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_super_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->tenantService->clearTenant();

        return $next($request);
    }
}
