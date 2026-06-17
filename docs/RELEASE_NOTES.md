# Release Notes - VendaPop

## 📋 Histórico de Versões

### v1.10.1 (17 de Junho de 2026) — Rebrand VendaPop + Config Dinâmica

**Rebranding:**
- 🔄 Renomeado de PopVenda para VendaPop em todo o código (55 arquivos)
- 📁 Diretório `.compozy/tasks/lancamento-popvenda` → `lancamento-vendapop`

**Configuração Dinâmica:**
- 📧 Blade templates agora usam `{{ config('app.name') }}` ao invés de nome hardcoded
- ✉️ Subjects dos Mailables usam `config('app.name')` dinamicamente
- 🌐 InviteController e InviteCreateCommand usam `config('services.frontend_url')`
- 🔔 NotificationService VAPID subject usa fallback `config('mail.from.address')`
- 👤 Emails de admin derivados de `config('app.url')`
- 🖥️ Frontend InvitePanel usa `window.location.origin` para URLs de convite
- 📝 Register/GoogleOnboarding usam `window.location.hostname`
- 🔧 Service worker usa `self.location.hostname` para tag de notificação
- 🚫 Adicionado `deploy/.gitignore` para evitar commit de `.env.production`

### v1.10.0 (17 de Junho de 2026) — Superadmin Dashboard

**Painel Superadmin:**
- 🏢 Dashboard de gestão da plataforma (`/superadmin`)
- 👤 Identificação via flag `is_super_admin` no modelo User existente
- 🔐 Login/logout independente em `/api/superadmin/login`
- 🛡️ Middleware `CheckSuperAdmin` que bypassa o escopo de tenant
- 🌱 Seeder `SuperAdminSeeder` (superadmin@vendapop.com.br)

**Gestão de Tenants:**
- 📋 Lista paginada com busca por nome/slug
- 🔍 Filtros por tipo de plano (free/basic/professional/premium) e status (active/trial/cancelled/expired)
- 📄 Detalhe do tenant: assinaturas, usuários, contagem de produtos e pedidos
- 📅 Último login dos usuários visível na listagem

**Gestão de Waitlist:**
- ✅ Aprovar/rejeitar entradas individualmente
- 📦 Aprovação em lote com geração automática de convites
- 🎫 Códigos de convite vinculados e exibidos após aprovação
- 🔍 Filtros por status e data

**Sistema de Feedback:**
- 💬 Widget flutuante no painel admin do tenant (canto inferior direito)
- 📬 Inbox no superadmin com filtro por status (unread/read/resolved)
- ✅ Marcar como lido/resolvido com um clique

**Convites Aprimorados:**
- 🔗 Link copiável (`vendapop.com.br/convite/CODE`) em cada invite
- 🔘 Toggle ativar/desativar invite (validação rejeita inativos)
- 📋 Lista de convites com status: ativo, expirado, esgotado, inativo

**Frontend:**
- 🎨 Layout dedicado com sidebar (Tenants, Waitlist, Feedback, Invites)
- 🌑 Tela de login com tema escuro
- 📊 Tabelas com paginação, busca e filtros
- 📱 Responsivo (sidebar colapsável em mobile)

**Técnico:**
- 🗄️ 4 novas migrations (users nullable, feedbacks, waitlist extend, invites is_active)
- 🧪 32 novos testes (91 total, 0 falhas)
- 🏗️ 5 novos UseCases seguindo arquitetura SOLID
- 🛣️ Rotas isoladas em `routes/superadmin.php`
- 🔧 `TenantService` registrado como singleton, adicionado `clearTenant()`
- 🪝 Patch no trait `BelongsToTenant` para suportar `tenant_id = null`

**Correções:**
- 🐛 Rota da waitlist na landing page corrigida (estava com `/api/api/` duplicado)

**Documentação:**
- 📋 PRD, TechSpec e 4 ADRs em `.compozy/tasks/superadmin/`
- 📝 13 task files com especificações detalhadas

### v1.9.0 (17 de Junho de 2026) — Lançamento Beta

**Rebrand:**
- 🔄 Renomeado de VesteZap para VendaPop (todos os arquivos, configs, docs)
- 🎨 Nova landing page componentizada (Hero, Cases, Como Funciona, Features, Waitlist)
- 🖼️ Screenshots reais das lojas demo (Moda Chic, Casa & Lar, TechStore, Pizzaria)
- 📱 Mockup de celular no Hero com screenshot real da loja
- 🚪 Header com "Entrar" para lojistas existentes

**Sistema de Convites:**
- 📨 Convites manuais: admin gera códigos individuais para founders selecionados
- 🔗 Links públicos com vagas limitadas: fecha automaticamente ao esgotar
- 🚫 Apenas founders manuais (selecionados pelo admin) podem gerar convites
- ⌨️ Comando `php artisan invite:create manual|public` para gerar convites via CLI
- 🖥️ Comando `php artisan vendapop:admin` para criar tenant + usuário admin

**Planos e Assinaturas:**
- 💎 Tabela `subscriptions` com rastreamento de origem e expiração
- ♾️ Founders manuais recebem Premium vitalício
- ⏳ Convidados e links públicos recebem Premium por 60 dias (trial)
- 📧 Notificação por email 7 dias antes da expiração do trial
- 🎯 Banner de aviso no painel admin nos últimos 14 dias de trial

**Tags de Rastreamento:**
- 📊 Google Analytics (GA4) por loja — injetado automaticamente na vitrine
- 📈 Facebook Pixel por loja — injetado automaticamente na vitrine
- 🎛️ Interface no Store Settings: dois campos separados, salva automático

**Lista de Espera:**
- 📧 Captura de email na landing page
- 📋 Painel admin para visualizar inscritos

**Técnico:**
- 🗄️ 5 novas migrations (`invites`, `subscriptions`, `tenant_trackings`, `waitlist_entries`, coluna banner)
- 🧪 40 testes (16 migrations + 9 invite service + 8 invite controller + 7 existentes)
- 🗃️ Testes isolados com SQLite in-memory — não tocam mais o banco de dev
- 🪝 Middleware `CheckSubscription` preparado para enforcement de limites
- ⏰ `TrialExpirationJob` registrado no Scheduler diário

**Documentação:**
- 📚 Docs consolidados em `docs/` (ROADMAP, SPEC, RELEASE_NOTES, todo, brainstorm, estudo-concorrentes, playbook, testes-manuais)
- 📋 PRD, TechSpec e ADRs em `.compozy/tasks/lancamento-vendapop/`

### v1.8.0 (12 de Junho de 2026)

**Novas Funcionalidades:**
- 📄 Páginas legais: Política de Privacidade, Termos de Uso, Política de Cookies e Seus Direitos LGPD
- 🏛️ Componente `LegalPage` reutilizável com sumário automático, navegação por âncoras e botão de impressão
- 🔗 Links legais no rodapé das lojas, landing page e tela de login
- ✅ Checkbox obrigatório de aceite dos Termos e Privacidade no cadastro do lojista
- 🗄️ Registro de `terms_accepted_at` no banco de dados para trilha de auditoria

**Melhorias:**
- 🔒 Conteúdo adaptado para contexto de e-commerce (produtos, pedidos, dados de clientes)
- 📧 Canais de contato: `contato@vendapop.com.br` (geral) e `privacidade@vendapop.com.br` (DPO)
- 🧪 Testes de feature para validação de aceite dos termos no registro

**Notas Técnicas:**
- Rotas públicas: `/privacidade`, `/termos`, `/cookies`, `/direitos-lgpd`
- Migração: `add_terms_accepted_at_to_users_table` (nullable timestamp)
- Validação: `terms_accepted` como `required|accepted` no endpoint `/api/admin/register`
- Conteúdo estático em JSX (sem CMS) — alterações requerem deploy

---

### v1.7.0 (12 de Junho de 2026)

**Novas Funcionalidades:**
- 🔄 Reenvio inline de e-mail de verificação na tela de login (com reCAPTCHA v3, opcional em local)
- 🔐 Login com Google OAuth via Laravel Socialite
  - Diálogo de vinculação para contas existentes com e-mail não verificado
  - Onboarding pós-Google para novos usuários
- 📧 Login sem senha via OTP de 6 dígitos + Magic Link
  - Código OTP e link mágico enviados no mesmo e-mail
  - Página de auto-autenticação via magic link
- 🧹 Comando `auth:cleanup-expired-tokens` para limpeza de tokens expirados (cron diário)

**Melhorias:**
- 🛡️ reCAPTCHA v3 opcional em ambiente local (3 endpoints: cadastro, reenvio, OTP)
- 📬 E-mails OTP e de verificação enviados via MailHog em desenvolvimento
- 🔧 Entrypoint Docker agora injeta `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT` no `.env`

**Correções:**
- ✅ Resend de verificação não regenera mais a senha do usuário
- ✅ Google OAuth retorna 302 redirect (não mais JSON) para fluxo OAuth correto
- ✅ User data (nome/email) salvo corretamente no localStorage após login Google

**Notas Técnicas:**
- Backend:
  - Migration `add_google_auth_fields_to_users_table`: colunas `google_id`, `google_token`, `google_refresh_token`
  - Migration `create_otp_tokens_table`: tabela para OTP + magic link tokens
  - Model `OtpToken` com casts de data e fillable
  - `GoogleAuthService` + `GoogleAuthController`: fluxo OAuth completo com `stateless()`
  - `OTPAuthService` + `OTPAuthController`: geração, verificação e expiração de tokens
  - `OTPMail` + `emails/otp.blade.php`: template de e-mail com código OTP e botão magic link
  - `CleanupExpiredTokens`: comando artisan + schedule diário
  - `WelcomeMail`: construtor com password opcional (`?string $password = null`)
  - Dependência: `laravel/socialite ^5.27`
- Frontend:
  - `SignIn.tsx`: reenvio inline, botão Google, toggle OTP com input de 6 dígitos
  - `GoogleCallback.tsx`: processa callback + diálogo de vinculação
  - `GoogleOnboarding.tsx`: formulário pós-Google para novos usuários
  - `MagicLogin.tsx`: página que autentica automaticamente via magic link

---

### v1.6.0 (11 de Junho de 2026)

**Novas Funcionalidades:**
- 🐳 Setup de produção Docker headless (fpm, worker, nginx, mysql, backup) atrás do edge compartilhado
- 🏷️ Rodapé "Desenvolvido por Dyna Solutions" com link para dynasolutions.com.br

**Build:**
- ⚡ Typecheck desacoplado do build de produção (reduz tempo de rebuild em CI/deploy)

**Correções:**
- ✅ Build do frontend direto em imagem nginx (remove dependência de volume compartilhado)
- ✅ Landing page: caminho relativo `/modachic` em vez de URL absoluta
- ✅ Não cachear config no build (corrige fallback para sqlite em produção)

**Notas Técnicas:**
- Stack de produção: Docker Compose com PHP-FPM, Nginx, MySQL 8, queue worker (database driver)
- Imagens multi-stage: `Dockerfile.backend` (fpm + worker targets) e `Dockerfile.frontend` (build-only)
- Conectado à rede `web` para edge proxy Caddy compartilhado
- Domínio: `vendapop.dynasolutions.com.br`
- .env.production.example com todas as variáveis documentadas

---

### v1.5.1 (10 de Junho de 2026)

**Correções:**
- ✅ Docker: entrypoint.sh injeta env vars no `.env` para compatibilidade com PHP built-in server
- ✅ Docker: volume anônimo para `vendor/` evitar sobrescrita pelo bind mount
- ✅ Docker: removido `APP_KEY` fixa do compose (gerada automaticamente)
- ✅ Docker: `.dockerignore` criado para evitar `.env` local no build
- ✅ Migrations: removido `->after('is_hot')` em colunas que não existem mais
- ✅ Seeders: removidas referências a `sizes`, `colors`, `is_hot` (substituídos por atributos)
- ✅ `.env.example`: corrigidos valores VAPID inválidos que quebravam o parser

**Notas Técnicas:**
- PHP built-in server (`php artisan serve`) não herda env vars do shell
- Entrypoint agora escreve todas as configs DB diretamente no `.env`
- Necessário rebuild da imagem: `docker compose build backend --no-cache`

---

### v1.5.0 (27 de Janeiro de 2025)

**Novas Funcionalidades:**
- 🏷️ Sistema completo de atributos dinâmicos de produtos
- 📦 Sistema de variações de produtos com estoque e preço individual
- 🎯 Controle de estoque por variação (habilitável por produto)
- 💰 Preço dinâmico baseado na variação selecionada
- 🛒 Botões de ação dinâmicos (Adicionar ao Carrinho, Link Afiliado, WhatsApp)
- 🏢 Business Sector (Ramo de Atividade) com criação automática de atributos
- 📋 Seeders para diferentes ramos: Imobiliários, Eletrônicos, Roupas, Joias, Bolo Caseiro, Encomendas e Afiliados
- 🎨 Interface de gerenciamento de variações com tabela completa (estoque, preço, SKU)
- 🔄 Comando de migração para converter sizes/colors existentes em atributos

**Melhorias:**
- 🎨 ProductResource para padronizar todas as respostas de API de produtos
- 🖱️ Drag & drop para reordenar banners no admin
- 📝 Slug da loja agora é read-only no admin
- 🎯 Botão label personalizável por produto
- 🏷️ Seleção de atributos estilo Select2 com criação inline
- 📊 Tabela de variações mostrando todas as combinações de atributos
- ✅ Validação de estoque considerando quantidade já no carrinho
- 🔍 Normalização de atributos (arrays indexados para objetos com IDs)

**Correções:**
- ✅ Remove campos `sizes` e `colors` da tabela products (substituídos por atributos)
- ✅ Remove tabela `product_attribute_values` (valores agora são livres nas variações)
- ✅ Corrige bug de multiplicação de quantidade no carrinho
- ✅ Corrige comparação de atributos para agrupar itens corretamente
- ✅ Remove campo `store_url` dos tenants (mantém apenas slug)
- ✅ Corrige resolveRouteBinding para tratar encoding de slug
- ✅ Banners aparecem apenas na página inicial da loja
- ✅ Remove referências ao AttributeList removido
- ✅ Cria manifest.json para corrigir erro de PWA
- ✅ Simplifica StoreController removendo UseCases desnecessários

**Notas Técnicas:**
- Backend:
  - Migration `create_product_attributes_tables` cria tabelas de atributos e variações
  - Migration `add_business_sector_to_tenants_table` adiciona campo de ramo de atividade
  - Migration `add_action_fields_to_products_table` adiciona action_type, affiliate_link, whatsapp_message
  - Migration `add_button_label_to_products_table` adiciona campo de label personalizado
  - Migration `add_stock_management_enabled_to_products_table` adiciona controle de estoque
  - Migration `add_attributes_to_order_items_table` adiciona coluna attributes (JSON)
  - Migration `remove_sizes_and_colors_from_products_table` remove campos obsoletos
  - Migration `drop_product_attribute_values_table` remove tabela de valores
  - Migration `remove_store_url_from_tenants_table` remove campo obsoleto
  - Model `ProductAttribute` para gerenciar atributos por tenant
  - Model `ProductVariation` para variações com estoque, preço e SKU
  - `ProductResource` padroniza todas as respostas de produtos
  - `ProductAttributeService` gerencia atributos por business sector
  - Comando `products:migrate-sizes-colors-to-attributes` para migração de dados
  - `resolveRouteBinding` melhorado para tratar encoding de slugs
- Frontend:
  - Formulário de produtos com interface de variações completa
  - Tabela de variações com estoque, preço e SKU editáveis
  - Seleção de atributos com criação inline (estilo Select2)
  - Validação de estoque no carrinho considerando quantidade existente
  - Preço dinâmico na página de detalhes do produto
  - Indicador de disponibilidade baseado em estoque
  - Botões de ação dinâmicos (`ProductActionButton`)
  - Banners aparecem apenas na página inicial
- Seeders:
  - `ImobiliariaSeeder` com produtos imobiliários e variações
  - `EletronicosSeeder`, `RoupasSeeder`, `JoiasSeeder`, `BoloCaseiroSeeder`, `EncomendasSeeder`, `AfiliadosSeeder`
  - Usuários admin criados para cada tenant de exemplo
  - Nomes realistas para casos de sucesso

**Breaking Changes:**
- ⚠️ Campos `sizes` e `colors` foram removidos da tabela products
- ⚠️ Estrutura de variações mudou para usar atributos dinâmicos
- ⚠️ Campo `store_url` foi removido dos tenants

**Migração:**
1. Execute todas as migrations: `php artisan migrate`
2. Para migrar dados existentes: `php artisan products:migrate-sizes-colors-to-attributes`
3. Configure business_sector nos tenants para criar atributos padrão automaticamente

---

### v1.4.0 (24 de Novembro de 2025)

**Novas Funcionalidades:**
- 🖼️ Sistema completo de banners rotativos
- 🎠 Carrossel automático na loja pública com rotação a cada 5 segundos
- 📸 Suporte a upload de imagem local ou URL externa
- 🔗 Links opcionais ao clicar nos banners
- 📝 Título e descrição opcionais para cada banner
- 🎯 Ordenação personalizada de banners
- ✅ Ativação/desativação individual de banners

**Melhorias:**
- 🎨 Interface admin completa para gerenciamento de banners
- 📋 Listagem com paginação, ordenação e filtros
- 🖱️ Drag & drop para upload de imagens
- 👁️ Preview de imagem antes de salvar
- 🗑️ Exclusão automática de imagens locais ao remover banners
- 🏗️ Arquitetura seguindo SOLID (Repository, Service, Use Cases)

**Correções:**
- 🔧 UserSeeder agora marca email como verificado automaticamente
- ✅ Permite login imediato após executar seeders
- 🔄 Usa `updateOrCreate` para garantir atualização correta

**Notas Técnicas:**
- Backend:
  - Migration `create_rotating_banners_table` com campos: image_url, image_path, is_external, link_url, order, is_active, title, description
  - Model `RotatingBanner` com relacionamento com `Tenant`
  - Repository Pattern: `RotatingBannerRepositoryInterface` + `RotatingBannerRepository`
  - `BannerService` para gerenciamento de imagens (upload/URL)
  - Use Cases: GetBannersUseCase, CreateBannerUseCase, UpdateBannerUseCase, DeleteBannerUseCase, UpdateBannerOrderUseCase
  - Endpoint público: `GET /api/{storeSlug}/banners`
  - Endpoints admin: CRUD completo em `/api/admin/banners`
  - `RotatingBannerSeeder` com 5 banners de exemplo
- Frontend:
  - Componente `RotatingBanners.tsx` com carrossel, navegação e indicadores
  - Páginas admin: `BannerList.tsx` e `BannerForm.tsx`
  - Integração no `PublicLayout` para exibição pública
  - Menu "Banners" adicionado ao `AppLayout`
- Arquitetura:
  - Separação de responsabilidades seguindo SOLID
  - Controllers delegam para Use Cases
  - Services lidam com lógica de negócio específica
  - Repositories abstraem acesso a dados

---

### v1.3.0 (24 de Novembro de 2025)

**Novas Funcionalidades:**
- 🏠 Landing page completa e responsiva
- 🎨 Página inicial com hero section, features e CTAs
- 📱 Design moderno com elementos visuais decorativos
- 🔗 Integração de botões de login e registro na landing page
- ✨ Seção de preview visual com cards mockup
- 🌈 Efeitos de glassmorphism e animações sutis

**Melhorias:**
- 🎯 Página de entrada profissional para atrair novos usuários
- 📊 Seção "Como Funciona" explicando o processo em 3 passos
- 💼 Showcase de 6 principais features do sistema
- 🎨 Background decorativo com formas geométricas e padrões
- 📝 Documentação de roadmap do produto

**Documentação:**
- 📄 Adicionado ROADMAP.md com visão do produto e features futuras
- 📋 Planejamento de integração com Instagram
- 💡 Estratégias de monetização documentadas

**Notas Técnicas:**
- Nova rota `/` para landing page
- Componente Landing.tsx com design responsivo
- Integração com rotas existentes mantida
- Elementos decorativos usando Tailwind CSS
- Z-index e overflow controlados para camadas visuais

---

### v1.2.4 (24 de Novembro de 2025)

**Novas Funcionalidades:**
- 🖼️ Imagens dos produtos no email de confirmação de pedido
- 📧 Thumbnails de 80x80px exibidos para cada item do pedido
- ✨ Layout melhorado com cards visuais para cada produto

**Melhorias:**
- 🎨 Email de confirmação mais visual e profissional
- 📱 Melhor confirmação visual do que foi comprado
- 🔍 Cliente pode ver as imagens dos produtos adquiridos no email

**Notas Técnicas:**
- Eager load de `items.product.images` no OrderService
- Template de email atualizado com HTML table para compatibilidade
- Imagens exibidas apenas se disponíveis (fallback gracioso)
- Layout responsivo e compatível com principais clientes de email

---

### v1.2.3 (24 de Novembro de 2025)

**Refatorações:**
- 🔄 Removida interface de push notifications para clientes na página de rastreamento
- 🧹 Código simplificado removendo funcionalidades de subscription de push
- 📝 Componente OrderTracking mais limpo e focado

**Notas Técnicas:**
- Removidos estados `isSubscribed` e `subscribing`
- Removidas funções `requestNotificationPermission`, `subscribeToPush`, `urlBase64ToUint8Array`, `arrayBufferToBase64`
- Funcionalidade de push notifications no backend mantida para uso futuro
- Interface de rastreamento agora foca apenas em exibir informações do pedido

---

### v1.2.2 (24 de Novembro de 2025)

**Correções Críticas:**
- 🔧 Correção de links usando localhost em produção
- ✅ Substituição de `env('FRONTEND_URL')` por `config('services.frontend_url')`
- 🐛 Resolve problema onde `env()` não funciona com cache de configuração ativo

**Melhorias:**
- ⚙️ Adicionado `frontend_url` ao arquivo `config/services.php`
- 🔄 Todos os serviços e controllers agora usam `config()` em vez de `env()`
- 📝 Links de pedidos, emails e notificações agora usam URL correta em produção

**Notas Técnicas:**
- `env()` não funciona quando há cache de configuração (`config:cache`)
- `config()` lê do cache de configuração, garantindo funcionamento correto
- Arquivos atualizados:
  - `config/services.php` - Adicionada configuração `frontend_url`
  - `WhatsAppService.php` - Usa `config('services.frontend_url')`
  - `NotificationService.php` - Usa `config('services.frontend_url')`
  - `OrderService.php` - Usa `config('services.frontend_url')`
  - `ManifestController.php` - Usa `config('services.frontend_url')`
  - `RegistrationController.php` - Usa `config('services.frontend_url')`
  - `EmailVerificationController.php` - Usa `config('services.frontend_url')`
  - `PasswordResetController.php` - Usa `config('services.frontend_url')`

**Como aplicar em produção:**
1. Certifique-se de que `FRONTEND_URL` está no `.env` do backend
2. Execute: `php artisan config:clear && php artisan config:cache`
3. Recarregue PHP-FPM: `systemctl reload php8.2-fpm`

---

### v1.2.1 (24 de Novembro de 2025)

**Correções:**
- 🔧 Correção de erro de TypeScript no build de produção
- ✅ Adicionado cast explícito `as BufferSource` para compatibilidade de tipos
- 🐛 Build do frontend agora completa sem erros de tipo

**Melhorias:**
- ⚙️ Configuração de `VITE_VAPID_PUBLIC_KEY` adicionada ao `docker-compose.yaml`
- 🚀 Ambiente Docker agora configurado automaticamente para push notifications
- 📝 Documentação atualizada com instruções de configuração

**Notas Técnicas:**
- Correção de incompatibilidade de tipos entre `Uint8Array` e `BufferSource` na subscription de push
- Variável de ambiente `VITE_VAPID_PUBLIC_KEY` configurada no Docker Compose para desenvolvimento
- Mesma chave VAPID do backend agora disponível automaticamente no frontend

---

### v1.2.0 (24 de Novembro de 2025)

**Novas Funcionalidades:**
- 🔔 Push notifications para clientes quando pedido é enviado ou concluído
- 📱 Sistema de subscriptions de push notifications para clientes (sem autenticação)
- 🎯 Interface na página de rastreamento para ativar notificações
- 💬 Mensagens personalizadas e amigáveis nas notificações push

**Melhorias:**
- 📨 Notificações push incluem nome do cliente, número do pedido e nome da loja
- 🎨 Mensagens mais amigáveis: "Olá {Nome}, seu pedido n. {número} da loja {loja} foi enviado!"
- 🔗 Links de rastreamento incluídos automaticamente nas notificações
- ✅ Detecção automática de mudanças de status para envio de notificações

**Notas Técnicas:**
- Criada tabela `customer_push_subscriptions` para armazenar subscriptions de clientes
- Model `CustomerPushSubscription` vinculado a `order_uuid` (sem necessidade de autenticação)
- Controller público `CustomerPushSubscriptionController` para gerenciar subscriptions
- Rotas públicas: `POST /{storeSlug}/order/{orderUuid}/push-subscriptions`
- `NotificationService` expandido com método `notifyCustomerOrderStatus()`
- `OrderController` detecta mudanças de status e notifica clientes automaticamente
- Frontend usa VAPID public key do ambiente para subscriptions
- Notificações enviadas apenas para status SENT e DONE

**Como usar:**
1. Cliente acessa a página de rastreamento do pedido
2. Cliente clica em "Ativar Notificações" e concede permissão
3. Quando admin atualiza status para "Enviado" ou "Concluído", cliente recebe notificação push
4. Cliente pode clicar na notificação para abrir a página de rastreamento

**Configuração:**
- Certifique-se de que `VAPID_PUBLIC_KEY` está configurada no frontend (`.env` ou variável de ambiente)
- Backend já usa `VAPID_PUBLIC_KEY` e `VAPID_PRIVATE_KEY` do `.env`
- Execute a migration: `docker compose exec backend php artisan migrate`

---

### v1.1.0 (24 de Novembro de 2025)

**Novas Funcionalidades:**
- 📝 Campo de observações no checkout para clientes adicionarem notas ao pedido
- 📧 Email de confirmação automático para clientes com e-mail cadastrado
- 📦 Novos status de pedido: "Em Separação" (PREPARING) e "Enviado" (SENT)
- 🔗 Configuração simplificada de redes sociais com URLs base fixas

**Melhorias:**
- 📱 Observações do pedido agora aparecem em todos os lugares: admin, rastreamento público, WhatsApp e emails
- 🎨 Cores diferenciadas para novos status: azul para "Em Separação" e roxo para "Enviado"
- 🔧 Links de rastreamento de pedidos agora usam FRONTEND_URL de forma consistente
- 🎯 Configuração de redes sociais mais intuitiva: usuário só precisa informar o username

**Correções:**
- ✅ Links de pedidos no WhatsApp agora usam formato correto (FRONTEND_URL + slug + UUID)
- ✅ Redes sociais: endereço base fixo no código, usuário informa apenas username
- ✅ Observações do pedido visíveis para admin e cliente em todas as interfaces

**Notas Técnicas:**
- Criado `OrderConfirmationMail` para emails de confirmação aos clientes
- Template de email `order-confirmation.blade.php` com detalhes completos do pedido
- Enum `OrderStatus` expandido com PREPARING e SENT
- `WhatsAppService` atualizado para incluir observações na mensagem
- `StoreSettings` atualizado com select de redes sociais e URLs base pré-configuradas

**Como usar:**
1. Clientes podem adicionar observações no checkout antes de finalizar
2. Clientes com e-mail recebem confirmação automática do pedido
3. Admin pode atualizar status do pedido para: Novo → Em Separação → Enviado → Concluído
4. Configurar redes sociais: selecionar rede e informar apenas o username

---

### v1.0.6 (24 de Novembro de 2025)

**Novas Funcionalidades:**
- 🔐 Sistema de UUIDs para Products, Categories, Customers e Orders
- 🔗 URLs amigáveis com slugs para produtos e categorias (rotas públicas)
- 📦 Comando Artisan para atualizar UUIDs de registros existentes
- 🎯 Enum OrderStatus para padronização de status de pedidos

**Melhorias:**
- 🔒 Segurança aprimorada: IDs numéricos não são mais expostos nas URLs
- 🌐 URLs amigáveis melhoram SEO e experiência do usuário
- 🎨 Route model binding inteligente: UUID para admin, slug para público
- 📝 Type safety melhorado com uso de enums em vez de strings
- 🔄 Consistência entre frontend e backend no uso de identificadores

**Refatorações:**
- ♻️ Atualização de todos os controllers admin para usar UUID
- ♻️ Atualização do frontend admin para usar UUID em navegação e APIs
- ♻️ Atualização do StoreController para usar slug em rotas públicas
- ♻️ Atualização do NotificationService para usar UUID em URLs de pedidos
- ♻️ Substituição de strings de status por OrderStatus enum

**Notas Técnicas:**
- Adicionada biblioteca `spatie/laravel-sluggable` para geração automática de slugs
- Migrations criadas para adicionar UUIDs e constraints de slug
- Modelos atualizados com geração automática de UUID e slug
- Route model binding configurado dinamicamente (UUID/slug baseado na rota)
- Comando `products:update-uuids` disponível para migração de dados existentes

**Como usar:**
1. Execute as migrations: `docker compose exec backend php artisan migrate`
2. Para atualizar registros existentes: `docker compose exec backend php artisan products:update-uuids`
3. URLs públicas agora usam slugs: `/{storeSlug}/product/{product-slug}`
4. URLs admin agora usam UUIDs: `/admin/products/{uuid}`

**Breaking Changes:**
- ⚠️ URLs de produtos públicos mudaram de `/{storeSlug}/product/{id}` para `/{storeSlug}/product/{slug}`
- ⚠️ URLs admin mudaram de `/admin/{resource}/{id}` para `/admin/{resource}/{uuid}`
- ⚠️ APIs admin agora esperam UUID em vez de ID numérico

---

### v1.0.5 (24 de Novembro de 2025)

**Novas Funcionalidades:**
- 🔔 Sistema completo de notificações para administradores
- 📧 Notificações por email quando novo pedido é criado
- 🔔 Suporte a push notifications via Web Push API
- 💬 Notificação via WhatsApp com número do pedido, link e nome do cliente
- 🔑 Script para gerar chaves VAPID automaticamente (`scripts/generate-vapid-keys.php`)

**Melhorias:**
- 📚 Documentação completa de configuração e testes de notificações
- 🛠️ Integração automática de notificações no fluxo de criação de pedidos
- 📝 Atualização do README.md com seção dedicada ao sistema de notificações
- 🔧 Adicionadas variáveis VAPID ao `.env.example`

**Notas Técnicas:**
- Adicionada biblioteca `minishlink/web-push` para push notifications
- Criado `NotificationService` para centralizar envio de notificações
- Criado `PushSubscriptionController` para gerenciar subscriptions
- Migration criada para tabela `push_subscriptions`
- Template de email criado para notificação de novo pedido
- Push notifications funcionam em localhost, 127.0.0.1 e HTTPS
- Script de geração de chaves instala automaticamente dependências se necessário

**Como usar:**
1. Execute: `docker compose exec backend php scripts/generate-vapid-keys.php`
2. Adicione as chaves ao arquivo `backend/.env`
3. Execute: `docker compose exec backend php artisan migrate`
4. Acesse o admin e permita notificações no navegador
5. Crie um pedido de teste para verificar as notificações

---

### v1.0.4 (24 de Novembro de 2025)

**Novas Funcionalidades:**
- ✨ Formatação automática do código do país (55) para números de WhatsApp
- 🔧 Função utilitária `formatWhatsAppNumber()` no frontend
- 🌐 Suporte automático ao código do país Brasil em todos os links WhatsApp

**Melhorias:**
- 📱 Links do WhatsApp agora funcionam corretamente mesmo sem código do país
- 🛠️ Formatação automática aplicada no Footer, botão flutuante e links de pedidos
- 🔄 Consistência entre frontend e backend na formatação de números

**Correções:**
- ✅ Números WhatsApp sem código do país são automaticamente formatados com "55"
- ✅ Evita erros de links WhatsApp quando usuário esquece de incluir código do país

**Notas Técnicas:**
- Criada função utilitária em `frontend/src/utils/whatsapp.ts`
- Atualizado `WhatsAppService.php` no backend para aplicar mesma lógica
- Função verifica se número já começa com "55" antes de adicionar

---

### v1.0.3 (Data Anterior)

**Correções:**
- ✅ Corrigida meta tag depreciada `apple-mobile-web-app-capable`
- ✅ Corrigido erro de sintaxe no ManifestController
- ✅ Adicionado pipeline Bitbucket para deploy automático

**Melhorias:**
- 📝 Atualizada documentação de deploy com instruções completas do reCAPTCHA v3

**Notas Técnicas:**
- ManifestController agora retorna JSON válido corretamente
- Meta tags PWA atualizadas para compatibilidade futura
- Guia de deploy inclui configuração de reCAPTCHA v3 passo a passo

---

# Release Notes - VendaPop v1.0.0 🎉

**Data de Lançamento:** 20 de Novembro de 2025

## 🎊 Primeira Versão Completa!

Esta é a primeira versão completa do VendaPop, uma plataforma SaaS multi-tenant para lojistas de moda feminina com finalização de pedidos via WhatsApp.

---

## ✨ Funcionalidades Principais

### 🏪 Multi-Tenancy
- Sistema completo de multi-loja com isolamento de dados por tenant
- Cada loja possui seu próprio catálogo, pedidos e clientes
- Resolução de tenant por slug na URL (`/{storeSlug}`)
- Segurança garantida com escopo de tenant em todas as operações

### 📱 Progressive Web App (PWA)
- PWA totalmente funcional com service worker customizado
- Manifest dinâmico por tenant com ícones e cores personalizados
- Suporte a instalação no dispositivo móvel
- Cache inteligente para melhor performance offline
- Meta tags dinâmicas (theme-color, apple-mobile-web-app-title)

### 🛍️ Loja Pública (Frontend)
- **Catálogo de Produtos**
  - Lista de produtos com imagens, preços e badges promocionais
  - Visualização em 1 ou 2 colunas (mobile)
  - Filtro por categoria
  - Busca de produtos
  - Badge "HOT" para produtos em destaque
  - Exibição de preço promocional quando disponível

- **Detalhes do Produto**
  - Galeria de imagens com carrossel
  - Informações completas do produto
  - Badge "HOT" e preço promocional
  - Seleção de tamanho e cor
  - Adicionar ao carrinho

- **Carrinho de Compras**
  - Gerenciamento completo de itens
  - Alteração de quantidades
  - Remoção de itens
  - Cálculo automático de totais
  - Persistência no localStorage

- **Checkout**
  - Formulário de dados do cliente
  - Validação de campos (nome obrigatório, email ou telefone)
  - Exibição de imagens dos produtos
  - Geração de pedido com número único
  - Redirecionamento para WhatsApp com mensagem pré-formatada

- **Rastreamento de Pedidos**
  - Página de rastreamento por UUID do pedido
  - Visualização de status e detalhes completos

- **Categorias**
  - Exibição de categorias com imagens
  - Fallback para iniciais em círculo colorido quando sem imagem
  - Navegação por categoria

- **Banner Promocional**
  - Banner configurável abaixo do header
  - Cores de texto alternadas configuráveis
  - Acompanha a rolagem da página

- **Header e Footer**
  - Logo da loja (imagem ou iniciais)
  - Nome da loja
  - Links para redes sociais
  - Informações de contato
  - Botão flutuante do WhatsApp
  - Link para admin quando logado

### 📊 Painel Administrativo

#### Autenticação e Segurança
- Sistema completo de autenticação com Laravel Sanctum
- Login/Logout
- Recuperação de senha via email
- Alteração de senha
- Verificação de email obrigatória
- Registro de novos tenants com reCAPTCHA
- Geração automática de senha no registro
- Email de boas-vindas com link de verificação

#### Dashboard
- Estatísticas em tempo real:
  - Vendas do dia
  - Pedidos novos
  - Produtos ativos
  - Pedidos do dia
  - Total de clientes
  - Vendas do mês
- Cards visuais com formatação de moeda
- Loading states com skeleton placeholders

#### Gestão de Produtos (CRUD)
- Listagem paginada e ordenável
- Criação e edição de produtos
- Campos:
  - Nome, descrição, preço
  - Preço promocional
  - Badge "HOT"
  - Categoria
  - Status (ativo/inativo)
- **Gerenciamento de Imagens:**
  - Upload com drag-and-drop
  - Recorte de imagens (600x900px)
  - Galeria de imagens (múltiplas URLs)
  - Definir imagem principal
  - Reordenar imagens (drag-and-drop)
  - Remover imagens (local e externas)
  - Suporte a imagens locais e URLs externas

#### Gestão de Categorias (CRUD)
- Listagem paginada e ordenável
- Criação e edição de categorias
- Upload de imagem da categoria
- Fallback para iniciais em círculo colorido

#### Gestão de Pedidos
- Listagem paginada e ordenável
- Filtros por status e data
- Detalhes completos do pedido
- Atualização de status (NEW, DONE, CANCELED)
- Visualização de itens, cliente e informações de pagamento

#### Gestão de Clientes
- Listagem paginada e ordenável
- Agrupamento por email ou WhatsApp
- Visualização de histórico de pedidos
- Adicionar/editar notas
- Completar cadastro (telefone, email)

#### Configurações da Loja
- **Identidade Visual:**
  - Upload de logo com recorte (200x200px)
  - URL externa de logo
  - Remoção de logo
  - Cores primária e secundária
  - Nome da loja
  - Descrição

- **Banner Promocional:**
  - Mensagem do banner
  - Cor de fundo
  - Cores de texto alternadas

- **Contato:**
  - Número do WhatsApp
  - Mensagem padrão do WhatsApp
  - Email de contato
  - Endereço

- **Rede Social:**
  - Links para Instagram, TikTok, YouTube, Facebook
  - Ícones automáticos por rede

- **URL da Loja:**
  - Aceita slug ou URL completa
  - Usado para rastreamento de pedidos

#### Navegação
- Menu responsivo com hamburger (mobile)
- Link "Ver Loja" no sidebar e header do admin
- Link para admin no header da loja (quando logado)
- Detecção em tempo real de login/logout

### 🔒 Segurança e Arquitetura

#### Arquitetura SOLID
- **Controllers**: Finos, apenas orquestram respostas HTTP
- **Use Cases**: Lógica de alto nível da aplicação
- **Services**: Regras de negócio reutilizáveis
- **Repositories**: Acesso a dados com interfaces
- **Models**: Eloquent com traits para multi-tenancy

#### Segurança
- Escopo de tenant em todas as operações
- Validação de permissões por tenant
- Autenticação via tokens (Sanctum)
- Proteção CSRF
- Validação de dados em todas as entradas
- reCAPTCHA no registro

### 🎨 Customização por Tenant
- Cores personalizadas (primária e secundária)
- Logo personalizado
- Banner promocional configurável
- Mensagem padrão do WhatsApp
- Links de redes sociais
- Nome e descrição da loja

### 📱 Responsividade
- Design totalmente responsivo
- Menu hamburger no admin (mobile)
- Toggle de visualização de produtos (1/2 colunas) no mobile
- Layout adaptativo para todos os dispositivos

### 🔧 Funcionalidades Técnicas

#### Backend
- API RESTful completa
- Paginação em todas as listagens
- Ordenação por colunas
- Filtros avançados
- Validação robusta
- Tratamento de erros padronizado
- Migrations organizadas
- Seeders para dados de teste

#### Frontend
- TypeScript para type safety
- Context API para gerenciamento de estado
- Componentes reutilizáveis
- Hooks customizados
- Tratamento de erros com toast notifications
- Loading states
- Confirmação de ações destrutivas

---

## 🛠️ Melhorias Técnicas

### Performance
- Service worker com estratégias de cache
- Lazy loading de imagens
- Otimização de queries no backend
- Paginação eficiente

### UX/UI
- Feedback visual em todas as ações
- Mensagens de erro claras
- Confirmações para ações destrutivas
- Loading states informativos
- Animações suaves

### Código
- Código limpo e organizado
- Separação de responsabilidades
- Reutilização de componentes
- Documentação inline
- Conventional Commits

---

## 📦 Dependências Principais

### Backend
- Laravel 12
- Laravel Sanctum
- MySQL 8.0
- Mailhog (desenvolvimento)

### Frontend
- React 18
- TypeScript
- Vite
- React Router
- Tailwind CSS
- Axios
- React Toastify
- React Dropzone
- React Easy Crop
- React Google reCAPTCHA
- Vite PWA Plugin

---

## 🐛 Correções Incluídas

- Correção de mass assignment nos models
- Correção de escopo de tenant em controllers
- Correção de permissões de arquivos
- Correção de validação de store_url
- Correção de exibição de imagens
- Correção de estado do carrinho

---

## 📝 Notas de Migração

### Para Desenvolvedores
1. Execute as migrations: `php artisan migrate`
2. Execute os seeders: `php artisan db:seed`
3. Configure as variáveis de ambiente
4. Configure o reCAPTCHA (chaves no .env)

### Para Usuários
1. Acesse `/admin/register` para criar sua loja
2. Verifique seu email para ativar a conta
3. Faça login e configure sua loja
4. Adicione produtos e categorias
5. Compartilhe o link da sua loja

---

## 🚀 Próximas Versões

Funcionalidades planejadas para versões futuras:
- Notificações push
- Relatórios avançados
- Integração com pagamentos
- App mobile nativo
- Multi-idioma
- Cupons de desconto
- Programa de fidelidade

---

## 🙏 Agradecimentos

Agradecemos a todos que contribuíram para esta primeira versão do VendaPop!

---

---

## 📦 Versões

### v1.0.4 (24 de Novembro de 2025)

**Novas Funcionalidades:**
- ✨ Formatação automática do código do país (55) para números de WhatsApp
- 🔧 Função utilitária `formatWhatsAppNumber()` no frontend
- 🌐 Suporte automático ao código do país Brasil em todos os links WhatsApp

**Melhorias:**
- 📱 Links do WhatsApp agora funcionam corretamente mesmo sem código do país
- 🛠️ Formatação automática aplicada no Footer, botão flutuante e links de pedidos
- 🔄 Consistência entre frontend e backend na formatação de números

**Correções:**
- ✅ Números WhatsApp sem código do país são automaticamente formatados com "55"
- ✅ Evita erros de links WhatsApp quando usuário esquece de incluir código do país

**Notas Técnicas:**
- Criada função utilitária em `frontend/src/utils/whatsapp.ts`
- Atualizado `WhatsAppService.php` no backend para aplicar mesma lógica
- Função verifica se número já começa com "55" antes de adicionar

---

### v1.0.3 (21 de Novembro de 2025)

**Correções:**
- 🐛 Corrigido erro de sintaxe no ManifestController que causava "Manifest: Syntax error"
- 🐛 Corrigida meta tag depreciada `apple-mobile-web-app-capable`
- 📝 Atualizada documentação de deploy com instruções completas do reCAPTCHA v3

**Melhorias:**
- ✅ Adicionada meta tag `mobile-web-app-capable` (nova especificação)
- 📚 Documentação melhorada para configuração do reCAPTCHA v3 no frontend e backend
- 🔧 Instruções atualizadas para usar subdomínio `api.vendapop.com.br`

**Notas Técnicas:**
- ManifestController agora retorna JSON válido corretamente
- Meta tags PWA atualizadas para compatibilidade futura
- Guia de deploy inclui configuração de reCAPTCHA v3 passo a passo

---

### v1.0.2 (20 de Novembro de 2025)

**Novas Funcionalidades:**
- ✨ Migração do reCAPTCHA v2 para v3
- 🔒 Implementação de verificação invisível do reCAPTCHA v3
- 🎯 Validação por score no backend (threshold de 0.5)

**Melhorias:**
- 🚀 Experiência do usuário aprimorada com reCAPTCHA invisível
- 📦 Atualização da biblioteca `react-google-recaptcha-v3` no frontend
- 🔧 Atualização da validação backend para suportar score do v3

**Notas Técnicas:**
- Substituída biblioteca `react-google-recaptcha` por `react-google-recaptcha-v3`
- Removida dependência `@types/react-google-recaptcha`
- reCAPTCHA v3 funciona de forma invisível, sem interação do usuário
- Score mínimo de 0.5 para aprovação (padrão recomendado pelo Google)

---

### v1.0.1 (20 de Novembro de 2025)

**Correções:**
- ✅ Corrigidos erros de TypeScript que impediam o build em produção
- ✅ Removidos imports não utilizados (React, Link, navigate)
- ✅ Adicionada propriedade `whatsapp_message` nas interfaces TypeScript
- ✅ Corrigida tipagem do parâmetro `token` no handler do reCAPTCHA
- ✅ Adicionado guia completo de deploy para VPS sem Docker

**Melhorias:**
- 📝 Documentação de deploy completa em `docs/DEPLOY.md`
- 🔧 Instruções para instalação em VPS Ubuntu/Debian
- 🔒 Configuração de SSL com Let's Encrypt
- 🐳 Suporte para instalação sem Docker

---

**VendaPop v1.0.0** - Transformando vendas de moda com WhatsApp! 💖

