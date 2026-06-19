<?php

namespace App\UseCases\Payment;

use App\Models\Subscription;
use App\Services\SubscriptionService;

class CancelSubscriptionUseCase
{
    public function __construct(
        private SubscriptionService $subscriptionService,
    ) {}

    public function execute(int $subscriptionId): Subscription
    {
        $subscription = Subscription::findOrFail($subscriptionId);

        return $this->subscriptionService->cancel($subscription);
    }
}
