# TASK 10 — Passo 4: Compartilhar + confetti

**Grupo:** B — Wizard  
**Depende de:** TASK 06  
**Bloqueia:** TASK 11

## Objetivo

Implementar o quarto e último passo do wizard: a tela de conclusão. Inclui a animação de confetti, o link da loja para copiar e compartilhar, e a marcação do onboarding como concluído. Ao fechar, redireciona para o dashboard.

## Entregáveis

### `StepCompartilhar.tsx`

**Arquivo:** `frontend/src/components/onboarding/StepCompartilhar.tsx`

**Props:**
```typescript
interface StepCompartilharProps {
  tenantSlug: string;
}
```

**Estado:**
```typescript
const [copied, setCopied] = useState(false);
const [done, setDone] = useState(false);
```

**Efeito de confetti ao montar:**
```typescript
import confetti from 'canvas-confetti';

useEffect(() => {
  // Disparar confetti imediatamente ao montar o passo 4
  confetti({
    particleCount: 120,
    spread: 70,
    origin: { y: 0.6 },
    colors: ['#7c3aed', '#db2777', '#2563eb', '#16a34a', '#f59e0b'],
  });
}, []);
```

**URL da loja:**
```typescript
const shopUrl = `${window.location.origin}/${tenantSlug}`;
```

**Copiar link:**
```typescript
const handleCopy = async () => {
  await navigator.clipboard.writeText(shopUrl);
  setCopied(true);
  setTimeout(() => setCopied(false), 2000);
};
```

**Marcar onboarding completo e ir para o dashboard:**
```typescript
const handleFinish = async () => {
  await api.put('/admin/onboarding-status', {
    onboarding_completed: true,
    onboarding_step: 4,
  });
  navigate('/admin');
};
```

**Layout:**
```
[Ícone de celebração 🎉 — grande, centralizado]
[Título: "Sua loja está pronta!"]
[Subtítulo: "Compartilhe o link e comece a vender agora mesmo."]

Link da loja:
┌─────────────────────────────────┬──────────┐
│ vendapop.com.br/sua-loja        │ [Copiar] │
└─────────────────────────────────┴──────────┘
["Link copiado!" — aparece por 2 segundos após clicar]

──── Compartilhe no Instagram ────
[Instrução textual com 3 passos:]
  1. Abra o Instagram
  2. Adicione aos Stories ou na bio
  3. Cole o link: vendapop.com.br/sua-loja

[Botão primário: "Ir para o painel →"]
```

**Instrução Instagram — componente:**
```tsx
const InstagramInstructions: React.FC<{ shopUrl: string }> = ({ shopUrl }) => (
  <div className="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-4 text-sm">
    <p className="font-medium text-gray-700 mb-2">📸 Compartilhe no Instagram</p>
    <ol className="list-decimal list-inside space-y-1 text-gray-600">
      <li>Abra o Instagram e vá em Editar Perfil</li>
      <li>Cole o link da sua loja no campo "Site"</li>
      <li>Salve e compartilhe nos Stories!</li>
    </ol>
    <p className="mt-2 text-xs text-gray-400 break-all">{shopUrl}</p>
  </div>
);
```

## Critérios de Aceitação

- [x] Confetti dispara ao entrar no passo 4
- [x] Link da loja exibe a URL correta baseada no slug do tenant
- [x] Clicar "Copiar" copia a URL para o clipboard e exibe "Link copiado!" por 2 segundos
- [x] Clicar "Ir para o painel" chama `PUT /api/admin/onboarding-status { onboarding_completed: true, onboarding_step: 4 }`
- [x] Após marcar como concluído, redireciona para `/admin`
- [x] Após o redirecionamento, o banner de retomada NÃO aparece (onboarding_completed = true)

## Testes

```typescript
// frontend/src/components/onboarding/StepCompartilhar.test.tsx

it('dispara confetti ao montar', () => {
  const mockConfetti = vi.fn();
  vi.mock('canvas-confetti', () => ({ default: mockConfetti }));

  render(<StepCompartilhar tenantSlug="modachic" />);
  expect(mockConfetti).toHaveBeenCalledOnce();
});

it('exibe link correto baseado no tenantSlug', () => {
  render(<StepCompartilhar tenantSlug="minha-loja" />);
  expect(screen.getByText(/minha-loja/)).toBeInTheDocument();
});

it('clicar Copiar copia URL e mostra feedback', async () => {
  const mockWrite = vi.fn().mockResolvedValue(undefined);
  Object.assign(navigator, { clipboard: { writeText: mockWrite } });

  render(<StepCompartilhar tenantSlug="modachic" />);
  fireEvent.click(screen.getByText('Copiar'));

  expect(mockWrite).toHaveBeenCalledWith(expect.stringContaining('modachic'));
  expect(await screen.findByText('Link copiado!')).toBeInTheDocument();
});

it('feedback de cópia desaparece após 2 segundos', async () => {
  vi.useFakeTimers();
  render(<StepCompartilhar tenantSlug="modachic" />);
  fireEvent.click(screen.getByText('Copiar'));
  expect(await screen.findByText('Link copiado!')).toBeInTheDocument();
  vi.advanceTimersByTime(2100);
  expect(screen.queryByText('Link copiado!')).not.toBeInTheDocument();
  vi.useRealTimers();
});

it('finalizar marca onboarding completo e navega para /admin', async () => {
  const mockPut = vi.spyOn(api, 'put').mockResolvedValue({});
  const mockNavigate = vi.fn();
  vi.mock('react-router-dom', async () => ({
    ...(await vi.importActual('react-router-dom')),
    useNavigate: () => mockNavigate,
  }));

  render(<StepCompartilhar tenantSlug="modachic" />);
  fireEvent.click(screen.getByText('Ir para o painel'));

  await waitFor(() => {
    expect(mockPut).toHaveBeenCalledWith('/admin/onboarding-status', {
      onboarding_completed: true,
      onboarding_step: 4,
    });
    expect(mockNavigate).toHaveBeenCalledWith('/admin');
  });
});
```
