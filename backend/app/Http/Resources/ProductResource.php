<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class ProductResource extends JsonResource
{
    /**
     * Cache de atributos por tenant para evitar N+1 queries
     */
    private static $attributesCache = [];

    /**
     * Indica se os dados devem ser envolvidos em { data: {...} }
     * Por padrão, Resources envolvem em { data: {...} }, mas podemos mudar isso
     */
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Carregar relações necessárias se ainda não estiverem carregadas
        if (!$this->relationLoaded('images')) {
            $this->load('images');
        }
        if (!$this->relationLoaded('variations')) {
            $this->load('variations');
        }
        if (!$this->relationLoaded('category')) {
            $this->load('category');
        }

        // Buscar atributos do tenant (com cache para evitar N+1)
        if (!isset(self::$attributesCache[$this->tenant_id])) {
            self::$attributesCache[$this->tenant_id] = \App\Models\ProductAttribute::where('tenant_id', $this->tenant_id)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');
        }
        $attributes = self::$attributesCache[$this->tenant_id];

        // Verificar se o produto tem variações
        $hasVariations = $this->variations && $this->variations->count() > 0;

        // Preparar variações com nomes dos atributos
        $variationsData = [];
        $attributesMap = [];

        if ($hasVariations) {
            foreach ($this->variations as $variation) {
                $variationAttributes = $variation->attributes ?? [];
                $normalizedAttributes = [];

                // Verificar se attributes é array indexado (formato antigo) ou objeto com IDs (formato novo)
                // Array indexado: [0 => "P", 1 => "Preto"] ou ["P", "Preto"]
                // Objeto com IDs: ["17" => "P", "18" => "Preto"]
                $isIndexedArray = false;
                if (is_array($variationAttributes) && !empty($variationAttributes)) {
                    $keys = array_keys($variationAttributes);
                    // Verifica se as chaves são numéricas sequenciais começando do 0
                    $isIndexedArray = $keys === range(0, count($variationAttributes) - 1);
                }

                if ($isIndexedArray) {
                    // Formato antigo: array indexado ["P", "Preto", ...]
                    // Vamos buscar os atributos usados neste produto para mapear corretamente
                    // Primeiro, vamos coletar todos os atributos únicos usados nas variações
                    $usedAttributeIds = [];
                    foreach ($this->variations as $v) {
                        if (is_array($v->attributes) && !empty($v->attributes)) {
                            $vKeys = array_keys($v->attributes);
                            if ($vKeys !== range(0, count($v->attributes) - 1)) {
                                // Se alguma variação tem formato com IDs, usar esses IDs
                                foreach ($vKeys as $key) {
                                    if (is_numeric($key) && $attributes->has((int)$key)) {
                                        $usedAttributeIds[(int)$key] = true;
                                    }
                                }
                            }
                        }
                    }

                    // Se não encontrou IDs nas outras variações, tentar mapear pela ordem dos atributos
                    if (empty($usedAttributeIds)) {
                        // Ordenar atributos por ID para manter ordem consistente
                        $sortedAttributes = $attributes->sortBy('id')->values();
                        foreach ($variationAttributes as $index => $value) {
                            if ($sortedAttributes->has($index)) {
                                $attrId = $sortedAttributes[$index]->id;
                                $normalizedAttributes[(string)$attrId] = $value;
                            }
                        }
                    } else {
                        // Usar os IDs encontrados, mapeando pela ordem
                        $sortedIds = array_keys($usedAttributeIds);
                        sort($sortedIds);
                        foreach ($variationAttributes as $index => $value) {
                            if (isset($sortedIds[$index])) {
                                $attrId = $sortedIds[$index];
                                $normalizedAttributes[(string)$attrId] = $value;
                            }
                        }
                    }
                } else {
                    // Formato novo: objeto com IDs como chaves {"17": "P", "18": "Preto"}
                    // Garantir que as chaves sejam strings
                    foreach ($variationAttributes as $attrId => $value) {
                        $normalizedAttributes[(string)$attrId] = $value;
                    }
                }

                $variationData = [
                    'id' => $variation->id,
                    'attributes' => $normalizedAttributes,
                    'attribute_names' => [],
                    'stock' => $variation->stock,
                    'price' => $variation->price,
                    'sku' => $variation->sku,
                    'is_active' => $variation->is_active,
                ];

                // Adicionar nomes dos atributos e criar mapeamento
                if (!empty($normalizedAttributes) && is_array($normalizedAttributes)) {
                    foreach ($normalizedAttributes as $attrId => $value) {
                        $attrIdInt = (int)$attrId;
                        if ($attributes->has($attrIdInt)) {
                            $variationData['attribute_names'][$attrId] = $attributes[$attrIdInt]->name;

                            // Criar mapeamento de atributos
                            if (!isset($attributesMap[$attrIdInt])) {
                                $attributesMap[$attrIdInt] = [
                                    'id' => $attrIdInt,
                                    'name' => $attributes[$attrIdInt]->name,
                                    'slug' => $attributes[$attrIdInt]->slug,
                                ];
                            }
                        }
                    }
                }

                $variationsData[] = $variationData;
            }
        }

        // Preparar dados base do produto
        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => $this->price !== null && $this->price !== '' ? number_format((float)$this->price, 2, '.', '') : '0.00',
            'promotional_price' => $this->promotional_price && $this->promotional_price > 0 ? number_format((float)$this->promotional_price, 2, '.', '') : null,
            'main_image_url' => $this->main_image_url,
            'images' => $this->images ? $this->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->url,
                    'is_main' => $image->is_main,
                ];
            })->toArray() : [],
            'is_hot' => $this->is_hot ?? false,
            'is_active' => $this->is_active,
            'action_type' => $this->action_type,
            'affiliate_link' => $this->affiliate_link,
            'whatsapp_message' => $this->whatsapp_message,
            'button_label' => $this->button_label,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),
            'variations' => $variationsData,
            'attributes_map' => array_values($attributesMap),
        ];

        // Se tiver variações, não incluir sizes e colors
        // Se não tiver variações, incluir sizes e colors (compatibilidade com produtos antigos)
        if ($hasVariations) {
            // Não incluir sizes e colors quando houver variações
        } else {
            // Incluir sizes e colors para compatibilidade com produtos antigos
            $data['sizes'] = $this->sizes ?? [];
            $data['colors'] = $this->colors ?? [];
        }

        return $data;
    }
}
