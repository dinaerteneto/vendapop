<?php

namespace Tests\Feature\Spots;

use App\Jobs\ReplenishSpotsJob;
use App\Mail\WaitlistReplenishMail;
use App\Models\SpotBatch;
use App\Models\WaitlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReplenishSpotsJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SpotBatch::query()->delete();
        WaitlistEntry::query()->delete();
    }

    public function test_job_creates_new_spot_batch_with_correct_label(): void
    {
        ReplenishSpotsJob::dispatchSync();

        $batch = SpotBatch::first();
        $this->assertNotNull($batch);
        $this->assertEquals(config('spots.replenish_amount'), $batch->total_spots);
        $this->assertEquals(0, $batch->used_spots);
        $this->assertStringContainsString('weekly-' . now()->format('Y-m-d'), $batch->batch_label);
        $this->assertNotNull($batch->replenishes_at);
    }

    public function test_job_sends_bcc_email_to_pending_waitlist_entries(): void
    {
        Mail::fake();

        WaitlistEntry::create(['email' => 'user1@example.com', 'status' => 'pending']);
        WaitlistEntry::create(['email' => 'user2@example.com', 'status' => 'pending']);

        ReplenishSpotsJob::dispatchSync();

        Mail::assertSent(WaitlistReplenishMail::class, 1);
    }

    public function test_job_does_not_send_email_when_waitlist_has_zero_pending_entries(): void
    {
        Mail::fake();

        WaitlistEntry::create(['email' => 'user1@example.com', 'status' => 'approved']);

        ReplenishSpotsJob::dispatchSync();

        Mail::assertNothingSent();
    }

    public function test_job_logs_replenishment_event_with_correct_data(): void
    {
        $logs = [];
        Log::swap(new class($logs) {
            public function __construct(private array &$logs) {}
            public function info(string $message, array $context = []): void
            {
                $this->logs[] = ['message' => $message, 'context' => $context];
            }
            public function error(string $message, array $context = []): void {}
            public function __call(string $name, array $arguments): void {}
        });

        WaitlistEntry::create(['email' => 'user@example.com', 'status' => 'pending']);

        ReplenishSpotsJob::dispatchSync();

        $completion = collect($logs)->firstWhere('message', 'ReplenishSpotsJob: complete');
        $this->assertNotNull($completion);
        $this->assertEquals(config('spots.replenish_amount'), $completion['context']['spots_replenished']);
        $this->assertEquals(1, $completion['context']['waitlist_notified']);

        Log::clearResolvedInstance('log');
    }

    public function test_job_handles_mail_failure_gracefully_and_continues(): void
    {
        Mail::swap(new class {
            public function bcc($users, $name = null)
            {
                throw new \Exception('SMTP connection failed');
            }
            public function __call($name, $args) { return $this; }
        });

        WaitlistEntry::create(['email' => 'user@example.com', 'status' => 'pending']);

        ReplenishSpotsJob::dispatchSync();

        $this->assertEquals(1, SpotBatch::count());

        Log::clearResolvedInstance('log');
    }

    public function test_multiple_job_runs_create_distinct_batches(): void
    {
        ReplenishSpotsJob::dispatchSync();
        ReplenishSpotsJob::dispatchSync();

        $this->assertEquals(2, SpotBatch::count());
    }

    public function test_waitlist_replenish_mail_has_correct_subject(): void
    {
        $mail = new WaitlistReplenishMail;
        $envelope = $mail->envelope();

        $this->assertStringContainsString('Novas vagas disponíveis', $envelope->subject);
        $this->assertStringContainsString(config('app.name'), $envelope->subject);
    }
}
