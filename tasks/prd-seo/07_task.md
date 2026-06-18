# Task 07 — Sitemap dinâmico no Laravel

**Status:** Pendente  
**Frente:** C  
**Dependências:** Nenhuma (independente do frontend)

## Objetivo

Criar o `SitemapController` no backend Laravel que serve um `sitemap.xml` dinâmico com todas as rotas fixas da plataforma, todas as lojas com onboarding concluído e todos os seus produtos ativos. O sitemap é cacheado por 1 hora.

## Contexto Técnico

- Rota em `routes/web.php` (não `api.php`) para servir em `/sitemap.xml` sem prefixo `/api`
- Filtro de tenants: `onboarding_completed = true` (campo adicionado em `2026_06_17_190001_add_onboarding_fields_to_tenants_table.php`)
- Filtro de produtos: `is_active = true` (campo na tabela de produtos)
- `APP_URL` no `.env` de produção é `https://vendapop.com.br`
- Cache via `Cache::remember` com TTL de 3600 segundos
- URL de produto: `/{tenant->slug}/product/{product->slug}` (confirmado pelo exemplo real)

## Arquivos a Criar/Modificar

### `backend/app/Http/Controllers/SitemapController.php` — CRIAR

```php
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
```

### `backend/routes/web.php` — MODIFICAR

```php
<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sitemap.xml', [SitemapController::class, 'show']);
```

## Testes de Verificação

```bash
# 1. Rota responde com XML válido
curl -s http://localhost:8000/sitemap.xml | head -5
# Esperado: <?xml version="1.0" encoding="UTF-8"?>...

# 2. XML é válido
curl -s http://localhost:8000/sitemap.xml | xmllint --format - | head -30
# Esperado: XML bem formatado sem erros

# 3. Contém rotas estáticas
curl -s http://localhost:8000/sitemap.xml | grep '<loc>'
# Esperado: entradas para /, /privacidade, /termos, /cookies, /direitos-lgpd

# 4. Contém lojas com onboarding_completed = true
# (verificar com uma loja que completou onboarding)
curl -s http://localhost:8000/sitemap.xml | grep 'casa-lar-imoveis'
# Esperado: entrada com /{slug}

# 5. Contém produtos ativos da loja
curl -s http://localhost:8000/sitemap.xml | grep 'product/'
# Esperado: entradas no formato /{slug}/product/{product-slug}

# 6. Lojas sem onboarding_completed NÃO aparecem
# Verificar manualmente que tenant com onboarding_completed = false está ausente

# 7. Content-Type correto
curl -sI http://localhost:8000/sitemap.xml | grep 'Content-Type'
# Esperado: application/xml

# 8. Header de cache
curl -sI http://localhost:8000/sitemap.xml | grep 'Cache-Control'
# Esperado: public, max-age=3600

# 9. Segundo request usa cache (verificar no tinker se necessário)
php artisan tinker
# Cache::has('sitemap_xml') // deve retornar true após primeiro request
```

**Validação W3C (após deploy):**
- https://www.xml-sitemaps.com/validate-xml-sitemap.html
