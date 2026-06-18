# Task 02 — Componente SEOHead + HelmetProvider

**Status:** Pendente  
**Frente:** A  
**Dependências:** Task 01 (og-image.png precisa existir em public/)

## Objetivo

Instalar `react-helmet-async`, envolver o app com `HelmetProvider` em `main.tsx` e criar o componente reutilizável `SEOHead` que todas as páginas usarão para injetar meta tags dinâmicas.

Esta task entrega a infraestrutura de SEO — sem ela as tasks 03-06 não podem ser executadas.

## Contexto Técnico

- Stack: React 18 + Vite 7 + TypeScript
- `react-helmet-async` é thread-safe e suportado no React 18 (ao contrário do `react-helmet` deprecated)
- `VITE_API_BASE_URL` já existe no projeto (ex: `https://vendapop.com.br/api`) — o `SEOHead` deriva o APP_URL removendo `/api`
- O `HelmetProvider` deve envolver todo o app em `main.tsx` para que o Helmet funcione em qualquer página

## Passos de Implementação

### 1. Instalar dependência

```bash
cd frontend
npm install react-helmet-async
```

### 2. Atualizar `frontend/src/main.tsx`

```tsx
import React from 'react'
import ReactDOM from 'react-dom/client'
import { HelmetProvider } from 'react-helmet-async'
import App from './App.tsx'
import './styles/index.css'

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <HelmetProvider>
      <App />
    </HelmetProvider>
  </React.StrictMode>,
)
```

### 3. Criar `frontend/src/components/common/SEOHead.tsx`

```tsx
import { Helmet } from 'react-helmet-async'

const APP_URL = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') ?? 'https://vendapop.com.br'

interface SEOHeadProps {
  title: string
  description?: string
  image?: string
  path?: string
  type?: 'website' | 'product'
  noIndex?: boolean
}

export function SEOHead({
  title,
  description,
  image,
  path = '',
  type = 'website',
  noIndex = false,
}: SEOHeadProps) {
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

## Testes de Verificação

```bash
# 1. Build sem erros de TypeScript
cd frontend && npm run typecheck
# Esperado: 0 erros

# 2. Dev server sobe sem erros
npm run dev
# Esperado: servidor rodando em localhost:5173
```

**Verificação no browser (DevTools → Elements → `<head>`):**

1. Abrir qualquer página do app em `localhost:5173`
2. Inspecionar o `<head>` — ainda não deve ter meta OG (nenhuma página usa SEOHead ainda)
3. Adicionar temporariamente `<SEOHead title="Teste" description="desc" />` em `App.tsx` e verificar que as tags aparecem no `<head>`
4. Remover o teste após verificar

**Verificação de regressão:**
- Navegar pelo app completo e confirmar que não há erros no console relacionados ao HelmetProvider
