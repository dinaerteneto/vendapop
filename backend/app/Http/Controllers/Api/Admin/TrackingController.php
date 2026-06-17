<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantTracking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        $trackings = TenantTracking::where('tenant_id', $tenant->id)->get();

        return response()->json($trackings);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => 'required|in:google_analytics,facebook_pixel',
            'tracking_code' => 'required|string|max:255',
        ]);

        $tenant = $request->user()->tenant;

        $tracking = TenantTracking::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'provider' => $validated['provider'],
            ],
            ['tracking_code' => $validated['tracking_code']]
        );

        return response()->json($tracking, 201);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $tenant = $request->user()->tenant;
        TenantTracking::where('tenant_id', $tenant->id)->where('id', $id)->delete();

        return response()->json(null, 204);
    }

    public function show(string $storeSlug): JsonResponse
    {
        $tenant = \App\Models\Tenant::where('slug', $storeSlug)->firstOrFail();

        if (!$tenant->is_active) {
            return response()->json([]);
        }

        $trackings = TenantTracking::where('tenant_id', $tenant->id)
            ->get()
            ->map(fn($t) => [
                'provider' => $t->provider,
                'tracking_code' => $t->tracking_code,
            ]);

        return response()->json($trackings);
    }
}
