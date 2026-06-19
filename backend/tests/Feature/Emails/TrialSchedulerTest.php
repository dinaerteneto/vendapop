<?php

namespace Tests\Feature\Emails;

use App\Jobs\SendTrialEmailsJob;
use App\Jobs\TrialExpirationJob;
use Illuminate\Support\Facades\Schedule;
use Tests\TestCase;

class TrialSchedulerTest extends TestCase
{
    public function test_send_trial_emails_job_registered_at_nine_am_brt(): void
    {
        $events = collect(Schedule::events())->filter(fn ($e) => str_contains($e->description, SendTrialEmailsJob::class));

        $this->assertCount(1, $events, 'SendTrialEmailsJob must be registered exactly once in the scheduler');

        $event = $events->first();

        $this->assertEquals('0 9 * * *', $event->expression, 'SendTrialEmailsJob must run daily at 09:00');
        $this->assertEquals('America/Sao_Paulo', $event->timezone, 'SendTrialEmailsJob must use America/Sao_Paulo timezone');
    }

    public function test_trial_expiration_job_registered_at_ten_am_brt(): void
    {
        $events = collect(Schedule::events())->filter(fn ($e) => str_contains($e->description, TrialExpirationJob::class));

        $this->assertCount(1, $events, 'TrialExpirationJob must be registered exactly once in the scheduler');

        $event = $events->first();

        $this->assertEquals('0 10 * * *', $event->expression, 'TrialExpirationJob must run daily at 10:00');
        $this->assertEquals('America/Sao_Paulo', $event->timezone, 'TrialExpirationJob must use America/Sao_Paulo timezone');
    }

    public function test_trial_expiration_job_not_using_bare_daily(): void
    {
        $events = collect(Schedule::events())->filter(fn ($e) => str_contains($e->description, TrialExpirationJob::class));

        $this->assertCount(1, $events);

        $event = $events->first();

        $this->assertNotEquals('0 0 * * *', $event->expression, 'TrialExpirationJob must NOT use bare daily() (midnight default)');
    }

    public function test_both_jobs_are_recognized_by_scheduler(): void
    {
        $events = Schedule::events();

        $descriptions = array_map(fn ($e) => $e->description, $events);

        $hasSend = false;
        $hasExpiration = false;

        foreach ($descriptions as $desc) {
            if (str_contains($desc, SendTrialEmailsJob::class)) {
                $hasSend = true;
            }
            if (str_contains($desc, TrialExpirationJob::class)) {
                $hasExpiration = true;
            }
        }

        $this->assertTrue($hasSend, 'SendTrialEmailsJob must be recognized by the scheduler');
        $this->assertTrue($hasExpiration, 'TrialExpirationJob must be recognized by the scheduler');
    }

    public function test_one_hour_gap_between_jobs(): void
    {
        $events = collect(Schedule::events());

        $sendEvent = $events->first(fn ($e) => str_contains($e->description, SendTrialEmailsJob::class));
        $expirationEvent = $events->first(fn ($e) => str_contains($e->description, TrialExpirationJob::class));

        $this->assertNotNull($sendEvent, 'SendTrialEmailsJob must be registered');
        $this->assertNotNull($expirationEvent, 'TrialExpirationJob must be registered');

        $this->assertEquals('0 9 * * *', $sendEvent->expression);
        $this->assertEquals('0 10 * * *', $expirationEvent->expression);
        $this->assertEquals('America/Sao_Paulo', $sendEvent->timezone);
        $this->assertEquals('America/Sao_Paulo', $expirationEvent->timezone);
    }
}
