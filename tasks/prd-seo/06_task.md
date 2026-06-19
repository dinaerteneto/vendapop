# Task 06 — noIndex nas páginas privadas e legais

**Status:** Concluído  
**Frente:** A  
**Dependências:** Task 02 (SEOHead criado)

## Objetivo

Adicionar `SEOHead` com `noIndex` em todas as páginas de autenticação e dashboard (que não devem aparecer no Google), e adicionar `SEOHead` com título e canonical corretos nas páginas legais (que podem ser indexadas).

## Contexto Técnico

- Páginas com `noIndex=true` recebem `<meta name="robots" content="noindex, nofollow">` e **não** recebem `<link rel="canonical">`
- Páginas legais são públicas e indexáveis — recebem title e canonical, sem noIndex
- Auth routes: `/admin/login`, `/admin/register`, `/admin/forgot-password`, `/admin/reset-password`, `/admin/verify-email`, `/admin/magic-login`, `/admin/auth/google/callback`, `/admin/onboarding`
- Dashboard routes: todas sob `/admin/` (protegidas)
- Legal routes: `/privacidade`, `/termos`, `/cookies`, `/direitos-lgpd`

## Páginas a Modificar

### Páginas Auth (noIndex)

**`src/pages/AuthPages/SignIn.tsx`**
```tsx
import { SEOHead } from '../../components/common/SEOHead'
// No JSX:
<SEOHead title="Entrar — VendaPop" noIndex />
```

**`src/pages/AuthPages/Register.tsx`**
```tsx
<SEOHead title="Criar conta — VendaPop" noIndex />
```

**`src/pages/AuthPages/ForgotPassword.tsx`**
```tsx
<SEOHead title="Recuperar senha — VendaPop" noIndex />
```

**`src/pages/AuthPages/ResetPassword.tsx`**
```tsx
<SEOHead title="Redefinir senha — VendaPop" noIndex />
```

**`src/pages/AuthPages/VerifyEmail.tsx`**
```tsx
<SEOHead title="Verificar e-mail — VendaPop" noIndex />
```

**`src/pages/AuthPages/MagicLogin.tsx`**
```tsx
<SEOHead title="Acesso rápido — VendaPop" noIndex />
```

**`src/pages/AuthPages/GoogleCallback.tsx`** e **`src/pages/AuthPages/GoogleOnboarding.tsx`**
```tsx
<SEOHead title="VendaPop" noIndex />
```

**`src/pages/AuthPages/OnboardingSetup.tsx`**
```tsx
<SEOHead title="Configurar loja — VendaPop" noIndex />
```

### Dashboard (noIndex)

**`src/pages/Dashboard/ECommerce.tsx`**
```tsx
<SEOHead title="Dashboard — VendaPop" noIndex />
```

Para demais páginas do Dashboard, aplicar o mesmo padrão com título descritivo + `noIndex`.

### Páginas Legais (indexáveis)

**`src/pages/legal/PrivacyPolicyPage.tsx`**
```tsx
<SEOHead title="Política de Privacidade — VendaPop" path="/privacidade" />
```

**`src/pages/legal/TermsOfServicePage.tsx`**
```tsx
<SEOHead title="Termos de Serviço — VendaPop" path="/termos" />
```

**`src/pages/legal/CookiePolicyPage.tsx`**
```tsx
<SEOHead title="Política de Cookies — VendaPop" path="/cookies" />
```

**`src/pages/legal/LgpdRightsPage.tsx`**
```tsx
<SEOHead title="Direitos LGPD — VendaPop" path="/direitos-lgpd" />
```

## Testes de Verificação

**Cenário 1 — Página de login tem noIndex:**
1. Abrir `http://localhost:5173/admin/login`
2. DevTools → `<head>`
3. Esperado: `<meta name="robots" content="noindex, nofollow">`
4. Esperado: **ausência** de `<link rel="canonical">`

**Cenário 2 — Página legal é indexável:**
1. Abrir `http://localhost:5173/privacidade`
2. DevTools → `<head>`
3. Esperado: `<title>Política de Privacidade — VendaPop</title>`
4. Esperado: `<link rel="canonical" href="https://vendapop.com.br/privacidade">`
5. Esperado: **ausência** de `<meta name="robots" content="noindex">`

**Cenário 3 — Dashboard tem noIndex:**
1. Fazer login e ir para `/admin`
2. DevTools → `<head>`
3. Esperado: `<meta name="robots" content="noindex, nofollow">`

```bash
# Typecheck
cd frontend && npm run typecheck
```
