# Task 05 — Meta tags na página de Produto

**Status:** Pendente  
**Frente:** A  
**Dependências:** Task 02 (SEOHead criado)

## Objetivo

Adicionar `SEOHead` à página de detalhe de produto (`/{slug}/product/{productSlug}`), usando os dados dinâmicos do produto já carregados na página e o nome da loja do `storeInfo` do context. Quando o produto não tem imagem, usa o `IconController` da loja como fallback.

## Contexto Técnico

- `ProductDetail.tsx` já carrega `product` via `api.get('/{storeSlug}/products/{productSlug}')` em `useEffect`
- `product` contém: `name`, `description`, `short_description`, `main_image_url`, `slug`
- `storeInfo` vem de `useOutletContext` (igual ao ProductList) e contém `name` e `logo_url`
- O SEOHead deve ser renderizado apenas após o `product` estar carregado
- `type="product"` sinaliza ao Open Graph que é uma página de produto

## Arquivos a Modificar

### `frontend/src/pages/Shop/ProductDetail.tsx`

Adicionar no topo dos imports:
```tsx
import { SEOHead } from '../../components/common/SEOHead'
```

Adicionar após as declarações de estado existentes:
```tsx
const apiBase = import.meta.env.VITE_API_BASE_URL ?? ''

const productImage = product?.main_image_url
  ?? `${apiBase}/${storeSlug}/icon.png`
```

Adicionar como primeiro elemento no JSX, dentro do fragmento raiz (após verificar que `product` existe):
```tsx
{product && (
  <SEOHead
    title={`${product.name} — ${context?.storeInfo?.name ?? 'VendaPop'}`}
    description={product.short_description ?? product.description}
    image={productImage}
    path={`/${storeSlug}/product/${product.slug}`}
    type="product"
  />
)}
```

## Testes de Verificação

**Setup:** Abrir `http://localhost:5173/casa-lar-imoveis/product/apartamento-3-quartos-vista-para-o-mar`

**DevTools → Elements → `<head>` após carregar a página:**

```
<title>Apartamento 3 Quartos Vista para o Mar — Casa Lar Imóveis</title>
<meta name="description" content="...descrição do produto...">
<meta property="og:title" content="Apartamento 3 Quartos Vista para o Mar — Casa Lar Imóveis">
<meta property="og:image" content="https://...foto do produto...">
<meta property="og:url" content="https://vendapop.com.br/casa-lar-imoveis/product/apartamento-3-quartos-vista-para-o-mar">
<meta property="og:type" content="product">
<link rel="canonical" href="https://vendapop.com.br/casa-lar-imoveis/product/...">
```

**Cenários a testar:**

1. **Produto com imagem:** og:image deve ser `main_image_url`
2. **Produto sem imagem:** og:image deve ser `{apiBase}/{storeSlug}/icon.png`
3. **Produto com `short_description`:** usar `short_description` como description
4. **Produto sem `short_description`:** usar `description` como fallback
5. **Navegar de produto para produto:** tags devem atualizar corretamente

```bash
# Typecheck
cd frontend && npm run typecheck
```

**Verificação de preview (após deploy):**
- Testar URL de produto em https://www.opengraph.xyz
- Esperado: imagem do produto, título com nome do produto e loja
