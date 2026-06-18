# Release Notes — VendaPop

---

## v1.12.2 — Ícones PWA offline com iniciais

**Data:** 2026-06-18 | **Branch:** `feature/offline-pwa-icons`

### Novidades

**Geração offline de ícones PWA com iniciais da loja**
- Novo endpoint `GET /api/{storeSlug}/icon.png?size=192|512` gera PNG em runtime com GD/FreeType
- Círculo preenchido com a cor primária da loja + iniciais centralizadas em branco (DejaVu Sans Bold)
- Substitui dependência externa `ui-avatars.com` por geração local 100% offline
- Cache de 7 dias nos headers (`max-age=604800, immutable`)

**Lógica de fallback no ManifestController**
- Loja **com** logo: manifest usa `logo_url` (comportamento mantido)
- Loja **sem** logo: manifest aponta para o endpoint local `/api/{slug}/icon.png`

**Docker: GD + FreeType**
- `Dockerfile.backend` agora inclui `freetype-dev`, `libpng-dev`, `libjpeg-turbo-dev`, `ttf-dejavu` e extensão `gd`

### Git Log

```
6afb178 feat: offline PWA icon generation with store initials using GD
```

---

## v1.12.1 — Correções nos Seeders

**Data:** 2026-06-18 | **Branch:** `fix/product-seeder-slug-duplicate`

### Correções

**Slug conflicts com Spatie Sluggable (UNIQUE constraint)**
- Modelos `Product` e `Category` usam `HasSlug` do Spatie, que regenera slugs a partir do `name`. Vários seeders usavam slugs hardcoded que não batiam com o que o sluggable produz, causando `Integrity constraint violation` em re-runs com `updateOrCreate`.
- Corrigido nos seeders: `OficinaMecanica` (4 categorias), `BoloCaseiro` (2 categorias), `Encomendas` (1 categoria), `Product` (1 produto).

**ProductSeeder: bulk products com `withoutEvents`**
- Os 50 produtos gerados por `generateBulkProducts` usam slugs randômicos que diferem a cada execução. Substituído `updateOrCreate` por `firstOrCreate` dentro de `Product::withoutEvents()` para evitar que o sluggable interfira. UUID gerado explicitamente.

**PizzariaSeeder: FK `order_items`**
- A limpeza prévia do tenant deletava `products` antes de `order_items`, violando a FK. Agora `order_items` são deletados primeiro.

### Git Log

```
052cd8a fix(seeder): delete order_items before products in PizzariaSeeder cleanup
610675b fix(seeders): align hardcoded slugs with Spatie sluggable output
98712a0 fix(seeder): prevent sluggable slug conflicts in ProductSeeder
```

---

## v1.12.0 — Onboarding Wizard & ImageUploader Unificado

**Data:** 2026-06-18 | **Branch:** `feature/onboarding-wizard`

### Novidades

**Onboarding Wizard (Primeiro Acesso)**
- Rota `/admin/setup` com layout de duas colunas: formulário à esquerda, preview da loja à direita
- 4 passos: Identidade (logo + cor), Vitrine (produtos demo), WhatsApp (número + mensagem), Compartilhar (link + confete)
- Redirect automático do login para `/admin/setup` se `onboarding_completed = false`
- Banner de retomada no dashboard com dismiss de 30 dias
- Navegação com botões "Voltar" e "Pular" entre passos

**ImageUploader Unificado**
- Componente único para upload em produto (2:3), logo (1:1) e banner (16:9)
- Drag & drop + URL externa via proxy server-side (`POST /api/admin/image-proxy`)
- Crop obrigatório via `react-easy-crop` — toda imagem passa pelo recorte

**Demo Data (Loja Pré-povoada)**
- `DemoDataService`: ao registrar, cria 2 categorias, 4 produtos e 1 banner demo
- Flag `is_demo` em `products` e `categories`

**Novos Endpoints**
- `PUT /api/admin/onboarding-status` — progresso do wizard
- `POST /api/admin/image-proxy` — fetch server-side de URL externa
- `GET /api/proxy-image/{path}` — serve imagens proxy com CORS
- Login response inclui `tenant.onboarding_completed` e `tenant.onboarding_step`

### Database

| Migration | Tabela | Campos |
|-----------|--------|--------|
| `2026_06_17_190001` | tenants | `onboarding_completed` (bool), `onboarding_step` (tinyint) |
| `2026_06_17_190002` | products | `is_demo` (bool) |
| `2026_06_17_190003` | categories | `is_demo` (bool) |

### Dependências Novas
- `canvas-confetti` — animação de confete no passo final
- `@playwright/test` — testes E2E (dev)

### Testes
- Backend: 107 pass (2 pre-existing failures)
- E2E Playwright: 13/13 pass

### Git Log

```
585ca9d fix(frontend): prevent ImageCropper buttons from submitting parent form
ff9bc40 fix(frontend): wizard upload and navigation fixes
1bea99d fix(backend): serve storage files via router script
b8f17be test(e2e): Playwright E2E tests + auth adjustments for testing
ee738fa feat(frontend): TASKS 06-11 - Wizard de Onboarding completo
5c1f913 feat(frontend): TASK 05 - Integrar ImageUploader nos formularios existentes
11fbdb0 feat(frontend): TASK 04 - ImageUploader unificado
b4aa226 feat(backend): TASK 03 - OnboardingController + ImageProxyController + login response
87dbe07 feat(backend): TASK 02 - DemoDataService + hook no RegistrationController
bf41f16 feat(backend): TASK 01 - migrations e models para onboarding wizard
```

---

## v1.11.0 — Landing Page: Animações & Cards Redesenhados

**Data:** 2026-06-17 | **Branch:** `feature/landing-page-movement`

### Novidades

**Slideshow no Mockup do Celular**
- O telefone no Hero Section exibe slideshow automático com as 4 lojas exemplo
- Transição vertical suave (slide-up) com pausa de ~3s por loja
- Visível em todos os breakpoints (mobile e desktop, responsivo)
- Animação 100% CSS — zero dependências, zero impacto no bundle
- Respeita `prefers-reduced-motion`: desabilita animação

**Cards de Lojas Maiores**
- Cartas aumentadas de 192px para 384px (dobro de altura)
- Crop reposicionado para mostrar banner + categorias
- Header e nome da loja cortados — foco no conteúdo visual

**Rename**
- "Pizzaria Boa Massa" → "Boa Massa" (slug: `/boa-massa`)

### Arquivos Alterados

| Arquivo | Mudança |
|---|---|
| `tailwind.config.js` | +`@keyframes phone-slide`, +`animate-phone-slide`, +prefers-reduced-motion |
| `PhoneSlideshow.tsx` | Novo — 4 imagens com animação CSS |
| `HeroSection.tsx` | Telefone estático → `<PhoneSlideshow />` |
| `CaseSection.tsx` | Cards `h-96` + crop `-140px` + rename Boa Massa |
