# TechSpec — Onboarding Wizard & ImageUploader Unificado

**PRD:** `tasks/prd-onboarding-wizard/prd.md`  
**Data:** 2026-06-17  
**Stack:** Laravel 12 (backend) · React 18 + TypeScript + Tailwind (frontend) · MySQL 8 · Docker

---

## 1. Visão Técnica

Três entregas independentes que se integram no fluxo de primeiro acesso:

| Entrega | Escopo | Arquivos principais |
|---------|--------|---------------------|
| **A — Demo Data** | Backend: seeder + hook no RegistrationController | `DemoDataService.php`, `RegistrationController.php`, 3 migrations |
| **B — Wizard** | Frontend: rota `/admin/setup` + componente 4 passos | `OnboardingSetup.tsx`, `OnboardingWizardStep*.tsx` |
| **C — ImageUploader** | Frontend: componente unificado + backend proxy | `ImageUploader.tsx`, `ImageProxyController.php` |

As entregas podem ser implementadas na ordem A → C → B (demo data primeiro, uploader segundo, wizard por último — pois o wizard usa os dois).

---

## 2. Migrações

### 2.1 `tenants` — onboarding state

**Arquivo:** `2026_06_17_190001_add_onboarding_fields_to_tenants_table.php`

```php
Schema::table('tenants', function (Blueprint $table) {
    $table->boolean('onboarding_completed')->default(false)->after('business_sector');
    $table->tinyInteger('onboarding_step')->default(0)->after('onboarding_completed');
});
```

`onboarding_step` valores: `0` = não iniciado, `1` = identidade concluído, `2` = vitrine concluído, `3` = whatsapp concluído, `4` = concluído (sinônimo de `onboarding_completed = true`).

### 2.2 `products` — flag demo

**Arquivo:** `2026_06_17_190002_add_is_demo_to_products_table.php`

```php
Schema::table('products', function (Blueprint $table) {
    $table->boolean('is_demo')->default(false)->after('is_hot');
});
```

### 2.3 `categories` — flag demo

**Arquivo:** `2026_06_17_190003_add_is_demo_to_categories_table.php`

```php
Schema::table('categories', function (Blueprint $table) {
    $table->boolean('is_demo')->default(false)->after('is_active');
});
```

---

## 3. Backend — Entrega A: Demo Data

### 3.1 `DemoDataService`

**Arquivo:** `app/Services/DemoDataService.php`

Responsabilidade única: criar categorias, produtos e banner demo para um `Tenant` recém-criado. Não é um seeder Artisan — é um service chamado programaticamente.

```php
namespace App\Services;

class DemoDataService
{
    public function seedFor(Tenant $tenant): void
    {
        // Criar atributos padrão de moda (Tamanho: P, M, G, GG)
        $attributeService = app(ProductAttributeService::class);
        $attributeService->createDefaultAttributesForSector($tenant, 'fashion');

        // 2 categorias demo
        $catNovidades = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Novidades',
            'is_active' => true,
            'is_demo' => true,
            'image_url' => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=500&h=500&fit=crop',
        ]);

        $catPromocoes = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Promoções',
            'is_active' => true,
            'is_demo' => true,
            'image_url' => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=500&h=500&fit=crop',
        ]);

        // 4 produtos demo
        $demoProducts = [
            [
                'name' => 'Vestido Floral Midi',
                'category_id' => $catNovidades->id,
                'description' => 'Exemplo de produto. Substitua pelo seu!',
                'price' => 149.90,
                'promotional_price' => 119.90,
                'main_image' => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=600&h=900&fit=crop',
            ],
            [
                'name' => 'Blusa Básica Manga Longa',
                'category_id' => $catNovidades->id,
                'description' => 'Exemplo de produto. Substitua pelo seu!',
                'price' => 79.90,
                'main_image' => 'https://images.unsplash.com/photo-1551163943-3f6a2b4ae78c?w=600&h=900&fit=crop',
            ],
            [
                'name' => 'Calça Jeans Skinny',
                'category_id' => $catPromocoes->id,
                'description' => 'Exemplo de produto. Substitua pelo seu!',
                'price' => 199.90,
                'promotional_price' => 169.90,
                'main_image' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=600&h=900&fit=crop',
            ],
            [
                'name' => 'Vestido Longo Elegante',
                'category_id' => $catPromocoes->id,
                'description' => 'Exemplo de produto. Substitua pelo seu!',
                'price' => 349.90,
                'main_image' => 'https://images.unsplash.com/photo-1594633313593-bab3825d0caf?w=600&h=900&fit=crop',
            ],
        ];

        foreach ($demoProducts as $data) {
            $mainImage = $data['main_image'];
            unset($data['main_image']);
            $product = Product::create(array_merge($data, [
                'tenant_id' => $tenant->id,
                'is_active' => true,
                'is_demo' => true,
            ]));
            $product->images()->create([
                'url' => $mainImage,
                'is_external' => true,
                'is_main' => true,
            ]);
        }

        // 1 banner demo
        RotatingBanner::create([
            'tenant_id' => $tenant->id,
            'title' => 'Bem-vinda à sua loja!',
            'description' => 'Personalize sua loja nas configurações.',
            'image_url' => 'https://images.unsplash.com/photo-1551163943-3f6a2b4ae78c?w=1200&h=675&fit=crop',
            'is_active' => true,
            'order' => 0,
            'is_external' => true,
        ]);
    }
}
```

### 3.2 Hook no `RegistrationController`

No método `store`, após a criação do tenant (ainda dentro do `DB::transaction`), adicionar:

```php
// Dentro do DB::transaction, após Tenant::create(...)
$demoService = app(\App\Services\DemoDataService::class);
$demoService->seedFor($tenant);
```

Também adicionar ao `$fillable` do `Tenant` model: `'onboarding_completed'`, `'onboarding_step'`.  
Adicionar ao `$fillable` do `Category` model: `'is_demo'`.  
Adicionar ao `$fillable` do `Product` model: `'is_demo'`.

### 3.3 `OnboardingController`

**Arquivo:** `app/Http/Controllers/Api/Admin/OnboardingController.php`  
**Rota:** `PUT /api/admin/onboarding-status` (autenticada, dentro do grupo `auth:sanctum`)

Atualiza `onboarding_step` e/ou `onboarding_completed` para o tenant do usuário autenticado.

```php
public function update(Request $request)
{
    $validated = $request->validate([
        'onboarding_step'      => 'nullable|integer|min:0|max:4',
        'onboarding_completed' => 'nullable|boolean',
    ]);

    $tenant = $request->user()->tenant;
    $tenant->update(array_filter($validated, fn($v) => $v !== null));

    return response()->json(['message' => 'ok', 'tenant' => $tenant]);
}
```

Adicionar ao `routes/api.php` dentro do grupo autenticado:
```php
Route::put('/onboarding-status', [OnboardingController::class, 'update']);
```

---

## 4. Backend — Entrega C: Image Proxy

### 4.1 `ImageProxyController`

**Arquivo:** `app/Http/Controllers/Api/Admin/ImageProxyController.php`  
**Rota:** `POST /api/admin/image-proxy` (autenticada)

Recebe uma URL de imagem externa, faz o fetch server-side, salva no storage local e retorna a URL pública.

```php
namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageProxyController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url|max:2048',
        ]);

        try {
            $response = Http::timeout(10)->get($validated['url']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Não foi possível carregar a imagem. Verifique o link e tente novamente.'], 422);
        }

        if (!$response->successful()) {
            return response()->json(['message' => 'Não foi possível carregar a imagem. Verifique o link e tente novamente.'], 422);
        }

        // Validate content type
        $contentType = $response->header('Content-Type');
        if (!str_starts_with($contentType, 'image/')) {
            return response()->json(['message' => 'O link não aponta para uma imagem válida.'], 422);
        }

        // Determine extension from content-type
        $ext = match(true) {
            str_contains($contentType, 'jpeg') => 'jpg',
            str_contains($contentType, 'png')  => 'png',
            str_contains($contentType, 'webp') => 'webp',
            str_contains($contentType, 'gif')  => 'gif',
            default => 'jpg',
        };

        $filename = 'proxy/' . Str::uuid() . '.' . $ext;
        Storage::disk('public')->put($filename, $response->body());

        return response()->json([
            'url'  => url(Storage::url($filename)),
            'path' => $filename,
        ]);
    }
}
```

Adicionar à rota autenticada:
```php
Route::post('/image-proxy', [ImageProxyController::class, 'store']);
```

---

## 5. Frontend — Entrega C: `ImageUploader`

### 5.1 Componente `ImageUploader.tsx`

**Arquivo:** `frontend/src/components/ui/ImageUploader.tsx`

Substitui o uso direto de `<input type="file">` + campo URL separado. É um wrapper que aceita ambos os modos e sempre passa pelo `ImageCropper` existente antes de retornar a imagem final.

**Props:**

```typescript
interface ImageUploaderProps {
  onImageReady: (file: File) => void;   // chamado após crop confirmado
  currentImageUrl?: string;             // imagem atual (preview)
  aspectRatio: '2:3' | '1:1' | '16:9'; // define as dimensões do crop
  label?: string;                       // label opcional acima do uploader
}
```

**Mapeamento de dimensões por `aspectRatio`:**

```typescript
const DIMENSIONS = {
  '2:3':   { width: 600,  height: 900 },
  '1:1':   { width: 400,  height: 400 },
  '16:9':  { width: 1200, height: 675 },
};
```

**Fluxo interno:**

```
[Usuária arrasta/clica arquivo]
    → setImageToCrop(URL.createObjectURL(file))
    → setShowCropper(true)

[Usuária cola URL + clica "Usar"]
    → POST /api/admin/image-proxy { url }
    → resposta: { url: localUrl }
    → setImageToCrop(localUrl)
    → setShowCropper(true)

[ImageCropper: onCropComplete(blob)]
    → new File([blob], 'image.jpg', { type: 'image/jpeg' })
    → onImageReady(file) ← callback para o pai salvar
    → setShowCropper(false)
```

**Estados internos:**

```typescript
const [showCropper, setShowCropper] = useState(false);
const [imageToCrop, setImageToCrop] = useState<string | null>(null);
const [urlInput, setUrlInput] = useState('');
const [urlLoading, setUrlLoading] = useState(false);
const [urlError, setUrlError] = useState<string | null>(null);
```

**Renderização (estrutura):**

```tsx
<div className="...">
  {/* Área de drop / clique */}
  <div
    onDrop={handleDrop}
    onDragOver={...}
    onClick={() => fileInputRef.current?.click()}
    className="border-2 border-dashed ... cursor-pointer"
  >
    {currentImageUrl
      ? <img src={currentImageUrl} className="w-full h-48 object-cover rounded" />
      : <p>Arraste ou clique para escolher</p>
    }
  </div>
  <input ref={fileInputRef} type="file" accept="image/*" className="hidden" onChange={handleFileChange} />

  {/* Separador */}
  <div className="flex items-center gap-2 my-2">
    <hr className="flex-1" /><span className="text-xs text-gray-400">ou</span><hr className="flex-1" />
  </div>

  {/* Campo URL */}
  <div className="flex gap-2">
    <input
      type="url"
      placeholder="Cole o link da imagem"
      value={urlInput}
      onChange={e => setUrlInput(e.target.value)}
      className="flex-1 border rounded px-3 py-2 text-sm"
    />
    <button onClick={handleUrlFetch} disabled={urlLoading} className="...">
      {urlLoading ? '...' : 'Usar'}
    </button>
  </div>
  {urlError && <p className="text-xs text-red-500 mt-1">{urlError}</p>}

  {/* ImageCropper (existente) */}
  {showCropper && imageToCrop && (
    <ImageCropper
      imageSrc={imageToCrop}
      onCropComplete={handleCropComplete}
      onCancel={() => setShowCropper(false)}
      targetWidth={DIMENSIONS[aspectRatio].width}
      targetHeight={DIMENSIONS[aspectRatio].height}
    />
  )}
</div>
```

### 5.2 Integração nos formulários existentes

**`ProductForm.tsx`** — substituir a lógica de upload/URL (linhas ~63–719) por:

```tsx
<ImageUploader
  aspectRatio="2:3"
  currentImageUrl={previewUrl ?? undefined}
  onImageReady={(file) => {
    setImageFile(file);
    setPreviewUrl(URL.createObjectURL(file));
    setImageMode('file');
  }}
  label="Foto do produto"
/>
```

**`StoreSettings`** — substituir campo de logo por:
```tsx
<ImageUploader aspectRatio="1:1" currentImageUrl={logoUrl} onImageReady={handleLogoReady} label="Logo da loja" />
```

**`RotatingBannerForm`** — substituir campo de imagem por:
```tsx
<ImageUploader aspectRatio="16:9" currentImageUrl={bannerUrl} onImageReady={handleBannerReady} label="Imagem do banner" />
```

---

## 6. Frontend — Entrega B: Wizard `/admin/setup`

### 6.1 Rota

**`App.tsx`** — adicionar dentro do grupo protegido:

```tsx
<Route path="setup" element={<OnboardingSetup />} />
```

### 6.2 Redirect automático no login

**`SignIn.tsx`** — após login bem-sucedido, antes de `navigate('/admin')`:

```typescript
const tenant = data.tenant; // assumindo que o endpoint de login retorna tenant
if (!tenant.onboarding_completed) {
  navigate('/admin/setup');
} else {
  navigate('/admin');
}
```

O endpoint `POST /api/admin/login` (verificar se já retorna o tenant — se não, adicionar ao response).

### 6.3 `OnboardingSetup.tsx`

**Arquivo:** `frontend/src/pages/AuthPages/OnboardingSetup.tsx`

**Estado global do wizard:**

```typescript
interface WizardState {
  step: 1 | 2 | 3 | 4;
  identity: { logoFile: File | null; primaryColor: string };
  vitrine: { editingProductId: number | null };
  whatsapp: { number: string; message: string };
}
```

**Estrutura de layout:**

```tsx
<div className="min-h-screen flex flex-col">
  {/* Barra de progresso */}
  <WizardProgressBar currentStep={state.step} />

  <div className="flex flex-1">
    {/* Coluna esquerda: formulário */}
    <div className="w-2/5 p-8 border-r overflow-y-auto">
      {state.step === 1 && <StepIdentidade onNext={handleNext} />}
      {state.step === 2 && <StepVitrine    onNext={handleNext} onSkip={handleSkip} />}
      {state.step === 3 && <StepWhatsapp   onNext={handleNext} />}
      {state.step === 4 && <StepCompartilhar onConcluir={handleConcluir} />}
    </div>

    {/* Coluna direita: preview ao vivo */}
    <div className="w-3/5 bg-gray-100 flex items-center justify-center p-8">
      <ShopPreview tenantSlug={tenantSlug} refreshKey={previewRefreshKey} />
    </div>
  </div>
</div>
```

### 6.4 `ShopPreview.tsx`

**Arquivo:** `frontend/src/components/onboarding/ShopPreview.tsx`

Renderiza o iframe da loja em viewport de celular. O `refreshKey` é incrementado a cada `handleNext` para forçar reload do iframe.

```tsx
interface ShopPreviewProps {
  tenantSlug: string;
  refreshKey: number;
}

const ShopPreview: React.FC<ShopPreviewProps> = ({ tenantSlug, refreshKey }) => {
  const shopUrl = `/${tenantSlug}?preview=1&t=${refreshKey}`;

  return (
    <div className="flex flex-col items-center gap-2">
      <p className="text-xs text-gray-500">Prévia da sua loja</p>
      {/* Moldura de celular */}
      <div className="border-4 border-gray-800 rounded-3xl overflow-hidden shadow-2xl"
           style={{ width: 375, height: 667 }}>
        <iframe
          src={shopUrl}
          width="375"
          height="667"
          title="Prévia da loja"
          className="border-none"
        />
      </div>
    </div>
  );
};
```

### 6.5 Passo 1 — `StepIdentidade.tsx`

**Arquivo:** `frontend/src/components/onboarding/StepIdentidade.tsx`

- `ImageUploader` com `aspectRatio="1:1"` para logo
- Seletor de cor: 8 chips pré-definidos + input hex
- Ao clicar "Próximo": `PATCH /api/admin/store-settings` com logo + `primary_color`, depois `PUT /api/admin/onboarding-status { onboarding_step: 1 }`, depois `onNext()`

**8 cores pré-definidas:**
```typescript
const PRESET_COLORS = [
  '#7c3aed', // roxo VendaPop
  '#2563eb', // azul
  '#16a34a', // verde
  '#dc2626', // vermelho
  '#d97706', // âmbar
  '#db2777', // rosa
  '#0891b2', // ciano
  '#374151', // cinza escuro
];
```

### 6.6 Passo 2 — `StepVitrine.tsx`

**Arquivo:** `frontend/src/components/onboarding/StepVitrine.tsx`

- Exibe grid 2×2 com os 4 produtos demo (busca via `GET /api/admin/products?is_demo=true` — ou simplesmente `GET /api/admin/products` filtrando por `is_demo === true` no frontend)
- Banner amarelo: *"Esses são produtos de exemplo. Clique em Editar para substituir pelo seu."*
- Card de produto com botão "Editar" → abre mini-formulário inline com:
  - Campo `name`
  - Campo `price`
  - `ImageUploader` com `aspectRatio="2:3"`
  - Botão "Salvar" → `PUT /api/admin/products/{slug}` com os dados alterados
- Botão "Pular" disponível (avança sem salvar)
- Botão "Próximo" → `PUT /api/admin/onboarding-status { onboarding_step: 2 }`, depois `onNext()`

### 6.7 Passo 3 — `StepWhatsapp.tsx`

**Arquivo:** `frontend/src/components/onboarding/StepWhatsapp.tsx`

- Campo WhatsApp com máscara usando a lib `react-imask` (já verificar se está instalada; se não, usar input com `onChange` validando manualmente o formato `+55 (XX) XXXXX-XXXX`)
- Textarea para mensagem padrão do pedido
- Mockup visual de conversa WhatsApp (componente estático com estilo de bolhas de mensagem)
- Ao clicar "Próximo": `PATCH /api/admin/store-settings` com `whatsapp_number` + `whatsapp_message`, depois `PUT /api/admin/onboarding-status { onboarding_step: 3 }`, depois `onNext()`

### 6.8 Passo 4 — `StepCompartilhar.tsx`

**Arquivo:** `frontend/src/components/onboarding/StepCompartilhar.tsx`

- Mostra a URL da loja: `vendapop.com.br/{slug}`
- Botão "Copiar link" → `navigator.clipboard.writeText(url)` + toast "Link copiado!"
- Instrução: dois cards sequenciais: "1. Copie o link" e "2. Cole na bio do Instagram"
- Botão "Ver minha loja" → `window.open(url, '_blank')`
- Botão "Concluir configuração" → `PUT /api/admin/onboarding-status { onboarding_completed: true, onboarding_step: 4 }` → `navigate('/admin')` + dispara confete

**Confete:** usar a lib `canvas-confetti` (instalar via `npm install canvas-confetti @types/canvas-confetti`).

```typescript
import confetti from 'canvas-confetti';

const handleConcluir = async () => {
  await api.put('/admin/onboarding-status', { onboarding_completed: true, onboarding_step: 4 });
  confetti({ particleCount: 150, spread: 80, origin: { y: 0.6 } });
  setTimeout(() => navigate('/admin'), 2500);
};
```

### 6.9 `WizardProgressBar.tsx`

**Arquivo:** `frontend/src/components/onboarding/WizardProgressBar.tsx`

4 ícones com labels: Identidade · Vitrine · WhatsApp · Compartilhar. O passo atual fica destacado. Os anteriores ficam com check. Os próximos ficam em cinza.

---

## 7. Contratos de API — Novos Endpoints

| Método | Rota | Auth | Body | Resposta |
|--------|------|------|------|----------|
| `PUT` | `/api/admin/onboarding-status` | sanctum | `{ onboarding_step?: int, onboarding_completed?: bool }` | `{ message, tenant }` |
| `POST` | `/api/admin/image-proxy` | sanctum | `{ url: string }` | `{ url: string, path: string }` |

**Endpoint de login** — verificar se `POST /api/admin/login` já inclui tenant no response. Se não, adicionar:
```json
{
  "token": "...",
  "user": { ... },
  "tenant": {
    "slug": "...",
    "onboarding_completed": false,
    "onboarding_step": 0
  }
}
```

---

## 8. Fluxo de Dados — Sequência de Chamadas por Passo

```
LOGIN
  → GET /api/admin/me (ou retorno do login)
  → se onboarding_completed === false → navigate('/admin/setup')

PASSO 1 (Identidade)
  → Se logo: POST /api/admin/image-proxy { url } (se URL) → recebe localUrl
  → PATCH /api/admin/store-settings (FormData: logo_file ou logo_url, primary_color)
  → PUT /api/admin/onboarding-status { onboarding_step: 1 }
  → iframe refresh (incrementar refreshKey)

PASSO 2 (Vitrine)
  → GET /api/admin/products (filtra is_demo no frontend)
  → [edição] PUT /api/admin/products/{slug} (name, price, image via upload)
  → PUT /api/admin/onboarding-status { onboarding_step: 2 }
  → iframe refresh

PASSO 3 (WhatsApp)
  → PATCH /api/admin/store-settings { whatsapp_number, whatsapp_message }
  → PUT /api/admin/onboarding-status { onboarding_step: 3 }
  → iframe refresh

PASSO 4 (Compartilhar)
  → PUT /api/admin/onboarding-status { onboarding_completed: true, onboarding_step: 4 }
  → confete → navigate('/admin')
```

---

## 9. Banner de Retomada no Dashboard

**Arquivo:** `frontend/src/pages/Dashboard/ECommerce.tsx`

Adicionar ao topo do dashboard, após carregar o tenant:

```tsx
{!tenant.onboarding_completed && !onboardingBannerDismissed && (
  <OnboardingBanner
    step={tenant.onboarding_step}
    onContinue={() => navigate('/admin/setup')}
    onDismiss={handleDismissBanner}
  />
)}
```

`onboardingBannerDismissed`: controlado por `localStorage` com key `onboarding_banner_dismissed_at`. Se a data salva for > 30 dias atrás, não mostrar. Se não existir data, mostrar.

```typescript
const BANNER_DISMISS_KEY = 'onboarding_banner_dismissed_at';

const onboardingBannerDismissed = (() => {
  const dismissed = localStorage.getItem(BANNER_DISMISS_KEY);
  if (!dismissed) return false;
  const daysAgo = (Date.now() - Number(dismissed)) / (1000 * 60 * 60 * 24);
  return daysAgo < 30;
})();

const handleDismissBanner = () => {
  localStorage.setItem(BANNER_DISMISS_KEY, String(Date.now()));
};
```

**`OnboardingBanner.tsx`** — componente simples:

```tsx
<div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex items-center justify-between mb-6">
  <div>
    <p className="font-medium text-yellow-800">Sua loja ainda não está configurada</p>
    <p className="text-sm text-yellow-700">
      {step > 0 ? `Você parou no passo ${step} de 4.` : 'Configure agora e compartilhe com suas clientes!'}
    </p>
  </div>
  <div className="flex gap-2">
    <button onClick={onContinue} className="bg-yellow-500 text-white px-4 py-2 rounded text-sm hover:bg-yellow-600">
      Continuar configuração
    </button>
    <button onClick={onDismiss} className="text-yellow-600 hover:text-yellow-800 text-sm px-2">
      ✕
    </button>
  </div>
</div>
```

---

## 10. Novos Arquivos a Criar

### Backend
```
app/Services/DemoDataService.php
app/Http/Controllers/Api/Admin/OnboardingController.php
app/Http/Controllers/Api/Admin/ImageProxyController.php
database/migrations/2026_06_17_190001_add_onboarding_fields_to_tenants_table.php
database/migrations/2026_06_17_190002_add_is_demo_to_products_table.php
database/migrations/2026_06_17_190003_add_is_demo_to_categories_table.php
```

### Frontend
```
src/pages/AuthPages/OnboardingSetup.tsx
src/components/onboarding/WizardProgressBar.tsx
src/components/onboarding/ShopPreview.tsx
src/components/onboarding/StepIdentidade.tsx
src/components/onboarding/StepVitrine.tsx
src/components/onboarding/StepWhatsapp.tsx
src/components/onboarding/StepCompartilhar.tsx
src/components/onboarding/OnboardingBanner.tsx
src/components/ui/ImageUploader.tsx          ← novo componente
```

### Arquivos Modificados
```
backend/app/Models/Tenant.php                ← +fillable: onboarding_completed, onboarding_step
backend/app/Models/Category.php              ← +fillable: is_demo
backend/app/Models/Product.php               ← +fillable: is_demo
backend/app/Http/Controllers/Api/Admin/RegistrationController.php  ← +DemoDataService
backend/routes/api.php                       ← +2 rotas novas
frontend/src/App.tsx                         ← +rota /admin/setup
frontend/src/pages/AuthPages/SignIn.tsx       ← +redirect para /admin/setup
frontend/src/pages/Dashboard/ECommerce.tsx   ← +OnboardingBanner
frontend/src/pages/Dashboard/Products/ProductForm.tsx  ← substituir upload por ImageUploader
frontend/src/pages/Dashboard/StoreSettings/*  ← substituir logo upload por ImageUploader
frontend/src/pages/Dashboard/Banners/*        ← substituir banner upload por ImageUploader
```

---

## 11. Dependências Novas

```bash
# Frontend
npm install canvas-confetti @types/canvas-confetti
```

Verificar se `react-imask` já está instalada para máscara de telefone:
```bash
grep -r "react-imask\|imask" frontend/package.json
```
Se não estiver: `npm install react-imask` — ou implementar máscara manual sem dependência.

---

## 12. Pontos de Atenção

| # | Ponto | Detalhe |
|---|-------|---------|
| 1 | `store-settings` não retorna tenant atualizado com campos de onboarding | Verificar se o response já inclui `onboarding_completed` e `onboarding_step` — se não, garantir que o `SignIn` leia esses campos |
| 2 | `login` response | Confirmar se `POST /api/admin/login` retorna o tenant. Se não, adicionar `onboarding_completed` e `onboarding_step` ao response |
| 3 | Imagens Unsplash no DemoDataService | URLs com parâmetros `?w=600&h=900&fit=crop` dependem do CDN do Unsplash. Estável historicamente, mas verificar se as fotos específicas ainda existem antes de ir para produção |
| 4 | `is_demo` no response de produtos | O `ProductResource` precisa incluir `is_demo` no JSON para o frontend filtrar os produtos demo no Passo 2 |
| 5 | Máscara de telefone | Se `react-imask` não estiver instalada, implementar validação simples com regex em vez de adicionar dependência |
| 6 | Crop em modo URL após proxy | O `image-proxy` retorna uma URL do próprio VendaPop (`/storage/proxy/xxx.jpg`) — o ImageCropper recebe essa URL local, sem problema de CORS |
