<?php

namespace App\UseCases\SuperAdmin;

use App\Models\Feedback;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetFeedbacksUseCase
{
    public function list(
        int $perPage = 20,
        ?string $status = null,
        ?int $tenantId = null,
    ): LengthAwarePaginator {
        return Feedback::query()
            ->with('tenant')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function markStatus(int $feedbackId, string $status): Feedback
    {
        $feedback = Feedback::findOrFail($feedbackId);
        $feedback->update(['status' => $status]);

        return $feedback;
    }
}
