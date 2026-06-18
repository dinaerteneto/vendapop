# PRD — SEO & Indexabilidade

**Produto:** VendaPop  
**Data:** 2026-06-18  
**Status:** Em revisão  
**Autor:** Dinaerte Neto  
**Design spec de referência:** `docs/superpowers/specs/2026-06-18-seo-design.md`

---

## 1. Visão Geral

### 1.1 Resumo

O VendaPop não possui nenhuma configuração de SEO: sem meta description, Open Graph, Twitter Card, robots.txt, sitemap ou favicon real. Isso impede que a plataforma e as lojas dos lojistas sejam encontradas no Google e que compartilhamentos no WhatsApp gerem previews visuais — o que é crítico num produto cuja proposta central é "vender pelo WhatsApp".

Esta feature implementa a camada completa de SEO: meta tags dinâmicas por página, pré-render estático da landing para crawlers, robots.txt, sitemap dinâmico com todas as lojas e produtos, e favicon real do VendaPop.

### 1.2 Problema

| # | Ponto de atrito | Impacto |
|---|-----------------|---------|
| 1 | Compartilhar o link da loja no WhatsApp não gera preview | Lojista perde credibilidade com o cliente |
| 2 | Páginas de produto sem og:image | Foto do produto não aparece no WhatsApp/Instagram |
| 3 | Landing sem meta description | Google não sabe o que descrever nos resultados |
| 4 | Sem robots.txt | Crawlers indexam áreas que não devem (dashboard, admin) |
| 5 | Sem sitemap | Google demora para descobrir lojas e produtos novos |
| 6 | Favicon do Vite | Aparência amadora nas abas do navegador e bookmarks |

### 1.3 Objetivo

Garantir que qualquer link do VendaPop — da landing, de uma loja ou de um produto — gere preview rico no WhatsApp, seja indexável pelo Google, e que a plataforma tenha presença visual profissional nos navegadores.

---

## 2. Usuários-Alvo

### 2.1 Perfis

**Lojista (usuário primário)**
- Compartilha o link da loja com clientes pelo WhatsApp, Instagram e grupos
- Quer que o preview do link mostre o nome da loja e foto dos produtos
- Não tem conhecimento técnico sobre SEO

**Cliente da loja (usuário secundário)**
- Recebe links de produtos pelo WhatsApp
- Espera ver foto, título e preço antes de abrir o link
- Decisão de clicar depende da qualidade do preview

**Google / crawlers**
- Precisa receber HTML estático na landing para indexá-la corretamente
- Usa robots.txt para saber o que não indexar
- Usa sitemap para descobrir lojas e produtos novos

### 2.2 Fora do Escopo de Usuários

- Super admins e equipe interna — não precisam de SEO no painel administrativo

---

## 3. Requisitos Funcionais

### 3.1 Meta Tags Dinâmicas por Página

**RF-01** — O sistema deve renderizar `<title>` único para cada página.

**RF-02** — O sistema deve renderizar `<meta name="description">` para todas as páginas públicas.

**RF-03** — O sistema deve renderizar tags Open Graph (`og:title`, `og:description`, `og:image`, `og:url`, `og:type`) em todas as páginas públicas.

**RF-04** — O sistema deve renderizar tags Twitter Card (`twitter:card`, `twitter:title`, `twitter:description`, `twitter:image`) em todas as páginas públicas.

**RF-05** — O sistema deve renderizar `<link rel="canonical">` apontando para a URL canônica da página atual.

**RF-06** — Páginas privadas (dashboard, admin, auth) devem ter `<meta name="robots" content="noindex, nofollow">`.

**RF-07** — Os valores das meta tags por contexto devem seguir a tabela:

| Página | title | description | og:image |
|--------|-------|-------------|----------|
| Landing (`/`) | `VendaPop — Sua loja no WhatsApp` | Texto de venda fixo da plataforma | `/og-image.png` (estática) |
| Loja (`/{slug}`) | `{nome da loja}` | `{descrição da loja}` | `{logo_url do tenant}` |
| Produto (`/{slug}/product/{slug}`) | `{nome do produto} — {nome da loja}` | `{descrição do produto}` | `{imagem principal do produto}` |
| Legais | `{título da página} — VendaPop` | — | — |
| Auth / Dashboard | `{título} — VendaPop` | — | — (noindex) |

### 3.2 Pré-render da Landing Page

**RF-08** — O script de build deve gerar `dist/index.html` com HTML completo e estático da landing, incluindo todas as meta tags já injetadas.

**RF-09** — Crawlers que acessarem `vendapop.com.br/` devem receber HTML sem necessidade de executar JavaScript.

**RF-10** — O pré-render não deve alterar o comportamento do SPA para rotas não-landing.

### 3.3 robots.txt

**RF-11** — O arquivo `robots.txt` deve ser servido em `vendapop.com.br/robots.txt`.

**RF-12** — O robots.txt deve permitir indexação de `/`, `/{slug}` e `/{slug}/product/{slug}`.

**RF-13** — O robots.txt deve bloquear indexação de `/dashboard/`, `/admin/`, `/super-admin/`, `/login`, `/register`, `/forgot-password`, `/reset-password`.

**RF-14** — O robots.txt deve declarar a URL do sitemap.

### 3.4 Sitemap Dinâmico

**RF-15** — O backend deve servir sitemap em `vendapop.com.br/sitemap.xml` (sem prefixo `/api`).

**RF-16** — O sitemap deve incluir as rotas fixas da plataforma: `/`, `/privacidade`, `/termos`, `/cookies`, `/lgpd`.

**RF-17** — O sitemap deve incluir uma entrada para cada tenant ativo: `/{slug}`.

**RF-18** — O sitemap deve incluir uma entrada para cada produto ativo de cada tenant ativo: `/{slug}/product/{product-slug}`.

**RF-19** — O sitemap deve ser um XML válido no padrão `sitemaps.org/schemas/sitemap/0.9` com `<loc>`, `<lastmod>` e `<changefreq>`.

**RF-20** — O sitemap deve ter header `Content-Type: application/xml` e cache de 1 hora.

### 3.5 Favicon do VendaPop

**RF-21** — O `index.html` deve referenciar `favicon.svg`, `favicon.ico` e `apple-touch-icon.png` reais do VendaPop.

**RF-22** — O `manifest.json` em `public/` deve ser atualizado com nome, descrição e ícones corretos do VendaPop (192x192 e 512x512).

**RF-23** — A referência ao `/vite.svg` padrão deve ser removida.

---

## 4. Requisitos Não Funcionais

**RNF-01** — O script de pré-render não deve aumentar o tempo de build em mais de 60 segundos.

**RNF-02** — O endpoint `/sitemap.xml` deve responder em menos de 2 segundos mesmo com centenas de lojas e produtos.

**RNF-03** — As meta tags dinâmicas devem ser injetadas antes do primeiro paint para evitar flash de títulos errados.

**RNF-04** — O sitemap não deve listar tenants ou produtos inativos.

---

## 5. Fora do Escopo

- **Manifest dinâmico por loja** — já implementado (`ManifestController` + `PublicLayout.tsx`)
- **OG image gerada dinamicamente** (tipo Vercel OG Image) — versão futura
- **SSR completo** (Next.js / Remix) — decisão arquitetural futura
- **Custom domain por loja** (plano premium) — PRD separado
- **Google Search Console / Analytics** — configuração de conta, não código
- **Schema.org / JSON-LD** — versão futura

---

## 6. Critérios de Aceite

| # | Critério | Como verificar |
|---|----------|----------------|
| AC-01 | Preview rico ao compartilhar `vendapop.com.br` no WhatsApp | WhatsApp Web ou ferramenta de debug de OG |
| AC-02 | Preview com logo e nome da loja ao compartilhar `/{slug}` | WhatsApp Web ou opengraph.xyz |
| AC-03 | Preview com foto e nome do produto ao compartilhar URL de produto | WhatsApp Web ou opengraph.xyz |
| AC-04 | `curl vendapop.com.br` retorna HTML completo com `<meta og:title>` sem JS | Terminal |
| AC-05 | `curl vendapop.com.br/sitemap.xml` retorna XML válido com lojas e produtos | Terminal |
| AC-06 | `curl vendapop.com.br/robots.txt` retorna arquivo com Disallow corretos | Terminal |
| AC-07 | Favicon aparece em abas do browser e bookmarks | Browser |
| AC-08 | Páginas de dashboard têm `noindex` no source | DevTools → View Source |
| AC-09 | Google Rich Results Test passa na landing | search.google.com/test/rich-results |

---

## 7. Dependências Técnicas

| Item | Tipo | Observação |
|------|------|------------|
| `react-helmet-async` | npm (frontend) | Gerenciamento de `<head>` por página |
| `tsx` | npm devDependency (frontend) | Executar script de pré-render em TypeScript |
| Playwright | já instalado | Usado no script de pré-render |
| Arquivo `og-image.png` | Asset visual | A ser fornecido pela equipe de design |
| Arquivos `favicon.ico`, `favicon.svg`, `apple-touch-icon.png` | Assets visuais | A ser fornecido pela equipe de design |
| Ícones PWA `192x192` e `512x512` | Assets visuais | A ser fornecido pela equipe de design |

---

## 9. Fase 2 — Pós-implementação

### 9.1 Seção "Sua loja aparece no Google" na Landing

Após o SEO estar implementado e as primeiras lojas indexadas, adicionar uma seção na landing page que use isso como argumento de venda:

- **Proposta:** exibir um mock visual de resultado de busca do Google com o nome, foto e preço de um produto real de uma loja VendaPop
- **Diferencial:** a maioria dos concorrentes de catálogo pelo WhatsApp não oferece indexação orgânica no Google — isso é verificável e concreto
- **Variação avançada:** widget dinâmico mostrando lojas ativas que já aparecem no Google (requer evidência real antes de publicar)
- **Pré-requisito:** pelo menos uma loja indexada e aparecendo em resultados de busca reais

---

## 8. Métricas de Sucesso

| Métrica | Baseline | Meta |
|---------|----------|------|
| Taxa de clique em links compartilhados no WhatsApp | Não medido | Estabelecer baseline pós-lançamento |
| Impressões no Google Search Console | 0 | Crescimento em 30 dias |
| Cobertura do sitemap | 0 URLs | 100% de tenants e produtos ativos |
| Core Web Vitals — LCP na landing | Não medido | < 2.5s |
