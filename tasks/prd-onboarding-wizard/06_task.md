# TASK 06 — Scaffold do Wizard: rota, layout, progress bar e preview

**Grupo:** B — Wizard  
**Depende de:** TASK 01, TASK 03  
**Bloqueia:** TASK 07, 08, 09, 10

## Objetivo

Criar a estrutura base do wizard: a rota `/admin/setup`, o layout de duas colunas, a barra de progresso, o componente de preview (iframe) e o redirect automático no login. Após esta task, a lojista já é redirecionada para o wizard no primeiro login e vê a estrutura visual com a prévia da loja.

## Entregáveis

### 1. Rota `/admin/setup` no `App.tsx`

**Arquivo:** `frontend/src/App.tsx`

Dentro do grupo protegido (`<ProtectedRoute>`), adicionar:
```tsx
<Route path="setup" element={<OnboardingSetup />} />
```

Importar o componente:
```tsx
import OnboardingSetup from './pages/AuthPages/OnboardingSetup';
```

### 2. Redirect no `SignIn.tsx`

**Arquivo:** `frontend/src/pages/AuthPages/SignIn.tsx`

Após login bem-sucedido, antes de `navigate('/admin')`:
```typescript
const tenant = data.tenant;
if (tenant && !tenant.onboarding_completed) {
  navigate('/admin/setup');
} else {
  navigate('/admin');
}
```

O campo `tenant` agora existe no response do login (TASK 03).

### 3. `OnboardingSetup.tsx` — página raiz

**Arquivo:** `frontend/src/pages/AuthPages/OnboardingSetup.tsx`

Estado:
```typescript
const [step, setStep] = useState<1 | 2 | 3 | 4>(() => {
  const saved = user?.tenant?.onboarding_step;
  return (saved && saved > 0 && saved <= 4) ? saved as 1|2|3|4 : 1;
});
const [previewRefreshKey, setPreviewRefreshKey] = useState(0);
const tenantSlug = user?.tenant?.slug ?? '';
```

Handlers:
```typescript
const handleNext = () => {
  setPreviewRefreshKey(k => k + 1);
  setStep(s => Math.min(s + 1, 4) as 1|2|3|4);
};
const handleSkip = () => setStep(s => Math.min(s + 1, 4) as 1|2|3|4);
```

Layout:
```tsx
<div className="min-h-screen flex flex-col bg-white">
  <WizardProgressBar currentStep={step} />
  <div className="flex flex-1 overflow-hidden">
    <div className="w-2/5 p-8 border-r overflow-y-auto">
      {step === 1 && <StepIdentidade onNext={handleNext} />}
      {step === 2 && <StepVitrine onNext={handleNext} onSkip={handleSkip} />}
      {step === 3 && <StepWhatsapp onNext={handleNext} />}
      {step === 4 && <StepCompartilhar />}
    </div>
    <div className="w-3/5 bg-gray-50 flex items-center justify-center p-8">
      <ShopPreview tenantSlug={tenantSlug} refreshKey={previewRefreshKey} />
    </div>
  </div>
</div>
```

Nesta task, os steps podem ser placeholders (`<div>Passo X</div>`) — serão implementados em TASK 07-10.

### 4. `WizardProgressBar.tsx`

**Arquivo:** `frontend/src/components/onboarding/WizardProgressBar.tsx`

```typescript
const STEPS = [
  { label: 'Identidade', icon: '🎨' },
  { label: 'Vitrine',    icon: '👗' },
  { label: 'WhatsApp',   icon: '💬' },
  { label: 'Compartilhar', icon: '🔗' },
];
```

Visual para cada passo:
- **Concluído** (< currentStep): ícone de check ✓, texto verde
- **Ativo** (= currentStep): círculo destacado com cor primária, texto bold
- **Pendente** (> currentStep): círculo cinza, texto cinza

### 5. `ShopPreview.tsx`

**Arquivo:** `frontend/src/components/onboarding/ShopPreview.tsx`

```tsx
const ShopPreview: React.FC<{ tenantSlug: string; refreshKey: number }> = ({ tenantSlug, refreshKey }) => {
  const shopUrl = `/${tenantSlug}?t=${refreshKey}`;
  return (
    <div className="flex flex-col items-center gap-3">
      <p className="text-xs text-gray-400 uppercase tracking-wide">Prévia da sua loja</p>
      <div className="border-[6px] border-gray-800 rounded-[2.5rem] overflow-hidden shadow-2xl bg-white"
           style={{ width: 375, height: 667 }}>
        <iframe src={shopUrl} width="375" height="667" title="Prévia" className="border-none" />
      </div>
    </div>
  );
};
```

## Critérios de Aceitação

- [x] Login com `onboarding_completed = false` redireciona para `/admin/setup`
- [x] Login com `onboarding_completed = true` redireciona para `/admin`
- [x] `/admin/setup` é acessível apenas para usuários autenticados (ProtectedRoute)
- [x] `WizardProgressBar` exibe 4 passos; o passo ativo está visualmente destacado
- [x] `ShopPreview` renderiza o iframe com a loja correta do tenant
- [x] Incrementar `refreshKey` causa reload do iframe (nova URL com `?t=N`)
- [x] Se `onboarding_step = 2` no tenant, wizard abre no passo 2

## Testes

```typescript
// frontend/src/pages/AuthPages/OnboardingSetup.test.tsx

it('redireciona para /admin/setup após login com onboarding incompleto', async () => {
  mockLoginResponse({ onboarding_completed: false, onboarding_step: 0 });
  render(<SignIn />);
  fireEvent.submit(screen.getByRole('form'));
  await waitFor(() => {
    expect(mockNavigate).toHaveBeenCalledWith('/admin/setup');
  });
});

it('redireciona para /admin após login com onboarding completo', async () => {
  mockLoginResponse({ onboarding_completed: true });
  render(<SignIn />);
  fireEvent.submit(screen.getByRole('form'));
  await waitFor(() => {
    expect(mockNavigate).toHaveBeenCalledWith('/admin');
  });
});

it('progressbar exibe passo 1 como ativo', () => {
  render(<WizardProgressBar currentStep={1} />);
  expect(screen.getByText('Identidade').closest('[data-active]')).toBeTruthy();
});

it('progressbar exibe passos anteriores como concluídos', () => {
  render(<WizardProgressBar currentStep={3} />);
  // Passos 1 e 2 têm check mark
  expect(screen.getAllByText('✓')).toHaveLength(2);
});

it('wizard abre no passo salvo do tenant', () => {
  mockTenant({ onboarding_step: 2 });
  render(<OnboardingSetup />);
  expect(screen.getByTestId('step-vitrine')).toBeInTheDocument();
});
```
