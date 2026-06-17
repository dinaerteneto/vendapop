<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\UseCases\SuperAdmin\ManageWaitlistUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    public function __construct(
        private ManageWaitlistUseCase $manageWaitlistUseCase,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'status' => 'nullable|in:pending,approved,rejected',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $paginator = $this->manageWaitlistUseCase->list(
            perPage: (int) ($validated['per_page'] ?? 20),
            status: $validated['status'] ?? null,
            dateFrom: $validated['date_from'] ?? null,
            dateTo: $validated['date_to'] ?? null,
        );

        return response()->json($paginator);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        if ($validated['status'] === 'approved') {
            $result = $this->manageWaitlistUseCase->approve($id);
            return response()->json($result);
        }

        $entry = $this->manageWaitlistUseCase->reject(
            $id,
            $validated['rejection_reason'] ?? null,
        );

        return response()->json($entry);
    }

    public function batchApprove(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:waitlist_entries,id',
        ]);

        $results = $this->manageWaitlistUseCase->batchApprove($validated['ids']);

        return response()->json($results);
    }
}
