# TechSpec — SEO & Indexabilidade

**PRD:** `tasks/prd-seo/prd.md`  
**Design spec:** `docs/superpowers/specs/2026-06-18-seo-design.md`  
**Data:** 2026-06-18  
**Stack:** React 18 + Vite 7 (frontend) · Laravel (backend)

---

## 1. Visão Técnica

Quatro frentes independentes de trabalho:

| Frente | Onde | Entregável |
|--------|------|------------|
| A — Meta tags dinâmicas | Frontend | Componente `SEOHead` + `HelmetProvider` |
| B — Pré-render da landing | Frontend | Script `scripts/prerender-landing.ts` + ajuste no build |
| C — Sitemap dinâmico | Backend Laravel | `SitemapController` + rota em `web.php` |
| D — Arquivos estáticos | Frontend | `robots.txt`, favicons, `manifest.json` atualizado |

---

## 2. Frente A — Meta Tags Dinâmicas

### 2.1 Dependência

```bash
npm install react-helmet-async
```

`tsx` **não** é necessário para esta frente (apenas para o script de pré-render).

### 2.2 `main.tsx` — HelmetProvider

Envolver o app inteiro com `HelmetProvider`:

```tsx
// src/main.tsx
import { HelmetProvider } from 'react-helmet-async'

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <HelmetProvider>
      <App />
    </HelmetProvider>
  </React.StrictMode>,
)
```

### 2.3 Componente `SEOHead`

**Caminho:** `src/components/common/SEOHead.tsx`

```tsx
import { Helmet } from 'react-helmet-async'

const APP_URL = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') ?? 'https://vendapop.com.br'

interface SEOHeadProps {
  title: string
  description?: string
  image?: string
  path?: string           // ex: '/casa-lar-imoveis' — sem domínio
  type?: 'website' | 'product'
  noIndex?: boolean
}

export function SEOHead({ title, description, image, path = '', type = 'website', noIndex = false }: SEOHeadProps) {
  const fullUrl = `${APP_URL}${path}`
  const ogImage = image ?? `${APP_URL}/og-image.png`

  return (
    <Helmet prioritizeSeoTags>
      <title>{title}</title>
      {description && <meta name="description" content={description} />}
      {noIndex && <meta name="robots" content="noindex, nofollow" />}

      {/* Open Graph */}
      <meta property="og:title" content={title} />
      {description && <meta property="og:description" content={description} />}
      <meta property="og:image" content={ogImage} />
      <meta property="og:url" content={fullUrl} />
      <meta property="og:type" content={type} />
      <meta property="og:site_name" content="VendaPop" />

      {/* Twitter Card */}
      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" content={title} />
      {description && <meta name="twitter:description" content={description} />}
      <meta name="twitter:image" content={ogImage} />

      {/* Canonical */}
      {!noIndex && <link rel="canonical" href={fullUrl} />}
    </Helmet>
  )
}
```

**Nota sobre `ogImage` fallback:** quando `image` não é passado, usa `/og-image.png` (asset estático da plataforma). Para lojas sem `logo_url` e produtos sem imagem, o chamador passa a URL do `IconController` existente (`{apiBase}/{storeSlug}/icon.png`) como `image`.

### 2.4 Uso por Página

#### Landing (`src/pages/Landing.tsx`)

```tsx
import { SEOHead } from '../components/common/SEOHead'

const Landing = () => (
  <div className="min-h-screen bg-white">
    <SEOHead
      title="VendaPop — Sua loja no WhatsApp"
      description="Monte sua loja em 5 minutos. Seus clientes navegam, escolhem e o pedido chega organizado no seu WhatsApp — sem calcular total na mão."
      path="/"
    />
    <HeroSection />
    {/* ... demais seções */}
  </div>
)
```

#### ProductList — página de loja (`src/pages/Shop/ProductList.tsx`)

O `storeInfo` já vem via `useOutletContext` do `PublicLayout`. Adicionar `SEOHead` após o loading inicial:

```tsx
const context = useOutletContext<{ storeInfo: any }>()
const { storeSlug } = useParams()
const apiBase = import.meta.env.VITE_API_BASE_URL ?? ''

const storeImage = context?.storeInfo?.logo_url
  ?? `${apiBase}/${storeSlug}/icon.png`

return (
  <div>
    {context?.storeInfo && (
      <SEOHead
        title={context.storeInfo.name}
        description={context.storeInfo.description ?? `Catálogo de ${context.storeInfo.name}`}
        image={storeImage}
        path={`/${storeSlug}`}
      />
    )}
    {/* ... resto do componente */}
  </div>
)
```

#### ProductDetail (`src/pages/Shop/ProductDetail.tsx`)

O produto já é carregado via `api.get(...)` no `useEffect`. Após o load:

```tsx
const apiBase = import.meta.env.VITE_API_BASE_URL ?? ''

// No JSX, após o produto estar carregado:
{product && (
  <SEOHead
    title={`${product.name} — ${context?.storeInfo?.name ?? ''}`}
    description={product.short_description ?? product.description}
    image={product.main_image_url ?? `${apiBase}/${storeSlug}/icon.png`}
    path={`/${storeSlug}/product/${product.slug}`}
    type="product"
  />
)}
```

#### Páginas com noIndex

Auth e Dashboard: adicionar `<SEOHead title="..." noIndex />` em cada página. Legais: `<SEOHead title="Política de Privacidade — VendaPop" path="/privacidade" />`.

---

## 3. Frente B — Pré-render da Landing

### 3.1 Dependência

```bash
npm install -D tsx
```

### 3.2 Script `scripts/prerender-landing.ts`

**Caminho:** `frontend/scripts/prerender-landing.ts`

```ts
import { createServer } from 'node:http'
import { readFileSync, writeFileSync } from 'node:fs'
import { resolve } from 'node:path'
import { chromium } from 'playwright'
import handler from 'serve-handler' // serve-handler já está no Playwright runtime

async function prerender() {
  const distDir = resolve(process.cwd(), 'dist')

  // 1. Subir servidor HTTP local servindo dist/
  const server = createServer((req, res) => handler(req, res, { public: distDir }))
  await new Promise<void>(r => server.listen(4173, r))

  try {
    // 2. Playwright captura a landing renderizada
    const browser = await chromium.launch()
    const page = await browser.newPage()

    await page.goto('http://localhost:4173/', { waitUntil: 'networkidle' })

    // Aguarda o Helmet injetar as meta tags
    await page.waitForSelector('meta[property="og:title"]', { timeout: 10_000 })

    const html = await page.evaluate(() => document.documentElement.outerHTML)
    await browser.close()

    // 3. Sobrescreve dist/index.html com o HTML estático
    writeFileSync(resolve(distDir, 'index.html'), `<!DOCTYPE html>\n${html}`)
    console.log('✓ Landing pré-renderizada com sucesso')
  } finally {
    server.close()
  }
}

prerender().catch(err => {
  console.error('Falha no pré-render:', err)
  process.exit(1)
})
```

**Nota:** `serve-handler` é uma dependência transitiva do Playwright — está disponível sem instalação adicional. Se não estiver, instalar `serve-handler` como devDependency.

### 3.3 Atualizar `package.json`

```json
"scripts": {
  "dev": "vite",
  "build": "vite build && tsx scripts/prerender-landing.ts",
  "build:no-prerender": "vite build",
  ...
}
```

O `build:no-prerender` serve para desenvolvimento local sem esperar o Playwright.

---

## 4. Frente C — Sitemap Dinâmico (Laravel)

### 4.1 Rota em `routes/web.php`

```php
use App\Http\Controllers\SitemapController;

Route::get('/sitemap.xml', [SitemapController::class, 'show']);
```

A rota fica em `web.php` (não `api.php`) para ser servida em `vendapop.com.br/sitemap.xml` sem o prefixo `/api`.

### 4.2 `SitemapController`

**Caminho:** `app/Http/Controllers/SitemapController.php`

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
        $baseUrl = config('app.url');
        $now = now()->toAtomString();

        $staticRoutes = ['/', '/privacidade', '/termos', '/cookies', '/direitos-lgpd'];

        $tenants = Tenant::where('onboarding_completed', true)
            ->with(['products' => fn($q) => $q->where('is_active', true)->select('tenant_id', 'slug', 'updated_at')])
            ->select('id', 'slug', 'updated_at')
            ->get();

        $urls = [];

        // Rotas estáticas
        foreach ($staticRoutes as $path) {
            $urls[] = $this->urlEntry("{$baseUrl}{$path}", $now, 'weekly');
        }

        // Lojas e produtos
        foreach ($tenants as $tenant) {
            $urls[] = $this->urlEntry("{$baseUrl}/{$tenant->slug}", $tenant->updated_at->toAtomString(), 'daily');

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
        return "<url><loc>{$loc}</loc><lastmod>{$lastmod}</lastmod><changefreq>{$changefreq}</changefreq></url>";
    }
}
```

**Cache:** 1 hora via `Cache::remember`. O cache é invalidado automaticamente pelo TTL — não há necessidade de invalidação manual neste momento.

### 4.3 Configuração `config/app.url`

Verificar que `APP_URL=https://vendapop.com.br` está no `.env` de produção (já deve estar dado o contexto existente).

---

## 5. Frente D — Arquivos Estáticos

### 5.1 `robots.txt`

**Caminho:** `frontend/public/robots.txt`

```
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /superadmin/
Disallow: /convite/
Sitemap: https://vendapop.com.br/sitemap.xml
```

**Nota:** As rotas auth (`/admin/login`, `/admin/register` etc.) já estão sob `/admin/` — o `Disallow: /admin/` cobre todas.

### 5.2 Favicon — `index.html`

Substituir:
```html
<!-- ANTES -->
<link rel="icon" type="image/svg+xml" href="/vite.svg" />
```

Por:
```html
<!-- DEPOIS -->
<link rel="icon" type="image/svg+xml" href="/favicon.svg" />
<link rel="icon" type="image/x-icon" href="/favicon.ico" sizes="32x32" />
<link rel="apple-touch-icon" href="/apple-touch-icon.png" />
```

**Assets necessários** (fornecidos pela equipe de design):
- `frontend/public/favicon.svg`
- `frontend/public/favicon.ico`
- `frontend/public/apple-touch-icon.png` (180×180px)
- `frontend/public/og-image.png` (1200×630px — proporção padrão OG)
- `frontend/public/icon-192.png` (para manifest)
- `frontend/public/icon-512.png` (para manifest)

### 5.3 `manifest.json` atualizado

**Caminho:** `frontend/public/manifest.json`

```json
{
  "name": "VendaPop",
  "short_name": "VendaPop",
  "description": "Sua loja online com pedidos direto no WhatsApp",
  "start_url": "/",
  "scope": "/",
  "display": "standalone",
  "theme_color": "#7c3aed",
  "background_color": "#ffffff",
  "icons": [
    { "src": "/favicon.ico", "sizes": "64x64 32x32 24x24 16x16", "type": "image/x-icon" },
    { "src": "/icon-192.png", "sizes": "192x192", "type": "image/png", "purpose": "any" },
    { "src": "/icon-512.png", "sizes": "512x512", "type": "image/png", "purpose": "any maskable" }
  ]
}
```

---

## 6. Estrutura de Arquivos

```
frontend/
├── public/
│   ├── robots.txt               ← NOVO
│   ├── og-image.png             ← NOVO (asset a fornecer)
│   ├── favicon.svg              ← NOVO (asset a fornecer)
│   ├── favicon.ico              ← NOVO (asset a fornecer)
│   ├── apple-touch-icon.png     ← NOVO (asset a fornecer)
│   ├── icon-192.png             ← NOVO (asset a fornecer)
│   ├── icon-512.png             ← NOVO (asset a fornecer)
│   └── manifest.json            ← ATUALIZAR
├── scripts/
│   └── prerender-landing.ts     ← NOVO
├── src/
│   ├── main.tsx                 ← ATUALIZAR (HelmetProvider)
│   ├── components/common/
│   │   └── SEOHead.tsx          ← NOVO
│   └── pages/
│       ├── Landing.tsx          ← ATUALIZAR
│       ├── Shop/
│       │   ├── ProductList.tsx  ← ATUALIZAR
│       │   └── ProductDetail.tsx ← ATUALIZAR
│       ├── AuthPages/*.tsx      ← ATUALIZAR (noIndex)
│       ├── Dashboard/*.tsx      ← ATUALIZAR (noIndex)
│       └── legal/*.tsx          ← ATUALIZAR
└── index.html                   ← ATUALIZAR (favicons)

backend/
├── app/Http/Controllers/
│   └── SitemapController.php    ← NOVO
└── routes/
    └── web.php                  ← ATUALIZAR
```

---

## 7. Variáveis de Ambiente

| Variável | Onde | Uso |
|----------|------|-----|
| `VITE_API_BASE_URL` | frontend `.env` | Derivar APP_URL: `.replace('/api', '')` |
| `APP_URL` | backend `.env` | Base das URLs no sitemap — já deve existir |

---

## 8. Ordem de Implementação Recomendada

1. **Frente D** (robots.txt + index.html) — sem dependências, merge rápido
2. **Frente A** (SEOHead) — depende do npm install react-helmet-async
3. **Frente C** (Sitemap Laravel) — independente do frontend
4. **Frente B** (Pré-render) — depende de Frente A estar funcionando (precisa das meta tags injetadas)

---

## 9. Critérios de Verificação

```bash
# robots.txt acessível
curl https://vendapop.com.br/robots.txt

# Sitemap com lojas e produtos
curl https://vendapop.com.br/sitemap.xml | xmllint --format -

# OG tags na landing (HTML estático — sem JS)
curl -s https://vendapop.com.br | grep 'og:title'

# OG tags em loja (requer JS — usar puppeteer ou ferramenta de preview)
# https://www.opengraph.xyz/url/https://vendapop.com.br/casa-lar-imoveis

# Preview no WhatsApp: compartilhar link e verificar card visual
```

---

## 10. Decisões Técnicas

| Decisão | Alternativa descartada | Motivo |
|---------|------------------------|--------|
| `react-helmet-async` em vez de `react-helmet` | `react-helmet` (deprecated) | Suporte a React 18, thread-safe, mantido ativamente |
| Pré-render com Playwright em vez de `vite-plugin-prerender` | `vite-plugin-prerender` | Playwright já está no projeto — zero dependência nova |
| Sitemap em `web.php` em vez de `api.php` | `api.php` com rota alternativa | Evita prefixo `/api` na URL pública |
| Cache de 1h no sitemap via `Cache::remember` | Sem cache / cache por evento | Simplicidade — sitemap é tolerante a dados de até 1h atrás |
| Derivar APP_URL de `VITE_API_BASE_URL` | Criar `VITE_APP_URL` separado | Evita nova variável de ambiente — menos configuração |
