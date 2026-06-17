<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\UseCases\SuperAdmin\GetFeedbacksUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function __construct(
        private GetFeedbacksUseCase $getFeedbacksUseCase,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'status' => 'nullable|in:unread,read,resolved',
            'tenant_id' => 'nullable|integer|exists:tenants,id',
        ]);

        $paginator = $this->getFeedbacksUseCase->list(
            perPage: (int) ($validated['per_page'] ?? 20),
            status: $validated['status'] ?? null,
            tenantId: isset($validated['tenant_id']) ? (int) $validated['tenant_id'] : null,
        );

        return response()->json($paginator);
    }

    public function show(int $id): JsonResponse
    {
        $feedback = \App\Models\Feedback::with('tenant')->findOrFail($id);

        return response()->json($feedback);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:read,resolved',
        ]);

        $feedback = $this->getFeedbacksUseCase->markStatus($id, $validated['status']);

        return response()->json($feedback);
    }
}
