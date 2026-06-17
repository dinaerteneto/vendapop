<?php

namespace App\Jobs;

use App\Mail\TrialExpiringMail;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TrialExpirationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle(SubscriptionService $subscriptionService): void
    {
        Log::info('TrialExpirationJob: starting');

        // Expire trials that have ended
        $subscriptionService->expireTrials();

        // Notify trials expiring in exactly 7 days
        $expiringSoon = Subscription::where('plan_status', 'trial')
            ->whereNotNull('ends_at')
            ->whereDate('ends_at', now()->addDays(7)->toDateString())
            ->get();

        foreach ($expiringSoon as $subscription) {
            $tenant = $subscription->tenant;
            if ($tenant && $tenant->email_contact) {
                try {
                    Mail::to($tenant->email_contact)->send(
                        new TrialExpiringMail($tenant, 7)
                    );
                    Log::info('Trial expiry notification sent', [
                        'tenant_id' => $tenant->id,
                        'subscription_id' => $subscription->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send trial expiry email', [
                        'tenant_id' => $tenant->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        Log::info('TrialExpirationJob: complete', [
            'expired' => $subscriptionService->expireTrials() !== null,
            'notified' => $expiringSoon->count(),
        ]);
    }
}
