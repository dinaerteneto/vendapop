# TASK 08 — Passo 2: Vitrine (editar produtos demo)

**Grupo:** B — Wizard  
**Depende de:** TASK 02, TASK 04, TASK 06  
**Bloqueia:** TASK 09

## Objetivo

Implementar o segundo passo do wizard onde a lojista personaliza os 4 produtos demo criados pelo `DemoDataService`. O objetivo é que ela veja uma vitrine real com suas próprias fotos e nomes, aumentando o senso de propriedade antes mesmo de completar o onboarding.

## Entregáveis

### `StepVitrine.tsx`

**Arquivo:** `frontend/src/components/onboarding/StepVitrine.tsx`

**Props:**
```typescript
interface StepVitrineProps {
  onNext: () => void;
  onSkip: () => void;
}
```

**Estado:**
```typescript
interface DemoProduct {
  id: number;
  name: string;
  price: number;
  main_image_url: string | null;
  is_demo: boolean;
}

const [products, setProducts] = useState<DemoProduct[]>([]);
const [editingId, setEditingId] = useState<number | null>(null);
const [editName, setEditName] = useState('');
const [saving, setSaving] = useState<number | null>(null); // product id being saved
const [loading, setLoading] = useState(true);
```

**Carregamento dos produtos demo:**
```typescript
useEffect(() => {
  api.get('/admin/products', { params: { is_demo: true, per_page: 4 } })
    .then(res => setProducts(res.data.data.slice(0, 4)))
    .finally(() => setLoading(false));
}, []);
```

**Grid 2×2 — cada card:**
```
┌─────────────────┐
│  [imagem 2:3]   │  ← clicável → abre ImageUploader
│                 │
├─────────────────┤
│  [nome editável]│  ← clique no nome → input inline
│  R$ 00,00       │
└─────────────────┘
```

**Edição inline de nome:**
- Clicar no nome do produto mostra um `<input>` no lugar
- `onBlur` ou `Enter` → `PATCH /api/admin/products/{id}` com `{ name: editName }`
- Salvo: atualiza o produto no state local

**Troca de foto:**
- Clicar na área da imagem abre `ImageUploader` em modo modal (overlay)
- `onImageReady(file)` → `FormData` com `image` + `_method=PATCH` → `POST /api/admin/products/{id}`
- Após upload bem-sucedido: atualiza `main_image_url` no state local

**Handler de ImageUploader em modal:**
```typescript
const handleProductImage = async (productId: number, file: File) => {
  setSaving(productId);
  const formData = new FormData();
  formData.append('image', file);
  formData.append('_method', 'PATCH');
  try {
    const res = await api.post(`/admin/products/${productId}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    setProducts(ps => ps.map(p => p.id === productId
      ? { ...p, main_image_url: res.data.main_image_url }
      : p
    ));
    setEditingId(null);
  } finally {
    setSaving(null);
  }
};
```

**Handler de "Próximo":**
```typescript
const handleNext = async () => {
  await api.put('/admin/onboarding-status', { onboarding_step: 2 });
  onNext();
};
```

**Layout completo:**
```
[Título: "Personalize sua vitrine"]
[Subtítulo: "Edite as fotos e nomes dos produtos. Você pode adicionar mais depois."]

[Grid 2×2 de produtos demo]

[Link "Pular este passo"]
[Botão "Próximo →"]
```

**Estados de carregamento:**
- Skeleton cards enquanto carrega a lista
- Spinner sobre o card cujo produto está sendo salvo

## Critérios de Aceitação

- [x] Carrega os 4 produtos com `is_demo=true` ao montar o componente
- [x] Clicar na imagem de um produto abre o `ImageUploader` com `aspectRatio="2:3"`
- [x] Confirmar crop faz upload e atualiza a imagem no card sem refresh da página
- [x] Clicar no nome do produto exibe um input inline
- [x] Pressionar Enter ou perder foco salva o nome via `PATCH /api/admin/products/{id}`
- [x] Clicar "Próximo" chama `PUT /api/admin/onboarding-status { onboarding_step: 2 }` e avança
- [x] Clicar "Pular este passo" avança sem salvar
- [x] Preview da loja (iframe) é atualizado após avançar (via `refreshKey`)

## Testes

```typescript
// frontend/src/components/onboarding/StepVitrine.test.tsx

it('carrega produtos demo ao montar', async () => {
  const mockGet = vi.spyOn(api, 'get').mockResolvedValue({
    data: { data: mockDemoProducts },
  });
  render(<StepVitrine onNext={vi.fn()} onSkip={vi.fn()} />);
  expect(mockGet).toHaveBeenCalledWith('/admin/products', {
    params: { is_demo: true, per_page: 4 },
  });
  await waitFor(() => {
    expect(screen.getByText('Vestido Floral')).toBeInTheDocument();
  });
});

it('edição inline de nome salva via PATCH', async () => {
  const mockPost = vi.spyOn(api, 'post').mockResolvedValue({ data: { name: 'Novo Nome' } });
  render(<StepVitrine onNext={vi.fn()} onSkip={vi.fn()} />);
  await screen.findByText('Vestido Floral');
  fireEvent.click(screen.getByText('Vestido Floral'));
  fireEvent.change(screen.getByRole('textbox'), { target: { value: 'Novo Nome' } });
  fireEvent.keyDown(screen.getByRole('textbox'), { key: 'Enter' });
  await waitFor(() => {
    expect(mockPost).toHaveBeenCalledWith(
      expect.stringContaining('/admin/products/'),
      expect.any(FormData),
      expect.any(Object)
    );
  });
});

it('pular avança sem chamar onboarding-status', async () => {
  const mockPut = vi.spyOn(api, 'put').mockResolvedValue({});
  const onSkip = vi.fn();
  render(<StepVitrine onNext={vi.fn()} onSkip={onSkip} />);
  await screen.findByText('Pular este passo');
  fireEvent.click(screen.getByText('Pular este passo'));
  expect(mockPut).not.toHaveBeenCalled();
  expect(onSkip).toHaveBeenCalled();
});

it('próximo salva step e chama onNext', async () => {
  const mockPut = vi.spyOn(api, 'put').mockResolvedValue({});
  const onNext = vi.fn();
  render(<StepVitrine onNext={onNext} onSkip={vi.fn()} />);
  await screen.findByText('Próximo');
  fireEvent.click(screen.getByText('Próximo'));
  await waitFor(() => {
    expect(mockPut).toHaveBeenCalledWith('/admin/onboarding-status', { onboarding_step: 2 });
    expect(onNext).toHaveBeenCalled();
  });
});
```
