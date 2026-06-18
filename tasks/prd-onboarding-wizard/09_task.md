# TASK 09 — Passo 3: WhatsApp

**Grupo:** B — Wizard  
**Depende de:** TASK 06  
**Bloqueia:** TASK 10

## Objetivo

Implementar o terceiro passo do wizard onde a lojista configura o número do WhatsApp e a mensagem de boas-vindas que será usada quando um cliente clicar em "Comprar". Inclui um mockup visual da conversa do WhatsApp para aumentar o engajamento.

## Entregáveis

### `StepWhatsapp.tsx`

**Arquivo:** `frontend/src/components/onboarding/StepWhatsapp.tsx`

**Props:**
```typescript
interface StepWhatsappProps {
  onNext: () => void;
}
```

**Estado:**
```typescript
const [phone, setPhone] = useState(tenant?.whatsapp_number ?? '');
const [message, setMessage] = useState(
  tenant?.whatsapp_message ?? 'Olá! Tenho interesse nos produtos da sua loja. 😊'
);
const [saving, setSaving] = useState(false);
const [error, setError] = useState<string | null>(null);
```

**Máscara de telefone brasileiro:**

Implementar máscara inline sem biblioteca externa:
```typescript
const formatPhone = (raw: string): string => {
  const digits = raw.replace(/\D/g, '').slice(0, 11);
  if (digits.length <= 2) return digits;
  if (digits.length <= 7) return `(${digits.slice(0,2)}) ${digits.slice(2)}`;
  if (digits.length <= 11) return `(${digits.slice(0,2)}) ${digits.slice(2,7)}-${digits.slice(7)}`;
  return `(${digits.slice(0,2)}) ${digits.slice(2,7)}-${digits.slice(7,11)}`;
};

const handlePhoneChange = (e: React.ChangeEvent<HTMLInputElement>) => {
  setPhone(formatPhone(e.target.value));
};
```

**Validação antes de salvar:**
```typescript
const phoneDigits = phone.replace(/\D/g, '');
if (phoneDigits.length < 10 || phoneDigits.length > 11) {
  setError('Número inválido. Use o formato (11) 99999-9999');
  return;
}
```

**Handler de submit:**
```typescript
const handleNext = async () => {
  const digits = phone.replace(/\D/g, '');
  if (digits.length < 10) {
    setError('Número inválido. Use o formato (11) 99999-9999');
    return;
  }
  setSaving(true);
  setError(null);
  try {
    await api.post('/admin/store-settings', {
      _method: 'PATCH',
      whatsapp_number: digits,
      whatsapp_message: message,
      // Campos obrigatórios pelo endpoint (manter valores atuais)
      name: tenant?.name,
    }, { headers: { 'Content-Type': 'application/json' } });
    await api.put('/admin/onboarding-status', { onboarding_step: 3 });
    onNext();
  } catch {
    setError('Não foi possível salvar. Tente novamente.');
  } finally {
    setSaving(false);
  }
};
```

**Mockup de conversa WhatsApp:**

Componente visual estático que usa o valor atual de `message`:
```tsx
const WhatsAppMockup: React.FC<{ message: string }> = ({ message }) => (
  <div className="bg-[#e5ddd5] rounded-xl p-4 max-w-xs mx-auto font-sans text-sm">
    {/* Header */}
    <div className="bg-[#075E54] text-white rounded-t-xl -mx-4 -mt-4 p-3 mb-4 flex items-center gap-2">
      <div className="w-8 h-8 rounded-full bg-gray-300" />
      <span className="font-medium">Sua Loja</span>
    </div>
    {/* Balão do cliente */}
    <div className="bg-white rounded-lg rounded-tl-none p-3 mb-2 shadow-sm max-w-[80%]">
      <p>{message}</p>
      <span className="text-[10px] text-gray-400 float-right ml-2">12:34 ✓✓</span>
    </div>
    {/* Balão da lojista */}
    <div className="bg-[#dcf8c6] rounded-lg rounded-tr-none p-3 ml-auto shadow-sm max-w-[80%]">
      <p>Oi! Pode me contar mais o que você gostou? 😊</p>
      <span className="text-[10px] text-gray-400 float-right ml-2">12:35 ✓✓</span>
    </div>
  </div>
);
```

O mockup é atualizado em tempo real conforme a lojista digita a mensagem (bind direto ao `message` state).

**Layout:**
```
[Título: "Como os clientes vão falar com você?"]
[Subtítulo: "Quando alguém clicar em 'Comprar', abrirá uma conversa no WhatsApp."]

Número do WhatsApp:
[(11) 99999-9999                    ]
[Mensagem de boas-vindas:]
[textarea — "Olá! Tenho interesse..."]

──── Prévia da conversa ────
[WhatsAppMockup]

[Mensagem de erro se houver]
[Botão "Próximo →" — desabilitado durante salvamento]
```

## Critérios de Aceitação

- [x] Campo de telefone aplica máscara `(xx) xxxxx-xxxx` conforme o usuário digita
- [x] Clicar "Próximo" sem preencher número exibe mensagem de erro
- [x] Número com menos de 10 dígitos exibe mensagem de erro
- [x] Número válido + clicar "Próximo" salva via `PATCH /api/admin/store-settings`
- [x] Após salvar, `PUT /api/admin/onboarding-status { onboarding_step: 3 }` é chamado
- [x] `onNext()` é chamado após as requisições completarem
- [x] Mockup do WhatsApp exibe o texto da mensagem em tempo real
- [x] Botão desabilitado durante salvamento

## Testes

```typescript
// frontend/src/components/onboarding/StepWhatsapp.test.tsx

it('aplica máscara de telefone brasileiro', () => {
  render(<StepWhatsapp onNext={vi.fn()} />);
  const input = screen.getByPlaceholderText('(11) 99999-9999');
  fireEvent.change(input, { target: { value: '11987654321' } });
  expect(input).toHaveValue('(11) 98765-4321');
});

it('exibe erro para número inválido', async () => {
  render(<StepWhatsapp onNext={vi.fn()} />);
  const input = screen.getByPlaceholderText('(11) 99999-9999');
  fireEvent.change(input, { target: { value: '119' } });
  fireEvent.click(screen.getByText('Próximo'));
  expect(await screen.findByText(/número inválido/i)).toBeInTheDocument();
});

it('salva número e mensagem ao clicar Próximo', async () => {
  const mockPost = vi.spyOn(api, 'post').mockResolvedValue({});
  const mockPut = vi.spyOn(api, 'put').mockResolvedValue({});
  const onNext = vi.fn();

  render(<StepWhatsapp onNext={onNext} />);
  fireEvent.change(screen.getByPlaceholderText('(11) 99999-9999'), {
    target: { value: '11987654321' },
  });
  fireEvent.click(screen.getByText('Próximo'));

  await waitFor(() => {
    expect(mockPost).toHaveBeenCalledWith('/admin/store-settings', expect.objectContaining({
      whatsapp_number: '11987654321',
    }), expect.any(Object));
    expect(mockPut).toHaveBeenCalledWith('/admin/onboarding-status', { onboarding_step: 3 });
    expect(onNext).toHaveBeenCalled();
  });
});

it('mockup exibe texto da mensagem em tempo real', () => {
  render(<StepWhatsapp onNext={vi.fn()} />);
  const textarea = screen.getByRole('textbox', { name: /mensagem/i });
  fireEvent.change(textarea, { target: { value: 'Nova mensagem teste' } });
  expect(screen.getByText('Nova mensagem teste')).toBeInTheDocument();
});
```
