# Release Notes - VesteZap

## 📋 Histórico de Versões

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

# Release Notes - VesteZap v1.0.0 🎉

**Data de Lançamento:** 20 de Novembro de 2025

## 🎊 Primeira Versão Completa!

Esta é a primeira versão completa do VesteZap, uma plataforma SaaS multi-tenant para lojistas de moda feminina com finalização de pedidos via WhatsApp.

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

Agradecemos a todos que contribuíram para esta primeira versão do VesteZap!

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
- 🔧 Instruções atualizadas para usar subdomínio `api.vestezap.com.br`

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

**VesteZap v1.0.0** - Transformando vendas de moda com WhatsApp! 💖

