# VesteZap 🛍️

**Plataforma de E-commerce Multi-loja para Moda Feminina com Finalização via WhatsApp**

[![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)](https://laravel.com)
[![React](https://img.shields.io/badge/React-18-blue.svg)](https://reactjs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.2-blue.svg)](https://www.typescriptlang.org)
[![Docker](https://img.shields.io/badge/Docker-Ready-blue.svg)](https://www.docker.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-orange.svg)](https://www.mysql.com)

## 📋 Sobre o Projeto

VesteZap é uma plataforma SaaS multi-tenant desenvolvida para lojistas de moda feminina. Permite que cada loja tenha seu próprio catálogo online com fluxo completo de compra, finalizando pedidos diretamente via WhatsApp.

### ✨ Funcionalidades Principais

- **🏪 Multi-loja**: Cada tenant tem sua própria loja isolada
- **📱 PWA**: Funciona como app mobile nativo
- **🛒 Carrinho de Compras**: Experiência completa de e-commerce
- **💬 WhatsApp Integration**: Finalização de pedidos via WhatsApp
- **📊 Painel Admin**: Gestão completa de produtos, categorias e pedidos
- **🎨 Customização**: Cores e identidade visual por loja

## 🚀 Tecnologias Utilizadas

### Backend
- **Laravel 12** - Framework PHP para API REST
- **MySQL 8.0** - Banco de dados relacional
- **Laravel Sanctum** - Autenticação API

### Frontend
- **React 18** - Biblioteca JavaScript para interface
- **TypeScript** - Superset JavaScript com tipagem
- **Vite** - Build tool e dev server
- **Tailwind CSS** - Framework CSS utility-first
- **React Router** - Roteamento SPA

### Infraestrutura
- **Docker & Docker Compose** - Containerização
- **Nginx** - Servidor web (produção)

## 🛠️ Instalação e Execução

### Pré-requisitos

- Docker e Docker Compose instalados
- Git

### Passos para Instalação

1. **Clone o repositório**
   ```bash
   git clone <repository-url>
   cd vestezap
   ```

2. **Suba os containers**
   ```bash
   docker compose up --build -d
   ```

3. **Instale dependências e configure o banco**
   ```bash
   docker compose exec backend composer install
   docker compose exec backend php artisan migrate --seed
   ```

4. **Acesse a aplicação**
   - **Loja**: http://localhost:5173/modachic
   - **Admin**: http://localhost:5173/admin/login
   - **API**: http://localhost:8000/api

### Credenciais de Teste

**Admin:**
- Email: `admin@modachic.com`
- Senha: `password`

## 📁 Estrutura do Projeto

```
vestezap/
├── backend/                 # API Laravel
│   ├── app/
│   │   ├── Models/         # Modelos Eloquent
│   │   ├── Http/Controllers/Api/  # Controllers da API
│   │   └── Services/       # Lógica de negócio
│   ├── database/
│   │   ├── migrations/     # Migrations do banco
│   │   └── seeders/        # Seeds para dados iniciais
│   └── routes/api.php      # Rotas da API
├── frontend/                # SPA React
│   ├── src/
│   │   ├── components/     # Componentes reutilizáveis
│   │   ├── pages/         # Páginas da aplicação
│   │   ├── services/      # Serviços de API
│   │   └── layout/        # Layouts e templates
│   ├── public/            # Assets estáticos
│   └── package.json       # Dependências Node.js
├── docker-compose.yaml     # Configuração Docker
└── spec.md                # Especificações técnicas
```

## 🔧 Comandos Úteis

### Desenvolvimento
```bash
# Subir containers
docker compose up -d

# Ver logs
docker compose logs -f

# Acessar container backend
docker compose exec backend bash

# Acessar container frontend
docker compose exec frontend sh
```

### Laravel (Backend)
```bash
# Criar migration
php artisan make:migration create_table_name

# Criar model
php artisan make:model ModelName

# Executar migrations
php artisan migrate

# Popular banco
php artisan db:seed
```

### React (Frontend)
```bash
# Instalar dependências
npm install

# Rodar em modo desenvolvimento
npm run dev

# Build para produção
npm run build
```

## 🗄️ Modelo de Dados

### Principais Entidades

- **Tenants**: Lojas/empresas
- **Users**: Administradores das lojas
- **Categories**: Categorias de produtos
- **Products**: Produtos do catálogo
- **Customers**: Clientes finais
- **Orders**: Pedidos realizados
- **OrderItems**: Itens dos pedidos

### Relacionamentos

```
Tenant (1) ──── (N) User
Tenant (1) ──── (N) Category
Tenant (1) ──── (N) Product
Tenant (1) ──── (N) Customer
Tenant (1) ──── (N) Order
Category (1) ── (N) Product
Customer (1) ── (N) Order
Order (1) ───── (N) OrderItem
Product (1) ─── (N) OrderItem
```

## 🚀 Deploy

### Produção

1. **Build das imagens**
   ```bash
   docker build -t vestezap-backend ./backend
   docker build -t vestezap-frontend ./frontend
   ```

2. **Configurar variáveis de ambiente**
   - Copie `.env.example` para `.env`
   - Configure banco de dados de produção
   - Configure URLs de produção

3. **Deploy com Docker Compose**
   ```bash
   docker compose -f docker-compose.prod.yml up -d
   ```

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📝 Convenções de Commit

Usamos [Conventional Commits](https://conventionalcommits.org/):

- `feat:` - Nova funcionalidade
- `fix:` - Correção de bug
- `docs:` - Mudanças na documentação
- `style:` - Mudanças de estilo (formatação, etc.)
- `refactor:` - Refatoração de código
- `test:` - Adição ou correção de testes
- `chore:` - Mudanças em ferramentas, config, etc.

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 👥 Autores

- **Dina Erteneto** - Desenvolvimento

## 🙏 Agradecimentos

- Laravel Framework
- React Community
- Docker Community
- Todos os contribuidores open source

---

**VesteZap** - Transformando vendas de moda com WhatsApp! 💖
