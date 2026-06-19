# Task 09 — Seção "Sua loja aparece no Google" na Landing

**Status:** Pendente  
**Frente:** Fase 2  
**Dependências:** Tasks 01-08 concluídas + pelo menos uma loja real indexada no Google

## Objetivo

Adicionar uma nova seção na landing page que usa a indexação no Google como argumento de venda. Exibe um mock visual de resultado de busca do Google com dados reais de um produto de uma loja VendaPop, demonstrando de forma concreta e verificável que as lojas aparecem no Google organicamente.

## Pré-requisito de Negócio

**Não implementar antes de ter evidência real:** esta seção só deve ir ao ar quando pelo menos uma loja VendaPop estiver indexada e aparecer no Google para uma busca real. Publicar antes disso com dados fictícios compromete a credibilidade.

Como verificar antes de implementar:
```
site:vendapop.com.br
```
Buscar no Google e confirmar que há resultados reais com as lojas.

## Contexto Técnico

- Segue o padrão das seções existentes: componente em `src/components/landing/`, importado em `Landing.tsx`
- Posição na landing: após `FeatureGrid` e antes de `WaitlistSection`
- O mock do Google deve ser estático (HTML/CSS) — não buscar a API do Google
- Usar dados reais de um produto/loja VendaPop como exemplo no mock (hardcoded na primeira versão)
- Tailwind CSS para estilização, seguindo o padrão `bg-white py-16` das demais seções

## Arquivos a Criar/Modificar

### `frontend/src/components/landing/GoogleIndexSection.tsx` — CRIAR

O componente renderiza:
1. **Título da seção:** "Sua loja aparece no Google"
2. **Subtítulo:** diferencial em relação a concorrentes
3. **Mock de resultado de busca:** card visual imitando o layout do Google Search (favicon, URL, título em azul, descrição em cinza)
4. **Badge/selo:** "Indexação orgânica gratuita"

```tsx
const EXEMPLO_LOJA = {
  nome: 'Casa Lar Imóveis',          // substituir por loja real indexada
  slug: 'casa-lar-imoveis',
  produto: 'Apartamento 3 Quartos Vista para o Mar',
  descricao: 'Confira nosso catálogo completo. Apartamento espaçoso com...',
  preco: 'R$ 450.000',
  imagem: '/images/stores/casa-lar-imoveis.png', // já existe em public/images/stores/
}

const GoogleIndexSection: React.FC = () => {
  return (
    <section className="bg-white py-16">
      <div className="container mx-auto px-4 max-w-4xl">
        
        {/* Título */}
        <div className="text-center mb-12">
          <span className="inline-block px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full mb-4">
            Indexação orgânica gratuita
          </span>
          <h2 className="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
            Sua loja aparece no Google
          </h2>
          <p className="text-gray-500 max-w-xl mx-auto">
            Diferente de outros catálogos, as lojas VendaPop são indexadas pelo Google automaticamente.
            Seus clientes encontram seus produtos mesmo sem ter o link.
          </p>
        </div>

        {/* Mock Google Search Result */}
        <div className="max-w-2xl mx-auto">
          
          {/* Barra de busca mockada */}
          <div className="flex items-center bg-white border border-gray-300 rounded-full px-4 py-2 mb-4 shadow-sm">
            <svg className="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span className="text-gray-700 text-sm">{EXEMPLO_LOJA.produto.toLowerCase()}</span>
          </div>

          {/* Card de resultado */}
          <div className="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition">
            {/* URL e favicon */}
            <div className="flex items-center gap-2 mb-1">
              <div className="w-4 h-4 rounded-full bg-purple-100 flex items-center justify-center">
                <span className="text-purple-700 text-[8px] font-bold">V</span>
              </div>
              <span className="text-xs text-gray-500">
                vendapop.com.br › {EXEMPLO_LOJA.slug} › product
              </span>
            </div>

            {/* Título clicável */}
            <h3 className="text-blue-700 text-lg font-medium hover:underline cursor-pointer mb-1">
              {EXEMPLO_LOJA.produto} — {EXEMPLO_LOJA.nome}
            </h3>

            {/* Descrição */}
            <p className="text-sm text-gray-600 leading-relaxed">
              {EXEMPLO_LOJA.descricao}
            </p>

            {/* Badge "resultado real" */}
            <div className="mt-3 flex items-center gap-2">
              <span className="text-xs text-green-600 bg-green-50 border border-green-200 rounded px-2 py-0.5">
                ✓ resultado real no Google
              </span>
            </div>
          </div>

          {/* Nota explicativa */}
          <p className="text-center text-xs text-gray-400 mt-4">
            Exemplo real de como sua loja aparece nas buscas do Google
          </p>
        </div>

      </div>
    </section>
  )
}

export default GoogleIndexSection
```

### `frontend/src/pages/Landing.tsx` — MODIFICAR

Adicionar o import e a seção entre `FeatureGrid` e `WaitlistSection`:

```tsx
import GoogleIndexSection from '../components/landing/GoogleIndexSection'

// No JSX:
<FeatureGrid />
<GoogleIndexSection />     {/* ← NOVO */}
<WaitlistSection />
```

## Personalização antes de lançar

Antes de fazer deploy, atualizar em `GoogleIndexSection.tsx`:
- `EXEMPLO_LOJA.nome` — nome da loja que está indexada
- `EXEMPLO_LOJA.slug` — slug real da loja no VendaPop
- `EXEMPLO_LOJA.produto` — nome do produto que aparece na busca
- `EXEMPLO_LOJA.descricao` — snippet real que o Google exibe
- `EXEMPLO_LOJA.imagem` — foto real do produto (se quiser exibir thumbnail)

## Testes de Verificação

```bash
# Typecheck sem erros
cd frontend && npm run typecheck
```

**Verificação visual no browser (`http://localhost:5173/`):**

1. Seção aparece entre FeatureGrid e WaitlistSection
2. Mock do Google Search está visualmente claro como um resultado de busca
3. URL `vendapop.com.br › {slug} › product` está legível
4. Título do produto aparece em azul (cor padrão de link do Google)
5. Badge "resultado real no Google" está visível
6. Responsivo: testar em mobile (375px) e desktop

**Checklist de negócio antes do deploy:**
- [ ] Confirmar que a loja do exemplo está realmente indexada (`site:vendapop.com.br/{slug}` retorna resultado)
- [ ] O produto do exemplo aparece na busca pelo nome
- [ ] O snippet de descrição bate com o que o Google mostra
