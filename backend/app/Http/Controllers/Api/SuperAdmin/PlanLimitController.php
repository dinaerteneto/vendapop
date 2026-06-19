<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PlanLimit;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanLimitController extends Controller
{
    public function __construct(
        private readonly PlanLimitService $planLimitService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(PlanLimit::orderBy('plan_type')->get());
    }

    public function update(Request $request, string $planType): JsonResponse
    {
        $validPlanTypes = ['free', 'basic', 'professional', 'premium'];

        if (!in_array($planType, $validPlanTypes)) {
            return response()->json([
                'message' => 'The selected plan type is invalid.',
                'errors' => ['plan_type' => ['The selected plan type is invalid.']],
            ], 422);
        }

        $validated = $request->validate([
            'max_products' => 'nullable|integer|min:0',
        ]);

        $planLimit = PlanLimit::byPlanType($planType)->firstOrFail();

        $planLimit->update([
            'max_products' => $validated['max_products'] ?? 0,
        ]);

        $this->planLimitService->clearCache($planType);

        return response()->json($planLimit);
    }
}
