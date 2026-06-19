<?php

use App\Jobs\SendTrialEmailsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('auth:cleanup-expired-tokens')->daily();
Schedule::job(SendTrialEmailsJob::class)->dailyAt('09:00')->timezone('America/Sao_Paulo');
Schedule::job(\App\Jobs\TrialExpirationJob::class)->dailyAt('10:00')->timezone('America/Sao_Paulo');
Schedule::job(\App\Jobs\ReplenishSpotsJob::class)->weeklyOn(
    config('spots.replenish_day', 'monday'),
    config('spots.replenish_time', '08:00')
)->timezone('America/Sao_Paulo');
