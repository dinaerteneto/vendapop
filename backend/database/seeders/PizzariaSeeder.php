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

class PizzariaSeeder extends Seeder
{
    private Tenant $tenant;
    private int $attrTamanhoId;
    private int $attrBordaId;
    private int $attrSabor1Id;
    private int $attrSabor2Id;
    private int $attrSabor3Id;
    private int $attrSabor4Id;

    public function run(): void
    {
        // Limpa dados antigos do tenant para reseed limpo
        $existing = Tenant::where('slug', 'primo-giuseppe')->first();
        if ($existing) {
            ProductVariation::whereIn('product_id', Product::where('tenant_id', $existing->id)->pluck('id'))->delete();
            Product::where('tenant_id', $existing->id)->delete();
            RotatingBanner::where('tenant_id', $existing->id)->delete();
            Category::where('tenant_id', $existing->id)->delete();
            ProductAttribute::where('tenant_id', $existing->id)->delete();
        }

        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'primo-giuseppe'],
            [
                'name' => 'Primo Giuseppe',
                'slug' => 'primo-giuseppe',
                'whatsapp_number' => '5579999999999',
                'description' => 'Pizzas artesanais com ingredientes selecionados. Tradição italiana em cada fatia!',
                'banner_message' => '🍕 PRIMO GIUSEPPE — PIZZA ARTESANAL 🍕',
                'banner_text_color_1' => '#ffffff',
                'banner_text_color_2' => '#FFD700',
                'banner_background_color' => '#8B0000',
                'primary_color' => '#8B0000',
                'secondary_color' => '#FFF8E1',
                'address' => 'Aracaju/SE',
                'email_contact' => 'contato@primogiuseppe.com.br',
                'business_sector' => 'food',
            ]
        );

        $attributeService = app(ProductAttributeService::class);
        $attributeService->createDefaultAttributesForSector($this->tenant, 'food');

        $this->createExtraAttributes();

        $this->attrTamanhoId = ProductAttribute::where('tenant_id', $this->tenant->id)->where('slug', 'tamanho')->value('id');
        $this->attrBordaId  = ProductAttribute::where('tenant_id', $this->tenant->id)->where('slug', 'borda')->value('id');
        $this->attrSabor1Id = ProductAttribute::where('tenant_id', $this->tenant->id)->where('slug', 'sabor-1')->value('id');
        $this->attrSabor2Id = ProductAttribute::where('tenant_id', $this->tenant->id)->where('slug', 'sabor-2')->value('id');
        $this->attrSabor3Id = ProductAttribute::where('tenant_id', $this->tenant->id)->where('slug', 'sabor-3')->value('id');
        $this->attrSabor4Id = ProductAttribute::where('tenant_id', $this->tenant->id)->where('slug', 'sabor-4')->value('id');

        User::updateOrCreate(
            ['email' => 'admin@primogiuseppe.com.br'],
            [
                'tenant_id' => $this->tenant->id,
                'name' => 'Primo Giuseppe',
                'password' => Hash::make('password'),
                'is_owner' => true,
                'email_verified_at' => now(),
            ]
        );

        $catSalgadas = Category::updateOrCreate(
            ['slug' => 'pizzas-salgadas', 'tenant_id' => $this->tenant->id],
            ['name' => 'Pizzas Salgadas', 'is_active' => true, 'image_url' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=500&h=500&fit=crop']
        );

        $catDoces = Category::updateOrCreate(
            ['slug' => 'pizzas-doces', 'tenant_id' => $this->tenant->id],
            ['name' => 'Pizzas Doces', 'is_active' => true, 'image_url' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=500&h=500&fit=crop']
        );

        $catBebidas = Category::updateOrCreate(
            ['slug' => 'bebidas', 'tenant_id' => $this->tenant->id],
            ['name' => 'Bebidas', 'is_active' => true, 'image_url' => 'https://images.unsplash.com/photo-1527960471264-932f39eb5846?w=500&h=500&fit=crop']
        );

        $this->createPizzaConcepts($catSalgadas);
        $this->createPizzaConceptsDoces($catDoces);
        $this->createBebidas($catBebidas);
        $this->createBanners();

        $this->command->info('Seeder da Primo Giuseppe criado com sucesso!');
    }

    // ═══════════════════════════════════════════════
    // ATRIBUTOS EXTRAS (Sabor 1, 2, 3, 4 + Borda)
    // ═══════════════════════════════════════════════
    private function createExtraAttributes(): void
    {
        ProductAttribute::firstOrCreate(
            ['slug' => 'borda', 'tenant_id' => $this->tenant->id],
            ['name' => 'Borda', 'order' => 2, 'is_active' => true]
        );
        ProductAttribute::firstOrCreate(
            ['slug' => 'sabor-1', 'tenant_id' => $this->tenant->id],
            ['name' => 'Sabor 1', 'order' => 3, 'is_active' => true]
        );
        ProductAttribute::firstOrCreate(
            ['slug' => 'sabor-2', 'tenant_id' => $this->tenant->id],
            ['name' => 'Sabor 2', 'order' => 4, 'is_active' => true]
        );
        ProductAttribute::firstOrCreate(
            ['slug' => 'sabor-3', 'tenant_id' => $this->tenant->id],
            ['name' => 'Sabor 3', 'order' => 5, 'is_active' => true]
        );
        ProductAttribute::firstOrCreate(
            ['slug' => 'sabor-4', 'tenant_id' => $this->tenant->id],
            ['name' => 'Sabor 4', 'order' => 6, 'is_active' => true]
        );
    }

    // ═══════════════════════════════════════════════
    // MAPAS DE PREÇO POR SABOR (chave: nome, valor: [P,M,G,F])
    // ═══════════════════════════════════════════════
    private function saboresSalgados(): array
    {
        $base = [32.00, 40.00, 55.00, 65.00]; // P, M, G, F
        return [
            'A Moda da Casa'          => $base,
            'A Moda da Casa 2'        => $base,
            'Abobrinha Especial'      => [40.00, 45.00, 60.00, 70.00],
            'Alho e Óleo'             => $base,
            'Alemã'                   => $base,
            'Atum'                    => $base,
            'Atum Sólido'             => [40.00, 45.00, 60.00, 70.00],
            'Bacalhau'                => [42.00, 48.00, 65.00, 75.00],
            'Bacon'                   => $base,
            'Baiana'                  => $base,
            'Barão'                   => [45.00, 52.00, 70.00, 80.00],
            'Bolonhesa'               => $base,
            'Brasileira'              => [45.00, 55.00, 70.00, 85.00],
            'Brócolis'                => [40.00, 45.00, 60.00, 70.00],
            'Calabresa'               => $base,
            'Camarão'                 => [42.00, 48.00, 65.00, 75.00],
            'Camarão Especial'        => [45.00, 52.00, 70.00, 85.00],
            'Caprichosa'              => [42.00, 48.00, 65.00, 75.00],
            'Catupiry'                => $base,
            'Cinco Queijos'           => [45.00, 55.00, 70.00, 85.00],
            'Da Mama'                 => [42.00, 48.00, 65.00, 75.00],
            'Escarola'                => $base,
            'Francesa'                => $base,
            'Frango'                  => $base,
            'Frango Caipira'          => $base,
            'Frango com Catupiry'     => $base,
            'Light'                   => [40.00, 45.00, 60.00, 70.00],
            'Lombo'                   => [40.00, 45.00, 60.00, 70.00],
            'Marguerita'              => $base,
            'Milho Verde'             => $base,
            'Mussarela'               => $base,
            'Mussarela e Tomate'      => $base,
            'Palmito'                 => [40.00, 45.00, 60.00, 70.00],
            'Peito de Peru'           => [40.00, 45.00, 60.00, 70.00],
            'Pepperoni'               => [45.00, 52.00, 70.00, 85.00],
            'Pizzaiolo'               => [40.00, 45.00, 60.00, 70.00],
            'Poderosa'                => [40.00, 45.00, 60.00, 70.00],
            'Portuguesa'              => $base,
            'Presunto'                => $base,
            'Primo Giuseppe'          => [45.00, 50.00, 65.00, 75.00],
            'Quatro Queijos'          => [40.00, 45.00, 60.00, 70.00],
            'Sabor Paulista'          => [40.00, 45.00, 60.00, 70.00],
            'Sergipana'               => [45.00, 50.00, 65.00, 75.00],
            'Sertaneja'               => [40.00, 45.00, 60.00, 70.00],
            'Siciliana'               => [40.00, 45.00, 60.00, 70.00],
            'Strogonoff de Carne'     => [40.00, 45.00, 60.00, 70.00],
            'Strogonoff de Frango'    => $base,
            'Sugestão Alex'           => [45.00, 50.00, 65.00, 75.00],
            'Tomate Seco'             => [40.00, 45.00, 60.00, 70.00],
            'Três Queijos'            => $base,
            'Vegetariana'             => [40.00, 45.00, 60.00, 70.00],
        ];
    }

    private function saboresDoces(): array
    {
        return [
            'Banana'               => [35.00, 40.00, 55.00, 65.00],
            'Banana II'            => [35.00, 40.00, 55.00, 65.00],
            'Banana com Chocolate' => [40.00, 45.00, 60.00, 75.00],
            'Brigadeiro'           => [40.00, 45.00, 60.00, 75.00],
            'Choco Baby'           => [40.00, 45.00, 60.00, 75.00],
            'Romeu e Julieta'      => [35.00, 40.00, 55.00, 65.00],
        ];
    }

    // ═══════════════════════════════════════════════
    // CRIAÇÃO DOS CONCEITOS (1, 2, 4 sabores)
    // ═══════════════════════════════════════════════
    private function createPizzaConcepts(Category $cat): void
    {
        $sabores = $this->saboresSalgados();
        $nomes   = array_keys($sabores);

        $this->createOneFlavorPizza($cat, 'Pizza 1 Sabor', $nomes, $sabores);
        $this->createTwoFlavorPizza($cat, 'Pizza 2 Sabores', $nomes, $sabores);
        $this->createFourFlavorPizza($cat, 'Pizza 4 Sabores', $nomes, $sabores);
    }

    private function createPizzaConceptsDoces(Category $cat): void
    {
        $sabores = $this->saboresDoces();
        $nomes   = array_keys($sabores);

        $this->createOneFlavorPizza($cat, 'Pizza Doce 1 Sabor', $nomes, $sabores);
        $this->createTwoFlavorPizza($cat, 'Pizza Doce 2 Sabores', $nomes, $sabores);
    }

    // ═══════════════════════════════════════════════
    // PIZZA 1 SABOR
    // ═══════════════════════════════════════════════
    private function createOneFlavorPizza(Category $cat, string $name, array $flavorNames, array $flavorPrices): void
    {
        $product = $this->createProduct($cat, $name, 'Escolha 1 sabor e o tamanho. Massa artesanal, ingredientes frescos.');

        $tamanhos = ['Pequena (4 fatias)', 'Média (6 fatias)', 'Grande (8 fatias)', 'Família (10 fatias)'];
        $sizeIdx  = ['Pequena (4 fatias)' => 0, 'Média (6 fatias)' => 1, 'Grande (8 fatias)' => 2, 'Família (10 fatias)' => 3];

        foreach ($flavorNames as $sabor) {
            foreach ($tamanhos as $tam) {
                $idx   = $sizeIdx[$tam];
                $price = $flavorPrices[$sabor][$idx] ?? 55.00;

                ProductVariation::create([
                    'product_id' => $product->id,
                    'attributes' => [
                        (string) $this->attrSabor1Id   => $sabor,
                        (string) $this->attrTamanhoId  => $tam,
                    ],
                    'price'     => $price,
                    'is_active' => true,
                ]);
            }
        }

        $this->syncImages($product);
    }

    // ═══════════════════════════════════════════════
    // PIZZA 2 SABORES
    // ═══════════════════════════════════════════════
    private function createTwoFlavorPizza(Category $cat, string $name, array $flavorNames, array $flavorPrices): void
    {
        $product = $this->createProduct($cat, $name, 'Escolha 2 sabores e o tamanho. Prevalecerá o de maior valor.');

        $tamanhos = ['Pequena (4 fatias)', 'Média (6 fatias)', 'Grande (8 fatias)', 'Família (10 fatias)'];
        $sizeIdx  = ['Pequena (4 fatias)' => 0, 'Média (6 fatias)' => 1, 'Grande (8 fatias)' => 2, 'Família (10 fatias)' => 3];

        $variations = [];

        foreach ($flavorNames as $s1) {
            foreach ($flavorNames as $s2) {
                if ($s1 === $s2) continue; // evita 2 sabores iguais
                foreach ($tamanhos as $tam) {
                    $idx     = $sizeIdx[$tam];
                    $price1  = $flavorPrices[$s1][$idx] ?? 55.00;
                    $price2  = $flavorPrices[$s2][$idx] ?? 55.00;
                    $price   = max($price1, $price2);

                    $variations[] = [
                        'product_id' => $product->id,
                        'attributes' => json_encode([
                            (string) $this->attrSabor1Id  => $s1,
                            (string) $this->attrSabor2Id  => $s2,
                            (string) $this->attrTamanhoId => $tam,
                        ]),
                        'price'     => $price,
                        'is_active' => true,
                    ];
                }
            }
        }

        // chunk insert para performance
        foreach (array_chunk($variations, 500) as $chunk) {
            ProductVariation::insert($chunk);
        }

        $this->syncImages($product);
    }

    // ═══════════════════════════════════════════════
    // PIZZA 4 SABORES (whatsapp_contact — combos complexos)
    // ═══════════════════════════════════════════════
    private function createFourFlavorPizza(Category $cat, string $name, array $flavorNames, array $flavorPrices): void
    {
        // 4 sabores é combo complexo via WhatsApp
        $saboresList = implode(', ', array_slice($flavorNames, 0, 20));
        $basePrice   = 70.00;

        Product::updateOrCreate(
            ['slug' => \Illuminate\Support\Str::slug($name), 'tenant_id' => $this->tenant->id],
            [
                'tenant_id'       => $this->tenant->id,
                'category_id'     => $cat->id,
                'name'            => $name,
                'short_description' => 'Monte sua pizza com 4 sabores. Preço sob consulta.',
                'description'     => "Escolha 4 sabores entre: {$saboresList}.\n\nPrevalecerá o de maior valor. Tamanhos: P (4 fatias), M (6 fatias), G (8 fatias), F (10 fatias).",
                'price'           => $basePrice,
                'action_type'     => 'whatsapp_contact',
                'whatsapp_message'=> 'Olá! Gostaria de montar uma Pizza 4 Sabores. Pode me ajudar com os sabores e o tamanho?',
                'is_active'       => true,
                'is_hot'          => false,
            ]
        );
    }

    // ═══════════════════════════════════════════════
    // BEBIDAS
    // ═══════════════════════════════════════════════
    private function createBebidas(Category $cat): void
    {
        $bebidas = [
            ['name' => 'Coca-Cola Lata',          'short_description' => 'Coca-Cola 350ml',          'price' => 6.00],
            ['name' => 'Coca-Cola 1 Litro',       'short_description' => 'Coca-Cola 1 litro',        'price' => 10.00],
            ['name' => 'Coca-Cola 2 Litros',      'short_description' => 'Coca-Cola 2 litros',       'price' => 15.00],
            ['name' => 'Guaraná Antarctica Lata',  'short_description' => 'Guaraná Antarctica 350ml', 'price' => 6.00],
            ['name' => 'Guaraná Antarctica 1L',   'short_description' => 'Guaraná Antarctica 1 litro','price' => 8.00],
            ['name' => 'Guaraná Antarctica 2L',   'short_description' => 'Guaraná Antarctica 2 litros','price' => 12.00],
            ['name' => 'Água com Gás',            'short_description' => 'Água com gás 500ml',       'price' => 4.00],
            ['name' => 'Água sem Gás',            'short_description' => 'Água mineral sem gás 500ml','price' => 3.00],
            ['name' => 'H2O',                     'short_description' => 'H2O limoneto 500ml',       'price' => 6.00],
            ['name' => 'Cerveja Lata',            'short_description' => 'Cerveja lata 350ml',       'price' => 6.00],
            ['name' => 'Cerveja Long Neck',       'short_description' => 'Cerveja long neck 330ml',  'price' => 8.00],
            ['name' => 'Heineken Long Neck',      'short_description' => 'Heineken long neck 330ml', 'price' => 10.00],
            ['name' => 'Suco Copo',               'short_description' => 'Suco natural copo 300ml',  'price' => 6.00],
            ['name' => 'Suco Jarra',              'short_description' => 'Suco natural jarra 800ml', 'price' => 12.00],
        ];

        foreach ($bebidas as $data) {
            $product = Product::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($data['name']), 'tenant_id' => $this->tenant->id],
                array_merge($data, [
                    'tenant_id'        => $this->tenant->id,
                    'category_id'      => $cat->id,
                    'description'      => $data['short_description'] . '.',
                    'action_type'      => 'add_to_cart',
                    'whatsapp_message' => null,
                    'is_active'        => true,
                    'is_hot'           => false,
                ])
            );
            $this->syncImages($product);
        }
    }

    // ═══════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════
    private function createProduct(Category $cat, string $name, string $desc): Product
    {
        return Product::updateOrCreate(
            ['slug' => \Illuminate\Support\Str::slug($name), 'tenant_id' => $this->tenant->id],
            [
                'tenant_id'        => $this->tenant->id,
                'category_id'      => $cat->id,
                'name'             => $name,
                'short_description' => $desc,
                'description'      => $desc,
                'price'            => 55.00,
                'action_type'      => 'add_to_cart',
                'whatsapp_message' => null,
                'is_active'        => true,
                'is_hot'           => false,
            ]
        );
    }

    private function syncImages(Product $product): void
    {
        $product->images()->delete();

        $urls = [
            'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=800&h=600&fit=crop',
        ];

        $product->images()->create(['url' => $urls[0], 'is_external' => true, 'is_main' => true]);

        foreach ($urls as $url) {
            $product->images()->create(['url' => $url, 'is_external' => true, 'is_main' => false]);
        }
    }

    private function createBanners(): void
    {
        $banners = [
            [
                'title'       => 'Pizza Meio a Meio',
                'description' => 'Escolha 2 sabores na mesma pizza! Prevalecerá o de maior valor.',
                'image_url'   => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=1200&h=600&fit=crop',
                'link_url'    => null,
                'order'       => 0,
                'is_active'   => true,
            ],
            [
                'title'       => 'Primo Giuseppe',
                'description' => 'Tradição italiana em cada fatia. Peça já!',
                'image_url'   => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=1200&h=600&fit=crop',
                'link_url'    => null,
                'order'       => 1,
                'is_active'   => true,
            ],
            [
                'title'       => 'Pizzas de 1, 2 ou 4 Sabores',
                'description' => 'Você escolhe quantos sabores e quais! Monte do seu jeito.',
                'image_url'   => 'https://images.unsplash.com/photo-1604382354936-07c5d9983bd3?w=1200&h=600&fit=crop',
                'link_url'    => null,
                'order'       => 2,
                'is_active'   => true,
            ],
        ];

        foreach ($banners as $bannerData) {
            RotatingBanner::updateOrCreate(
                ['tenant_id' => $this->tenant->id, 'image_url' => $bannerData['image_url']],
                array_merge($bannerData, [
                    'tenant_id'   => $this->tenant->id,
                    'image_path'  => null,
                    'is_external' => true,
                ])
            );
        }
    }
}
