# TASK 07 — Passo 1: Identidade (logo + cor)

**Grupo:** B — Wizard  
**Depende de:** TASK 04, TASK 06  
**Bloqueia:** TASK 08

## Objetivo

Implementar o primeiro passo do wizard onde a lojista personaliza a identidade visual da loja: faz upload da logo e escolhe a cor primária. Ao concluir, o preview ao vivo reflete as mudanças.

## Entregáveis

### `StepIdentidade.tsx`

**Arquivo:** `frontend/src/components/onboarding/StepIdentidade.tsx`

**Props:**
```typescript
interface StepIdentidadeProps {
  onNext: () => void;
}
```

**Estado:**
```typescript
const [logoFile, setLogoFile] = useState<File | null>(null);
const [primaryColor, setPrimaryColor] = useState('#7c3aed');
const [saving, setSaving] = useState(false);
```

**8 cores pré-definidas:**
```typescript
const PRESET_COLORS = [
  { hex: '#7c3aed', label: 'Roxo' },
  { hex: '#2563eb', label: 'Azul' },
  { hex: '#16a34a', label: 'Verde' },
  { hex: '#dc2626', label: 'Vermelho' },
  { hex: '#d97706', label: 'Âmbar' },
  { hex: '#db2777', label: 'Rosa' },
  { hex: '#0891b2', label: 'Ciano' },
  { hex: '#374151', label: 'Grafite' },
];
```

**Seletor de cor — UI:**
- Grid 4×2 de chips circulares clicáveis (32×32px)
- Chip selecionado: borda de 2px branca + sombra colorida
- Campo hex abaixo: `<input type="color">` + `<input type="text" value={primaryColor}>` sincronizados
- Preview ao vivo: faixa horizontal com a cor selecionada, texto "Sua cor primária"

**Handler de submit:**
```typescript
const handleNext = async () => {
  setSaving(true);
  try {
    const formData = new FormData();
    formData.append('name', tenant.name);           // obrigatório pelo endpoint
    formData.append('whatsapp_number', tenant.whatsapp_number); // obrigatório
    formData.append('primary_color', primaryColor);
    if (logoFile) formData.append('logo', logoFile);
    formData.append('_method', 'PATCH');

    await api.post('/admin/store-settings', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    await api.put('/admin/onboarding-status', { onboarding_step: 1 });
    onNext();
  } finally {
    setSaving(false);
  }
};
```

**Layout:**
```
[Título: "Como é a identidade da sua loja?"]
[Subtítulo: "Você pode mudar isso depois nas configurações."]

Logo da loja:
[ImageUploader aspectRatio="1:1" label="Logo da loja"]

Cor principal:
[chip #7c3aed] [chip #2563eb] ... (8 chips)
[input type=color] [input text hex]

[Botão "Próximo →" — desabilitado durante salvamento]
[Link "Pular este passo" — avança sem salvar]
```

## Critérios de Aceitação

- [x] Selecionar chip de cor atualiza o preview da cor imediatamente
- [x] Editar o campo hex atualiza os chips (seleciona o mais próximo ou mostra cor customizada)
- [x] Upload de logo via drag & drop abre o ImageCropper 1:1
- [x] Clicar "Próximo" salva cor e logo via `PATCH /api/admin/store-settings`
- [x] Após salvar, `PUT /api/admin/onboarding-status { onboarding_step: 1 }` é chamado
- [x] `onNext()` é chamado após ambas as requisições completarem
- [x] Preview da loja (iframe) é atualizado após avançar
- [x] "Pular este passo" avança sem fazer nenhuma requisição
- [x] Botão "Próximo" desabilitado durante o salvamento

## Testes

```typescript
// frontend/src/components/onboarding/StepIdentidade.test.tsx

it('selecionar cor atualiza preview imediatamente', () => {
  render(<StepIdentidade onNext={vi.fn()} />);
  fireEvent.click(screen.getByTitle('Azul'));
  expect(screen.getByTestId('color-preview')).toHaveStyle({ backgroundColor: '#2563eb' });
});

it('clicar Próximo salva cor e chama onNext', async () => {
  const mockPatch = vi.spyOn(api, 'post').mockResolvedValue({});
  const mockPut = vi.spyOn(api, 'put').mockResolvedValue({});
  const onNext = vi.fn();

  render(<StepIdentidade onNext={onNext} />);
  fireEvent.click(screen.getByTitle('Verde'));
  fireEvent.click(screen.getByText('Próximo'));

  await waitFor(() => {
    expect(mockPatch).toHaveBeenCalledWith('/admin/store-settings', expect.any(FormData), expect.any(Object));
    expect(mockPut).toHaveBeenCalledWith('/admin/onboarding-status', { onboarding_step: 1 });
    expect(onNext).toHaveBeenCalled();
  });
});

it('pular não faz requisições', async () => {
  const mockApi = vi.spyOn(api, 'post').mockResolvedValue({});
  const onNext = vi.fn();

  render(<StepIdentidade onNext={onNext} />);
  fireEvent.click(screen.getByText('Pular este passo'));

  expect(mockApi).not.toHaveBeenCalled();
  expect(onNext).toHaveBeenCalled();
});

it('botão desabilitado durante salvamento', async () => {
  vi.spyOn(api, 'post').mockImplementation(() => new Promise(() => {})); // nunca resolve
  render(<StepIdentidade onNext={vi.fn()} />);
  fireEvent.click(screen.getByText('Próximo'));
  expect(screen.getByText('Próximo')).toBeDisabled();
});
```
