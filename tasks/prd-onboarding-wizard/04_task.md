# TASK 04 — Componente `ImageUploader` unificado

**Grupo:** C — ImageUploader  
**Depende de:** TASK 03 (endpoint `/image-proxy`)  
**Bloqueia:** TASK 05, TASK 07, TASK 08

## Objetivo

Criar o componente `ImageUploader.tsx` que unifica upload de arquivo e URL em uma única interface, garantindo que toda imagem passe pelo crop antes de ser retornada ao componente pai. Resolve o principal atrito técnico do onboarding: a lojista consegue usar foto do Instagram (via URL ou print).

## Entregáveis

### 1. `ImageUploader.tsx`

**Arquivo:** `frontend/src/components/ui/ImageUploader.tsx`

**Props:**
```typescript
interface ImageUploaderProps {
  onImageReady: (file: File) => void;
  currentImageUrl?: string;
  aspectRatio: '2:3' | '1:1' | '16:9';
  label?: string;
  disabled?: boolean;
}
```

**Dimensões por aspectRatio:**
```typescript
const DIMENSIONS = {
  '2:3':  { width: 600,  height: 900 },
  '1:1':  { width: 400,  height: 400 },
  '16:9': { width: 1200, height: 675 },
};
```

**Estados internos:**
```typescript
const [showCropper, setShowCropper] = useState(false);
const [imageToCrop, setImageToCrop] = useState<string | null>(null);
const [urlInput, setUrlInput] = useState('');
const [urlLoading, setUrlLoading] = useState(false);
const [urlError, setUrlError] = useState<string | null>(null);
const fileInputRef = useRef<HTMLInputElement>(null);
```

**Fluxo — arquivo:**
1. Usuária arrasta ou clica → `handleFileChange` ou `handleDrop`
2. `setImageToCrop(URL.createObjectURL(file))`
3. `setShowCropper(true)`
4. Crop confirmado → `handleCropComplete(blob)` → `new File([blob], 'image.jpg')` → `onImageReady(file)`

**Fluxo — URL:**
1. Usuária cola URL + clica "Usar"
2. `setUrlLoading(true)`
3. `POST /api/admin/image-proxy { url: urlInput }`
4. Sucesso: `setImageToCrop(response.data.url)` → `setShowCropper(true)`
5. Erro: `setUrlError(response.data.message)`

**Drag & drop:**
- `onDragOver`: `e.preventDefault()`
- `onDrop`: `e.preventDefault()` → `e.dataTransfer.files[0]` → mesmo fluxo de arquivo

**Interface visual (layout):**
```
┌─────────────────────────────────────────┐
│  [preview ou ícone de câmera + texto]   │  ← clicável, drag target
│  "Arraste ou clique para escolher"      │
└─────────────────────────────────────────┘
           ──── ou ────
[input de URL                    ] [Usar]
[mensagem de erro se houver          ]
```

Se `currentImageUrl` existir, exibir como preview na área de drop (com `object-cover`).

**Reuso do `ImageCropper` existente:**
```tsx
{showCropper && imageToCrop && (
  <ImageCropper
    imageSrc={imageToCrop}
    onCropComplete={handleCropComplete}
    onCancel={() => { setShowCropper(false); setImageToCrop(null); }}
    targetWidth={DIMENSIONS[aspectRatio].width}
    targetHeight={DIMENSIONS[aspectRatio].height}
  />
)}
```

### 2. Instalar dependência `canvas-confetti` (antecipando TASK 10)

```bash
cd frontend && npm install canvas-confetti @types/canvas-confetti
```

Incluir nesta task para não bloquear builds nas tasks seguintes.

## Critérios de Aceitação

- [x] Arrastar um arquivo de imagem abre o ImageCropper
- [x] Clicar na área abre o seletor de arquivo, que abre o ImageCropper
- [x] Colar uma URL válida + clicar "Usar" chama `/image-proxy`, depois abre o ImageCropper
- [x] Confirmar o crop chama `onImageReady(file)` com um `File` válido
- [x] Cancelar o crop fecha o modal sem chamar `onImageReady`
- [x] URL inválida/inacessível exibe mensagem de erro abaixo do campo
- [x] Prop `aspectRatio="1:1"` passa `targetWidth=400, targetHeight=400` ao ImageCropper
- [x] Prop `aspectRatio="16:9"` passa `targetWidth=1200, targetHeight=675` ao ImageCropper
- [x] `currentImageUrl` aparece como preview na área de drop
- [x] Funciona em mobile (touch para abrir galeria)

## Testes

**Testes de componente:** `frontend/src/components/ui/ImageUploader.test.tsx`

```typescript
describe('ImageUploader', () => {
  it('abre o cropper ao selecionar arquivo', async () => {
    render(<ImageUploader aspectRatio="2:3" onImageReady={vi.fn()} />);
    const file = new File(['img'], 'photo.jpg', { type: 'image/jpeg' });
    const input = document.querySelector('input[type="file"]') as HTMLInputElement;
    fireEvent.change(input, { target: { files: [file] } });
    expect(await screen.findByText('Confirmar Recorte')).toBeInTheDocument();
  });

  it('chama image-proxy ao colar URL e clicar Usar', async () => {
    const mockPost = vi.spyOn(api, 'post').mockResolvedValue({ data: { url: '/storage/proxy/abc.jpg' } });
    render(<ImageUploader aspectRatio="2:3" onImageReady={vi.fn()} />);
    fireEvent.change(screen.getByPlaceholderText('Cole o link da imagem'), {
      target: { value: 'https://example.com/foto.jpg' },
    });
    fireEvent.click(screen.getByText('Usar'));
    expect(mockPost).toHaveBeenCalledWith('/admin/image-proxy', { url: 'https://example.com/foto.jpg' });
    expect(await screen.findByText('Confirmar Recorte')).toBeInTheDocument();
  });

  it('exibe erro quando proxy falha', async () => {
    vi.spyOn(api, 'post').mockRejectedValue({
      response: { data: { message: 'Não foi possível carregar a imagem.' } },
    });
    render(<ImageUploader aspectRatio="2:3" onImageReady={vi.fn()} />);
    fireEvent.change(screen.getByPlaceholderText('Cole o link da imagem'), {
      target: { value: 'https://bad.url/x.jpg' },
    });
    fireEvent.click(screen.getByText('Usar'));
    expect(await screen.findByText('Não foi possível carregar a imagem.')).toBeInTheDocument();
  });

  it('chama onImageReady após confirmar crop', async () => {
    const onImageReady = vi.fn();
    // ... setup mock do getCroppedImg
    // confirmar que onImageReady é chamado com File
  });

  it('não chama onImageReady ao cancelar crop', async () => {
    const onImageReady = vi.fn();
    // ... abrir cropper, clicar cancelar
    expect(onImageReady).not.toHaveBeenCalled();
  });
});
```
