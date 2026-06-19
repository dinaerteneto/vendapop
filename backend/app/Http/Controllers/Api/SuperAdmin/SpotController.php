<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SpotBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpotController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $paginator = SpotBatch::query()
            ->orderBy('created_at', 'desc')
            ->paginate((int) ($validated['per_page'] ?? 20));

        return response()->json($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'total_spots' => 'required|integer|min:1',
            'batch_label' => 'nullable|string|max:255',
        ]);

        $batch = SpotBatch::create([
            'total_spots' => (int) $validated['total_spots'],
            'used_spots' => 0,
            'batch_label' => $validated['batch_label'] ?? null,
        ]);

        return response()->json($batch, 201);
    }

    public function replenish(int $id): JsonResponse
    {
        $batch = SpotBatch::findOrFail($id);
        $batch->update(['used_spots' => 0]);

        return response()->json($batch);
    }

    public function destroy(int $id): JsonResponse
    {
        $batch = SpotBatch::findOrFail($id);
        $batch->delete();

        return response()->json(['message' => 'Batch deleted']);
    }

    public function stats(): JsonResponse
    {
        $total = SpotBatch::sum('total_spots');
        $used = SpotBatch::sum('used_spots');
        $remaining = max(0, $total - $used);

        return response()->json([
            'total_spots' => (int) $total,
            'used_spots' => (int) $used,
            'remaining' => $remaining,
        ]);
    }
}
