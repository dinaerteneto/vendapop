<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function show(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        $subscription = $this->subscriptionService->getActive($tenant);

        if (!$subscription) {
            return response()->json([
                'plan_type' => 'free',
                'plan_status' => null,
                'days_remaining' => null,
                'is_active' => false,
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
        ]);
    }

    public function dismissBanner(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        $tenant->update(['plan_expiry_banner_dismissed_at' => now()]);

        return response()->json(['message' => 'OK']);
    }
}
