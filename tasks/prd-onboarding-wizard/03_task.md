# TASK 03 — OnboardingController + ImageProxyController + login response

**Grupo:** A — Backend  
**Depende de:** TASK 01  
**Bloqueia:** TASK 04 (proxy), TASK 06 (redirect no login)

## Objetivo

Criar os dois novos endpoints de API e atualizar o response do login para incluir os campos de onboarding. Após esta task, o frontend tem tudo que precisa para controlar o estado do wizard e fazer fetch de imagens externas.

## Entregáveis

### 1. `OnboardingController`

**Arquivo:** `backend/app/Http/Controllers/Api/Admin/OnboardingController.php`

Endpoint: `PUT /api/admin/onboarding-status`  
Auth: `auth:sanctum`

```php
public function update(Request $request)
{
    $validated = $request->validate([
        'onboarding_step'      => 'nullable|integer|min:0|max:4',
        'onboarding_completed' => 'nullable|boolean',
    ]);

    $tenant = $request->user()->tenant;
    // array_filter remove nulls — não sobrescreve campos não enviados
    $tenant->update(array_filter($validated, fn($v) => $v !== null));

    return response()->json(['message' => 'ok', 'tenant' => $tenant]);
}
```

### 2. `ImageProxyController`

**Arquivo:** `backend/app/Http/Controllers/Api/Admin/ImageProxyController.php`

Endpoint: `POST /api/admin/image-proxy`  
Auth: `auth:sanctum`

Comportamento:
- Valida que `url` é uma URL válida (max 2048 chars)
- Faz `Http::timeout(10)->get($url)` — captura exceções de rede
- Verifica `$response->successful()` (status 2xx)
- Verifica `Content-Type` começa com `image/`
- Determina extensão: jpeg→jpg, png→png, webp→webp, gif→gif, default→jpg
- Salva em `storage/app/public/proxy/{uuid}.{ext}`
- Retorna `{ url: string, path: string }`

Respostas de erro:
- Rede/timeout/404: HTTP 422, `{ message: 'Não foi possível carregar a imagem. Verifique o link e tente novamente.' }`
- Content-Type inválido: HTTP 422, `{ message: 'O link não aponta para uma imagem válida.' }`

### 3. Atualizar `routes/api.php`

Dentro do grupo autenticado (`auth:sanctum`), adicionar:

```php
Route::put('/onboarding-status', [OnboardingController::class, 'update']);
Route::post('/image-proxy', [ImageProxyController::class, 'store']);
```

### 4. Atualizar response do login

**Arquivo:** `backend/app/Http/Controllers/Api/Admin/AuthController.php` (ou equivalente)

Adicionar ao JSON de retorno do login bem-sucedido:
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

Verificar qual controller processa `POST /api/admin/login` e incluir `$user->tenant` no response.

## Critérios de Aceitação

- [x] `PUT /api/admin/onboarding-status` com `{ onboarding_step: 2 }` atualiza o banco e retorna o tenant atualizado
- [x] `PUT /api/admin/onboarding-status` sem autenticação retorna 401
- [x] `POST /api/admin/image-proxy` com URL de imagem válida retorna `{ url, path }` e o arquivo existe no storage
- [x] `POST /api/admin/image-proxy` com URL inexistente retorna 422 com mensagem amigável
- [x] `POST /api/admin/image-proxy` com URL de arquivo não-imagem retorna 422
- [x] Response do `POST /api/admin/login` inclui `tenant.onboarding_completed` e `tenant.onboarding_step`

## Testes

**Feature tests:** `tests/Feature/Onboarding/OnboardingControllerTest.php`

```php
public function test_update_onboarding_step(): void
{
    $user = User::factory()->withTenant()->create();
    Sanctum::actingAs($user);

    $this->putJson('/api/admin/onboarding-status', ['onboarding_step' => 2])
         ->assertOk()
         ->assertJsonPath('tenant.onboarding_step', 2);

    $this->assertEquals(2, $user->tenant->fresh()->onboarding_step);
}

public function test_update_onboarding_completed(): void
{
    $user = User::factory()->withTenant()->create();
    Sanctum::actingAs($user);

    $this->putJson('/api/admin/onboarding-status', ['onboarding_completed' => true])
         ->assertOk();

    $this->assertTrue($user->tenant->fresh()->onboarding_completed);
}

public function test_onboarding_status_requires_auth(): void
{
    $this->putJson('/api/admin/onboarding-status', ['onboarding_step' => 1])
         ->assertUnauthorized();
}
```

**Feature tests:** `tests/Feature/Onboarding/ImageProxyControllerTest.php`

```php
public function test_proxy_downloads_valid_image(): void
{
    Http::fake(['https://example.com/photo.jpg' => Http::response(
        file_get_contents(base_path('tests/fixtures/test-image.jpg')),
        200,
        ['Content-Type' => 'image/jpeg']
    )]);

    $user = User::factory()->withTenant()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/admin/image-proxy', ['url' => 'https://example.com/photo.jpg'])
                     ->assertOk()
                     ->assertJsonStructure(['url', 'path']);

    Storage::disk('public')->assertExists($response->json('path'));
}

public function test_proxy_rejects_non_image_url(): void
{
    Http::fake(['https://example.com/page.html' => Http::response('<html>', 200, ['Content-Type' => 'text/html'])]);

    $user = User::factory()->withTenant()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/admin/image-proxy', ['url' => 'https://example.com/page.html'])
         ->assertUnprocessable()
         ->assertJsonFragment(['message' => 'O link não aponta para uma imagem válida.']);
}

public function test_proxy_handles_failed_request(): void
{
    Http::fake(['*' => Http::response(null, 404)]);

    $user = User::factory()->withTenant()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/admin/image-proxy', ['url' => 'https://example.com/missing.jpg'])
         ->assertUnprocessable();
}

public function test_login_response_includes_onboarding_fields(): void
{
    $user = User::factory()->withTenant()->create(['password' => Hash::make('password')]);

    $this->postJson('/api/admin/login', ['email' => $user->email, 'password' => 'password'])
         ->assertOk()
         ->assertJsonPath('tenant.onboarding_completed', false)
         ->assertJsonPath('tenant.onboarding_step', 0);
}
```
