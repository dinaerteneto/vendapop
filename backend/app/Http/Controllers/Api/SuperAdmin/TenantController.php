<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\UseCases\SuperAdmin\GetTenantDetailUseCase;
use App\UseCases\SuperAdmin\GetTenantsUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(
        private GetTenantsUseCase $getTenantsUseCase,
        private GetTenantDetailUseCase $getTenantDetailUseCase,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'sort_by' => 'nullable|in:created_at,name,updated_at',
            'sort_direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'plan_type' => 'nullable|in:free,basic,professional,premium',
            'plan_status' => 'nullable|in:active,trial,cancelled,expired',
        ]);

        $paginator = $this->getTenantsUseCase->execute(
            search: $validated['search'] ?? '',
            sortBy: $validated['sort_by'] ?? 'created_at',
            sortDirection: $validated['sort_direction'] ?? 'desc',
            perPage: (int) ($validated['per_page'] ?? 20),
            planType: $validated['plan_type'] ?? null,
            planStatus: $validated['plan_status'] ?? null,
        );

        return response()->json($paginator);
    }

    public function show(int $id): JsonResponse
    {
        $tenant = $this->getTenantDetailUseCase->execute($id);

        return response()->json($tenant);
    }
}
