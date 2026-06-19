# Especificacao Tecnica — VendaPop

> **Data:** 19 de Junho de 2026  
> **Versao:** v1.13.0

---

## Visao Geral

O VendaPop e um **micro-SaaS multi-tenant B2B** para catalogos online com checkout via WhatsApp. Atende qualquer negocio que venda pelo Instagram/WhatsApp: moda, imobiliarias, eletronicos, alimentacao, encomendas, afiliados.

Cada lojista ganha sua propria vitrine online com carrinho, checkout e pedido caindo organizado no WhatsApp. A plataforma e PWA, com painel admin completo e customizacao de identidade visual por loja.

---

## Stack

| Camada | Tecnologia |
|--------|-----------|
| Backend API | Laravel 12, PHP 8.2+ |
| Frontend SPA | React 18, TypeScript, Vite, Tailwind CSS |
| PWA | Service worker customizado, manifest dinamico por loja |
| Banco | MySQL 8.0 |
| Infra dev | Docker Compose (PHP-FPM + Nginx + MySQL + MailHog) |
| Infra prod | VPS Ubuntu + Nginx + PHP-FPM + MySQL + SSL (Let's Encrypt) |
| Autenticacao | Laravel Sanctum, Google OAuth (Socialite), OTP + Magic Link |
| Queue | Database driver |
| Cache | File driver (prod), array (testes) |
| Storage | Local (futuro: S3/Cloudflare R2) |
| Testes | PHPUnit (backend, SQLite in-memory), Playwright (E2E) |

---

## Arquitetura

### Backend — SOLID

```
Controller (fino, orquestra HTTP)
  -> UseCase (logica de alto nivel)
    -> Service (regras de negocio reutilizaveis)
      -> Repository (acesso a dados com interfaces)
```

- **Controllers:** `App/Http/Controllers/` — finos, delegam para UseCases
- **UseCases:** `App/UseCases/` — orquestram servicos
- **Services:** `App/Services/` — regras de negocio
- **Repositories:** `App/Repositories/Interfaces/` + `Eloquent/` — abstraem dados
- **Models:** `App/Models/` — Eloquent com trait `BelongsToTenant`

### Frontend — SPA + PWA

- **Pages:** `src/pages/` — agrupadas por dominio (Shop, Dashboard, Auth, Landing)
- **Components:** `src/components/` — reutilizaveis por categoria (ui, landing, ecommerce)
- **Services:** `src/services/` — camada de API (axios)
- **Contexts:** Context API para estado global (CartContext, SidebarContext)
- **Hooks:** `src/hooks/` — custom hooks (useScrollReveal, useStoreSlug)
- **Layout:** `AppLayout` (admin), `PublicLayout` (loja publica)

---

## Multi-Tenancy

- Tabela `tenants` e a raiz de todos os dados
- Resolucao por slug na URL: `https://vendapop.com.br/{storeSlug}`
- Middleware `CheckTenant` resolve o tenant e injeta `tenant_id` em todas as queries
- Trait `BelongsToTenant` aplica escopo automaticamente nos models
- Superadmin usa `CheckSuperAdmin` que bypassa o escopo de tenant

---

## Modelo de Dados

### Principais Entidades

| Tabela | Descricao | Campos-chave |
|--------|-----------|-------------|
| `tenants` | Lojas/catalogos | name, slug, plan_type, plan_status, onboarding_completed, pix_key, business_sector |
| `users` | Admins das lojas | tenant_id, email, google_id, is_super_admin |
| `categories` | Categorias de produtos | tenant_id, name, slug, is_demo |
| `products` | Produtos do catalogo | tenant_id, category_id, price, promotional_price, action_type, is_demo |
| `product_images` | Imagens dos produtos | product_id, image_url, image_path, is_external, order |
| `product_attributes` | Atributos por tenant | tenant_id, name, slug |
| `product_variations` | Combinacoes com estoque | product_id, attributes (json), stock, price, sku |
| `customers` | Clientes finais | tenant_id, name, email, phone |
| `orders` | Pedidos | tenant_id, customer_id, order_number, total_amount, status, notes |
| `order_items` | Itens do pedido | order_id, product_id, unit_price, quantity, attributes |
| `rotating_banners` | Banners da loja | tenant_id, image_url, link_url, is_active, order |
| `subscriptions` | Assinaturas/planos | tenant_id, plan_type, plan_status, invite_source, ends_at |
| `invites` | Convites | code, type, max_uses, current_uses, created_by_tenant, expires_at |
| `waitlist_entries` | Lista de espera | email, status, approved_at |
| `tenant_trackings` | Tags GA/Pixel | tenant_id, provider, tracking_code |
| `feedbacks` | Feedback lojista->admin | tenant_id, user_id, message, status |
| `push_subscriptions` | Push notif. admin | user_id, endpoint, keys |
| `customer_push_subscriptions` | Push notif. cliente | order_uuid, endpoint, keys |
| `otp_tokens` | Login sem senha | email, token, type, expires_at |

### Relacionamentos

```
Tenant (1) --- (N) User, Category, Product, Customer, Order, RotatingBanner, Subscription, Invite
Category (1) --- (N) Product
Product (1) --- (N) ProductImage, ProductVariation
Order (1) --- (N) OrderItem
OrderItem (1) --- (1) Product
```

---

## Rotas da API

### Publicas (cliente da loja)

Prefixo: `/api/{storeSlug}`

| Metodo | Rota | Descricao |
|--------|------|-----------|
| GET | `/products` | Lista produtos ativos |
| GET | `/products/{productSlug}` | Detalhes do produto |
| GET | `/categories` | Lista categorias |
| GET | `/banners` | Banners ativos |
| GET | `/trackings` | Tags de rastreamento |
| POST | `/checkout` | Cria pedido |
| GET | `/order/{orderUuid}` | Rastreamento de pedido |

### Admin (autenticado, escopo do tenant)

Prefixo: `/api/admin`

| Metodo | Rota | Descricao |
|--------|------|-----------|
| POST | `/login` | Autenticacao |
| POST | `/logout` | Logout |
| GET/POST/PUT/DELETE | `/products` | CRUD produtos |
| GET/POST/PUT/DELETE | `/categories` | CRUD categorias |
| GET | `/orders` | Lista pedidos com filtros |
| GET | `/orders/{id}` | Detalhe do pedido |
| PUT | `/orders/{id}/status` | Atualiza status |
| GET/PUT | `/store` | Configuracoes da loja |
| GET/POST/DELETE | `/banners` | Gestao de banners |
| GET/POST/DELETE | `/trackings` | Tags GA/Pixel |
| GET | `/subscription` | Plano atual |
| POST | `/invites/manual` | Gerar convite |
| GET/POST | `/waitlist` | Gestao da waitlist |
| POST | `/feedback` | Enviar feedback |
| POST | `/image-proxy` | Fetch server-side de imagem externa |
| PUT | `/onboarding-status` | Progresso do wizard |

### Superadmin

Prefixo: `/api/superadmin`

| Metodo | Rota | Descricao |
|--------|------|-----------|
| POST | `/login` | Login superadmin |
| GET | `/tenants` | Lista tenants |
| GET | `/tenants/{id}` | Detalhe tenant |
| GET/POST | `/waitlist` | Gestao waitlist |
| GET | `/feedbacks` | Inbox feedback |
| GET | `/invites` | Lista invites |

### Rotas Web (HTML/XML)

| Rota | Descricao |
|------|-----------|
| `/sitemap.xml` | Sitemap com lojas e produtos |
| `/robots.txt` | Robots.txt servido pelo Vite |
| `/{storeSlug}/manifest.json` | Manifest dinamico por loja |
| `/{storeSlug}/icon.png` | Icone PWA com iniciais (GD) |

---

## Fronte nd — Rotas SPA

### Publicas
| Rota | Pagina |
|------|--------|
| `/` | Landing page |
| `/{storeSlug}` | Catalogo da loja |
| `/{storeSlug}/product/{productSlug}` | Detalhe do produto |
| `/{storeSlug}/cart` | Carrinho |
| `/{storeSlug}/checkout` | Checkout |
| `/{storeSlug}/order/{orderUuid}` | Rastreamento |
| `/privacidade`, `/termos`, `/cookies`, `/direitos-lgpd` | Paginas legais |

### Admin (autenticado)
| Rota | Pagina |
|------|--------|
| `/admin` | Dashboard |
| `/admin/setup` | Onboarding wizard |
| `/admin/products` | Lista produtos |
| `/admin/products/new`, `/:id` | Form produto |
| `/admin/categories` | Lista categorias |
| `/admin/orders` | Lista pedidos |
| `/admin/customers` | Lista clientes |
| `/admin/store-settings` | Configuracoes |
| `/admin/banners` | Banners |

### Superadmin
| Rota | Pagina |
|------|--------|
| `/superadmin` | Dashboard |
| `/superadmin/tenants` | Tenants |
| `/superadmin/waitlist` | Waitlist |
| `/superadmin/feedback` | Feedback |
| `/superadmin/invites` | Invites |

---

## SEO

- **Landing page:** pre-renderizada via Playwright no build (`prerender-landing.ts`)
- **Meta tags:** `react-helmet-async` com componente `SEOHead`
- **Lojas e produtos:** meta tags dinamicas (title, description, OG, Twitter Card, canonical)
- **JSON-LD:** Organization, WebSite, HowTo, FAQPage na landing
- **Sitemap:** dinamico via Laravel (`SitemapController`)
- **robots.txt:** estatico, block em `/admin`, `/dashboard`, `/super-admin`, `/login`, `/register`

---

## Seguranca

- **Autenticacao:** Laravel Sanctum (tokens API)
- **Multi-tenancy:** escopo `tenant_id` em todas as queries
- **reCAPTCHA v3:** registro, reenvio de verificacao, OTP (opcional em local)
- **LGPD:** checkbox obrigatorio no registro, `terms_accepted_at` no banco
- **CORS:** configurado para o dominio do frontend
- **Headers:** X-Frame-Options, X-Content-Type-Options, cache-control

---

## Deploy

- **Guia completo:** `docs/DEPLOY.md`
- **Producao:** `vendapop.dynasolutions.com.br`
- **Containeres prod:** backend (PHP-FPM), nginx, mysql
- **SSL:** Let's Encrypt com renovacao automatica
- **Pipeline:** Bitbucket Pipelines para CI/CD

---

## Testes

- **Backend:** PHPUnit com SQLite in-memory, ~107 testes
- **E2E:** Playwright, 13 cenarios
- **Manuais:** checklist em `docs/testes-manuais.md`
- **Isolamento:** testes nao tocam banco de dev

---

## Documentacao Relacionada

| Documento | Conteudo |
|-----------|----------|
| `roadmap.md` | Visao do produto, status das features, proximos passos |
| `estrategia-monetizacao.md` | Planos, precos, projecao de receita |
| `playbook-prospeccao.md` | Guia pratico de aquisicao de clientes por setor |
| `analise-concorrentes.md` | Posicionamento competitivo |
| `backlog.md` | Tarefas pendentes e prioridades |
| `DEPLOY.md` | Guia completo de deploy em VPS |
| `testes-manuais.md` | Checklist de testes manuais |
