<?php

namespace App\UseCases\SuperAdmin;

use App\Mail\WaitlistInviteMail;
use App\Models\Invite;
use App\Models\WaitlistEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ManageWaitlistUseCase
{
    public function list(
        int $perPage = 20,
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): LengthAwarePaginator {
        return WaitlistEntry::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('created_at', '<=', $dateTo))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function approve(int $entryId): array
    {
        $entry = WaitlistEntry::findOrFail($entryId);

        $invite = Invite::create([
            'code' => $this->uniqueCode(),
            'type' => 'manual',
            'created_by_tenant_id' => null,
            'max_uses' => 1,
            'current_uses' => 0,
            'expires_at' => now()->addDays(7),
        ]);

        $entry->update([
            'status' => 'approved',
            'invite_id' => $invite->id,
        ]);

        $inviteLink = config('services.frontend_url') . '/convite/' . $invite->code;

        Mail::to($entry->email)->send(new WaitlistInviteMail($invite->code, $inviteLink));

        return [
            'entry' => $entry->fresh(),
            'invite_code' => $invite->code,
            'invite_link' => $inviteLink,
            'email_sent' => true,
        ];
    }

    public function reject(int $entryId, ?string $reason = null): WaitlistEntry
    {
        $entry = WaitlistEntry::findOrFail($entryId);

        $entry->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return $entry;
    }

    public function batchApprove(array $ids): array
    {
        $results = [];

        foreach ($ids as $id) {
            $results[] = $this->approve($id);
        }

        return $results;
    }

    private function uniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (Invite::where('code', $code)->exists());

        return $code;
    }
}
