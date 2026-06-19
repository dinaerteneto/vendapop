<?php

namespace App\Jobs;

use App\Mail\TrialCaseStudyMail;
use App\Mail\TrialEndedMail;
use App\Mail\TrialReminderMail;
use App\Mail\TrialTipsMail;
use App\Mail\TrialUrgentMail;
use App\Mail\TrialWelcomeMail;
use App\Models\Subscription;
use App\Models\TrialEmailsSent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTrialEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle(): void
    {
        $subscriptions = Subscription::with('tenant')
            ->where('plan_status', 'trial')
            ->where('ends_at', '>', now())
            ->get();

        Log::info('SendTrialEmailsJob: starting', ['trial_count' => $subscriptions->count()]);

        foreach ($subscriptions as $subscription) {
            try {
                $tenant = $subscription->tenant;
                $trialDay = (int) now()->diffInDays($subscription->started_at, true);
                $mailableClass = static::resolveMailable($trialDay);

                if ($mailableClass === null) {
                    continue;
                }

                $alreadySent = TrialEmailsSent::where('tenant_id', $tenant->id)
                    ->where('subscription_id', $subscription->id)
                    ->where('email_day', $trialDay)
                    ->exists();

                if ($alreadySent) {
                    Log::debug('SendTrialEmailsJob: already sent', [
                        'tenant_id' => $tenant->id,
                        'subscription_id' => $subscription->id,
                        'email_day' => $trialDay,
                    ]);
                    continue;
                }

                if (empty($tenant->email_contact)) {
                    Log::warning('SendTrialEmailsJob: tenant without email_contact', [
                        'tenant_id' => $tenant->id,
                        'subscription_id' => $subscription->id,
                    ]);
                    continue;
                }

                Mail::to($tenant->email_contact)->send(new $mailableClass($tenant));

                TrialEmailsSent::create([
                    'tenant_id' => $tenant->id,
                    'subscription_id' => $subscription->id,
                    'email_day' => $trialDay,
                    'sent_at' => now(),
                ]);

                Log::info('SendTrialEmailsJob: sent', [
                    'tenant_id' => $tenant->id,
                    'subscription_id' => $subscription->id,
                    'email_day' => $trialDay,
                    'mailable' => $mailableClass,
                ]);
            } catch (\Exception $e) {
                Log::error('SendTrialEmailsJob: failed for tenant', [
                    'tenant_id' => $subscription->tenant->id ?? null,
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('SendTrialEmailsJob: complete');
    }

    public static function resolveMailable(int $day): ?string
    {
        return match ($day) {
            0 => TrialWelcomeMail::class,
            7 => TrialCaseStudyMail::class,
            15 => TrialTipsMail::class,
            30 => TrialReminderMail::class,
            40 => TrialUrgentMail::class,
            45 => TrialEndedMail::class,
            default => null,
        };
    }
}
