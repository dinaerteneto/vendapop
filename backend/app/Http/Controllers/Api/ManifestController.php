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
        $backgroundColor = $tenant->secondary_color ?? '#ffffff';

        // Use tenant logo or generate a default
        $iconUrl = $tenant->logo_url;
        
        // Se não tiver logo, criar um placeholder com as iniciais
        if (!$iconUrl) {
            $initials = strtoupper(substr($tenant->name, 0, 2));
            $iconUrl = "https://ui-avatars.com/api/?name={$initials}&size=512&background=" . str_replace('#', '', $primaryColor) . "&color=ffffff&bold=true";
        }

        $baseUrl = env('FRONTEND_URL', 'http://localhost:5173');
        
        return response()->json([
            'name' => $tenant->name,
            'short_name' => substr($tenant->name, 0, 12),
            'description' => $tenant->description ?? "Catálogo digital de {$tenant->name}",
            'start_url' => "/{$storeSlug}",
            'scope' => "/{$storeSlug}",
            'display' => 'standalone',
            'background_color' => $backgroundColor,
            'theme_color' => $primaryColor,
            'orientation' => 'portrait',
            'icons' => [
                [
                    'src' => $iconUrl,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ],
                [
                    'src' => $iconUrl,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ]
            ],
            'categories' => ['shopping', 'business'],
            'screenshots' => [],
            'prefer_related_applications' => false
        ];
        
        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
    }
}

