<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupExpiredTokens extends Command
{
    protected $signature = 'auth:cleanup-expired-tokens';
    protected $description = 'Remove expired tokens from otp_tokens and email_verifications tables';

    public function handle(): int
    {
        $deletedOtp = DB::table('otp_tokens')
            ->where(function ($query) {
                $query->where('expires_at', '<', now())
                    ->orWhereNotNull('used_at');
            })
            ->where('created_at', '<', now()->subDays(7))
            ->delete();

        $deletedEmailVerifications = DB::table('email_verifications')
            ->where('created_at', '<', now()->subHours(48))
            ->delete();

        $total = $deletedOtp + $deletedEmailVerifications;
        $this->info("Cleaned up {$total} expired tokens ({$deletedOtp} otp, {$deletedEmailVerifications} email verifications).");
        Log::info("auth:cleanup-expired-tokens removed {$total} records.");

        return Command::SUCCESS;
    }
}
