<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\RotatingBanner;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ProductAttributeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoupasSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'moda-fashion'],
            [
                'name' => 'Moda Fashion',
                'slug' => 'moda-fashion',
                'whatsapp_number' => '5511987654323',
                'description' => 'Moda feminina com estilo e qualidade!',
                'banner_message' => '👗 NOVA COLEÇÃO VERÃO 2025! 👗',
                'banner_text_color_1' => '#ffffff',
                'banner_text_color_2' => '#ff69b4',
                'banner_background_color' => '#000000',
                'primary_color' => '#ff69b4',
                'secondary_color' => '#fff0f5',
                'address' => 'Rua Oscar Freire, 500 - Jardins, São Paulo/SP',
                'email_contact' => 'contato@modafashion.com.br',
                'business_sector' => 'fashion',
            ]
        );

        // Criar atributos padrão para moda
        $attributeService = app(ProductAttributeService::class);
        $attributeService->createDefaultAttributesForSector($tenant, 'fashion');

        // Criar usuário para o tenant
        User::updateOrCreate(
            ['email' => 'admin@modafashion.com.br'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Mariana Costa',
                'password' => Hash::make('password'),
                'is_owner' => true,
                'email_verified_at' => now(),
            ]
        );

        // Categorias
        $catVestidos = Category::updateOrCreate(
            ['slug' => 'vestidos', 'tenant_id' => $tenant->id],
            [
                'name' => 'Vestidos',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=500&h=500&fit=crop',
            ]
        );

        $catBlusas = Category::updateOrCreate(
            ['slug' => 'blusas', 'tenant_id' => $tenant->id],
            [
                'name' => 'Blusas',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1551163943-3f6a2b4ae78c?w=500&h=500&fit=crop',
            ]
        );

        $catCalcas = Category::updateOrCreate(
            ['slug' => 'calcas', 'tenant_id' => $tenant->id],
            [
                'name' => 'Calças',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=500&h=500&fit=crop',
            ]
        );

        // Produtos (6 produtos)
        $produtos = [
            [
                'name' => 'Vestido Floral Midi',
                'category_id' => $catVestidos->id,
                'short_description' => 'Vestido floral midi, manga curta, tecido leve',
                'description' => 'Lindo vestido floral midi perfeito para o verão. Manga curta, decote arredondado, tecido leve e fluido. Ideal para ocasiões casuais e eventos diurnos. Disponível em vários tamanhos.',
                'price' => 149.90,
                'promotional_price' => 119.90,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Blusa Básica Manga Longa',
                'category_id' => $catBlusas->id,
                'short_description' => 'Blusa básica manga longa, algodão, várias cores',
                'description' => 'Blusa básica manga longa em algodão macio e confortável. Modelo versátil que combina com tudo. Perfeita para o dia a dia, trabalho ou lazer. Disponível em várias cores.',
                'price' => 79.90,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1551163943-3f6a2b4ae78c?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1551163943-3f6a2b4ae78c?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1594633313593-bab3825d0caf?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Calça Jeans Skinny',
                'category_id' => $catCalcas->id,
                'short_description' => 'Calça jeans skinny, cintura alta, elástico',
                'description' => 'Calça jeans skinny com cintura alta e elástico na cintura para maior conforto. Modelo clássico que nunca sai de moda. Perfeita para compor looks casuais e despojados.',
                'price' => 199.90,
                'promotional_price' => 169.90,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1582418702059-97ebaf8e0e2f?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Vestido Longo Elegante',
                'category_id' => $catVestidos->id,
                'short_description' => 'Vestido longo elegante, sem manga, tecido nobre',
                'description' => 'Vestido longo elegante perfeito para eventos e ocasiões especiais. Sem manga, decote em V, tecido nobre com caimento perfeito. Modelo sofisticado e atemporal.',
                'price' => 349.90,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1594633313593-bab3825d0caf?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1594633313593-bab3825d0caf?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Blusa Cropped Estampada',
                'category_id' => $catBlusas->id,
                'short_description' => 'Blusa cropped estampada, manga curta, jovem',
                'description' => 'Blusa cropped estampada com manga curta, modelo jovem e despojado. Perfeita para compor looks casuais e modernos. Ideal para o verão e dias quentes.',
                'price' => 89.90,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1551163943-3f6a2b4ae78c?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Calça Wide Leg',
                'category_id' => $catCalcas->id,
                'short_description' => 'Calça wide leg, cintura alta, tecido fluido',
                'description' => 'Calça wide leg com cintura alta e tecido fluido. Modelo confortável e moderno, perfeito para o dia a dia. Combina com blusas, camisas e tops.',
                'price' => 179.90,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1582418702059-97ebaf8e0e2f?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1582418702059-97ebaf8e0e2f?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=800&h=600&fit=crop',
                ],
            ],
        ];

        foreach ($produtos as $produtoData) {
            $mainImage = $produtoData['main_image'];
            $galleryImages = $produtoData['gallery_images'];
            unset($produtoData['main_image'], $produtoData['gallery_images']);

            $product = Product::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($produtoData['name']), 'tenant_id' => $tenant->id],
                array_merge($produtoData, [
                    'tenant_id' => $tenant->id,
                    'is_active' => true,
                    'is_hot' => false,
                ])
            );

            $this->syncImages($product, $mainImage, $galleryImages);
        }

        // Banners
        $banners = [
            [
                'title' => 'Nova Coleção Verão 2025',
                'description' => 'Descubra as últimas tendências da moda',
                'image_url' => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Promoção Especial',
                'description' => 'Até 30% OFF em toda a loja',
                'image_url' => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Frete Grátis',
                'description' => 'Em compras acima de R$ 200,00',
                'image_url' => 'https://images.unsplash.com/photo-1551163943-3f6a2b4ae78c?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $bannerData) {
            RotatingBanner::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'image_url' => $bannerData['image_url'],
                ],
                array_merge($bannerData, [
                    'tenant_id' => $tenant->id,
                    'image_path' => null,
                    'is_external' => true,
                ])
            );
        }

        $this->command->info('Seeder de Roupas criado com sucesso!');
    }

    private function syncImages(Product $product, string $mainUrl, array $galleryUrls)
    {
        $product->images()->delete();

        $product->images()->create([
            'url' => $mainUrl,
            'is_external' => true,
            'is_main' => true,
        ]);

        foreach ($galleryUrls as $url) {
            $product->images()->create([
                'url' => $url,
                'is_external' => true,
                'is_main' => false,
            ]);
        }
    }
}

