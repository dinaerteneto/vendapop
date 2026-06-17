<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Invite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InviteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'type' => 'nullable|in:manual,public',
        ]);

        $paginator = Invite::query()
            ->when($validated['type'] ?? null, fn ($q) => $q->where('type', $validated['type']))
            ->orderBy('created_at', 'desc')
            ->paginate((int) ($validated['per_page'] ?? 20));

        return response()->json($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'count' => 'required|integer|min:1|max:20',
        ]);

        $invites = [];
        for ($i = 0; $i < (int) $validated['count']; $i++) {
            $invites[] = Invite::create([
                'code' => $this->uniqueCode(),
                'type' => 'manual',
                'created_by_tenant_id' => null,
                'max_uses' => 1,
                'current_uses' => 0,
                'expires_at' => now()->addDays(7),
            ]);
        }

        return response()->json($invites, 201);
    }

    public function toggle(int $id): JsonResponse
    {
        $invite = Invite::findOrFail($id);
        $invite->update(['is_active' => !$invite->is_active]);

        return response()->json($invite);
    }

    private function uniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (Invite::where('code', $code)->exists());

        return $code;
    }
}
