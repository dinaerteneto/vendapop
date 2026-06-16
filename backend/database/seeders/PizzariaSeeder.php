<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\RotatingBanner;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ProductAttributeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PizzariaSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'pizzaria-boa-massa'],
            [
                'name' => 'Pizzaria Boa Massa',
                'slug' => 'pizzaria-boa-massa',
                'whatsapp_number' => '5511987654328',
                'description' => 'A melhor pizza de bairro, feita na hora e entregue quentinha! Delivery rápido e sabores que todo mundo ama.',
                'banner_message' => '🍕 PIZZA DO DIA! PEÇA AGORA E RECEBA EM 40 MIN 🍕',
                'banner_text_color_1' => '#ffffff',
                'banner_text_color_2' => '#FFD700',
                'banner_background_color' => '#C62828',
                'primary_color' => '#C62828',
                'secondary_color' => '#FFF8E1',
                'address' => 'Rua das Pizzas, 500 - Vila Mariana, São Paulo/SP',
                'email_contact' => 'contato@pizzariaboamassa.com.br',
                'business_sector' => 'food',
            ]
        );

        // Criar atributos padrão para comida
        $attributeService = app(ProductAttributeService::class);
        $attributeService->createDefaultAttributesForSector($tenant, 'food');

        // Criar atributos extras para pizzaria (borda e sabor 2 para meio a meio)
        $this->createExtraAttributes($tenant);

        // Criar usuário para o tenant
        User::updateOrCreate(
            ['email' => 'admin@pizzariaboamassa.com.br'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Toninho da Pizza',
                'password' => Hash::make('password'),
                'is_owner' => true,
                'email_verified_at' => now(),
            ]
        );

        // Categorias
        $catPizzasSalgadas = Category::updateOrCreate(
            ['slug' => 'pizzas-salgadas', 'tenant_id' => $tenant->id],
            [
                'name' => 'Pizzas Salgadas',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=500&h=500&fit=crop',
            ]
        );

        $catPizzasDoces = Category::updateOrCreate(
            ['slug' => 'pizzas-doces', 'tenant_id' => $tenant->id],
            [
                'name' => 'Pizzas Doces',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=500&h=500&fit=crop',
            ]
        );

        $catBebidas = Category::updateOrCreate(
            ['slug' => 'bebidas', 'tenant_id' => $tenant->id],
            [
                'name' => 'Bebidas',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1527960471264-932f39eb5846?w=500&h=500&fit=crop',
            ]
        );

        // Produtos (6 produtos: 3 add_to_cart + 3 whatsapp_contact)
        $produtos = [
            [
                'name' => 'Brotinho de Calabresa',
                'category_id' => $catPizzasSalgadas->id,
                'short_description' => 'Massa fina, calabresa defumada, cebola e azeitona',
                'description' => 'Pizza individual (brotinho) com massa fina e crocante, coberta com calabresa defumada fatiada, cebola roxa, azeitonas pretas e muçarela derretida. Perfeita para uma refeição rápida e saborosa. Tamanho: 25cm. Serve 1 pessoa.',
                'price' => 28.90,
                'action_type' => 'add_to_cart',
                'whatsapp_message' => null,
                'main_image' => 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Brotinho de Marguerita',
                'category_id' => $catPizzasSalgadas->id,
                'short_description' => 'Molho de tomate, muçarela fresca, manjericão e parmesão',
                'description' => 'Pizza individual (brotinho) com molho de tomate artesanal, muçarela de búfala, folhas de manjericão fresco e finalizada com parmesão ralado. Simples e deliciosa, como manda a tradição italiana. Tamanho: 25cm. Serve 1 pessoa.',
                'price' => 25.90,
                'action_type' => 'add_to_cart',
                'whatsapp_message' => null,
                'main_image' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Pizza Família 4 Queijos',
                'category_id' => $catPizzasSalgadas->id,
                'short_description' => 'Muçarela, provolone, gorgonzola e parmesão. Massa grossa.',
                'description' => 'Pizza tamanho família com muçarela, provolone defumado, gorgonzola cremoso e parmesão gratinado sobre massa grossa estilo pan. Combinação irresistível de queijos selecionados. Tamanho: 35cm. Serve 3 a 4 pessoas. Disponível meio a meio — escolha 2 sabores!',
                'price' => 59.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Gostaria de pedir uma Pizza Família 4 Queijos. Pode me informar sobre os sabores disponíveis para meio a meio?',
                'main_image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Pizza Família Portuguesa',
                'category_id' => $catPizzasSalgadas->id,
                'short_description' => 'Presunto, ovos, cebola, pimentão, ervilha e muçarela',
                'description' => 'Pizza tamanho família no capricho: presunto magro, ovos fatiados, cebola roxa, pimentão verde, ervilhas frescas, azeitonas e muita muçarela. A clássica portuguesa que todo mundo conhece e ama. Tamanho: 35cm. Serve 3 a 4 pessoas. Disponível meio a meio!',
                'price' => 64.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Gostaria de pedir uma Pizza Família Portuguesa. Tem opção meio a meio? Quais sabores?',
                'main_image' => 'https://images.unsplash.com/photo-1604382354936-07c5d9983bd3?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1604382354936-07c5d9983bd3?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Pizza Doce de Chocolate com Morango',
                'category_id' => $catPizzasDoces->id,
                'short_description' => 'Chocolate ao leite, morangos frescos, leite condensado',
                'description' => 'Deliciosa pizza doce com base de chocolate ao leite cremoso, morangos frescos fatiados, granulado e fios de leite condensado. Sobremesa perfeita para compartilhar depois da pizza salgada. Tamanho: 30cm. Serve 2 a 3 pessoas.',
                'price' => 49.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Gostaria de pedir uma Pizza Doce de Chocolate com Morango. Qual a disponibilidade para hoje?',
                'main_image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Combo Bebidas (Coca-Cola 2L + Guaraná 2L)',
                'category_id' => $catBebidas->id,
                'short_description' => 'Coca-Cola 2 litros + Guaraná Antarctica 2 litros',
                'description' => 'Combo perfeito para acompanhar sua pizza: 1 Coca-Cola 2 litros + 1 Guaraná Antarctica 2 litros. Bebidas geladas entregues junto com seu pedido. Aproveite!',
                'price' => 22.90,
                'action_type' => 'add_to_cart',
                'whatsapp_message' => null,
                'main_image' => 'https://images.unsplash.com/photo-1527960471264-932f39eb5846?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1527960471264-932f39eb5846?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=800&h=600&fit=crop',
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
                'title' => 'Delivery em 40 Minutos',
                'description' => 'Pizza quentinha na sua porta — ou é grátis!',
                'image_url' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Pizza Meio a Meio',
                'description' => 'Agora você pode escolher 2 sabores na mesma pizza família!',
                'image_url' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Combo Família em Dobro',
                'description' => '2 pizzas família + bebida com preço especial. Chama no WhatsApp!',
                'image_url' => 'https://images.unsplash.com/photo-1604382354936-07c5d9983bd3?w=1200&h=600&fit=crop',
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

        $this->command->info('Seeder da Pizzaria Boa Massa criado com sucesso!');
    }

    private function createExtraAttributes(Tenant $tenant): void
    {
        ProductAttribute::firstOrCreate(
            ['slug' => 'borda', 'tenant_id' => $tenant->id],
            [
                'name' => 'Borda',
                'order' => 2,
                'is_active' => true,
            ]
        );

        ProductAttribute::firstOrCreate(
            ['slug' => 'sabor-2', 'tenant_id' => $tenant->id],
            [
                'name' => 'Sabor 2',
                'order' => 3,
                'is_active' => true,
            ]
        );
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
