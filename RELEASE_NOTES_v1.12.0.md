# Release Notes — v1.12.0

**Date:** 2026-06-18
**Branch:** feature/onboarding-wizard → main
**Scope:** Onboarding Wizard + ImageUploader Unificado

## New Features

### Onboarding Wizard (First-Access Setup)
- **Rota `/admin/setup`** com layout de duas colunas: formulário à esquerda, preview da loja à direita
- **Passo 1 — Identidade:** upload de logo (1:1) via drag & drop ou URL, 8 cores primárias pré-definidas com seletor de cor
- **Passo 2 — Vitrine:** grid 2×2 para editar produtos demo (nome inline + upload de foto 2:3)
- **Passo 3 — WhatsApp:** número com máscara brasileira, mensagem padrão do pedido, mockup de conversa
- **Passo 4 — Compartilhar:** copiar link da loja, instruções visuais (bio Instagram), botão concluir com animação de confete
- **Redirect automático:** login redireciona para `/admin/setup` se `onboarding_completed = false`
- **Banner de retomada:** no dashboard, com dismiss de 30 dias via localStorage
- **Navegação entre passos:** botões "Voltar" em todos os passos, "Pular" disponível nos passos 1 e 2

### ImageUploader Unificado
- **Componente único** para upload de imagem em produto (2:3), logo (1:1) e banner (16:9)
- **Drag & drop** de arquivo com preview + clique para selecionar
- **URL externa** via proxy server-side (`POST /api/admin/image-proxy`) — download automático para storage local
- **Crop obrigatório** via `react-easy-crop` — toda imagem passa pelo recorte antes de salvar
- **Preview persistente** após crop — sem flicker

### Demo Data (Loja Pré-povoada)
- **DemoDataService:** ao registrar novo tenant, cria automaticamente 2 categorias, 4 produtos e 1 banner demo
- **Flag `is_demo`** em products e categories para identificação
- **Fallback:** wizard mostra produtos ativos quando não há demo data (ex: tenants existentes)

### Backend — Novos Endpoints
- `PUT /api/admin/onboarding-status` — atualiza progresso do wizard (step + completed)
- `POST /api/admin/image-proxy` — faz fetch server-side de URL externa, salva localmente como arquivo
- `GET /api/proxy-image/{path}` — serve imagens proxy com CORS headers
- Login response agora inclui `tenant.onboarding_completed` e `tenant.onboarding_step`

### Testes
- **13 testes E2E (Playwright):** wizard flow completo + upload de imagem em produto/logo/banner
- **23 testes backend:** DemoDataService, OnboardingController, ImageProxyController, migrations
- **Frontend:** typecheck e build de produção sem erros

## Database Changes

| Migration | Tabela | Campos |
|-----------|--------|--------|
| `2026_06_17_190001` | tenants | `onboarding_completed` (bool), `onboarding_step` (tinyint) |
| `2026_06_17_190002` | products | `is_demo` (bool) |
| `2026_06_17_190003` | categories | `is_demo` (bool) |

## Technical Details

- **Dependencies novas:** `canvas-confetti`, `@playwright/test` (dev)
- **Storage fix:** PHP built-in server com router script para servir `/storage/*` (antes retornava 403)
- **Login local:** email verification skip em ambiente local para facilitar testes
- **Registro local:** campo `password` opcional em ambiente local (testes E2E)

## Deploy Checklist

- [x] `php artisan migrate`
- [x] `npm run build`
- [x] PHP backend tests: 107 pass, 2 pre-existing failures (SuperAdminSeeder)
- [x] Frontend typecheck: sem novos erros
- [x] E2E Playwright: 13/13 pass
- [ ] Criar symlink de storage (ou usar o router script do server.php)
- [ ] Verificar `APP_URL` no .env de produção para URLs de imagem corretas

## Git Log (feature/onboarding-wizard)

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
