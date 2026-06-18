# SEO — VendaPop

**Data:** 2026-06-18
**Escopo:** Meta tags, pré-render da landing, robots.txt, sitemap dinâmico, favicon

---

## Contexto

O VendaPop é uma SPA React + Vite sem qualquer configuração de SEO: sem meta description, sem Open Graph, sem Twitter Card, sem robots.txt, sem sitemap, e com favicon padrão do Vite. O objetivo é implementar SEO completo cobrindo a landing page da plataforma, as páginas de loja (`/{slug}`) e as páginas de produto (`/{slug}/product/{product-slug}`).

O manifest dinâmico por loja já está implementado (`ManifestController` + `PublicLayout.tsx`) e está fora do escopo deste PRD.

---

## Objetivos

1. Landing page indexável com HTML estático (pré-renderizado)
2. Páginas de loja e produto com meta tags dinâmicas (title, description, OG, Twitter Card, canonical)
3. robots.txt configurado corretamente
4. Sitemap dinâmico servido pelo Laravel com lojas e produtos ativos
5. Favicon real do VendaPop substituindo o padrão do Vite

---

## Arquitetura

### 1. Gerenciamento de Meta Tags — react-helmet-async

**Instalação:** `react-helmet-async`

**Configuração global:** `HelmetProvider` envolve o app no `main.tsx`.

**Componente `SEOHead`** em `src/components/common/SEOHead.tsx`:

```typescript
interface SEOHeadProps {
  title: string
  description: string
  image?: string
  url?: string
  type?: 'website' | 'product'
  noIndex?: boolean
}
```

Tags geradas por instância:
- `<title>` e `<meta name="description">`
- `og:title`, `og:description`, `og:image`, `og:url`, `og:type`
- `twitter:card`, `twitter:title`, `twitter:description`, `twitter:image`
- `<link rel="canonical">`

**Uso por página:**

| Página | title | description | image | noIndex |
|--------|-------|-------------|-------|---------|
| Landing | `VendaPop — Sua loja no WhatsApp` | Texto de venda fixo | Imagem OG estática `/og-image.png` | false |
| ProductList (`/{slug}`) | `{nome da loja}` | `{descrição da loja}` | `{logo_url da loja}` | false |
| ProductDetail (`/{slug}/product/{slug}`) | `{nome do produto} — {nome da loja}` | `{descrição do produto}` | `{foto do produto}` | false |
| Auth (login, registro, etc.) | `{título da página} — VendaPop` | — | — | true |
| Dashboard | `{título da página} — VendaPop` | — | — | true |
| Legal (privacidade, termos, etc.) | `{título} — VendaPop` | — | — | false |

Os dados de loja e produto já são carregados via API nas páginas `ProductList` e `ProductDetail` — o `SEOHead` consome esses dados já disponíveis no estado da página.

---

### 2. Pré-render da Landing Page

**Objetivo:** servir HTML estático para crawlers na rota `/`, sem depender de execução de JavaScript.

**Fluxo:**
1. `vite build` gera a SPA normalmente em `dist/`
2. Script `scripts/prerender-landing.ts` executa pós-build:
   - Sobe servidor HTTP local servindo `dist/`
   - Playwright navega para `/` e aguarda hydration completa
   - Captura `document.documentElement.outerHTML`
   - Sobrescreve `dist/index.html` com o HTML capturado (incluindo as meta tags já injetadas pelo react-helmet-async)
   - Encerra o servidor
3. O `dist/` resultante é o artefato de deploy

**Comando de build atualizado** em `package.json`:
```json
"build": "vite build && tsx scripts/prerender-landing.ts"
```

**Dependência:** `tsx` (se não existir no projeto).
**Playwright** já está instalado no projeto.

---

### 3. robots.txt

Arquivo estático em `frontend/public/robots.txt`:

```
User-agent: *
Allow: /
Disallow: /dashboard/
Disallow: /admin/
Disallow: /super-admin/
Disallow: /login
Disallow: /register
Disallow: /forgot-password
Disallow: /reset-password
Sitemap: https://vendapop.com.br/sitemap.xml
```

---

### 4. Sitemap Dinâmico — Laravel

**Nova rota pública** em `routes/web.php` (não `api.php`, para servir sem prefixo `/api`):

```
GET /sitemap.xml → SitemapController@show
```

**`SitemapController`** consulta:
- Rotas fixas: `/`, `/privacidade`, `/termos`, `/cookies`, `/lgpd`
- `Tenant::where('active', true)->get()` → gera entradas `/{slug}`
- Para cada tenant ativo, `Product::where('tenant_id', $tenant->id)->where('active', true)->get()` → gera entradas `/{slug}/product/{product-slug}`

**Response:**
```php
return response($xml, 200)
    ->header('Content-Type', 'application/xml')
    ->header('Cache-Control', 'public, max-age=3600');
```

**Formato XML:** sitemap padrão `xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"` com `<loc>`, `<lastmod>` e `<changefreq>`.

---

### 5. Favicon do VendaPop

Substituir o `/vite.svg` padrão por assets reais do VendaPop:

**Assets necessários** (a serem fornecidos):
- `public/favicon.ico` — 16x16 e 32x32
- `public/favicon.svg` — SVG escalável
- `public/apple-touch-icon.png` — 180x180

**`index.html` atualizado:**
```html
<link rel="icon" type="image/svg+xml" href="/favicon.svg" />
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
<link rel="apple-touch-icon" href="/apple-touch-icon.png" />
```

**`manifest.json` atualizado** com nome, descrição e ícones corretos do VendaPop (192x192 e 512x512).

---

## Fora do Escopo

- Manifest dinâmico por loja — já implementado (`ManifestController`)
- OG image gerada dinamicamente por servidor — versão futura
- SSR completo / migração para Next.js ou Remix — versão futura
- Custom domain por loja (plano premium) — PRD separado

---

## Dependências

| Pacote | Onde | Motivo |
|--------|------|--------|
| `react-helmet-async` | frontend | Gerenciamento de meta tags por página |
| `tsx` | frontend (devDependency) | Executar script de pré-render |

---

## Critérios de Aceite

- [ ] Compartilhar `vendapop.com.br` no WhatsApp exibe preview com imagem, título e descrição
- [ ] Compartilhar `vendapop.com.br/casa-lar-imoveis` exibe logo e nome da loja como preview
- [ ] Compartilhar URL de produto exibe foto e nome do produto como preview
- [ ] Google Search Console não reporta erros de indexação na landing
- [ ] `curl https://vendapop.com.br/sitemap.xml` retorna XML válido com lojas e produtos
- [ ] `curl https://vendapop.com.br/robots.txt` retorna o arquivo correto
- [ ] Páginas de dashboard e auth têm `noindex`
- [ ] Favicon aparece corretamente em abas do browser e bookmarks
