# TASK 11 — Banner de retomada do onboarding

**Grupo:** D — Banner  
**Depende de:** TASK 03, TASK 06  
**Bloqueia:** nenhuma (entregável final)

## Objetivo

Implementar o banner não-intrusivo que aparece no dashboard para lojistas que iniciaram mas não concluíram o onboarding. O banner permite retomar o wizard no passo onde parou, e pode ser dispensado temporariamente (30 dias via localStorage).

## Entregáveis

### 1. `OnboardingBanner.tsx`

**Arquivo:** `frontend/src/components/onboarding/OnboardingBanner.tsx`

**Props:**
```typescript
interface OnboardingBannerProps {
  currentStep: number;        // onboarding_step do tenant (0-3)
  onboardingCompleted: boolean;
}
```

**Lógica de visibilidade:**
```typescript
const DISMISS_KEY = 'onboarding_banner_dismissed_until';

const isDismissed = (): boolean => {
  const until = localStorage.getItem(DISMISS_KEY);
  if (!until) return false;
  return new Date(until) > new Date();
};

const [visible, setVisible] = useState(
  !onboardingCompleted && !isDismissed()
);
```

**Handler de dispensar:**
```typescript
const handleDismiss = () => {
  const thirtyDays = new Date();
  thirtyDays.setDate(thirtyDays.getDate() + 30);
  localStorage.setItem(DISMISS_KEY, thirtyDays.toISOString());
  setVisible(false);
};
```

**Labels dos passos:**
```typescript
const STEP_LABELS: Record<number, string> = {
  0: 'Configure a identidade da sua loja',
  1: 'Personalize seus produtos da vitrine',
  2: 'Adicione seu número do WhatsApp',
  3: 'Compartilhe sua loja e comece a vender',
};

const stepLabel = STEP_LABELS[currentStep] ?? STEP_LABELS[0];
```

**Renderização:**
```tsx
if (!visible) return null;

return (
  <div
    className="bg-purple-50 border border-purple-200 rounded-xl p-4 mb-6 flex items-center justify-between gap-4"
    data-testid="onboarding-banner"
  >
    <div className="flex items-center gap-3">
      <span className="text-2xl">🚀</span>
      <div>
        <p className="font-medium text-purple-900 text-sm">
          Continue configurando sua loja
        </p>
        <p className="text-purple-700 text-xs mt-0.5">
          Próximo: {stepLabel}
        </p>
      </div>
    </div>
    <div className="flex items-center gap-2 flex-shrink-0">
      <Link
        to="/admin/setup"
        className="bg-purple-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors"
      >
        Continuar →
      </Link>
      <button
        onClick={handleDismiss}
        className="text-purple-400 hover:text-purple-600 p-1"
        aria-label="Dispensar"
        title="Lembrar depois"
      >
        ✕
      </button>
    </div>
  </div>
);
```

### 2. Integrar na `ECommerce.tsx` (Dashboard principal)

**Arquivo:** `frontend/src/pages/Dashboard/ECommerce.tsx`

Localizar o início do conteúdo principal do dashboard (após o header/título) e adicionar:

```tsx
import OnboardingBanner from '../../components/onboarding/OnboardingBanner';

// Dentro do JSX, antes dos cards de métricas:
<OnboardingBanner
  currentStep={user?.tenant?.onboarding_step ?? 0}
  onboardingCompleted={user?.tenant?.onboarding_completed ?? false}
/>
```

O componente renderiza `null` quando o onboarding está completo ou foi dispensado, então não há custo visual para lojistas que já concluíram.

### 3. Garantir que `/admin/setup` retoma no passo correto

O `OnboardingSetup.tsx` (TASK 06) já inicializa o `step` a partir de `user?.tenant?.onboarding_step`. Verificar que o link "Continuar →" do banner — ao acessar `/admin/setup` — carrega o estado atualizado do tenant antes de definir o step inicial.

Se o tenant for carregado de forma assíncrona (contexto global), adicionar um guard:
```typescript
const [step, setStep] = useState<1|2|3|4>(() => {
  const saved = user?.tenant?.onboarding_step ?? 0;
  const validStep = Math.max(1, Math.min(4, saved + 1)) as 1|2|3|4;
  // +1 porque onboarding_step = passo concluído (0 = nenhum)
  // step 1 = identidade, step 2 = vitrine, etc.
  return validStep;
});
```

**Observação sobre a convenção de `onboarding_step`:**
- `onboarding_step = 0` → nenhum passo concluído → abrir no passo 1
- `onboarding_step = 1` → passo 1 concluído → abrir no passo 2
- `onboarding_step = 2` → passo 2 concluído → abrir no passo 3
- `onboarding_step = 3` → passo 3 concluído → abrir no passo 4

## Critérios de Aceitação

- [x] Banner aparece no dashboard quando `onboarding_completed = false`
- [x] Banner não aparece quando `onboarding_completed = true`
- [x] Banner exibe o label correto do próximo passo baseado em `currentStep`
- [x] Clicar "Continuar →" navega para `/admin/setup`
- [x] `/admin/setup` abre no passo correto após o redirect do banner
- [x] Clicar "✕" esconde o banner e salva a dispensa no localStorage por 30 dias
- [x] Banner permanece oculto ao recarregar a página dentro dos 30 dias
- [x] Banner volta a aparecer após os 30 dias de dispensa
- [x] Banner não aparece após completar o onboarding (mesmo sem recarregar)

## Testes

```typescript
// frontend/src/components/onboarding/OnboardingBanner.test.tsx

describe('OnboardingBanner', () => {
  beforeEach(() => localStorage.clear());

  it('não renderiza quando onboarding está completo', () => {
    render(<OnboardingBanner currentStep={4} onboardingCompleted={true} />);
    expect(screen.queryByTestId('onboarding-banner')).not.toBeInTheDocument();
  });

  it('renderiza quando onboarding incompleto', () => {
    render(<OnboardingBanner currentStep={0} onboardingCompleted={false} />);
    expect(screen.getByTestId('onboarding-banner')).toBeInTheDocument();
  });

  it('exibe label correto para step 0', () => {
    render(<OnboardingBanner currentStep={0} onboardingCompleted={false} />);
    expect(screen.getByText(/Configure a identidade/i)).toBeInTheDocument();
  });

  it('exibe label correto para step 2', () => {
    render(<OnboardingBanner currentStep={2} onboardingCompleted={false} />);
    expect(screen.getByText(/Adicione seu número do WhatsApp/i)).toBeInTheDocument();
  });

  it('dispensar salva no localStorage por 30 dias', () => {
    render(<OnboardingBanner currentStep={1} onboardingCompleted={false} />);
    fireEvent.click(screen.getByLabelText('Dispensar'));

    expect(screen.queryByTestId('onboarding-banner')).not.toBeInTheDocument();
    const stored = localStorage.getItem('onboarding_banner_dismissed_until');
    expect(stored).not.toBeNull();
    const date = new Date(stored!);
    const diffDays = (date.getTime() - Date.now()) / (1000 * 60 * 60 * 24);
    expect(diffDays).toBeGreaterThan(29);
  });

  it('banner oculto quando localStorage indica dispensa ativa', () => {
    const future = new Date();
    future.setDate(future.getDate() + 15);
    localStorage.setItem('onboarding_banner_dismissed_until', future.toISOString());

    render(<OnboardingBanner currentStep={1} onboardingCompleted={false} />);
    expect(screen.queryByTestId('onboarding-banner')).not.toBeInTheDocument();
  });

  it('banner visível quando dispensa no localStorage está expirada', () => {
    const past = new Date();
    past.setDate(past.getDate() - 1);
    localStorage.setItem('onboarding_banner_dismissed_until', past.toISOString());

    render(<OnboardingBanner currentStep={1} onboardingCompleted={false} />);
    expect(screen.getByTestId('onboarding-banner')).toBeInTheDocument();
  });

  it('link Continuar aponta para /admin/setup', () => {
    render(
      <MemoryRouter>
        <OnboardingBanner currentStep={2} onboardingCompleted={false} />
      </MemoryRouter>
    );
    expect(screen.getByRole('link', { name: /Continuar/i })).toHaveAttribute('href', '/admin/setup');
  });
});
```

**Teste de integração no dashboard:**
```typescript
// frontend/src/pages/Dashboard/ECommerce.test.tsx (adicionar ao arquivo existente)

it('banner aparece no dashboard para usuário com onboarding incompleto', () => {
  mockUser({ tenant: { onboarding_completed: false, onboarding_step: 1 } });
  render(<ECommerce />);
  expect(screen.getByTestId('onboarding-banner')).toBeInTheDocument();
});

it('banner não aparece para usuário com onboarding completo', () => {
  mockUser({ tenant: { onboarding_completed: true } });
  render(<ECommerce />);
  expect(screen.queryByTestId('onboarding-banner')).not.toBeInTheDocument();
});
```
