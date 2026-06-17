<?php

namespace App\Services;

use App\Contracts\InviteServiceInterface;
use App\Models\Invite;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InviteService implements InviteServiceInterface
{
    public function generateManual(Tenant $creator, int $count = 1): array
    {
        $invites = [];

        for ($i = 0; $i < $count; $i++) {
            $invites[] = Invite::create([
                'code' => $this->uniqueCode(),
                'type' => 'manual',
                'created_by_tenant_id' => $creator->id,
                'max_uses' => 1,
                'current_uses' => 0,
                'expires_at' => now()->addDays(7),
            ]);
        }

        return $invites;
    }

    public function createPublicLink(int $maxUses, ?int $expiresInHours = 48): Invite
    {
        return Invite::create([
            'code' => $this->uniqueCode(),
            'type' => 'public',
            'created_by_tenant_id' => null,
            'max_uses' => $maxUses,
            'current_uses' => 0,
            'expires_at' => now()->addHours($expiresInHours ?? 48),
        ]);
    }

    public function validate(string $code): Invite
    {
        $invite = Invite::where('code', $code)->first();

        if (!$invite) {
            throw ValidationException::withMessages([
                'invite_code' => ['Código de convite inválido.'],
            ]);
        }

        if ($invite->isExpired()) {
            throw ValidationException::withMessages([
                'invite_code' => ['Este convite expirou.'],
            ]);
        }

        if ($invite->isExhausted()) {
            throw ValidationException::withMessages([
                'invite_code' => ['Vagas esgotadas para este convite.'],
            ]);
        }

        if ($invite->isInactive()) {
            throw ValidationException::withMessages([
                'invite_code' => ['Este convite foi desativado.'],
            ]);
        }

        return $invite;
    }

    public function consume(Invite $invite, Tenant $tenant): void
    {
        $invite->increment('current_uses');
    }

    public function remainingForTenant(Tenant $tenant): int
    {
        // Only manually-selected founders (invite_source=manual) can generate invites
        $isManualFounder = \App\Models\Subscription::where('tenant_id', $tenant->id)
            ->where('invite_source', 'manual')
            ->whereIn('plan_status', ['active', 'trial'])
            ->exists();

        if (!$isManualFounder) {
            return 0;
        }

        $used = Invite::where('created_by_tenant_id', $tenant->id)
            ->where('type', 'manual')
            ->count();

        return max(0, 2 - $used);
    }

    private function uniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (Invite::where('code', $code)->exists());

        return $code;
    }
}
