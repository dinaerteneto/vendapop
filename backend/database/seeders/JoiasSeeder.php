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

class JoiasSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'brilho-elegancia'],
            [
                'name' => 'Brilho & Elegância',
                'slug' => 'brilho-elegancia',
                'whatsapp_number' => '5511987654324',
                'description' => 'Joias finas e semijoias com design exclusivo!',
                'banner_message' => '💎 JOIAS FINAS E SEMIJOIAS COM DESIGN EXCLUSIVO! 💎',
                'banner_text_color_1' => '#ffffff',
                'banner_text_color_2' => '#FFD700',
                'banner_background_color' => '#1a1a2e',
                'primary_color' => '#1a1a2e',
                'secondary_color' => '#f5f5dc',
                'address' => 'Rua Haddock Lobo, 800 - Cerqueira César, São Paulo/SP',
                'email_contact' => 'contato@brilhoelegancia.com.br',
                'business_sector' => 'jewelry',
            ]
        );

        // Criar atributos padrão para joias
        $attributeService = app(ProductAttributeService::class);
        $attributeService->createDefaultAttributesForSector($tenant, 'jewelry');

        // Criar usuário para o tenant
        User::updateOrCreate(
            ['email' => 'admin@brilhoelegancia.com.br'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Juliana Almeida',
                'password' => Hash::make('password'),
                'is_owner' => true,
                'email_verified_at' => now(),
            ]
        );

        // Categorias
        $catAneis = Category::updateOrCreate(
            ['slug' => 'aneis', 'tenant_id' => $tenant->id],
            [
                'name' => 'Anéis',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=500&h=500&fit=crop',
            ]
        );

        $catColares = Category::updateOrCreate(
            ['slug' => 'colares', 'tenant_id' => $tenant->id],
            [
                'name' => 'Colares',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=500&h=500&fit=crop',
            ]
        );

        $catBrincos = Category::updateOrCreate(
            ['slug' => 'brincos', 'tenant_id' => $tenant->id],
            [
                'name' => 'Brincos',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=500&h=500&fit=crop',
            ]
        );

        // Produtos (6 produtos)
        $produtos = [
            [
                'name' => 'Anel de Ouro 18k com Diamante',
                'category_id' => $catAneis->id,
                'short_description' => 'Anel de ouro 18k com diamante solitário, tamanho ajustável',
                'description' => 'Elegante anel de ouro 18k com diamante solitário de 0.5ct. Design clássico e atemporal, perfeito para noivado ou presente especial. Tamanho ajustável, acompanha certificado de autenticidade.',
                'price' => 8999.00,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Colar de Prata com Pérola',
                'category_id' => $catColares->id,
                'short_description' => 'Colar de prata 925 com pérola natural, 45cm',
                'description' => 'Delicado colar de prata 925 com pérola natural. Comprimento de 45cm, fecho de segurança. Design elegante e sofisticado, perfeito para ocasiões especiais ou uso diário.',
                'price' => 599.00,
                'promotional_price' => 499.00,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Brincos de Ouro 18k com Zircônia',
                'category_id' => $catBrincos->id,
                'short_description' => 'Brincos de ouro 18k com zircônia cúbica, fecho de segurança',
                'description' => 'Lindos brincos de ouro 18k com zircônia cúbica. Design clássico tipo argola, fecho de segurança. Perfeitos para uso diário ou ocasiões especiais. Hipoaalergênicos.',
                'price' => 1299.00,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Anel de Prata com Pedra Colorida',
                'category_id' => $catAneis->id,
                'short_description' => 'Anel de prata 925 com pedra colorida, vários tamanhos',
                'description' => 'Anel de prata 925 com pedra colorida (água-marinha, quartzo rosa ou ametista). Design moderno e versátil, perfeito para compor looks casuais ou elegantes. Disponível em vários tamanhos.',
                'price' => 299.00,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Colar de Ouro 18k com Pingente',
                'category_id' => $catColares->id,
                'short_description' => 'Colar de ouro 18k com pingente personalizado, 50cm',
                'description' => 'Elegante colar de ouro 18k com pingente personalizado. Comprimento de 50cm, corrente ajustável. Perfeito para presentear ou usar como peça especial. Acompanha estojo de presente.',
                'price' => 2499.00,
                'promotional_price' => 2199.00,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Brincos de Prata com Cristal',
                'category_id' => $catBrincos->id,
                'short_description' => 'Brincos de prata 925 com cristal Swarovski, fecho de segurança',
                'description' => 'Brincos de prata 925 com cristal Swarovski. Design delicado e brilhante, perfeito para iluminar qualquer look. Fecho de segurança, hipoaalergênicos. Ideal para uso diário ou eventos.',
                'price' => 399.00,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=800&h=600&fit=crop',
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
                'title' => 'Joias Finas e Semijoias',
                'description' => 'Design exclusivo e qualidade garantida',
                'image_url' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Promoção Especial',
                'description' => 'Até 20% OFF em toda a loja',
                'image_url' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Frete Grátis',
                'description' => 'Em compras acima de R$ 500,00',
                'image_url' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=1200&h=600&fit=crop',
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

        $this->command->info('Seeder de Joias criado com sucesso!');
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

