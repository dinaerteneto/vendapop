<?php

namespace App\Jobs;

use App\Contracts\SpotServiceInterface;
use App\Mail\WaitlistReplenishMail;
use App\Models\WaitlistEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReplenishSpotsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(SpotServiceInterface $spotService): void
    {
        Log::info('ReplenishSpotsJob: starting');

        $spotService->replenish();

        $pendingEmails = WaitlistEntry::where('status', 'pending')
            ->pluck('email')
            ->toArray();

        $emailCount = count($pendingEmails);

        if ($emailCount > 0) {
            try {
                Mail::bcc($pendingEmails)->send(new WaitlistReplenishMail);
                Log::info('Waitlist replenish notification sent', [
                    'recipients' => $emailCount,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send waitlist replenish email', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('ReplenishSpotsJob: complete', [
            'spots_replenished' => config('spots.replenish_amount'),
            'waitlist_notified' => $emailCount,
        ]);
    }
}
