<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\RotatingBanner;
use App\Models\Tenant;

class DemoDataService
{
    public function seedFor(Tenant $tenant): void
    {
        app(ProductAttributeService::class)->createDefaultAttributesForSector($tenant, 'fashion');

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
