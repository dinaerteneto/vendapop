# Task 03 — Meta tags na Landing Page

**Status:** Pendente  
**Frente:** A  
**Dependências:** Task 02 (SEOHead criado)

## Objetivo

Adicionar o `SEOHead` à landing page (`/`) com título, descrição e OG image estáticos do VendaPop. Esta é a página mais importante para indexação no Google e para preview ao compartilhar o link da plataforma.

## Contexto Técnico

- `src/pages/Landing.tsx` é um componente simples que renderiza seções (HeroSection, CaseSection etc.)
- A descrição deve refletir o copy atual do HeroSection: "Monte sua loja em 5 minutos..."
- `og-image.png` (1200×630px) é o asset estático em `public/` — fornecido pela equipe de design
- A landing não tem dados dinâmicos — todas as meta tags são fixas

## Arquivos a Modificar

### `frontend/src/pages/Landing.tsx`

```tsx
import { SEOHead } from '../components/common/SEOHead'
import HeroSection from '../components/landing/HeroSection'
import CaseSection from '../components/landing/CaseSection'
import HowItWorksSection from '../components/landing/HowItWorksSection'
import FeatureGrid from '../components/landing/FeatureGrid'
import WaitlistSection from '../components/landing/WaitlistSection'
import FooterSection from '../components/landing/FooterSection'

const Landing = () => {
  return (
    <div className="min-h-screen bg-white">
      <SEOHead
        title="VendaPop — Sua loja no WhatsApp"
        description="Monte sua loja em 5 minutos. Seus clientes navegam, escolhem variações e o pedido chega organizado no seu WhatsApp — sem calcular total na mão."
        path="/"
      />
      <HeroSection />
      <CaseSection />
      <HowItWorksSection />
      <FeatureGrid />
      <WaitlistSection />
      <FooterSection />
    </div>
  )
}

export default Landing
```

## Testes de Verificação

```bash
# 1. Verificar meta tags no source com curl (SPA ainda não é pré-renderizada — será na Task 08)
# Neste momento, as tags são injetadas via JS. Verificar via DevTools.

# 2. Typecheck
cd frontend && npm run typecheck
```

**Verificação no browser (DevTools → Elements → `<head>` após carregar `/`):**

```
<title>VendaPop — Sua loja no WhatsApp</title>
<meta name="description" content="Monte sua loja em 5 minutos...">
<meta property="og:title" content="VendaPop — Sua loja no WhatsApp">
<meta property="og:description" content="Monte sua loja em 5 minutos...">
<meta property="og:image" content="https://vendapop.com.br/og-image.png">
<meta property="og:url" content="https://vendapop.com.br/">
<meta property="og:type" content="website">
<meta name="twitter:card" content="summary_large_image">
<link rel="canonical" href="https://vendapop.com.br/">
```

**Ferramenta de preview OG (após deploy):**
- Acessar https://www.opengraph.xyz e testar `https://vendapop.com.br`
- Esperado: preview com título, descrição e imagem OG
