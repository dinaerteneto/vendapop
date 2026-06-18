# TASK 02 — DemoDataService + hook no RegistrationController

**Grupo:** A — Backend  
**Depende de:** TASK 01  
**Bloqueia:** TASK 06 (wizard precisa de dados demo para o preview)

## Objetivo

Criar o `DemoDataService` que popula a loja de uma nova lojista com conteúdo demo imediato (2 categorias, 4 produtos, 1 banner) e injetar sua chamada no fluxo de cadastro. Após esta task, toda nova loja cadastrada nasce com um link funcional e produtos visíveis.

## Entregáveis

### 1. `DemoDataService`

**Arquivo:** `backend/app/Services/DemoDataService.php`

Responsabilidades:
- Criar atributos padrão de moda via `ProductAttributeService::createDefaultAttributesForSector($tenant, 'fashion')`
- Criar 2 categorias com `is_demo = true`
- Criar 4 produtos com `is_demo = true`, cada um com 1 imagem principal (URL Unsplash)
- Criar 1 banner rotativo demo

Dados demo a usar:

| Produto | Categoria | Preço | Promoção | Imagem Unsplash |
|---------|-----------|-------|----------|-----------------|
| Vestido Floral Midi | Novidades | 149,90 | 119,90 | `photo-1515372039744-b8f02a3ae446` |
| Blusa Básica Manga Longa | Novidades | 79,90 | — | `photo-1551163943-3f6a2b4ae78c` |
| Calça Jeans Skinny | Promoções | 199,90 | 169,90 | `photo-1541099649105-f69ad21f3246` |
| Vestido Longo Elegante | Promoções | 349,90 | — | `photo-1594633313593-bab3825d0caf` |

Parâmetros de URL Unsplash: `?w=600&h=900&fit=crop` para produtos, `?w=1200&h=675&fit=crop` para banner.

Descrição padrão dos produtos: `'Exemplo de produto. Substitua pelo seu!'`

### 2. Hook no `RegistrationController`

**Arquivo:** `backend/app/Http/Controllers/Api/Admin/RegistrationController.php`

Dentro do `DB::transaction`, **após** `Tenant::create(...)` e **antes** do `return $tenant`:

```php
$demoService = app(\App\Services\DemoDataService::class);
$demoService->seedFor($tenant);
```

O seeding ocorre dentro da mesma transaction — se falhar, o cadastro inteiro é revertido.

## Critérios de Aceitação

- [x] Após `POST /api/admin/register`, o tenant tem exatamente 2 categorias
- [x] Ambas as categorias têm `is_demo = true`
- [x] O tenant tem exatamente 4 produtos, todos com `is_demo = true`
- [x] Cada produto tem 1 imagem associada (`product_images`)
- [x] O tenant tem 1 banner rotativo criado
- [x] A loja pública (`GET /{slug}`) retorna dados e está acessível sem login
- [x] Se o `DemoDataService` lançar exceção, o cadastro é revertido (tenant não é criado)

## Testes

**Feature test:** `tests/Feature/Registration/DemoDataServiceTest.php`

```php
public function test_registration_creates_demo_categories(): void
{
    $this->post('/api/admin/register', $this->validPayload());

    $tenant = Tenant::where('slug', 'minha-loja')->first();
    $this->assertCount(2, $tenant->categories);
    $this->assertTrue($tenant->categories->every(fn($c) => $c->is_demo));
}

public function test_registration_creates_four_demo_products(): void
{
    $this->post('/api/admin/register', $this->validPayload());

    $tenant = Tenant::where('slug', 'minha-loja')->first();
    $this->assertCount(4, $tenant->products);
    $this->assertTrue($tenant->products->every(fn($p) => $p->is_demo));
}

public function test_each_demo_product_has_main_image(): void
{
    $this->post('/api/admin/register', $this->validPayload());

    $tenant = Tenant::where('slug', 'minha-loja')->first();
    foreach ($tenant->products as $product) {
        $this->assertNotNull($product->images()->where('is_main', true)->first());
    }
}

public function test_registration_creates_demo_banner(): void
{
    $this->post('/api/admin/register', $this->validPayload());

    $tenant = Tenant::where('slug', 'minha-loja')->first();
    $this->assertCount(1, $tenant->rotatingBanners);
}

public function test_demo_data_failure_rolls_back_registration(): void
{
    // Mock DemoDataService para lançar exceção
    $this->mock(\App\Services\DemoDataService::class, function ($mock) {
        $mock->shouldReceive('seedFor')->andThrow(new \Exception('fail'));
    });

    $this->post('/api/admin/register', $this->validPayload());

    $this->assertDatabaseMissing('tenants', ['slug' => 'minha-loja']);
}
```
