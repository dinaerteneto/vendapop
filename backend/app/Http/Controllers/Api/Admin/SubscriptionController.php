<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\PlanLimitService;
use App\Services\SubscriptionService;
use App\UseCases\Payment\CancelSubscriptionUseCase;
use App\UseCases\Payment\CreateCheckoutUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private PlanLimitService $planLimitService,
        private CreateCheckoutUseCase $createCheckoutUseCase,
        private CancelSubscriptionUseCase $cancelSubscriptionUseCase,
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

        $latestTransaction = $subscription
            ? PaymentTransaction::where('subscription_id', $subscription->id)->latest()->first()
            : null;

        if (!$subscription) {
            return response()->json([
                'plan_type' => 'free',
                'plan_status' => null,
                'days_remaining' => null,
                'is_active' => false,
                'is_pending' => false,
                'gateway_status' => null,
                'next_billing_date' => null,
                'current_transaction' => null,
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
            'is_pending' => (bool) $subscription->is_pending,
            'gateway_status' => $latestTransaction?->status,
            'next_billing_date' => $subscription->ends_at?->format('Y-m-d'),
            'current_transaction' => $latestTransaction ? [
                'id' => $latestTransaction->id,
                'transaction_id' => $latestTransaction->transaction_id,
                'plan_type' => $latestTransaction->plan_type,
                'amount' => $latestTransaction->amount,
                'status' => $latestTransaction->status,
                'gateway' => $latestTransaction->gateway,
                'paid_at' => $latestTransaction->paid_at,
            ] : null,
            'limits' => $limitData,
        ]);
    }

    public function createCheckout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_type' => 'required|string|in:basic,professional,premium',
            'billing_cycle' => 'required|string|in:monthly,annual',
        ]);

        $tenant = $request->user()->tenant;
        $subscription = $this->subscriptionService->getActive($tenant);

        if (!$subscription) {
            return response()->json(['message' => 'No active subscription found.'], 400);
        }

        if ($subscription->is_pending) {
            return response()->json(['message' => 'A pending payment already exists for this subscription.'], 409);
        }

        $currentPlanType = $this->planLimitService->resolvePlanType($tenant);
        $planOrder = ['free' => 0, 'basic' => 1, 'professional' => 2, 'premium' => 3];

        if (($planOrder[$validated['plan_type']] ?? 0) < ($planOrder[$currentPlanType] ?? 0)) {
            return response()->json(['message' => 'Downgrade is not allowed.'], 400);
        }

        $response = $this->createCheckoutUseCase->execute(
            subscriptionId: $subscription->id,
            planType: $validated['plan_type'] . '_' . $validated['billing_cycle'],
            returnUrl: $request->input('return_url', config('app.frontend_url', config('app.url')) . '/admin/planos/sucesso'),
            cancelUrl: $request->input('cancel_url', config('app.frontend_url', config('app.url')) . '/admin/planos/erro'),
        );

        return response()->json([
            'checkout_url' => $response->checkout_url,
            'subscription_id' => $subscription->id,
            'gateway' => config('services.payment.gateway', 'mercadopago'),
            'gateway_payment_id' => $response->transaction_id,
            'status' => 'pending',
        ], 201);
    }

    public function cancel(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        $subscription = $this->subscriptionService->getActive($tenant);

        if (!$subscription) {
            return response()->json(['message' => 'No active subscription found.'], 400);
        }

        if ((int) $subscription->tenant_id !== (int) $tenant->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $this->cancelSubscriptionUseCase->execute($subscription->id);

        return response()->json(['status' => 'cancelled']);
    }

    public function dismissBanner(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        $tenant->update(['plan_expiry_banner_dismissed_at' => now()]);

        return response()->json(['message' => 'OK']);
    }
}
