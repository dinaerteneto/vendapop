<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function handle(Request $request, Closure $next)
    {
        $tenant = app(\App\Services\TenantService::class)->getTenant();

        if ($tenant && !$this->subscriptionService->isActive($tenant)) {
            // Tenant has no active subscription — apply Free tier restrictions
            // For now, we just pass through. Enforcement will be added in Phase 2.
            // The active subscription info is available via $request->attributes for controllers.
        }

        return $next($request);
    }
}
