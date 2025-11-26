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

class AfiliadosSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'ofertas-do-dia'],
            [
                'name' => 'Ofertas do Dia',
                'slug' => 'ofertas-do-dia',
                'whatsapp_number' => '5511987654327',
                'description' => 'Produtos selecionados com os melhores preços! Links de afiliados.',
                'banner_message' => '🛒 PRODUTOS SELECIONADOS COM OS MELHORES PREÇOS! 🛒',
                'banner_text_color_1' => '#ffffff',
                'banner_text_color_2' => '#ff6b6b',
                'banner_background_color' => '#1a1a2e',
                'primary_color' => '#1a1a2e',
                'secondary_color' => '#f0f0f0',
                'address' => 'Av. Faria Lima, 3000 - Itaim Bibi, São Paulo/SP',
                'email_contact' => 'contato@ofertasdodia.com.br',
                'business_sector' => 'affiliates',
            ]
        );

        // Criar atributos padrão para afiliados
        $attributeService = app(ProductAttributeService::class);
        $attributeService->createDefaultAttributesForSector($tenant, 'affiliates');

        // Criar usuário para o tenant
        User::updateOrCreate(
            ['email' => 'admin@ofertasdodia.com.br'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Fernando Lima',
                'password' => Hash::make('password'),
                'is_owner' => true,
                'email_verified_at' => now(),
            ]
        );

        // Categorias
        $catCasa = Category::updateOrCreate(
            ['slug' => 'casa-e-decoracao', 'tenant_id' => $tenant->id],
            [
                'name' => 'Casa e Decoração',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=500&h=500&fit=crop',
            ]
        );

        $catBeleza = Category::updateOrCreate(
            ['slug' => 'beleza-e-cuidados', 'tenant_id' => $tenant->id],
            [
                'name' => 'Beleza e Cuidados',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=500&h=500&fit=crop',
            ]
        );

        $catFitness = Category::updateOrCreate(
            ['slug' => 'fitness-e-saude', 'tenant_id' => $tenant->id],
            [
                'name' => 'Fitness e Saúde',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=500&h=500&fit=crop',
            ]
        );

        // Produtos (6 produtos com links de afiliado)
        $produtos = [
            [
                'name' => 'Aspirador de Pó Robô Inteligente',
                'category_id' => $catCasa->id,
                'short_description' => 'Aspirador robô com mapeamento inteligente, controle por app',
                'description' => 'Aspirador robô inteligente com mapeamento a laser, controle via aplicativo, programação de limpeza, bateria de longa duração e sistema de navegação avançado. Perfeito para manter sua casa sempre limpa.',
                'price' => 1299.00,
                'promotional_price' => 999.00,
                'action_type' => 'affiliate_link',
                'affiliate_link' => 'https://www.amazon.com.br/dp/B08XYZ1234?tag=afiliado-20',
                'main_image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Kit de Skincare Completo',
                'category_id' => $catBeleza->id,
                'short_description' => 'Kit com 5 produtos de skincare, limpeza e hidratação',
                'description' => 'Kit completo de skincare com 5 produtos: gel de limpeza, tônico, sérum vitamina C, creme hidratante e protetor solar. Produtos testados dermatologicamente, ideais para todos os tipos de pele.',
                'price' => 299.00,
                'promotional_price' => 249.00,
                'action_type' => 'affiliate_link',
                'affiliate_link' => 'https://www.amazon.com.br/dp/B09ABC5678?tag=afiliado-20',
                'main_image' => 'https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Suplemento Whey Protein 1kg',
                'category_id' => $catFitness->id,
                'short_description' => 'Whey protein isolado, 1kg, sabor chocolate',
                'description' => 'Whey protein isolado de alta qualidade, 1kg, sabor chocolate. Rico em proteínas, baixo teor de carboidratos e gorduras. Ideal para atletas e praticantes de atividade física. Produto importado.',
                'price' => 149.90,
                'promotional_price' => 129.90,
                'action_type' => 'affiliate_link',
                'affiliate_link' => 'https://www.amazon.com.br/dp/B10DEF9012?tag=afiliado-20',
                'main_image' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1576678927484-cc907957088c?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Smart TV 55" 4K UHD',
                'category_id' => $catCasa->id,
                'short_description' => 'Smart TV 55 polegadas, 4K UHD, Android TV',
                'description' => 'Smart TV 55 polegadas com resolução 4K UHD, Android TV integrado, HDR10, Wi-Fi e Bluetooth. Controle remoto por voz, múltiplas entradas HDMI e USB. Perfeita para entretenimento em casa.',
                'price' => 2499.00,
                'promotional_price' => 2199.00,
                'action_type' => 'affiliate_link',
                'affiliate_link' => 'https://www.amazon.com.br/dp/B11GHI3456?tag=afiliado-20',
                'main_image' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Perfume Importado 100ml',
                'category_id' => $catBeleza->id,
                'short_description' => 'Perfume importado, fragrância exclusiva, 100ml',
                'description' => 'Perfume importado com fragrância exclusiva e duradoura. Frasco de 100ml, embalagem de presente. Fragrância sofisticada e elegante, perfeita para ocasiões especiais.',
                'price' => 399.00,
                'action_type' => 'affiliate_link',
                'affiliate_link' => 'https://www.amazon.com.br/dp/B12JKL7890?tag=afiliado-20',
                'main_image' => 'https://images.unsplash.com/photo-1541643600914-78b084683601?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1541643600914-78b084683601?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Esteira Elétrica Dobrável',
                'category_id' => $catFitness->id,
                'short_description' => 'Esteira elétrica dobrável, display LCD, controle de velocidade',
                'description' => 'Esteira elétrica dobrável com display LCD, controle de velocidade e inclinação, sistema de amortecimento, espaço para guardar e ideal para uso doméstico. Perfeita para manter a forma em casa.',
                'price' => 1899.00,
                'promotional_price' => 1699.00,
                'action_type' => 'affiliate_link',
                'affiliate_link' => 'https://www.amazon.com.br/dp/B13MNO1234?tag=afiliado-20',
                'main_image' => 'https://images.unsplash.com/photo-1576678927484-cc907957088c?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1576678927484-cc907957088c?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop',
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
                'title' => 'Produtos Selecionados',
                'description' => 'Os melhores produtos com os melhores preços',
                'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Ofertas Exclusivas',
                'description' => 'Aproveite descontos especiais',
                'image_url' => 'https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Frete Grátis',
                'description' => 'Em compras acima de R$ 200,00',
                'image_url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=1200&h=600&fit=crop',
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

        $this->command->info('Seeder de Afiliados criado com sucesso!');
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

