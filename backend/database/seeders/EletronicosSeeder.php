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

class EletronicosSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'techstore-brasil'],
            [
                'name' => 'TechStore Brasil',
                'slug' => 'techstore-brasil',
                'whatsapp_number' => '5511987654322',
                'description' => 'Os melhores eletrônicos com os melhores preços!',
                'banner_message' => '⚡ OFERTAS ESPECIAIS EM ELETRÔNICOS! ⚡',
                'banner_text_color_1' => '#ffffff',
                'banner_text_color_2' => '#00ff00',
                'banner_background_color' => '#000000',
                'primary_color' => '#0066cc',
                'secondary_color' => '#e6f2ff',
                'address' => 'Rua Augusta, 2000 - Consolação, São Paulo/SP',
                'email_contact' => 'contato@techstorebrasil.com.br',
                'business_sector' => 'electronics',
            ]
        );

        // Criar atributos padrão para eletrônicos
        $attributeService = app(ProductAttributeService::class);
        $attributeService->createDefaultAttributesForSector($tenant, 'electronics');

        // Criar usuário para o tenant
        User::updateOrCreate(
            ['email' => 'admin@techstorebrasil.com.br'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Ana Paula Santos',
                'password' => Hash::make('password'),
                'is_owner' => true,
                'email_verified_at' => now(),
            ]
        );

        // Categorias
        $catSmartphones = Category::updateOrCreate(
            ['slug' => 'smartphones', 'tenant_id' => $tenant->id],
            [
                'name' => 'Smartphones',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=500&h=500&fit=crop',
            ]
        );

        $catNotebooks = Category::updateOrCreate(
            ['slug' => 'notebooks', 'tenant_id' => $tenant->id],
            [
                'name' => 'Notebooks',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=500&h=500&fit=crop',
            ]
        );

        $catAcessorios = Category::updateOrCreate(
            ['slug' => 'acessorios', 'tenant_id' => $tenant->id],
            [
                'name' => 'Acessórios',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&h=500&fit=crop',
            ]
        );

        // Produtos (6 produtos)
        $produtos = [
            [
                'name' => 'Smartphone Galaxy S24 Ultra 256GB',
                'category_id' => $catSmartphones->id,
                'short_description' => 'Smartphone Android, 256GB, 12GB RAM, câmera 200MP',
                'description' => 'Smartphone Samsung Galaxy S24 Ultra com 256GB de armazenamento, 12GB de RAM, tela AMOLED de 6.8 polegadas, câmera principal de 200MP, bateria de 5000mAh e carregamento rápido. Inclui carregador e capa protetora.',
                'price' => 5499.00,
                'promotional_price' => 4999.00,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Notebook Dell Inspiron 15',
                'category_id' => $catNotebooks->id,
                'short_description' => 'Notebook 15.6", Intel Core i7, 16GB RAM, SSD 512GB',
                'description' => 'Notebook Dell Inspiron 15 com processador Intel Core i7 de 11ª geração, 16GB de RAM DDR4, SSD de 512GB, tela Full HD de 15.6 polegadas, placa de vídeo dedicada e Windows 11 pré-instalado. Ideal para trabalho e estudos.',
                'price' => 3899.00,
                'promotional_price' => 3499.00,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Fone de Ouvido Bluetooth JBL',
                'category_id' => $catAcessorios->id,
                'short_description' => 'Fone Bluetooth com cancelamento de ruído, bateria 30h',
                'description' => 'Fone de ouvido JBL com tecnologia Bluetooth 5.0, cancelamento de ruído ativo, bateria de até 30 horas de uso contínuo, microfone integrado para chamadas e som de alta qualidade. Inclui estojo de carregamento.',
                'price' => 599.00,
                'promotional_price' => 499.00,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Smartphone iPhone 15 Pro 128GB',
                'category_id' => $catSmartphones->id,
                'short_description' => 'iPhone 15 Pro, 128GB, câmera tripla, chip A17 Pro',
                'description' => 'Apple iPhone 15 Pro com 128GB de armazenamento, chip A17 Pro, tela Super Retina XDR de 6.1 polegadas, sistema de câmeras triplas com zoom óptico, bateria de longa duração e resistente à água. Inclui cabo USB-C.',
                'price' => 7999.00,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Tablet Samsung Galaxy Tab S9',
                'category_id' => $catNotebooks->id,
                'short_description' => 'Tablet 11", 128GB, S Pen incluso, ideal para produtividade',
                'description' => 'Tablet Samsung Galaxy Tab S9 com tela de 11 polegadas, 128GB de armazenamento, 8GB de RAM, processador Snapdragon 8 Gen 2, S Pen incluso, bateria de longa duração e suporte para teclado. Perfeito para trabalho e entretenimento.',
                'price' => 3299.00,
                'promotional_price' => 2999.00,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1544244015-0df4b3a8f75d?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1544244015-0df4b3a8f75d?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Smartwatch Apple Watch Series 9',
                'category_id' => $catAcessorios->id,
                'short_description' => 'Apple Watch Series 9, GPS, monitoramento de saúde',
                'description' => 'Apple Watch Series 9 com GPS, tela Always-On Retina, monitoramento de frequência cardíaca, ECG, oxigênio no sangue, rastreamento de atividades físicas, resistente à água e bateria de até 18 horas. Disponível em várias cores de pulseira.',
                'price' => 2999.00,
                'action_type' => 'add_to_cart',
                'main_image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?w=800&h=600&fit=crop',
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
                'title' => 'Ofertas Especiais em Eletrônicos',
                'description' => 'Os melhores produtos com os melhores preços',
                'image_url' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Frete Grátis',
                'description' => 'Em compras acima de R$ 500,00',
                'image_url' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Novos Lançamentos',
                'description' => 'Confira os produtos que acabaram de chegar',
                'image_url' => 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=1200&h=600&fit=crop',
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

        $this->command->info('Seeder de Eletrônicos criado com sucesso!');
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

