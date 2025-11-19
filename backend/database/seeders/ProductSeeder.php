<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'modachic')->first();

        if (!$tenant) return;

        $catVestidos = Category::where('slug', 'vestidos')->where('tenant_id', $tenant->id)->first();
        $catBlusas = Category::where('slug', 'blusas')->where('tenant_id', $tenant->id)->first();
        $catCalcas = Category::where('slug', 'calcas')->where('tenant_id', $tenant->id)->first();

        if ($catVestidos) {
            Product::firstOrCreate(
                ['slug' => 'vestido-longo-floral', 'tenant_id' => $tenant->id],
                [
                    'category_id' => $catVestidos->id,
                    'name' => 'Vestido Longo Floral',
                    'short_description' => 'Vestido perfeito para o verão.',
                    'description' => 'Vestido longo com estampa floral, tecido leve e confortável.',
                    'price' => 129.90,
                    'sizes' => ['P', 'M', 'G'],
                    'colors' => ['Rosa', 'Azul'],
                    'main_image_url' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?q=80&w=600&auto=format&fit=crop',
                    'is_active' => true,
                ]
            );

            Product::firstOrCreate(
                ['slug' => 'vestido-midi-preto', 'tenant_id' => $tenant->id],
                [
                    'category_id' => $catVestidos->id,
                    'name' => 'Vestido Midi Preto',
                    'short_description' => 'Elegância para a noite.',
                    'description' => 'Vestido midi preto básico, tecido encorpado.',
                    'price' => 159.90,
                    'sizes' => ['M', 'G'],
                    'colors' => ['Preto'],
                    'main_image_url' => 'https://images.unsplash.com/photo-1539008835657-9e8e9680c956?q=80&w=600&auto=format&fit=crop',
                    'is_active' => true,
                ]
            );
        }

        if ($catBlusas) {
            Product::firstOrCreate(
                ['slug' => 'blusa-basica-branca', 'tenant_id' => $tenant->id],
                [
                    'category_id' => $catBlusas->id,
                    'name' => 'Blusa Básica Branca',
                    'short_description' => 'Peça essencial no guarda-roupa.',
                    'description' => 'Blusa branca de algodão, corte moderno e versátil.',
                    'price' => 49.90,
                    'sizes' => ['P', 'M', 'G', 'GG'],
                    'colors' => ['Branco', 'Preto', 'Off-white'],
                    'main_image_url' => 'https://images.unsplash.com/photo-1585487000160-6ebcfceb0d03?q=80&w=600&auto=format&fit=crop',
                    'is_active' => true,
                ]
            );
        }

        if ($catCalcas) {
            Product::firstOrCreate(
                ['slug' => 'calca-jeans-skinny', 'tenant_id' => $tenant->id],
                [
                    'category_id' => $catCalcas->id,
                    'name' => 'Calça Jeans Skinny',
                    'short_description' => 'Jeans confortável com elastano.',
                    'description' => 'Calça jeans modelagem skinny, lavagem escura.',
                    'price' => 89.90,
                    'sizes' => ['36', '38', '40', '42', '44'],
                    'colors' => ['Jeans Escuro'],
                    'main_image_url' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=600&auto=format&fit=crop',
                    'is_active' => true,
                ]
            );
        }
    }
}

