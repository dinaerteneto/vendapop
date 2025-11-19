<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TenantService;

class StoreSettingsController extends Controller
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        // Admin routes are guarded by Auth, but we need to access the tenant of the logged user.
    }

    public function show(Request $request)
    {
        return $request->user()->tenant;
    }

    public function update(Request $request)
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'name' => 'string',
            'whatsapp_number' => 'string',
            'primary_color' => 'nullable|string',
            'secondary_color' => 'nullable|string',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|url',
            'address' => 'nullable|string',
            'email_contact' => 'nullable|email',
            'instagram_url' => 'nullable|url',
            'facebook_url' => 'nullable|url',
            'tiktok_url' => 'nullable|url',
        ]);

        // Slug usually not changeable easily as it breaks URLs, but could be allowed with redirects.
        // Let's block slug update for now.

        $tenant->update($validated);

        return response()->json($tenant);
    }
}
