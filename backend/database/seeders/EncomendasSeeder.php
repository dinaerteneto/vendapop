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

class EncomendasSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'personaliza-facil'],
            [
                'name' => 'Personaliza Fácil',
                'slug' => 'personaliza-facil',
                'whatsapp_number' => '5511987654326',
                'description' => 'Produtos personalizados e sob encomenda!',
                'banner_message' => '📦 PRODUTOS PERSONALIZADOS E SOB ENCOMENDA! 📦',
                'banner_text_color_1' => '#ffffff',
                'banner_text_color_2' => '#ff6b6b',
                'banner_background_color' => '#2c3e50',
                'primary_color' => '#2c3e50',
                'secondary_color' => '#ecf0f1',
                'address' => 'Rua Teodoro Sampaio, 1200 - Pinheiros, São Paulo/SP',
                'email_contact' => 'contato@personalizafacil.com.br',
                'business_sector' => 'custom_orders',
            ]
        );

        // Criar atributos padrão para encomendas personalizadas
        $attributeService = app(ProductAttributeService::class);
        $attributeService->createDefaultAttributesForSector($tenant, 'custom_orders');

        // Criar usuário para o tenant
        User::updateOrCreate(
            ['email' => 'admin@personalizafacil.com.br'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Roberto Oliveira',
                'password' => Hash::make('password'),
                'is_owner' => true,
                'email_verified_at' => now(),
            ]
        );

        // Categorias
        $catPersonalizados = Category::updateOrCreate(
            ['slug' => 'personalizados', 'tenant_id' => $tenant->id],
            [
                'name' => 'Produtos Personalizados',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=500&h=500&fit=crop',
            ]
        );

        $catArtesanato = Category::updateOrCreate(
            ['slug' => 'artesanato', 'tenant_id' => $tenant->id],
            [
                'name' => 'Artesanato',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=500&h=500&fit=crop',
            ]
        );

        $catPresentes = Category::updateOrCreate(
            ['slug' => 'presentes', 'tenant_id' => $tenant->id],
            [
                'name' => 'Presentes',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=500&h=500&fit=crop',
            ]
        );

        // Produtos (6 produtos)
        $produtos = [
            [
                'name' => 'Caneca Personalizada com Foto',
                'category_id' => $catPersonalizados->id,
                'short_description' => 'Caneca personalizada com sua foto ou mensagem',
                'description' => 'Caneca de cerâmica personalizada com sua foto, mensagem ou design exclusivo. Capacidade de 350ml, impressão de alta qualidade que não desbota. Perfeita para presente ou uso pessoal. Prazo de produção: 5 a 7 dias úteis.',
                'price' => 49.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Gostaria de encomendar uma Caneca Personalizada com Foto. Poderia me enviar mais informações?',
                'main_image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Camiseta Personalizada',
                'category_id' => $catPersonalizados->id,
                'short_description' => 'Camiseta com estampa personalizada, vários tamanhos',
                'description' => 'Camiseta 100% algodão com estampa personalizada. Você escolhe o design, cor da camiseta e tamanho. Estampa em alta qualidade que não desbota. Disponível em vários tamanhos e cores. Prazo de produção: 7 a 10 dias úteis.',
                'price' => 79.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Tenho interesse em uma Camiseta Personalizada. Poderia me informar sobre valores e prazos?',
                'main_image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Quadro Decorativo Personalizado',
                'category_id' => $catArtesanato->id,
                'short_description' => 'Quadro decorativo com foto ou texto personalizado',
                'description' => 'Quadro decorativo personalizado com sua foto ou texto. Disponível em vários tamanhos e estilos de moldura. Impressão de alta qualidade em papel fotográfico. Perfeito para decorar sua casa ou presentear. Prazo de produção: 5 dias úteis.',
                'price' => 89.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Gostaria de encomendar um Quadro Decorativo Personalizado. Poderia me enviar mais informações?',
                'main_image' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Almofada Personalizada',
                'category_id' => $catPresentes->id,
                'short_description' => 'Almofada decorativa com estampa personalizada',
                'description' => 'Almofada decorativa com estampa personalizada. Capa removível, enchimento de fibra siliconada, impressão de alta qualidade. Perfeita para decorar sofá, cama ou cadeira. Disponível em vários tamanhos. Prazo de produção: 7 dias úteis.',
                'price' => 69.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Tenho interesse em uma Almofada Personalizada. Qual o tamanho disponível?',
                'main_image' => 'https://images.unsplash.com/photo-1584100936595-8f892e48d2b1?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1584100936595-8f892e48d2b1?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Kit Presente Personalizado',
                'category_id' => $catPresentes->id,
                'short_description' => 'Kit presente com produtos personalizados',
                'description' => 'Kit presente personalizado com caneca, camiseta e outros itens conforme sua escolha. Embalagem especial para presente. Ideal para aniversários, casamentos ou datas comemorativas. Orçamento personalizado conforme itens escolhidos.',
                'price' => 199.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Gostaria de solicitar um orçamento para Kit Presente Personalizado. Poderia me enviar mais informações?',
                'main_image' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Mochila Personalizada',
                'category_id' => $catPersonalizados->id,
                'short_description' => 'Mochila com estampa personalizada, vários modelos',
                'description' => 'Mochila personalizada com estampa de alta qualidade. Disponível em vários modelos (escolar, esportiva, casual) e tamanhos. Estampa personalizada conforme seu design. Perfeita para uso diário ou presente. Prazo de produção: 10 a 15 dias úteis.',
                'price' => 129.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Tenho interesse em uma Mochila Personalizada. Poderia me informar sobre modelos e valores?',
                'main_image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800&h=600&fit=crop',
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
                'title' => 'Produtos Personalizados',
                'description' => 'Criamos produtos únicos para você',
                'image_url' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Encomendas Sob Medida',
                'description' => 'Produzimos conforme sua necessidade',
                'image_url' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Presentes Especiais',
                'description' => 'Presenteie com produtos personalizados',
                'image_url' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=1200&h=600&fit=crop',
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

        $this->command->info('Seeder de Encomendas criado com sucesso!');
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

