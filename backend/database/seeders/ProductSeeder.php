<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'modachic')->first();

        if (!$tenant) return;

        $catVestidos = Category::where('slug', 'vestidos')->where('tenant_id', $tenant->id)->first();
        $catBlusas = Category::where('slug', 'blusas')->where('tenant_id', $tenant->id)->first();
        $catCalcas = Category::where('slug', 'calcas')->where('tenant_id', $tenant->id)->first();
        $catConjuntos = Category::where('slug', 'conjuntos')->where('tenant_id', $tenant->id)->first();
        $catMacaquinhos = Category::where('slug', 'macaquinhos')->where('tenant_id', $tenant->id)->first();

        // Vestidos
        if ($catVestidos) {
            $p1 = Product::updateOrCreate(
                ['slug' => 'vestido-longo-floral', 'tenant_id' => $tenant->id],
                [
                    'category_id' => $catVestidos->id,
                    'name' => 'Vestido Longo Floral',
                    'short_description' => 'Vestido perfeito para o verão.',
                    'description' => 'Vestido longo com estampa floral, tecido leve e confortável. Ideal para dias quentes e passeios ao ar livre.',
                    'price' => 129.90,
                    'is_active' => true,
                ]
            );
            $this->syncImages($p1,
                'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?q=80&w=600&auto=format&fit=crop',
                [
                    'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?q=80&w=600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1496747611176-843222e1e57c?q=80&w=600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1583336663277-620dc1996580?q=80&w=600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1566174053879-31528523f8ae?q=80&w=600&auto=format&fit=crop'
                ]
            );

            $p2 = Product::updateOrCreate(
                ['slug' => 'vestido-midi-preto', 'tenant_id' => $tenant->id],
                [
                    'category_id' => $catVestidos->id,
                    'name' => 'Vestido Midi Preto',
                    'short_description' => 'Elegância para a noite.',
                    'description' => 'Vestido midi preto básico, tecido encorpado. Perfeito para jantares e eventos sociais.',
                    'price' => 159.90,
                    'is_active' => true,
                ]
            );
            $this->syncImages($p2,
                'https://images.unsplash.com/photo-1539008835657-9e8e9680c956?q=80&w=600&auto=format&fit=crop',
                [
                    'https://images.unsplash.com/photo-1502716119720-b23a93e5fe1b?q=80&w=600&auto=format&fit=crop'
                ]
            );

            $p3 = Product::updateOrCreate(
                ['slug' => 'vestido-festa-paete', 'tenant_id' => $tenant->id],
                [
                    'category_id' => $catVestidos->id,
                    'name' => 'Vestido de Festa Paetê',
                    'short_description' => 'Brilhe muito nas festas!',
                    'description' => 'Vestido curto todo em paetê, alças finas e decote v. Disponível em diversas cores.',
                    'price' => 299.90,
                    'is_active' => true,
                ]
            );
            $this->syncImages($p3,
                'https://images.unsplash.com/photo-1566174053879-31528523f8ae?q=80&w=600&auto=format&fit=crop',
                [
                    'https://images.unsplash.com/photo-1595777457583-95e059d581b8?q=80&w=600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?q=80&w=600&auto=format&fit=crop'
                ]
            );
        }

        // Blusas
        if ($catBlusas) {
            $p4 = Product::updateOrCreate(
                ['slug' => 'blusa-basica-branca', 'tenant_id' => $tenant->id],
                [
                    'category_id' => $catBlusas->id,
                    'name' => 'Blusa Básica Branca',
                    'short_description' => 'Peça essencial no guarda-roupa.',
                    'description' => 'Blusa branca de algodão, corte moderno e versátil.',
                    'price' => 49.90,
                    'is_active' => true,
                ]
            );
            $this->syncImages($p4,
                'https://images.unsplash.com/photo-1585487000160-6ebcfceb0d03?q=80&w=600&auto=format&fit=crop',
                [
                    'https://images.unsplash.com/photo-1576566588028-4147f3842f27?q=80&w=600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=600&auto=format&fit=crop'
                ]
            );

            $p5 = Product::updateOrCreate(
                ['slug' => 'cropped-estampado', 'tenant_id' => $tenant->id],
                [
                    'category_id' => $catBlusas->id,
                    'name' => 'Cropped Estampado',
                    'short_description' => 'Estilo e frescor.',
                    'description' => 'Cropped com estampa tropical, tecido viscose.',
                    'price' => 59.90,
                    'is_active' => true,
                ]
            );
            $this->syncImages($p5,
                'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?q=80&w=600&auto=format&fit=crop',
                [
                    'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?q=80&w=600&auto=format&fit=crop'
                ]
            );
        }

        // Calças
        if ($catCalcas) {
            $p6 = Product::updateOrCreate(
                ['slug' => 'calca-jeans-skinny', 'tenant_id' => $tenant->id],
                [
                    'category_id' => $catCalcas->id,
                    'name' => 'Calça Jeans Skinny',
                    'short_description' => 'Jeans confortável com elastano.',
                    'description' => 'Calça jeans modelagem skinny, lavagem escura. Cintura alta.',
                    'price' => 89.90,
                    'is_active' => true,
                ]
            );
            $this->syncImages($p6,
                'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=600&auto=format&fit=crop',
                [
                    'https://images.unsplash.com/photo-1475178626620-a4d074967452?q=80&w=600&auto=format&fit=crop'
                ]
            );

            $p7 = Product::updateOrCreate(
                ['slug' => 'calca-pantalona-bege', 'tenant_id' => $tenant->id],
                [
                    'category_id' => $catCalcas->id,
                    'name' => 'Calça Pantalona Bege',
                    'short_description' => 'Elegância e conforto.',
                    'description' => 'Calça pantalona em linho misto, cor bege. Ótima para look office.',
                    'price' => 139.90,
                    'is_active' => true,
                ]
            );
            $this->syncImages($p7,
                'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?q=80&w=600&auto=format&fit=crop',
                [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1551488852-0801758cb63f?q=80&w=600&auto=format&fit=crop'
                ]
            );
        }

        // Conjuntos
        if ($catConjuntos) {
            $p8 = Product::updateOrCreate(
                ['slug' => 'conjunto-alfaiataria-rosa', 'tenant_id' => $tenant->id],
                [
                    'category_id' => $catConjuntos->id,
                    'name' => 'Conjunto Alfaiataria Rosa',
                    'short_description' => 'Moderno e sofisticado.',
                    'description' => 'Conjunto de blazer cropped e short saia em alfaiataria rosa.',
                    'price' => 219.90,
                    'is_active' => true,
                ]
            );
            $this->syncImages($p8,
                'https://images.unsplash.com/photo-1591369822096-35c938988a51?q=80&w=600&auto=format&fit=crop',
                [
                    'https://images.unsplash.com/photo-1532453288672-3a27e9be9efd?q=80&w=600&auto=format&fit=crop'
                ]
            );
        }

        // Macaquinhos
        if ($catMacaquinhos) {
            $p9 = Product::updateOrCreate(
                ['slug' => 'macaquinho-estampado', 'tenant_id' => $tenant->id],
                [
                    'category_id' => $catMacaquinhos->id,
                    'name' => 'Macaquinho Estampado',
                    'short_description' => 'Leveza para o dia a dia.',
                    'description' => 'Macaquinho soltinho com estampa floral.',
                    'price' => 89.90,
                    'is_active' => true,
                ]
            );
            $this->syncImages($p9,
                'https://images.unsplash.com/photo-1495385794356-15371f348c31?q=80&w=600&auto=format&fit=crop',
                [
                    'https://images.unsplash.com/photo-1515934751635-c81c6bc9a2d8?q=80&w=600&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1574634534894-89d7576c8259?q=80&w=600&auto=format&fit=crop'
                ]
            );
        }

        // Adicionar mais 50 produtos para testar paginação
        $this->generateBulkProducts($tenant, [
            $catVestidos,
            $catBlusas,
            $catCalcas,
            $catConjuntos,
            $catMacaquinhos,
        ]);
    }

    private function generateBulkProducts(Tenant $tenant, array $categories)
    {
        $categories = array_filter($categories); // Remove nulls
        
        if (empty($categories)) {
            return;
        }

        $productNames = [
            'Vestido', 'Blusa', 'Calça', 'Conjunto', 'Macaquinho',
            'Top', 'Saia', 'Shorts', 'Jaqueta', 'Cardigan',
            'Blazer', 'Casaco', 'Regata', 'Camiseta', 'Body',
        ];

        $adjectives = [
            'Elegante', 'Moderno', 'Clássico', 'Estiloso', 'Confortável',
            'Sofisticado', 'Despojado', 'Feminino', 'Jovem', 'Versátil',
            'Exclusivo', 'Premium', 'Básico', 'Colorido', 'Neutro',
        ];

        $prices = [29.90, 39.90, 49.90, 59.90, 69.90, 79.90, 89.90, 99.90, 109.90, 119.90, 129.90, 139.90, 149.90, 159.90, 179.90, 199.90, 219.90, 249.90, 279.90, 299.90];

        $imageUrls = [
            'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1496747611176-843222e1e57c?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1566174053879-31528523f8ae?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1539008835657-9e8e9680c956?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1585487000160-6ebcfceb0d03?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1591369822096-35c938988a51?q=80&w=600&auto=format&fit=crop',
        ];

        for ($i = 1; $i <= 50; $i++) {
            $category = $categories[array_rand($categories)];
            $productName = $productNames[array_rand($productNames)];
            $adjective = $adjectives[array_rand($adjectives)];
            $price = $prices[array_rand($prices)];
            $mainImage = $imageUrls[array_rand($imageUrls)];
            
            $name = "$adjective $productName";
            $slug = 'produto-' . $i . '-' . strtolower(str_replace(' ', '-', $name));
            
            $product = Product::updateOrCreate(
                ['slug' => $slug, 'tenant_id' => $tenant->id],
                [
                    'category_id' => $category->id,
                    'name' => $name,
                    'short_description' => "Produto $i - $adjective e confortável.",
                    'description' => "Descrição detalhada do $name. Produto de alta qualidade, perfeito para o seu guarda-roupa.",
                    'price' => $price,
                    'promotional_price' => (rand(0, 100) > 70) ? $price * 0.8 : null, // 30% chance de ter promoção
                    'is_active' => true,
                ]
            );

            $galleryImages = [];
            for ($j = 0; $j < rand(1, 3); $j++) {
                $galleryImages[] = $imageUrls[array_rand($imageUrls)];
            }

            $this->syncImages($product, $mainImage, $galleryImages);
        }
    }

    private function syncImages(Product $product, string $mainUrl, array $galleryUrls)
    {
        // Clear existing
        $product->images()->delete();

        // Main
        $product->images()->create([
            'url' => $mainUrl,
            'is_external' => true,
            'is_main' => true,
        ]);

        // Gallery
        foreach ($galleryUrls as $url) {
            $product->images()->create([
                'url' => $url,
                'is_external' => true,
                'is_main' => false,
            ]);
        }
    }
}
