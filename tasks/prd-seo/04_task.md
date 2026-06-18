# Task 04 — Meta tags na página de Loja

**Status:** Pendente  
**Frente:** A  
**Dependências:** Task 02 (SEOHead criado)

## Objetivo

Adicionar `SEOHead` à página de listagem de produtos de cada loja (`/{slug}`), usando os dados dinâmicos da loja que já são carregados via `useOutletContext` do `PublicLayout`. Quando a loja não tem `logo_url`, usa a URL do `IconController` existente como fallback de og:image.

## Contexto Técnico

- `PublicLayout` já faz `api.get('/{storeSlug}')` e passa `storeInfo` via `useOutletContext`
- `storeInfo` já contém: `name`, `description`, `logo_url`, `primary_color`
- `IconController` existe em `/api/{storeSlug}/icon.png` e gera imagem com iniciais da loja — já em produção
- `VITE_API_BASE_URL` é a base para construir a URL do ícone (ex: `https://vendapop.com.br/api`)
- O SEOHead deve ser renderizado apenas após `storeInfo` estar carregado (evitar flash de tags erradas)

## Arquivos a Modificar

### `frontend/src/pages/Shop/ProductList.tsx`

Adicionar no topo dos imports:
```tsx
import { SEOHead } from '../../components/common/SEOHead'
```

Adicionar após as declarações de estado existentes:
```tsx
const apiBase = import.meta.env.VITE_API_BASE_URL ?? ''

const storeImage = context?.storeInfo?.logo_url
  ?? `${apiBase}/${storeSlug}/icon.png`
```

Adicionar como primeiro elemento no JSX retornado (antes do `<div>`):
```tsx
{context?.storeInfo && (
  <SEOHead
    title={context.storeInfo.name}
    description={context.storeInfo.description ?? `Catálogo de ${context.storeInfo.name}`}
    image={storeImage}
    path={`/${storeSlug}`}
  />
)}
```

O JSX final deve ficar:
```tsx
return (
  <>
    {context?.storeInfo && (
      <SEOHead
        title={context.storeInfo.name}
        description={context.storeInfo.description ?? `Catálogo de ${context.storeInfo.name}`}
        image={storeImage}
        path={`/${storeSlug}`}
      />
    )}
    <div>
      {/* ... resto do componente inalterado */}
    </div>
  </>
)
```

## Testes de Verificação

**Setup:** Abrir `http://localhost:5173/casa-lar-imoveis` (ou qualquer loja com dados)

**DevTools → Elements → `<head>` após carregar a página:**

```
<title>Casa Lar Imóveis</title>
<meta name="description" content="...descrição da loja...">
<meta property="og:title" content="Casa Lar Imóveis">
<meta property="og:image" content="https://...logo ou icon.png">
<meta property="og:url" content="https://vendapop.com.br/casa-lar-imoveis">
<link rel="canonical" href="https://vendapop.com.br/casa-lar-imoveis">
```

**Cenários a testar:**

1. **Loja com logo_url:** og:image deve ser o logo da loja
2. **Loja sem logo_url:** og:image deve ser `{apiBase}/casa-lar-imoveis/icon.png`
3. **Loja sem description:** og:description deve ser `"Catálogo de {nome}"` (fallback)
4. **Navegar entre lojas:** tags devem atualizar ao mudar de `/{slug1}` para `/{slug2}`

```bash
# Typecheck
cd frontend && npm run typecheck
```
