<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductVariation;
use Illuminate\Console\Command;

class MigrateProductSizesAndColorsToAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:migrate-sizes-colors-to-attributes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra os campos sizes e colors dos produtos para o sistema de atributos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando migração de sizes e colors para atributos...');

        $tenants = \App\Models\Tenant::all();
        $totalMigrated = 0;
        $totalErrors = 0;

        foreach ($tenants as $tenant) {
            $this->info("Processando tenant: {$tenant->name} (ID: {$tenant->id})");

            // Buscar ou criar atributos "Tamanho" e "Cor" para este tenant
            $attrTamanho = $this->getOrCreateAttribute($tenant, 'Tamanho', 'tamanho', 0);
            $attrCor = $this->getOrCreateAttribute($tenant, 'Cor', 'cor', 1);

            // Buscar produtos deste tenant que têm sizes ou colors
            $products = Product::where('tenant_id', $tenant->id)
                ->where(function ($query) {
                    $query->whereNotNull('sizes')
                          ->orWhereNotNull('colors');
                })
                ->get();

            foreach ($products as $product) {
                try {
                    $this->line("  - Processando produto: {$product->name} (ID: {$product->id})");

                    // Verificar se o produto já tem variações (pular se já tiver)
                    if ($product->variations()->count() > 0) {
                        $this->warn("    Produto já possui variações, pulando...");
                        continue;
                    }

                    $sizes = $product->sizes ?? [];
                    $colors = $product->colors ?? [];

                    // Se não tiver sizes nem colors, pular
                    if (empty($sizes) && empty($colors)) {
                        continue;
                    }

                    // Preparar combinações
                    $variationsToCreate = $this->generateVariations($sizes, $colors, $attrTamanho->id, $attrCor->id);

                    // Criar variações
                    foreach ($variationsToCreate as $variationAttrs) {
                        ProductVariation::create([
                            'product_id' => $product->id,
                            'attributes' => $variationAttrs,
                            'is_active' => true,
                        ]);
                    }

                    $totalMigrated++;
                    $this->info("    ✓ Migrado com sucesso (" . count($variationsToCreate) . " variação(ões))");
                } catch (\Exception $e) {
                    $totalErrors++;
                    $this->error("    ✗ Erro ao migrar produto {$product->id}: " . $e->getMessage());
                }
            }
        }

        $this->info("\nMigração concluída!");
        $this->info("Total de produtos migrados: {$totalMigrated}");
        if ($totalErrors > 0) {
            $this->warn("Total de erros: {$totalErrors}");
        }
    }

    /**
     * Busca ou cria um atributo para o tenant
     */
    private function getOrCreateAttribute($tenant, string $name, string $slug, int $order): ProductAttribute
    {
        $attribute = ProductAttribute::where('tenant_id', $tenant->id)
            ->where('slug', $slug)
            ->first();

        if (!$attribute) {
            $attribute = ProductAttribute::create([
                'tenant_id' => $tenant->id,
                'name' => $name,
                'slug' => $slug,
                'order' => $order,
                'is_active' => true,
            ]);
            $this->line("    Criado atributo: {$name}");
        }

        return $attribute;
    }

    /**
     * Gera variações baseadas em sizes e colors
     */
    private function generateVariations(array $sizes, array $colors, int $attrTamanhoId, int $attrCorId): array
    {
        $variations = [];

        // Se tiver apenas sizes (sem colors)
        if (!empty($sizes) && empty($colors)) {
            foreach ($sizes as $size) {
                if (empty($size)) continue;
                $variations[] = [
                    (string)$attrTamanhoId => $size,
                ];
            }
        }
        // Se tiver apenas colors (sem sizes)
        elseif (empty($sizes) && !empty($colors)) {
            foreach ($colors as $color) {
                if (empty($color)) continue;
                $variations[] = [
                    (string)$attrCorId => $color,
                ];
            }
        }
        // Se tiver ambos, gerar combinações
        elseif (!empty($sizes) && !empty($colors)) {
            foreach ($sizes as $size) {
                if (empty($size)) continue;
                foreach ($colors as $color) {
                    if (empty($color)) continue;
                    $variations[] = [
                        (string)$attrTamanhoId => $size,
                        (string)$attrCorId => $color,
                    ];
                }
            }
        }

        return $variations;
    }
}
