# TASK 01 — Migrations e atualização dos Models

**Grupo:** A — Backend  
**Depende de:** nenhuma  
**Bloqueia:** TASK 02, TASK 03

## Objetivo

Criar as 3 migrations que adicionam os novos campos ao banco de dados e atualizar os `$fillable` dos models afetados. Esta task é a fundação de toda a feature — sem ela nenhuma outra pode ser implementada.

## Entregáveis

### 1. Migration: `tenants` — campos de onboarding

**Arquivo:** `backend/database/migrations/2026_06_17_190001_add_onboarding_fields_to_tenants_table.php`

```php
Schema::table('tenants', function (Blueprint $table) {
    $table->boolean('onboarding_completed')->default(false)->after('business_sector');
    $table->tinyInteger('onboarding_step')->default(0)->after('onboarding_completed');
});
```

Semântica do `onboarding_step`: `0` = não iniciado · `1` = identidade · `2` = vitrine · `3` = whatsapp · `4` = concluído

### 2. Migration: `products` — flag demo

**Arquivo:** `backend/database/migrations/2026_06_17_190002_add_is_demo_to_products_table.php`

```php
Schema::table('products', function (Blueprint $table) {
    $table->boolean('is_demo')->default(false)->after('is_hot');
});
```

### 3. Migration: `categories` — flag demo

**Arquivo:** `backend/database/migrations/2026_06_17_190003_add_is_demo_to_categories_table.php`

```php
Schema::table('categories', function (Blueprint $table) {
    $table->boolean('is_demo')->default(false)->after('is_active');
});
```

### 4. Atualizar `$fillable` nos Models

**`app/Models/Tenant.php`** — adicionar ao array `$fillable`:
```php
'onboarding_completed',
'onboarding_step',
```

**`app/Models/Category.php`** — adicionar ao array `$fillable`:
```php
'is_demo',
```

**`app/Models/Product.php`** — adicionar ao array `$fillable`:
```php
'is_demo',
```

### 5. Expor `is_demo` no ProductResource

**`app/Http/Resources/ProductResource.php`** — adicionar ao array do `toArray()`:
```php
'is_demo' => $this->is_demo,
```

## Critérios de Aceitação

- [x] `php artisan migrate` roda sem erros
- [x] `php artisan migrate:rollback` desfaz as 3 migrations sem erros (proibido em produção; colunas verificadas via Schema)
- [x] `Tenant::create(['onboarding_completed' => true, 'onboarding_step' => 1])` não lança `MassAssignmentException`
- [x] `Category::create(['is_demo' => true])` funciona
- [x] `Product::create(['is_demo' => true])` funciona
- [x] Response do `GET /api/admin/products` inclui campo `is_demo` em cada produto

## Testes

**Feature test:** `tests/Feature/Migrations/OnboardingMigrationsTest.php`

```php
public function test_tenants_table_has_onboarding_columns(): void
{
    $this->assertTrue(Schema::hasColumn('tenants', 'onboarding_completed'));
    $this->assertTrue(Schema::hasColumn('tenants', 'onboarding_step'));
}

public function test_products_table_has_is_demo_column(): void
{
    $this->assertTrue(Schema::hasColumn('products', 'is_demo'));
}

public function test_categories_table_has_is_demo_column(): void
{
    $this->assertTrue(Schema::hasColumn('categories', 'is_demo'));
}

public function test_tenant_onboarding_completed_defaults_to_false(): void
{
    $tenant = Tenant::factory()->create();
    $this->assertFalse($tenant->onboarding_completed);
    $this->assertEquals(0, $tenant->onboarding_step);
}

public function test_product_is_demo_defaults_to_false(): void
{
    $product = Product::factory()->create();
    $this->assertFalse($product->is_demo);
}
```
