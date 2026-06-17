<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\InviteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InviteController extends Controller
{
    protected InviteService $inviteService;

    public function __construct(InviteService $inviteService)
    {
        $this->inviteService = $inviteService;
    }

    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        $invites = \App\Models\Invite::where('created_by_tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($invites);
    }

    public function store(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        $isAdmin = $tenant->slug === 'popvenda';
        $remaining = $isAdmin ? 999 : $this->inviteService->remainingForTenant($tenant);

        if (!$isAdmin && $remaining <= 0) {
            return response()->json([
                'message' => 'Você já usou todos os seus convites.',
            ], 403);
        }

        $invites = $this->inviteService->generateManual($tenant, 1);
        $invite = $invites[0];

        Log::info('Invite created', [
            'code' => $invite->code,
            'type' => $invite->type,
            'tenant_id' => $tenant->id,
        ]);

        return response()->json([
            'code' => $invite->code,
            'url' => config('app.frontend_url', 'https://popvenda.com.br') . '/convite/' . $invite->code,
            'expires_at' => $invite->expires_at,
            'remaining' => $remaining - 1,
        ], 201);
    }

    public function createPublicLink(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'max_uses' => 'required|integer|min:1|max:100',
            'expires_in_hours' => 'nullable|integer|min:1|max:168',
        ]);

        $invite = $this->inviteService->createPublicLink(
            $validated['max_uses'],
            $validated['expires_in_hours'] ?? 48
        );

        Log::info('Public invite link created', [
            'code' => $invite->code,
            'max_uses' => $invite->max_uses,
        ]);

        return response()->json([
            'code' => $invite->code,
            'url' => config('app.frontend_url', 'https://popvenda.com.br') . '/convite/' . $invite->code,
            'max_uses' => $invite->max_uses,
            'expires_at' => $invite->expires_at,
        ], 201);
    }

    public function listPublicLinks(Request $request): JsonResponse
    {
        $links = \App\Models\Invite::where('type', 'public')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($links);
    }

    public function remaining(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        $remaining = $tenant->slug === 'popvenda'
            ? 999
            : $this->inviteService->remainingForTenant($tenant);

        return response()->json(['remaining' => $remaining]);
    }

    public function validateCode(string $code): JsonResponse
    {
        try {
            $invite = $this->inviteService->validate($code);

            return response()->json([
                'valid' => true,
                'code' => $invite->code,
                'type' => $invite->type,
                'remaining' => $invite->slotsRemaining(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'valid' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
