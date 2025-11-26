<?php

namespace App\Services;

use App\Models\ProductAttribute;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class ProductAttributeService
{
    /**
     * Cria atributos padrão baseado no ramo de atividade
     */
    public function createDefaultAttributesForSector(Tenant $tenant, string $businessSector): void
    {
        $attributesConfig = $this->getAttributesBySector($businessSector);

        foreach ($attributesConfig as $attrConfig) {
            // Verifica se o atributo já existe
            $attribute = ProductAttribute::where('tenant_id', $tenant->id)
                ->where('slug', $attrConfig['slug'])
                ->first();

            if (!$attribute) {
                $attribute = ProductAttribute::create([
                    'tenant_id' => $tenant->id,
                    'name' => $attrConfig['name'],
                    'slug' => $attrConfig['slug'],
                    'order' => $attrConfig['order'] ?? 0,
                    'is_active' => true,
                ]);
            }

            // Valores são livres agora, não precisam ser pré-cadastrados
        }
    }

    /**
     * Retorna configuração de atributos por ramo de atividade
     */
    private function getAttributesBySector(string $businessSector): array
    {
        $configs = [
            'fashion' => [
                [
                    'name' => 'Tamanho',
                    'slug' => 'tamanho',
                    'order' => 0,
                    'values' => ['PP', 'P', 'M', 'G', 'GG', 'XG'],
                ],
                [
                    'name' => 'Cor',
                    'slug' => 'cor',
                    'order' => 1,
                    'values' => ['Preto', 'Branco', 'Azul', 'Rosa', 'Vermelho', 'Verde'],
                ],
            ],
            'electronics' => [
                [
                    'name' => 'Capacidade',
                    'slug' => 'capacidade',
                    'order' => 0,
                    'values' => ['64GB', '128GB', '256GB', '512GB', '1TB'],
                ],
                [
                    'name' => 'Cor',
                    'slug' => 'cor',
                    'order' => 1,
                    'values' => ['Preto', 'Branco', 'Prata', 'Dourado', 'Azul'],
                ],
            ],
            'jewelry' => [
                [
                    'name' => 'Tamanho',
                    'slug' => 'tamanho',
                    'order' => 0,
                    'values' => ['14', '15', '16', '17', '18'],
                ],
                [
                    'name' => 'Material',
                    'slug' => 'material',
                    'order' => 1,
                    'values' => ['Ouro 18k', 'Ouro 14k', 'Prata 925', 'Aço Inox'],
                ],
            ],
            'real_estate' => [
                [
                    'name' => 'Tipo de Operação',
                    'slug' => 'tipo-operacao',
                    'order' => 0,
                    'values' => ['Venda', 'Aluguel'],
                ],
                [
                    'name' => 'Área',
                    'slug' => 'area',
                    'order' => 1,
                    'values' => ['Até 50m²', '50-100m²', '100-200m²', '200-300m²', 'Acima de 300m²'],
                ],
            ],
            'food' => [
                [
                    'name' => 'Tamanho',
                    'slug' => 'tamanho',
                    'order' => 0,
                    'values' => ['Pequeno (15cm)', 'Médio (20cm)', 'Grande (25cm)', 'Extra Grande (30cm)'],
                ],
                [
                    'name' => 'Sabor',
                    'slug' => 'sabor',
                    'order' => 1,
                    'values' => ['Chocolate', 'Morango', 'Baunilha', 'Limão', 'Cenoura'],
                ],
            ],
            'custom_orders' => [
                [
                    'name' => 'Tamanho',
                    'slug' => 'tamanho',
                    'order' => 0,
                    'values' => ['Pequeno', 'Médio', 'Grande'],
                ],
                [
                    'name' => 'Cor',
                    'slug' => 'cor',
                    'order' => 1,
                    'values' => ['Branco', 'Preto', 'Personalizado'],
                ],
            ],
            'affiliates' => [
                [
                    'name' => 'Categoria',
                    'slug' => 'categoria',
                    'order' => 0,
                    'values' => ['Casa', 'Beleza', 'Fitness', 'Eletrônicos'],
                ],
            ],
            'other' => [
                [
                    'name' => 'Tamanho',
                    'slug' => 'tamanho',
                    'order' => 0,
                    'values' => ['Pequeno', 'Médio', 'Grande'],
                ],
            ],
        ];

        return $configs[$businessSector] ?? [];
    }

    /**
     * Verifica se um atributo está em uso (tem variações associadas)
     */
    public function isAttributeInUse(ProductAttribute $attribute): bool
    {
        return \App\Models\ProductVariation::whereIn('product_id', function ($query) use ($attribute) {
            $query->select('id')
                ->from('products')
                ->where('tenant_id', $attribute->tenant_id);
        })
        ->whereNotNull(DB::raw("JSON_EXTRACT(attributes, '$.{$attribute->slug}')"))
        ->exists();
    }

}

