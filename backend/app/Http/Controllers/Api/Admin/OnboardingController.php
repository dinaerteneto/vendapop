<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'onboarding_step'      => 'nullable|integer|min:0|max:4',
            'onboarding_completed' => 'nullable|boolean',
        ]);

        $tenant = $request->user()->tenant;
        $tenant->update(array_filter($validated, fn($v) => $v !== null));

        return response()->json(['message' => 'ok', 'tenant' => $tenant]);
    }
}
