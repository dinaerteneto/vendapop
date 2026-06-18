# TASK 05 — Integrar ImageUploader nos formulários existentes

**Grupo:** C — ImageUploader  
**Depende de:** TASK 04  
**Bloqueia:** nenhuma (paralela ao Grupo B)

## Objetivo

Substituir a lógica de upload/URL atual nos 3 formulários existentes pelo novo `ImageUploader`. Esta task garante que toda imagem salva na plataforma — seja via produto, logo ou banner — passe pelo crop e seja armazenada localmente.

## Entregáveis

### 1. `ProductForm.tsx` — substituir upload de imagem principal

**Arquivo:** `frontend/src/pages/Dashboard/Products/ProductForm.tsx`

Remover os estados e handlers relacionados ao upload atual:
- `imageMode`, `imageFile`, `previewUrl`, `showCropper`, `imageToCrop`
- Handlers: `onCropComplete`, `onCancelCrop`, `handleFileSelect`
- A seção de toggle "URL / Arquivo" e o `ImageCropper` inline

Substituir pela integração com `ImageUploader`:
```tsx
<ImageUploader
  aspectRatio="2:3"
  currentImageUrl={previewUrl ?? undefined}
  onImageReady={(file) => {
    setImageFile(file);
    setPreviewUrl(URL.createObjectURL(file));
  }}
  label="Foto principal do produto"
/>
```

Manter os estados `imageFile` e `previewUrl` — são usados no `handleSubmit` para construir o `FormData`.

O campo `imageMode` e a lógica de `main_image_url` como URL externa deixam de existir: toda imagem vira arquivo (`imageFile`).

### 2. `StoreSettings` — substituir upload de logo

**Arquivo:** `frontend/src/pages/Dashboard/StoreSettings/` (verificar arquivo correto)

Localizar o campo de upload de logo e substituir pela integração:
```tsx
<ImageUploader
  aspectRatio="1:1"
  currentImageUrl={formData.logo_url ?? undefined}
  onImageReady={(file) => setLogoFile(file)}
  label="Logo da loja"
/>
```

Garantir que `logoFile` seja incluído no `FormData` ao submeter as configurações.

### 3. `RotatingBannerForm` — substituir upload de imagem

**Arquivo:** `frontend/src/pages/Dashboard/Banners/` (verificar arquivo correto)

Localizar o campo de imagem do banner e substituir:
```tsx
<ImageUploader
  aspectRatio="16:9"
  currentImageUrl={formData.image_url ?? undefined}
  onImageReady={(file) => setBannerFile(file)}
  label="Imagem do banner"
/>
```

## Critérios de Aceitação

- [x] Criar produto com foto via drag & drop funciona — produto salvo com imagem correta
- [x] Criar produto com foto via URL funciona — produto salvo com imagem local (não URL externa)
- [x] Editar produto e trocar foto funciona corretamente
- [x] Salvar logo da loja via drag & drop funciona
- [x] Salvar logo via URL funciona
- [x] Criar banner com imagem funciona
- [x] Imagens de produto exibidas na loja pública aparecem em proporção 2:3 sem distorção
- [x] `is_external` nos `product_images` novos é sempre `false` (imagem local)

## Testes

**Testes de integração E2E (Playwright ou manual):**

```
Cenário 1 — Produto com upload de arquivo:
  1. Acessar /admin/products/new
  2. Preencher nome e preço
  3. Arrastar imagem JPEG para o ImageUploader
  4. Ajustar crop e confirmar
  5. Salvar produto
  ✓ Produto aparece na lista com a imagem
  ✓ Imagem na loja pública exibe em proporção 2:3

Cenário 2 — Produto com URL:
  1. Acessar /admin/products/new
  2. Colar URL de imagem Unsplash
  3. Clicar "Usar"
  4. Ajustar crop e confirmar
  5. Salvar produto
  ✓ Produto salvo com imagem local (URL começa com /storage/)
  ✓ Campo is_external = false no banco

Cenário 3 — Logo via URL:
  1. Acessar configurações da loja
  2. Colar URL de logo
  3. Confirmar crop quadrado (1:1)
  4. Salvar
  ✓ Logo aparece no header da loja pública

Cenário 4 — Banner 16:9:
  1. Criar novo banner
  2. Fazer upload de imagem
  3. Confirmar crop 16:9
  4. Salvar
  ✓ Banner aparece na loja com proporção correta
```

**Teste de regressão crítico:** verificar que a listagem de produtos, edição e exclusão continuam funcionando após a refatoração do `ProductForm`.
