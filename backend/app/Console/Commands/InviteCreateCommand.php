<?php

namespace App\Console\Commands;

use App\Services\InviteService;
use Illuminate\Console\Command;

class InviteCreateCommand extends Command
{
    protected $signature = 'invite:create 
                            {type : manual|public}
                            {--count=1 : Number of invites (manual only)}
                            {--slots=5 : Max uses (public only)}
                            {--hours=48 : Expiry in hours (public only)}';

    protected $description = 'Generate invite codes for the platform';

    public function handle(InviteService $inviteService): int
    {
        $type = $this->argument('type');

        if ($type === 'manual') {
            return $this->createManual($inviteService);
        }

        if ($type === 'public') {
            return $this->createPublic($inviteService);
        }

        $this->error("Invalid type. Use 'manual' or 'public'.");
        return self::FAILURE;
    }

    private function createManual(InviteService $inviteService): int
    {
        $count = (int) $this->option('count');

        $this->info("Generating {$count} manual invite(s)...");

        // Create a dummy tenant for the admin to generate invites
        $adminTenant = \App\Models\Tenant::where('slug', 'vendapop')->first();
        if (!$adminTenant) {
            $this->error("Run 'php artisan vendapop:admin' first to create the admin tenant.");
            return self::FAILURE;
        }

        for ($i = 1; $i <= $count; $i++) {
            $invites = $inviteService->generateManual($adminTenant, 1);
            $invite = $invites[0];
            $url = config('services.frontend_url') . "/convite/{$invite->code}";
            $this->line("  [{$i}] {$invite->code} → {$url}");
        }

        $this->newLine();
        $this->info("Done. Send each link to a different person.");
        return self::SUCCESS;
    }

    private function createPublic(InviteService $inviteService): int
    {
        $slots = (int) $this->option('slots');
        $hours = (int) $this->option('hours');

        $invite = $inviteService->createPublicLink($slots, $hours);
        $url = config('services.frontend_url') . "/convite/{$invite->code}";

        $this->info("Public link created:");
        $this->line("  Code: {$invite->code}");
        $this->line("  URL:  {$url}");
        $this->line("  Slots: {$slots}");
        $this->line("  Expires: {$hours}h from now ({$invite->expires_at->format('d/m/Y H:i')})");
        $this->newLine();
        $this->info("Share this link in WhatsApp groups. It auto-closes when {$slots} people register.");

        return self::SUCCESS;
    }
}
