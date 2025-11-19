# Sistema de Catálogo de Roupas para Vendas no WhatsApp (Laravel API + React PWA, Multi-loja)

Este projeto é um **micro-SaaS multi-tenant** para lojistas de moda feminina, permitindo que cada loja tenha:

- Seu próprio catálogo de produtos
- Fluxo de compra com carrinho
- Checkout simples
- Finalização do pedido via **WhatsApp**
- PWA para funcionar bem em celulares

O objetivo é ser algo como uma “mini Shopee” focada em moda, mas com fechamento via WhatsApp.

Use como **referência visual de UI/UX** o Figma abaixo (não precisa ser idêntico, mas siga o estilo geral de layout e componentes):

> https://www.figma.com/pt-br/comunidade/file/1530269195327585988/app-de-vendas-de-roupas

---

## 1. Arquitetura geral

- Monorepo simples com duas pastas na raiz:

```txt
/backend   → API em Laravel (somente backend, sem Blade)
/frontend  → SPA em React (PWA) consumindo a API
```

- A aplicação deve ser multi-tenant (multi-loja) usando uma única base de dados, com separação por tenant_id.
- O Laravel será usado apenas como API RESTful.
- O React será SPA (Single Page Application) + PWA.
- Toda a experiência do cliente (catálogo, carrinho, checkout) é feita no React.
- O checkout gera um pedido e redireciona para o WhatsApp da loja com a mensagem pré-preenchida.

## 1.1. Arquitetura SOLID Implementada

O backend segue os princípios SOLID com separação clara de responsabilidades:

### Camadas de Arquitetura

#### **Use Cases** (`app/UseCases/`)
- **Responsabilidade**: Casos de uso da aplicação (lógica de alto nível)
- **Exemplos**:
  - `CreateOrderUseCase` - Orquestra criação de pedidos
  - `GetProductsUseCase` - Busca produtos com filtros
  - `GetStoreInfoUseCase` - Obtém informações da loja
  - `AuthenticateUserUseCase` - Autenticação de usuários

#### **Services** (`app/Services/`)
- **Responsabilidade**: Regras de negócio reutilizáveis
- **Implementados**:
  - `OrderService` - Lógica de criação e processamento de pedidos
  - `CustomerService` - Gerenciamento de clientes
  - `WhatsAppService` - Geração de mensagens e links WhatsApp

#### **Repositories** (`app/Repositories/`)
- **Responsabilidade**: Acesso a dados (padrão Repository)
- **Estrutura**:
  ```txt
  Interfaces/          # Contratos (ProductRepositoryInterface, etc.)
  Eloquent/           # Implementações concretas
  ```
- **Benefícios**: Abstração de dados, testabilidade, facilidade de troca de ORM

#### **Controllers** (`app/Http/Controllers/`)
- **Responsabilidade**: Apenas orquestrar respostas HTTP
- **Padrão**: Controllers finos que delegam para Use Cases

### Benefícios da Arquitetura

- **Testabilidade**: Cada camada pode ser testada isoladamente
- **Manutenibilidade**: Mudanças são localizadas em camadas específicas
- **Reutilização**: Services e Use Cases podem ser reutilizados
- **Separação de Preocupações**: UI, negócio e dados completamente isolados
- **Extensibilidade**: Fácil adicionar novas funcionalidades seguindo o padrão

### Exemplo de Fluxo: Checkout

```php
// Controller (apenas orquestra)
public function checkout(Request $request, $storeSlug) {
    // Validação básica
    $validated = $request->validate([...]);

    // Delega para Use Case
    $result = $this->createOrderUseCase->execute(
        $tenant, $customerData, $items
    );

    return response()->json($result);
}

// Use Case (orquestra Services)
public function execute($tenant, $customerData, $items) {
    $customer = $this->customerService->findOrCreate($tenant, $customerData);
    $order = $this->orderService->createOrder($tenant, $customer, $items);
    $whatsAppLink = $this->orderService->generateWhatsAppLink($tenant, $order, $customer);

    return ['order' => $order, 'whatsapp_link' => $whatsAppLink];
}

// Service (lógica de negócio)
public function createOrder($tenant, $customer, $items) {
    return DB::transaction(function () use ($tenant, $customer, $items) {
        // Lógica de criação do pedido
        $order = $this->orderRepository->create([...]);
        // Cria items, etc.
    });
}
```

## 2. Stack técnica

### Backend (Laravel)

- Laravel (versão estável atual, ex.: 11 ou 10)
- PHP 8.2+
- MySQL ou PostgreSQL
- Autenticação de lojista (Laravel Sanctum com tokens API)
- **Arquitetura SOLID implementada:**
  - `App\Http\Controllers` (controllers finos, apenas orquestram respostas HTTP)
  - `App\UseCases` (casos de uso da aplicação - lógica de alto nível)
  - `App\Services` (regras de negócio reutilizáveis)
  - `App\Repositories` (acesso a dados com interfaces)
  - `App\Models` (modelos Eloquent com traits para multi-tenancy)
- Estrutura de injeção de dependência via service container
- Middleware para resolução de tenants por URL slug

### Frontend (React SPA + PWA)

- React (com Vite ou CRA — preferir Vite)
- React Router para rotas da SPA
- Axios (ou Fetch) para consumo da API Laravel
- PWA:
  - `manifest.json`
  - Service Worker básico
  - Suporte a “Adicionar à tela inicial”

## 3. Multi-tenancy

- Ter uma tabela `tenants` (ou `stores`) representando cada loja.
- Todos os dados ligados à loja (produtos, pedidos, clientes, etc.) devem ter um campo `tenant_id`.
- O tenant deve ser resolvido pela URL, por exemplo:
  - Rota pública: `https://dominio.com/{storeSlug}`
  - API: `/api/{storeSlug}/products`, `/api/{storeSlug}/checkout`, etc.
- Criar um middleware em Laravel que:
  - Receba `storeSlug` na rota
  - Busque o tenant correspondente
  - Aplique o `tenant_id` em consultas relevantes (ou injete o tenant resolvido em serviços).

## 4. Funcionalidades principais

### 4.1. Lado público (cliente da loja)

- **Página inicial da loja:**
  - Lista de produtos (cards com imagem, nome, preço)
  - Busca por nome
  - Filtro por categoria
  - Destaques/promos (se possível)
- **Página de detalhes do produto:**
  - Imagens grandes
  - Nome, preço, descrição
  - Seleção de tamanho (P, M, G, etc.)
  - Seleção de cor (opcional)
  - Botão “Adicionar ao carrinho”
- **Carrinho:**
  - Itens adicionados (nome, tamanho, cor, quantidade, preço)
  - Alterar quantidade
  - Remover item
  - Total geral
- **Checkout:**
  - Formulário com campos:
    - Nome (obrigatório)
    - E-mail (opcional individualmente)
    - Celular (opcional individualmente)
  - Validação obrigatória:
    - Nome deve ser preenchido
    - Pelo menos um entre E-mail ou Celular deve ser preenchido
      - Ex.: E-mail vazio + Celular preenchido → OK
      - E-mail preenchido + Celular vazio → OK
      - Ambos vazios → ERRO
  - Ao confirmar:
    - Criar um registro de pedido no backend (tabela `orders` + `order_items`)
    - Gerar um número de pedido (ex.: LOJA-2025-000123)
    - Retornar para o frontend os dados necessários para montar o link do WhatsApp (número da loja, mensagem, etc.)
  - Redirecionamento para WhatsApp:
    - Usar `https://wa.me/` ou `https://api.whatsapp.com/send` com o número do WhatsApp da loja.
    - A mensagem deve seguir o formato (em pt-BR):
      `"Olá, gostaria de confirmar meu pedido nº {NUMERO_PEDIDO} na loja {NOME_LOJA}:\n\nItens:\n- {PRODUTO_1} – Tamanho: {TAM} – Qtde: {QTD}\n...\nTotal: R$ {TOTAL}\n\nMeus dados:\nNome: {NOME_CLIENTE}\nE-mail: {EMAIL}\nCelular: {CELULAR}"`
    - O React deve abrir essa URL usando `window.location.href`.

### 4.2. Painel do lojista (admin por tenant)

- Login do lojista por e-mail/senha.
- Após login, acesso a um painel com:
  - Gestão de produtos (CRUD)
  - Gestão de categorias
  - Lista de pedidos:
    - Filtros por data
    - Status (opcional: novo, em atendimento, concluído)
- Detalhe do pedido:
  - Itens, dados do cliente, número do pedido, data/hora
- Configurações da loja:
  - Nome da loja
  - Slug
  - Logo
  - Cores
  - Número de WhatsApp
  - Pequena descrição/sobre

## 5. Modelo de dados e migrations (Laravel)

Crie as migrations para as tabelas abaixo. Campos de auditoria (`id`, `created_at`, `updated_at`) são padrão.

### 5.1. `tenants` (ou `stores`)
- `id` (bigIncrements)
- `name` (string)
- `slug` (string, único)
- `whatsapp_number` (string) – armazenar no formato internacional, ex.: +5599999999999
- `logo_url` (string, nullable)
- `primary_color` (string, nullable)
- `secondary_color` (string, nullable)
- `description` (text, nullable)

### 5.2. `users` (lojistas)
- `id`
- `tenant_id` (foreignId → tenants.id)
- `name`
- `email` (único por tenant)
- `password`
- `is_owner` (boolean, default true) – opcional
- Campos padrão de autenticação.

### 5.3. `categories`
- `id`
- `tenant_id`
- `name` (string)
- `slug` (string)
- `is_active` (boolean)

### 5.4. `products`
- `id`
- `tenant_id`
- `category_id` (nullable, foreign)
- `name` (string)
- `slug` (string)
- `short_description` (string, nullable)
- `description` (text, nullable)
- `price` (decimal, ex.: 10,2)
- `sizes` (json) – ex.: ["P","M","G"]
- `colors` (json, nullable) – ex.: ["preto","azul"]
- `main_image_url` (string, nullable)
- `images` (json, nullable) – URLs adicionais
- `is_active` (boolean, default true)

### 5.5. `customers` (clientes finais)
- `id`
- `tenant_id`
- `name` (string)
- `email` (string, nullable)
- `phone` (string, nullable)
- (Regra de negócio: não permitir que email e phone sejam ambos nulos na hora de criação de pedido.)

### 5.6. `orders`
- `id`
- `tenant_id`
- `customer_id`
- `order_number` (string, único por tenant) – ex.: LOJA-2025-000123
- `total_amount` (decimal 10,2)
- `status` (string, default 'novo') – opcional
- `notes` (text, nullable) – observações do cliente no checkout
- `created_at` / `updated_at`

### 5.7. `order_items`
- `id`
- `order_id`
- `product_id`
- `product_name` (string) – salvar nome do produto no momento do pedido
- `unit_price` (decimal 10,2)
- `quantity` (integer)
- `size` (string, nullable)
- `color` (string, nullable)
- `subtotal` (decimal 10,2)

**Instrução para a IA:**
Criar as migrations Laravel para todas as tabelas acima, incluindo chaves estrangeiras, índices apropriados e `onDelete('cascade')` quando fizer sentido (por exemplo, ao apagar um tenant, apagar subordinados).

## 6. API (Laravel)

### 6.1. Rotas públicas (cliente)

Prefixo sugerido: `/api/{storeSlug}`

- **GET** `/api/{storeSlug}/products`
  - Lista de produtos ativos do tenant.
- **GET** `/api/{storeSlug}/products/{productId}`
  - Detalhes de um produto.
- **POST** `/api/{storeSlug}/checkout`
  - Payload:
    - `customer`:
      - `name` (obrigatório)
      - `email` (opcional)
      - `phone` (opcional)
    - `items`: array
      - `product_id`
      - `quantity`
      - `size` (opcional)
      - `color` (opcional)
      - `notes` (opcional)
  - Regra de validação:
    - `customer.name` obrigatório.
    - Pelo menos um de `customer.email` ou `customer.phone` obrigatório.
    - `items` não pode ser vazio.
  - Resposta:
    - Dados do pedido criado:
      - `order_number`
      - `total_amount`
    - Dados do tenant (incluindo `whatsapp_number`)
    - Dados do cliente e itens
    - Sugestão: retornar já o texto da mensagem pronta para o WhatsApp.

### 6.2. Rotas protegidas (painel do lojista)

Prefixo sugerido: `/api/admin`

- **POST** `/api/admin/login`
- **POST** `/api/admin/logout`

Após autenticação (token), todas as rotas abaixo respeitam o `tenant_id` do usuário logado:

- **GET** `/api/admin/products`
- **POST** `/api/admin/products`
- **PUT** `/api/admin/products/{id}`
- **DELETE** `/api/admin/products/{id}`
- **GET** `/api/admin/categories`
- **POST** `/api/admin/categories`
- **PUT** `/api/admin/categories/{id}`
- **DELETE** `/api/admin/categories/{id}`
- **GET** `/api/admin/orders`
  - Filtros: status, created_at intervalo
- **GET** `/api/admin/orders/{id}`
- **GET** `/api/admin/store`
- **PUT** `/api/admin/store`
  - Atualiza dados da loja (nome, logo, cores, whatsapp_number, description).

## 7. Frontend (React SPA)

### 7.1. Estrutura de Diretórios e Padrões

**IMPORTANTE:** O projeto frontend deve seguir estritamente a estrutura de diretórios e organização de arquivos definida no arquivo de referência (`frontend/docs/frontend.txt`). Isso inclui:

- **Layouts:** Uso de `AppLayout`, `AppHeader`, `AppSidebar` para a área administrativa.
- **Componentes:** Organização em `src/components/{auth, common, ecommerce, form, header, tables, ui}`.
- **Contextos:** Uso de Context API (`SidebarContext`, `ThemeContext`) conforme o modelo.
- **Serviços:** Camada de API isolada em `src/services`.
- **Páginas:** Agrupamento por domínio em `src/pages/{AuthPages, Dashboard, Products, etc.}`.
- **Estilos:** Manter CSS modular e arquivos de estilo globais em `src/styles`.

A SPA deve implementar as seguintes rotas principais, adaptando-se à estrutura acima:

#### Rotas Públicas (Loja do Cliente)
- `/:storeSlug` (Home/Catálogo)
- `/:storeSlug/product/:productId` (Detalhe)
- `/:storeSlug/cart` (Carrinho)
- `/:storeSlug/checkout` (Checkout)

#### Rotas Administrativas (Painel do Lojista)
- `/admin/login`
- `/admin/*` (Dashboard, Produtos, Pedidos, Configurações - protegidas por auth)

### 7.2. PWA

- Adicionar manifesto (`manifest.json`)
- Registrar service worker simples para PWA
- Permitir instalação no celular (Add to Home Screen)

## 8. Comandos iniciais sugeridos

### 8.1. Backend (Laravel)

```bash
# Na raiz do repositório
composer create-project laravel/laravel backend

cd backend

# Instalar pacotes necessários (ex.: autenticação API, se desejar Sanctum)
composer require laravel/sanctum

# A arquitetura SOLID já está implementada com:
# - Use Cases em app/UseCases/
# - Services em app/Services/
# - Repositories com interfaces em app/Repositories/
# - Controllers finos delegando para Use Cases

php artisan migrate
```

### 8.2. Frontend (React com Vite)

```bash
# Na raiz do repositório
npm create vite@latest frontend -- --template react

cd frontend
npm install

# Instalar dependências úteis
npm install react-router-dom axios
```

## 9. Infraestrutura Docker (Ambiente de Desenvolvimento)

O projeto conta com configuração Docker para facilitar o setup local.

### 9.1. Estrutura de Containers
- **Backend**: PHP 8.2 CLI servindo via `php artisan serve` (porta 8000).
- **Frontend**: Node.js/Vite com Hot Module Replacement (porta 5173).
- **Database**: MySQL 8.0 (porta externa 3307, interna 3306).

### 9.2. Como rodar
1. Certifique-se de ter Docker e Docker Compose instalados.
2. Na raiz, execute:
   ```bash
   docker-compose up --build
   ```
3. Acessar:
   - Frontend: http://localhost:5173
   - API: http://localhost:8000/api

### 9.3. Detalhes dos Arquivos

**docker-compose.yaml**
Define os serviços, redes e volumes. O frontend possui variável `VITE_API_BASE_URL` apontando para o backend local. O backend roda migrações automaticamente ao iniciar (`migrate --force`).

**backend/Dockerfile**
- Base: `php:8.2-cli`
- Extensões: `pdo_mysql`, `zip`, `git`
- Instala Composer e dependências.

**frontend/Dockerfile**
- Base: `node:20-alpine`
- Exposição de host `0.0.0.0` para funcionar corretamente no container.

## 10. Status da Implementação

### ✅ Concluído
- **Arquitetura SOLID**: Use Cases, Services, Repositories implementados
- **Backend Laravel**: API completa com multi-tenancy
- **Frontend React**: SPA + PWA básica estruturada
- **Docker**: Ambiente de desenvolvimento configurado
- **Migrations**: Todas as tabelas criadas com relacionamentos
- **Checkout**: Funcional com geração de pedidos e WhatsApp
- **Autenticação**: Laravel Sanctum implementado
- **Seeders**: Dados de exemplo para desenvolvimento

### 📋 Próximos Passos Sugeridos
1. **Frontend Completo**: Implementar todas as páginas React seguindo o padrão TailAdmin
2. **Painel Admin**: CRUD completo de produtos, categorias, pedidos
3. **Testes**: Criar testes unitários e de integração
4. **Validações**: Implementar Form Requests para validações robustas
5. **Documentação API**: Swagger/OpenAPI para documentar endpoints
6. **Deploy**: Configurar produção com Docker e CI/CD

### 🎯 Como Contribuir
- Seguir os padrões de arquitetura SOLID já estabelecidos
- Usar conventional commits para mensagens
- Manter a separação clara entre camadas
- Adicionar testes para novas funcionalidades
