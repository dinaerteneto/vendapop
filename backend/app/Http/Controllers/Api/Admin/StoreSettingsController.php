<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\TenantService;
use Illuminate\Http\Request;

class StoreSettingsController extends Controller
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        // Admin routes are guarded by Auth, but we need to access the tenant of the logged user.
    }

    public function show(Request $request)
    {
        return $request->user()->tenant->load('socials');
    }

    public function update(Request $request)
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'name' => 'string',
            'whatsapp_number' => 'string',
            'store_url' => 'nullable|url',
            'primary_color' => 'nullable|string',
            'secondary_color' => 'nullable|string',
            'description' => 'nullable|string',
            'banner_message' => 'nullable|string',
            'banner_text_color_1' => 'nullable|string',
            'banner_text_color_2' => 'nullable|string',
            'banner_background_color' => 'nullable|string',
            'logo_url' => 'nullable|url',
            'address' => 'nullable|string',
            'email_contact' => 'nullable|email',
            'socials' => 'nullable|array',
            'socials.*.name' => 'required|string',
            'socials.*.url' => 'required|url',
            'socials.*.icon' => 'nullable|string',
        ]);

        $tenant->update($validated);

        if ($request->has('socials')) {
            $tenant->socials()->delete();
            foreach ($request->input('socials') as $social) {
                $tenant->socials()->create($social);
            }
        }

        return response()->json($tenant->load('socials'));
    }
}
