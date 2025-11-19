<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    public function show($storeSlug)
    {
        $tenant = Tenant::where('slug', $storeSlug)->firstOrFail();

        // Default values if tenant doesn't have specific branding
        $primaryColor = $tenant->primary_color ?? '#7c3aed';
        $backgroundColor = $tenant->banner_background_color ?? '#ffffff';

        // Use tenant logo or a default placeholder
        $iconUrl = $tenant->logo_url ?? 'https://via.placeholder.com/512x512.png?text=' . urlencode($tenant->name);

        return response()->json([
            'name' => $tenant->name,
            'short_name' => substr($tenant->name, 0, 12),
            'start_url' => "/{$storeSlug}",
            'display' => 'standalone',
            'background_color' => $backgroundColor,
            'theme_color' => $primaryColor,
            'orientation' => 'portrait',
            'icons' => [
                [
                    'src' => $iconUrl,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => $iconUrl,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ]
            ]
        ]);
    }
}

