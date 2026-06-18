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

class BoloCaseiroSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'confeitaria-artesanal'],
            [
                'name' => 'Confeitaria Artesanal',
                'slug' => 'confeitaria-artesanal',
                'whatsapp_number' => '5511987654325',
                'description' => 'Bolos caseiros feitos com amor e ingredientes selecionados!',
                'banner_message' => '🎂 BOLOS CASEIROS FEITOS COM AMOR! 🎂',
                'banner_text_color_1' => '#ffffff',
                'banner_text_color_2' => '#ff69b4',
                'banner_background_color' => '#8b4513',
                'primary_color' => '#8b4513',
                'secondary_color' => '#fff8dc',
                'address' => 'Rua dos Três Irmãos, 200 - Vila Progredior, São Paulo/SP',
                'email_contact' => 'contato@confeitariaartesanal.com.br',
                'business_sector' => 'food',
            ]
        );

        // Criar atributos padrão para comida
        $attributeService = app(ProductAttributeService::class);
        $attributeService->createDefaultAttributesForSector($tenant, 'food');

        // Criar usuário para o tenant
        User::updateOrCreate(
            ['email' => 'admin@confeitariaartesanal.com.br'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Maria da Silva',
                'password' => Hash::make('password'),
                'is_owner' => true,
                'email_verified_at' => now(),
            ]
        );

        // Categorias
        $catBolosAniversario = Category::updateOrCreate(
            ['slug' => 'bolos-de-aniversario', 'tenant_id' => $tenant->id],
            [
                'name' => 'Bolos de Aniversário',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=500&h=500&fit=crop',
            ]
        );

        $catBolosCasamento = Category::updateOrCreate(
            ['slug' => 'bolos-de-casamento', 'tenant_id' => $tenant->id],
            [
                'name' => 'Bolos de Casamento',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=500&h=500&fit=crop',
            ]
        );

        $catBolosEspeciais = Category::updateOrCreate(
            ['slug' => 'bolos-especiais', 'tenant_id' => $tenant->id],
            [
                'name' => 'Bolos Especiais',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1587668178277-295251f900ce?w=500&h=500&fit=crop',
            ]
        );

        // Produtos (6 produtos)
        $produtos = [
            [
                'name' => 'Bolo de Chocolate com Morango',
                'category_id' => $catBolosAniversario->id,
                'short_description' => 'Bolo de chocolate com recheio de morango, cobertura de ganache',
                'description' => 'Delicioso bolo de chocolate caseiro com recheio de morango fresco e cobertura de ganache. Feito com ingredientes selecionados, sem conservantes. Perfeito para aniversários e comemorações. Encomenda com 3 dias de antecedência.',
                'price' => 89.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Tenho interesse no Bolo de Chocolate com Morango. Poderia me informar sobre disponibilidade e valores?',
                'main_image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1587668178277-295251f900ce?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Bolo de Cenoura com Cobertura',
                'category_id' => $catBolosAniversario->id,
                'short_description' => 'Bolo de cenoura caseiro com cobertura de chocolate',
                'description' => 'Tradicional bolo de cenoura caseiro com cobertura de chocolate cremoso. Receita da família, feito com carinho e ingredientes frescos. Disponível em vários tamanhos. Encomenda com 2 dias de antecedência.',
                'price' => 69.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Gostaria de encomendar um Bolo de Cenoura com Cobertura. Qual o tamanho disponível?',
                'main_image' => 'https://images.unsplash.com/photo-1587668178277-295251f900ce?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1587668178277-295251f900ce?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Bolo de Casamento 3 Andares',
                'category_id' => $catBolosCasamento->id,
                'short_description' => 'Bolo de casamento elegante, 3 andares, decoração personalizada',
                'description' => 'Elegante bolo de casamento com 3 andares, decoração personalizada conforme tema do casamento. Sabores disponíveis: chocolate, baunilha, morango, limão. Inclui decoração com flores e detalhes em açúcar. Orçamento personalizado.',
                'price' => 899.00,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Gostaria de solicitar um orçamento para Bolo de Casamento 3 Andares. Poderia me enviar mais informações?',
                'main_image' => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Bolo Red Velvet',
                'category_id' => $catBolosEspeciais->id,
                'short_description' => 'Bolo Red Velvet com cream cheese, sabor único',
                'description' => 'Delicioso bolo Red Velvet com cobertura de cream cheese. Sabor único e sofisticado, perfeito para ocasiões especiais. Feito com ingredientes premium. Encomenda com 3 dias de antecedência.',
                'price' => 129.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Tenho interesse no Bolo Red Velvet. Poderia me informar sobre disponibilidade?',
                'main_image' => 'https://images.unsplash.com/photo-1587668178277-295251f900ce?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1587668178277-295251f900ce?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Bolo de Limão com Merengue',
                'category_id' => $catBolosAniversario->id,
                'short_description' => 'Bolo de limão caseiro com merengue italiano',
                'description' => 'Refrescante bolo de limão caseiro com merengue italiano. Sabor cítrico e suave, perfeito para o verão. Feito com limões frescos e ingredientes selecionados. Encomenda com 2 dias de antecedência.',
                'price' => 79.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Gostaria de encomendar um Bolo de Limão com Merengue. Qual o tamanho disponível?',
                'main_image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1587668178277-295251f900ce?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Bolo Temático Personalizado',
                'category_id' => $catBolosEspeciais->id,
                'short_description' => 'Bolo temático personalizado, decoração conforme tema',
                'description' => 'Bolo temático personalizado conforme o tema da festa. Decoração com personagens, cores e detalhes personalizados. Sabores disponíveis: chocolate, baunilha, morango, cenoura. Orçamento personalizado conforme complexidade.',
                'price' => 199.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Gostaria de solicitar um orçamento para Bolo Temático Personalizado. Poderia me enviar mais informações?',
                'main_image' => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&h=600&fit=crop',
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
                'title' => 'Bolos Caseiros Feitos com Amor',
                'description' => 'Ingredientes selecionados e receitas da família',
                'image_url' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Encomendas Personalizadas',
                'description' => 'Criamos o bolo dos seus sonhos',
                'image_url' => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Entrega em Domicílio',
                'description' => 'Entregamos em toda a região',
                'image_url' => 'https://images.unsplash.com/photo-1587668178277-295251f900ce?w=1200&h=600&fit=crop',
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

        $this->command->info('Seeder de Bolos Caseiros criado com sucesso!');
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

