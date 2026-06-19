<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlanLimitService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private PlanLimitService $planLimitService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        $subscription = $this->subscriptionService->getActive($tenant);

        $planType = $this->planLimitService->resolvePlanType($tenant);
        $limits = $this->planLimitService->getLimits($planType);

        $currentProducts = $this->planLimitService->countActiveProducts($tenant);
        $currentCategories = $this->planLimitService->countActiveCategories($tenant);

        $rawMaxProducts = $limits?->max_products;

        $isUnlimited = $rawMaxProducts === 0 || $rawMaxProducts === null;

        $limitData = [
            'max_products' => $isUnlimited ? null : $rawMaxProducts,
            'max_categories' => $limits?->max_categories,
            'allow_checkout_pix' => $limits?->allow_checkout_pix ?? false,
            'allow_checkout_credit_card' => $limits?->allow_checkout_credit_card ?? false,
            'allow_analytics' => $limits?->allow_analytics ?? false,
            'max_staff_accounts' => $limits?->max_staff_accounts,
            'max_orders_per_month' => $limits?->max_orders_per_month,
            'current_products' => $currentProducts,
            'current_categories' => $currentCategories,
            'can_add_product' => $this->planLimitService->canAddProducts($planType, $currentProducts),
            'can_add_category' => $this->planLimitService->canAddCategories($planType, $currentCategories),
        ];

        if (!$subscription) {
            return response()->json([
                'plan_type' => 'free',
                'plan_status' => null,
                'days_remaining' => null,
                'is_active' => false,
                'limits' => $limitData,
            ]);
        }

        return response()->json([
            'plan_type' => $subscription->plan_type,
            'plan_status' => $subscription->plan_status,
            'invite_source' => $subscription->invite_source,
            'started_at' => $subscription->started_at,
            'ends_at' => $subscription->ends_at,
            'days_remaining' => $subscription->daysRemaining(),
            'is_active' => $this->subscriptionService->isActive($tenant),
            'limits' => $limitData,
        ]);
    }

    public function dismissBanner(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        $tenant->update(['plan_expiry_banner_dismissed_at' => now()]);

        return response()->json(['message' => 'OK']);
    }
}
