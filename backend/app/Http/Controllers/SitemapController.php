<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function show()
    {
        $xml = Cache::remember('sitemap_xml', 3600, function () {
            return $this->generateXml();
        });

        return response($xml, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    private function generateXml(): string
    {
        $baseUrl = rtrim(config('app.url'), '/');

        $staticRoutes = [
            ['path' => '/',              'changefreq' => 'weekly'],
            ['path' => '/privacidade',   'changefreq' => 'monthly'],
            ['path' => '/termos',        'changefreq' => 'monthly'],
            ['path' => '/cookies',       'changefreq' => 'monthly'],
            ['path' => '/direitos-lgpd', 'changefreq' => 'monthly'],
        ];

        $tenants = Tenant::where('onboarding_completed', true)
            ->with(['products' => fn($q) => $q->where('is_active', true)->select('tenant_id', 'slug', 'updated_at')])
            ->select('id', 'slug', 'updated_at')
            ->get();

        $urls = [];

        foreach ($staticRoutes as $route) {
            $urls[] = $this->urlEntry("{$baseUrl}{$route['path']}", now()->toAtomString(), $route['changefreq']);
        }

        foreach ($tenants as $tenant) {
            $urls[] = $this->urlEntry(
                "{$baseUrl}/{$tenant->slug}",
                $tenant->updated_at->toAtomString(),
                'daily'
            );

            foreach ($tenant->products as $product) {
                $urls[] = $this->urlEntry(
                    "{$baseUrl}/{$tenant->slug}/product/{$product->slug}",
                    $product->updated_at->toAtomString(),
                    'weekly'
                );
            }
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            . implode('', $urls)
            . '</urlset>';
    }

    private function urlEntry(string $loc, string $lastmod, string $changefreq): string
    {
        return "<url>"
            . "<loc>" . htmlspecialchars($loc) . "</loc>"
            . "<lastmod>{$lastmod}</lastmod>"
            . "<changefreq>{$changefreq}</changefreq>"
            . "</url>";
    }
}
