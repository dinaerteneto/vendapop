<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductVariation;
use App\Models\RotatingBanner;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ProductAttributeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImobiliariaSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'casa-lar-imoveis'],
            [
                'name' => 'Casa & Lar Imóveis',
                'slug' => 'casa-lar-imoveis',
                'whatsapp_number' => '5511987654321',
                'description' => 'Sua imobiliária de confiança! Encontre o imóvel dos seus sonhos.',
                'banner_message' => '🏠 Encontre seu imóvel ideal! Fale conosco pelo WhatsApp.',
                'banner_text_color_1' => '#ffffff',
                'banner_text_color_2' => '#FFD700',
                'banner_background_color' => '#1a472a',
                'primary_color' => '#1a472a',
                'secondary_color' => '#f0f8f0',
                'address' => 'Av. Paulista, 1000 - Bela Vista, São Paulo/SP',
                'email_contact' => 'contato@casalarimoveis.com.br',
                'business_sector' => 'real_estate',
            ]
        );

        // Criar atributos padrão para imobiliária
        $attributeService = app(ProductAttributeService::class);
        $attributeService->createDefaultAttributesForSector($tenant, 'real_estate');

        // Buscar atributos criados
        $attrTipoOperacao = ProductAttribute::where('tenant_id', $tenant->id)
            ->where('slug', 'tipo-operacao')
            ->first();
        $attrArea = ProductAttribute::where('tenant_id', $tenant->id)
            ->where('slug', 'area')
            ->first();

        // Criar usuário para o tenant
        User::updateOrCreate(
            ['email' => 'admin@casalarimoveis.com.br'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Carlos Silva',
                'password' => Hash::make('password'),
                'is_owner' => true,
                'email_verified_at' => now(),
            ]
        );

        // Categorias
        $catCasas = Category::updateOrCreate(
            ['slug' => 'casas', 'tenant_id' => $tenant->id],
            [
                'name' => 'Casas',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=500&h=500&fit=crop',
            ]
        );

        $catApartamentos = Category::updateOrCreate(
            ['slug' => 'apartamentos', 'tenant_id' => $tenant->id],
            [
                'name' => 'Apartamentos',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=500&h=500&fit=crop',
            ]
        );

        $catTerrenos = Category::updateOrCreate(
            ['slug' => 'terrenos', 'tenant_id' => $tenant->id],
            [
                'name' => 'Terrenos',
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=500&h=500&fit=crop',
            ]
        );

        // Produtos (6 produtos)
        $produtos = [
            [
                'name' => 'Casa com 3 Quartos - Jardim das Flores',
                'category_id' => $catCasas->id,
                'short_description' => 'Casa espaçosa com 3 quartos, 2 banheiros, garagem para 2 carros',
                'description' => 'Excelente casa localizada no bairro Jardim das Flores. Possui 3 quartos sendo 1 suíte, 2 banheiros, sala ampla, cozinha integrada, área de serviço e garagem coberta para 2 carros. Próximo a escolas, supermercados e transporte público.',
                'price' => 450000.00,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Tenho interesse na Casa com 3 Quartos - Jardim das Flores. Poderia me enviar mais informações?',
                'button_label' => 'Fale com um Corretor',
                'main_image' => 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&h=600&fit=crop',
                ],
                'attributes' => [
                    'tipo_operacao' => 'Venda',
                    'area' => '100-200m²',
                ],
            ],
            [
                'name' => 'Apartamento 2 Quartos - Centro',
                'category_id' => $catApartamentos->id,
                'short_description' => 'Apartamento moderno no centro, 2 quartos, 1 banheiro, varanda',
                'description' => 'Apartamento bem localizado no centro da cidade. Possui 2 quartos, 1 banheiro, sala, cozinha, área de serviço e varanda. Prédio com portaria 24h, elevador e vaga na garagem. Ideal para investimento ou moradia.',
                'price' => 280000.00,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Tenho interesse no Apartamento 2 Quartos - Centro. Poderia agendar uma visita?',
                'button_label' => 'Fale com um Corretor',
                'main_image' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop',
                ],
                'attributes' => [
                    'tipo_operacao' => 'Venda',
                    'area' => '50-100m²',
                ],
            ],
            [
                'name' => 'Terreno 500m² - Zona Norte',
                'category_id' => $catTerrenos->id,
                'short_description' => 'Terreno plano, 500m², documentação em dia, pronto para construir',
                'description' => 'Excelente terreno de 500m² localizado na Zona Norte. Terreno plano, sem necessidade de aterro, com documentação em dia e pronto para construir. Área residencial, próximo a comércio e transporte público.',
                'price' => 180000.00,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Tenho interesse no Terreno 500m² - Zona Norte. Poderia me enviar mais detalhes?',
                'button_label' => 'Fale com um Corretor',
                'main_image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1501594907352-04cda38ebc29?w=800&h=600&fit=crop',
                ],
                'attributes' => [
                    'tipo_operacao' => 'Venda',
                    'area' => 'Acima de 300m²',
                ],
            ],
            [
                'name' => 'Casa de Luxo - Bairro Nobre',
                'category_id' => $catCasas->id,
                'short_description' => 'Casa de alto padrão, 4 quartos, 3 banheiros, piscina, área gourmet',
                'description' => 'Casa de luxo em bairro nobre. Possui 4 quartos sendo 2 suítes, 3 banheiros, sala de estar e jantar, cozinha planejada, área gourmet completa, piscina, churrasqueira, garagem para 3 carros. Condomínio fechado com segurança 24h.',
                'price' => 1200000.00,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Tenho interesse na Casa de Luxo - Bairro Nobre. Poderia agendar uma visita?',
                'button_label' => 'Fale com um Corretor',
                'main_image' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop',
                ],
                'attributes' => [
                    'tipo_operacao' => 'Venda',
                    'area' => 'Acima de 300m²',
                ],
            ],
            [
                'name' => 'Apartamento 3 Quartos - Vista para o Mar',
                'category_id' => $catApartamentos->id,
                'short_description' => 'Apartamento com vista para o mar, 3 quartos, 2 banheiros, varanda ampla',
                'description' => 'Apartamento de alto padrão com vista privilegiada para o mar. Possui 3 quartos sendo 1 suíte, 2 banheiros, sala ampla, cozinha integrada, varanda com churrasqueira e vaga na garagem. Prédio com piscina, academia e salão de festas.',
                'price' => 650000.00,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Tenho interesse no Apartamento 3 Quartos - Vista para o Mar. Poderia me enviar mais informações?',
                'button_label' => 'Fale com um Corretor',
                'main_image' => 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop',
                ],
                'attributes' => [
                    'tipo_operacao' => 'Venda',
                    'area' => '100-200m²',
                ],
            ],
            [
                'name' => 'Terreno Comercial - Avenida Principal',
                'category_id' => $catTerrenos->id,
                'short_description' => 'Terreno comercial, 300m², esquina, localização estratégica',
                'description' => 'Terreno comercial em localização estratégica na avenida principal. Terreno de esquina com 300m², documentação em dia, ideal para construção de loja, escritório ou posto de gasolina. Alto fluxo de veículos e pedestres.',
                'price' => 320000.00,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Tenho interesse no Terreno Comercial - Avenida Principal. Poderia agendar uma visita?',
                'button_label' => 'Fale com um Corretor',
                'main_image' => 'https://images.unsplash.com/photo-1448630360428-65456885c650?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1448630360428-65456885c650?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1501594907352-04cda38ebc29?w=800&h=600&fit=crop',
                ],
                'attributes' => [
                    'tipo_operacao' => 'Venda',
                    'area' => '200-300m²',
                ],
            ],
        ];

        foreach ($produtos as $produtoData) {
            $mainImage = $produtoData['main_image'];
            $galleryImages = $produtoData['gallery_images'];
            $attributes = $produtoData['attributes'] ?? [];
            unset($produtoData['main_image'], $produtoData['gallery_images'], $produtoData['attributes']);

            $product = Product::updateOrCreate(
                ['slug' => Str::slug($produtoData['name']), 'tenant_id' => $tenant->id],
                array_merge($produtoData, [
                    'tenant_id' => $tenant->id,
                    'is_active' => true,
                    'is_hot' => false,
                ])
            );

            $this->syncImages($product, $mainImage, $galleryImages);

            // Criar variações com atributos
            $this->createProductVariations($product, $attributes, $attrTipoOperacao, $attrArea);
        }

        // Banners
        $banners = [
            [
                'title' => 'Encontre seu Imóvel Ideal',
                'description' => 'Casas, apartamentos e terrenos para venda e locação',
                'image_url' => 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Financiamento Facilitado',
                'description' => 'Aproveite condições especiais de financiamento',
                'image_url' => 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Avaliação Gratuita',
                'description' => 'Avaliamos seu imóvel sem compromisso',
                'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&h=600&fit=crop',
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

        $this->command->info('Seeder de Imobiliária criado com sucesso!');
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

    private function createProductVariations(Product $product, array $attributes, ?ProductAttribute $attrTipoOperacao, ?ProductAttribute $attrArea)
    {
        // Deletar variações existentes
        $product->variations()->delete();

        if (empty($attributes)) {
            return;
        }

        // Criar variação única com os atributos fornecidos
        $variationAttributes = [];

        if ($attrTipoOperacao && isset($attributes['tipo_operacao'])) {
            $variationAttributes[(string)$attrTipoOperacao->id] = $attributes['tipo_operacao'];
        }

        if ($attrArea && isset($attributes['area'])) {
            $variationAttributes[(string)$attrArea->id] = $attributes['area'];
        }

        if (!empty($variationAttributes)) {
            ProductVariation::create([
                'product_id' => $product->id,
                'attributes' => $variationAttributes,
                'is_active' => true,
            ]);
        }
    }
}

