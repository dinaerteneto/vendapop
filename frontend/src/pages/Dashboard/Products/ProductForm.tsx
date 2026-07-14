import React, { useEffect, useState, useRef } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../../../services/api';
import { SEOHead } from '../../../components/common/SEOHead';
import { toast } from 'react-toastify';
import ImageUploader from '../../../components/ui/ImageUploader';
import CurrencyInput from 'react-currency-input-field';

function formatCurrency(value: string): string {
  const digits = value.replace(/\D/g, '');
  const padded = digits.padStart(3, '0');
  const len = padded.length;
  const integer = padded.slice(0, len - 2).replace(/^0+/, '') || '0';
  const cents = padded.slice(len - 2);
  return `${integer},${cents}`;
}

function parseCurrency(formatted: string): number {
  const cleaned = formatted.replace(/[^\d,]/g, '').replace(',', '.');
  return parseFloat(cleaned) || 0;
}

function floatToCurrency(value: string | number | null | undefined): string {
  if (value === null || value === undefined || value === '') return '';
  const num = parseFloat(String(value).replace(',', '.'));
  if (isNaN(num)) return '';
  return num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
import UpgradeModal from '../../../components/admin/UpgradeModal';

interface Category {
  id: number;
  name: string;
}

interface Attribute {
  id: number;
  name: string;
  slug: string;
  order: number;
  is_active: boolean;
}

interface ProductAttribute {
  attributeId: number | null;
  attributeName: string;
  values: string[]; // Valores como tags (livres, sem valores pré-cadastrados)
}

interface ProductVariationRow {
  id?: number; // ID da variação se já existe
  attributes: { [attributeId: string]: string }; // { "1": "P", "2": "Azul" }
  stock: number | null;
  price: number | null; // Preço específico da variação (opcional, se null usa preço base)
  sku: string | null;
  is_active: boolean;
}

interface ProductImage {
  id?: number;
  url: string;
  is_main: boolean;
  is_external: boolean;
  path?: string | null;
}

const ProductForm: React.FC = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEditMode = !!id;

  const [categories, setCategories] = useState<Category[]>([]);
  const [availableAttributes, setAvailableAttributes] = useState<Attribute[]>([]);
  const [productAttributes, setProductAttributes] = useState<ProductAttribute[]>([]);
  const [variations, setVariations] = useState<ProductVariationRow[]>([]); // Tabela de variações
  const [showAttributeSelect, setShowAttributeSelect] = useState(false);
  const [attributeSearchTerm, setAttributeSearchTerm] = useState('');
  const [loading, setLoading] = useState(false);
  const [isLoadingProduct, setIsLoadingProduct] = useState(false); // Flag para evitar regenerar variações durante carregamento
  const [variationsLoadedFromBackend, setVariationsLoadedFromBackend] = useState(false); // Flag para indicar que variações vieram do backend
  
  // Ref para evitar chamadas duplicadas de categorias
  const categoriesLoadedRef = useRef(false);

  const [imageFile, setImageFile] = useState<File | null>(null);
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);

  const [upgradeInfo, setUpgradeInfo] = useState<{
    planType: string;
    current: number;
    limit: number;
  } | null>(null);


  // All product images (main + gallery)
  const [productImages, setProductImages] = useState<ProductImage[]>([]);
  const [newGalleryUrl, setNewGalleryUrl] = useState('');
  const [draggedIndex, setDraggedIndex] = useState<number | null>(null);

  const [formData, setFormData] = useState({
    name: '',
    price: '',
    promotional_price: '',
    category_id: '',
    description: '',
    main_image_url: '',
    is_active: true,
    is_hot: false,
    stock_management_enabled: false,
    action_type: 'add_to_cart' as 'add_to_cart' | 'affiliate_link' | 'whatsapp_contact',
    affiliate_link: '',
    whatsapp_message: '',
    button_label: '',
  });

  useEffect(() => {
    console.log('ProductForm useEffect - isEditMode:', isEditMode, 'id:', id);
    let isMounted = true;
    
    const fetchData = async () => {
      try {
        // Carregar categorias
        await loadCategories();
        
        // Só carregar atributos disponíveis se NÃO estiver em modo de edição
        // No modo de edição, os atributos vêm no attributes_map do produto
        if (!isEditMode) {
          await loadAvailableAttributes();
        }
        
        // Só carregar produto se ainda estiver montado e for modo de edição
        if (isMounted && isEditMode && id) {
          console.log('Carregando produto com ID:', id);
          await loadProduct(id);
        }
      } catch (error) {
        console.error('Erro ao inicializar formulário', error);
      }
    };
    
    fetchData();
    
    return () => {
      isMounted = false;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id]);

  const loadCategories = async () => {
    // Evitar chamadas duplicadas (comum no React StrictMode)
    if (categoriesLoadedRef.current) {
      console.log('Categorias já carregadas, pulando requisição');
      return;
    }

    try {
      const { data } = await api.get('/admin/categories');
      // Handle paginated response
      if (data.data && Array.isArray(data.data)) {
        setCategories(data.data);
      } else if (Array.isArray(data)) {
        setCategories(data);
      } else {
        setCategories([]);
      }
      categoriesLoadedRef.current = true; // Marcar como carregadas
    } catch (error) {
      console.error('Erro ao carregar categorias', error);
      toast.error('Erro ao carregar categorias.');
    }
  };

  const loadAvailableAttributes = async () => {
    try {
      const response = await api.get('/admin/product-attributes');
      const attributes = response.data || [];
      setAvailableAttributes(attributes);
      return attributes;
    } catch (error) {
      console.error('Erro ao carregar atributos', error);
      // Não mostrar erro, pode não ter atributos ainda
      setAvailableAttributes([]);
      return [];
    }
  };


  const loadProduct = async (productId: string | undefined) => {
    if (!productId) {
      console.error('ID do produto não fornecido');
      return;
    }
    
    setLoading(true);
    setIsLoadingProduct(true);
    try {
      console.log('Carregando produto...', productId);
      const { data } = await api.get(`/admin/products/${productId}`);
      console.log('Produto carregado:', data);
      console.log('Variações:', data.variations);
      console.log('Attributes map do backend:', data.attributes_map);
      
      // Load all images as objects
      const allImages: ProductImage[] = (data.images || []).map((img: any) => ({
        id: img.id,
        url: img.url,
        is_main: img.is_main || false,
        is_external: img.is_external || false,
        path: img.path || null,
      }));

      // Sort: main image first, then others
      const sortedImages = allImages.sort((a, b) => (b.is_main ? 1 : 0) - (a.is_main ? 1 : 0));
      setProductImages(sortedImages);

      const mainImage = sortedImages.find(img => img.is_main);

      setFormData({
        name: data.name || '',
        price: floatToCurrency(data.price),
        promotional_price: floatToCurrency(data.promotional_price),
        category_id: data.category_id?.toString() || '',
        description: data.description || '',
        main_image_url: mainImage ? mainImage.url : '',
        is_active: data.is_active !== undefined ? !!data.is_active : true,
        is_hot: data.is_hot !== undefined ? !!data.is_hot : false,
        stock_management_enabled: data.stock_management_enabled !== undefined ? !!data.stock_management_enabled : false,
        action_type: data.action_type || 'add_to_cart',
        affiliate_link: data.affiliate_link || '',
        whatsapp_message: data.whatsapp_message || '',
        button_label: data.button_label || '',
      });

      // Carregar atributos do produto a partir das variações
      // O attributes_map já vem com todas as informações necessárias do backend
      // NÃO precisamos fazer request adicional para product-attributes
      if (data.variations && Array.isArray(data.variations) && data.variations.length > 0) {
        // Converter attributes_map do backend para o formato Attribute[]
        const attrsFromMap: Attribute[] = (data.attributes_map && Array.isArray(data.attributes_map) && data.attributes_map.length > 0)
          ? data.attributes_map.map((attr: any) => ({
              id: attr.id,
              name: attr.name,
              slug: attr.slug,
              order: 0,
              is_active: true,
            }))
          : [];
        
        if (attrsFromMap.length > 0) {
          // Atualizar availableAttributes com os atributos do produto
          // Isso evita precisar buscar depois quando for adicionar novos atributos
          // E permite que o usuário veja todos os atributos disponíveis no select
          setAvailableAttributes(prevAttrs => {
            // Merge: adicionar novos atributos sem duplicar
            const merged = [...prevAttrs];
            attrsFromMap.forEach(newAttr => {
              if (!merged.find(a => a.id === newAttr.id)) {
                merged.push(newAttr);
              }
            });
            return merged;
          });
          
          // Extrair atributos e valores únicos das variações para popular o formulário
          // Primeiro, coletar todos os atributos únicos e seus valores
          const attributeValuesMap: { [attrId: number]: { attributeId: number; attributeName: string; values: Set<string> } } = {};
          
          data.variations.forEach((variation: any) => {
            let attrsObj: { [key: string]: string } = {};
            
            // Converter para objeto se vier como array
            if (Array.isArray(variation.attributes)) {
              // Formato array: ["P", "Preto", ...]
              if (Array.isArray(variation.attribute_names) && variation.attribute_names.length === variation.attributes.length) {
                variation.attributes.forEach((value: string, index: number) => {
                  const attrName = variation.attribute_names[index];
                  const attrItem = attrsFromMap.find((a: Attribute) => a.name === attrName);
                  if (attrItem) {
                    attrsObj[attrItem.id.toString()] = value;
                  }
                });
              } else if (attrsFromMap.length === variation.attributes.length) {
                // Mapear pela ordem do attributes_map
                variation.attributes.forEach((value: string, index: number) => {
                  if (attrsFromMap[index]) {
                    attrsObj[attrsFromMap[index].id.toString()] = value;
                  }
                });
              }
            } else if (variation.attributes && typeof variation.attributes === 'object') {
              // Formato objeto: {"17": "P", "18": "Preto"}
              attrsObj = variation.attributes;
            }
            
            // Coletar valores únicos de cada atributo
            Object.keys(attrsObj).forEach((attrIdStr) => {
              const attrId = parseInt(attrIdStr, 10);
              const attrItem = attrsFromMap.find((a: Attribute) => a.id === attrId);
              if (attrItem) {
                if (!attributeValuesMap[attrId]) {
                  attributeValuesMap[attrId] = {
                    attributeId: attrId,
                    attributeName: attrItem.name,
                    values: new Set<string>(),
                  };
                }
                if (attrsObj[attrIdStr] && typeof attrsObj[attrIdStr] === 'string') {
                  attributeValuesMap[attrId].values.add(attrsObj[attrIdStr].trim());
                }
              }
            });
          });
          
          // Converter para array de ProductAttribute
          const loadedAttributes: ProductAttribute[] = Object.values(attributeValuesMap).map((attr) => ({
            attributeId: attr.attributeId,
            attributeName: attr.attributeName,
            values: Array.from(attr.values).sort(),
          }));
          
          console.log('=== CARREGAMENTO DE PRODUTO ===');
          console.log('Atributos extraídos:', loadedAttributes);
          console.log('Total de atributos:', loadedAttributes.length);
          
          // Carregar variações completas (com estoque, preço, SKU)
          const loadedVariations: ProductVariationRow[] = data.variations.map((variation: any) => {
            let normalizedAttributes: { [key: string]: string } = {};
            
            // Converter para objeto se vier como array
            if (Array.isArray(variation.attributes)) {
              if (Array.isArray(variation.attribute_names) && variation.attribute_names.length === variation.attributes.length) {
                variation.attributes.forEach((value: string, index: number) => {
                  const attrName = variation.attribute_names[index];
                  const attrItem = attrsFromMap.find((a: Attribute) => a.name === attrName);
                  if (attrItem) {
                    normalizedAttributes[attrItem.id.toString()] = value;
                  }
                });
              } else if (attrsFromMap.length === variation.attributes.length) {
                variation.attributes.forEach((value: string, index: number) => {
                  if (attrsFromMap[index]) {
                    normalizedAttributes[attrsFromMap[index].id.toString()] = value;
                  }
                });
              }
            } else if (variation.attributes && typeof variation.attributes === 'object' && !Array.isArray(variation.attributes)) {
              // Já está no formato correto (objeto com IDs)
              normalizedAttributes = variation.attributes;
            }
            
            return {
              id: variation.id,
              attributes: normalizedAttributes,
              stock: variation.stock !== null && variation.stock !== undefined ? parseInt(variation.stock) : null,
              price: variation.price !== null && variation.price !== undefined && variation.price !== '' ? parseFloat(variation.price) : null,
              sku: variation.sku || null,
              is_active: variation.is_active !== undefined ? variation.is_active : true,
            };
          });
          
          console.log('Variações carregadas:', loadedVariations);
          console.log('Total de variações:', loadedVariations.length);
          
          // Definir variações primeiro (com isLoadingProduct ainda true)
          setVariations(loadedVariations);
          setVariationsLoadedFromBackend(true); // Marcar que as variações vieram do backend
          
          // Depois definir atributos (o useEffect não vai executar pois isLoadingProduct ainda é true)
          setProductAttributes(loadedAttributes);
          console.log('Atributos definidos no estado. isLoadingProduct ainda é true, então useEffect não vai executar.');
        } else {
          console.warn('Produto tem variações mas não tem attributes_map');
          setProductAttributes([]);
        }
      } else {
        console.log('Produto não tem variações');
        setProductAttributes([]);
        setVariations([]);
        setVariationsLoadedFromBackend(false); // Não veio do backend se não tem variações
        
        // Se não tem variações e não temos atributos carregados ainda, 
        // carregar atributos disponíveis apenas para usar no select (adicionar novos atributos)
        if (availableAttributes.length === 0) {
          await loadAvailableAttributes();
        }
      }
      
      if (mainImage) {
        setPreviewUrl(mainImage.url);
      }

    } catch (error: any) {
      console.error('Erro ao carregar produto', error);
      const errorMessage = error.response?.data?.message || 'Erro ao carregar detalhes do produto.';
      toast.error(errorMessage);
      // Não navegar automaticamente, deixar o usuário ver o erro
    } finally {
      setLoading(false);
      // Aguardar um pouco antes de liberar isLoadingProduct para garantir que tudo foi setado
      setTimeout(() => {
        setIsLoadingProduct(false);
      }, 100);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleCheckboxChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, checked } = e.target;
    setFormData(prev => ({ ...prev, [name]: checked }));
  };

  // Funções para gerenciar atributos do produto
  const handleAddAttribute = async () => {
    const name = attributeSearchTerm.trim();
    if (!name) {
      toast.error('Por favor, informe o nome do atributo.');
      return;
    }

    // Verificar se já foi adicionado ao produto
    if (productAttributes.some(attr => 
      attr.attributeName.toLowerCase() === name.toLowerCase()
    )) {
      toast.error('Este atributo já foi adicionado ao produto.');
      setAttributeSearchTerm('');
      setShowAttributeSelect(false);
      return;
    }

    // Verificar se existe nos atributos disponíveis
    const existingAttr = availableAttributes.find(a => 
      a.name.toLowerCase() === name.toLowerCase()
    );

    let attributeId: number | null = null;
    let attributeName = name;

    if (existingAttr) {
      // Usar atributo existente
      attributeId = existingAttr.id;
      attributeName = existingAttr.name;
    } else {
      // Criar novo atributo
      try {
        const response = await api.post('/admin/product-attributes', {
          name: name,
        });
        attributeId = response.data.id;
        attributeName = response.data.name;
        // Recarregar lista de atributos disponíveis
        await loadAvailableAttributes();
        toast.success('Atributo criado com sucesso!');
      } catch (error: any) {
        console.error('Erro ao criar atributo', error);
        toast.error(error.response?.data?.message || 'Erro ao criar atributo.');
        return;
      }
    }

    // Adicionar ao produto
    setProductAttributes([...productAttributes, {
      attributeId: attributeId,
      attributeName: attributeName,
      values: [],
    }]);
    setVariationsLoadedFromBackend(false); // Permitir regenerar quando atributo é adicionado

    setAttributeSearchTerm('');
    setShowAttributeSelect(false);
  };

  const handleSelectExistingAttribute = async (attributeId: number) => {
    const attribute = availableAttributes.find(a => a.id === attributeId);
    if (!attribute) return;

    // Verificar se já foi adicionado ao produto
    if (productAttributes.some(attr => attr.attributeId === attributeId)) {
      toast.error('Este atributo já foi adicionado ao produto.');
      setAttributeSearchTerm('');
      setShowAttributeSelect(false);
      return;
    }

    setProductAttributes([...productAttributes, {
      attributeId: attribute.id,
      attributeName: attribute.name,
      values: [],
    }]);
    setVariationsLoadedFromBackend(false); // Permitir regenerar quando atributo é adicionado

    setAttributeSearchTerm('');
    setShowAttributeSelect(false);
  };

  const handleRemoveAttribute = (index: number) => {
    const updated = productAttributes.filter((_, i) => i !== index);
    setProductAttributes(updated);
    setVariationsLoadedFromBackend(false); // Permitir regenerar quando atributo é removido
  };

  const handleAddAttributeValue = (index: number, e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      const input = e.target as HTMLInputElement;
      const value = input.value.trim();
      if (value) {
        const updated = [...productAttributes];
        if (!updated[index].values.includes(value)) {
          updated[index].values.push(value);
          setProductAttributes(updated);
          setVariationsLoadedFromBackend(false); // Permitir regenerar quando valor é adicionado
        }
        input.value = '';
      }
    }
  };

  const handleRemoveAttributeValue = (index: number, valueIndex: number) => {
    const updated = [...productAttributes];
    updated[index].values.splice(valueIndex, 1);
    setProductAttributes(updated);
    setVariationsLoadedFromBackend(false); // Permitir regenerar quando valor é removido
    // O useEffect vai regenerar automaticamente
  };

  // Função para gerar todas as combinações de atributos e criar/atualizar variações
  const generateVariationsFromAttributes = (attrs: ProductAttribute[]) => {
    // Filtrar atributos que têm valores
    const attrsWithValues = attrs.filter(attr => attr.values.length > 0);
    
    if (attrsWithValues.length === 0) {
      setVariations([]);
      return;
    }

    // Gerar produto cartesiano de todos os valores
    const combinations: { [key: string]: string }[] = [];
    
    // Função recursiva para gerar combinações
    const generateCombinations = (current: { [key: string]: string }, remaining: ProductAttribute[], index: number) => {
      if (index >= remaining.length) {
        combinations.push({ ...current });
        return;
      }
      
      const attr = remaining[index];
      const attrId = attr.attributeId?.toString() || '';
      
      if (attr.values.length === 0) {
        generateCombinations(current, remaining, index + 1);
      } else {
        attr.values.forEach(value => {
          generateCombinations({ ...current, [attrId]: value }, remaining, index + 1);
        });
      }
    };

    generateCombinations({}, attrsWithValues, 0);

    // Para cada combinação, verificar se já existe uma variação com esses atributos
    const newVariations: ProductVariationRow[] = combinations.map(combo => {
      // Tentar encontrar variação existente com mesma combinação de atributos
      const existing = variations.find(v => {
        const vKeys = Object.keys(v.attributes).sort();
        const comboKeys = Object.keys(combo).sort();
        
        if (vKeys.length !== comboKeys.length) return false;
        
        return vKeys.every(key => 
          v.attributes[key] === combo[key]
        );
      });

      if (existing) {
        // Manter dados existentes (estoque, preço, SKU)
        return existing;
      }

      // Criar nova variação
      return {
        attributes: combo,
        stock: null,
        price: null,
        sku: null,
        is_active: true,
      };
    });

    setVariations(newVariations);
  };

  // Atualizar variação individual
  const handleVariationChange = (index: number, field: 'stock' | 'price' | 'sku' | 'is_active', value: any) => {
    const updated = [...variations];
    updated[index] = {
      ...updated[index],
      [field]: value,
    };
    setVariations(updated);
  };

  // Função para formatar a combinação de atributos como texto
  const formatVariationAttributes = (variation: ProductVariationRow): string => {
    const parts: string[] = [];
    
    // Ordenar os atributos pela ordem dos productAttributes para manter consistência
    productAttributes.forEach(attr => {
      const attrId = attr.attributeId?.toString() || '';
      const value = variation.attributes[attrId];
      if (value) {
        parts.push(value);
      }
    });
    
    return parts.join(', ') || '-';
  };

  // Efeito para regenerar combinações quando atributos/valores mudarem
  useEffect(() => {
    // Não regenerar se estivermos carregando um produto (para não perder dados existentes)
    if (isLoadingProduct) {
      console.log('useEffect - Pulando regeneração (carregando produto)');
      return;
    }
    
    // Não regenerar se as variações já vieram do backend e ainda estão lá
    // Isso preserva as variações existentes (com estoque, preço, SKU) ao editar um produto
    if (variationsLoadedFromBackend && variations.length > 0) {
      console.log('useEffect - Pulando regeneração (variações já carregadas do backend)');
      // Se os atributos mudaram manualmente (não foi carregamento), resetar a flag
      // Mas primeiro vamos verificar se os atributos atuais batem com as variações existentes
      return;
    }
    
    console.log('useEffect - Regenerando variações, productAttributes:', productAttributes);
    console.log('useEffect - Variações atuais:', variations);
    
    if (productAttributes.length > 0) {
      const hasValues = productAttributes.some(attr => attr.values.length > 0);
      if (hasValues) {
        console.log('useEffect - Gerando variações a partir de atributos');
        generateVariationsFromAttributes(productAttributes);
        setVariationsLoadedFromBackend(false); // Marcamos que foram geradas localmente
      } else {
        console.log('useEffect - Sem valores, limpando variações');
        setVariations([]);
        setVariationsLoadedFromBackend(false);
      }
    } else {
      console.log('useEffect - Sem atributos, limpando variações');
      setVariations([]);
      setVariationsLoadedFromBackend(false);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [productAttributes, isLoadingProduct]);


  // Função para extrair atributos das variações (agora usando IDs)
  const extractAttributesFromVariations = (variations: any[], availableAttrs: Attribute[]): ProductAttribute[] => {
    console.log('extractAttributesFromVariations - variações:', variations);
    console.log('extractAttributesFromVariations - atributos disponíveis:', availableAttrs);
    
    const attributesMap: { [key: number]: { attributeId: number; attributeName: string; values: Set<string> } } = {};

    // Percorrer todas as variações
    variations.forEach((variation, index) => {
      console.log(`Processando variação ${index}:`, variation);
      if (variation.attributes && typeof variation.attributes === 'object') {
        Object.keys(variation.attributes).forEach((attrKey) => {
          const attrValue = variation.attributes[attrKey];
          console.log(`  - Atributo chave: ${attrKey}, valor: ${attrValue}`);
          
          // A chave agora é o ID do atributo (string ou number)
          const attributeId = parseInt(attrKey, 10);
          
          if (isNaN(attributeId)) {
            console.warn(`  - Chave de atributo inválida: ${attrKey}`);
            return;
          }
          
          // Buscar o atributo pelo ID
          const foundAttr = availableAttrs.find(a => a.id === attributeId);
          
          if (!foundAttr) {
            // Se não encontrou pelo ID, pular (atributo pode ter sido removido)
            console.warn(`  - Atributo com ID ${attributeId} não encontrado nos atributos disponíveis`);
            return;
          }
          
          console.log(`  - Atributo encontrado: ${foundAttr.name} (ID: ${foundAttr.id})`);
          
          if (!attributesMap[attributeId]) {
            attributesMap[attributeId] = {
              attributeId: foundAttr.id,
              attributeName: foundAttr.name,
              values: new Set<string>(),
            };
          }
          
          if (attrValue && typeof attrValue === 'string' && attrValue.trim()) {
            attributesMap[attributeId].values.add(attrValue.trim());
            console.log(`  - Valor adicionado: ${attrValue.trim()}`);
          }
        });
      } else {
        console.log(`  - Variação ${index} não tem atributos ou não é um objeto`);
      }
    });

    // Converter o Map para array e ordenar valores
    const result = Object.values(attributesMap).map((attr) => ({
      attributeId: attr.attributeId,
      attributeName: attr.attributeName,
      values: Array.from(attr.values).sort(),
    }));
    
    console.log('extractAttributesFromVariations - resultado:', result);
    return result;
  };

  // Image Handlers
  const addGalleryImage = () => {
      if (!newGalleryUrl.trim()) return;
      const newImage: ProductImage = {
        url: newGalleryUrl.trim(),
        is_main: false,
        is_external: true,
      };
      setProductImages([...productImages, newImage]);
      setNewGalleryUrl('');
  };

  const removeImage = async (image: ProductImage) => {
      if (!image.id) {
          // New image not saved yet, just remove from state
          setProductImages(productImages.filter(img => img.url !== image.url));
          if (image.is_main) {
              setPreviewUrl(null);
              setFormData(prev => ({ ...prev, main_image_url: '' }));
          }
          return;
      }

      // Existing image - delete from backend
      try {
          await api.delete(`/admin/product-images/${image.id}`);
          toast.success('Imagem removida com sucesso!');
          
          // Remove from state
          const updated = productImages.filter(img => img.id !== image.id);
          setProductImages(updated);
          
          // If it was the main image, update preview
          if (image.is_main && updated.length > 0) {
              // Make first image main
              const newMain = updated[0];
              newMain.is_main = true;
              setPreviewUrl(newMain.url);
              setFormData(prev => ({ ...prev, main_image_url: newMain.url }));
          } else if (image.is_main) {
              setPreviewUrl(null);
              setFormData(prev => ({ ...prev, main_image_url: '' }));
          }
      } catch (error: any) {
          console.error('Erro ao remover imagem', error);
          toast.error(error.response?.data?.message || 'Erro ao remover imagem.');
      }
  };

  const setAsMain = async (image: ProductImage) => {
      if (image.is_main) return;

      if (!image.id) {
          // New image - just update state
          const updated = productImages.map(img => ({
              ...img,
              is_main: img.url === image.url,
          }));
          // Sort: main first
          const sorted = updated.sort((a, b) => (b.is_main ? 1 : 0) - (a.is_main ? 1 : 0));
          setProductImages(sorted);
          setPreviewUrl(image.url);
          setFormData(prev => ({ ...prev, main_image_url: image.url }));
          return;
      }

      // Existing image - update in backend
      try {
          await api.put(`/admin/product-images/${image.id}/set-as-main`);
          toast.success('Imagem principal atualizada!');
          
          // Update state and sort
          const updated = productImages.map(img => ({
              ...img,
              is_main: img.id === image.id,
          }));
          const sorted = updated.sort((a, b) => (b.is_main ? 1 : 0) - (a.is_main ? 1 : 0));
          setProductImages(sorted);
          setPreviewUrl(image.url);
          setFormData(prev => ({ ...prev, main_image_url: image.url }));
      } catch (error: any) {
          console.error('Erro ao definir imagem principal', error);
          toast.error(error.response?.data?.message || 'Erro ao definir imagem principal.');
      }
  };

  // Drag and Drop handlers
  const handleDragStart = (index: number) => {
      setDraggedIndex(index);
  };

  const handleDragOver = (e: React.DragEvent) => {
      e.preventDefault();
  };

  const handleDrop = (e: React.DragEvent, dropIndex: number) => {
      e.preventDefault();
      if (draggedIndex === null || draggedIndex === dropIndex) return;

      const newImages = [...productImages];
      const dragged = newImages[draggedIndex];
      newImages.splice(draggedIndex, 1);
      newImages.splice(dropIndex, 0, dragged);
      
      setProductImages(newImages);
      setDraggedIndex(null);
  };

  const handleSubmitError = (error: unknown) => {
    const err = error as { response?: { status?: number; data?: { message?: string; plan_type?: string; current?: number; limit?: number } } };
    if (err?.response?.status === 402) {
      const data = err.response.data;
      window.gtag?.('event', 'limit_reached', {
        plan_type: data?.plan_type || 'free',
        current: data?.current || 0,
        limit: data?.limit || 0,
      });
      setUpgradeInfo({
        planType: data?.plan_type || 'free',
        current: data?.current || 0,
        limit: data?.limit || 0,
      });
      return;
    }
    toast.error('Erro ao salvar produto.');
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    // Preparar atributos e variações para envio
    // Se houver variações, enviar as variações completas
    // Senão, enviar apenas os atributos (para gerar combinações no backend)
    const attributesPayload = variations.length > 0 
      ? productAttributes.map(attr => ({
          attributeId: attr.attributeId || null,
          attributeName: attr.attributeName,
          values: attr.values,
        }))
      : productAttributes.map(attr => ({
          attributeId: attr.attributeId || null,
          attributeName: attr.attributeName,
          values: attr.values,
        }));
    
    // Preparar variações com estoque, preço e SKU
    const variationsPayload = variations.length > 0 
      ? variations.map(variation => ({
          id: variation.id || null, // ID se já existe (para update)
          attributes: variation.attributes,
          stock: variation.stock,
          price: variation.price,
          sku: variation.sku,
          is_active: variation.is_active,
        }))
      : [];

    // If file is present, use FormData
    if (imageFile) {
        const data = new FormData();
        data.append('name', formData.name);
        data.append('price', String(parseCurrency(formData.price)));
        if(formData.promotional_price) data.append('promotional_price', String(parseCurrency(formData.promotional_price)));
        
        if(formData.category_id) data.append('category_id', formData.category_id);
        data.append('attributes', JSON.stringify(attributesPayload));
        if (variationsPayload.length > 0) {
          data.append('variations', JSON.stringify(variationsPayload));
        }
        data.append('description', formData.description);
        data.append('is_active', formData.is_active ? '1' : '0');
        data.append('is_hot', formData.is_hot ? '1' : '0');
        data.append('stock_management_enabled', formData.stock_management_enabled ? '1' : '0');
        data.append('action_type', formData.action_type);
        if (formData.affiliate_link) data.append('affiliate_link', formData.affiliate_link);
        if (formData.whatsapp_message) data.append('whatsapp_message', formData.whatsapp_message);
        if (formData.button_label) data.append('button_label', formData.button_label);
        data.append('image', imageFile);

        // Gallery Images (only non-main images that are URLs, not files)
        const galleryUrls = productImages
            .filter(img => !img.is_main && !img.id) // Only new gallery images (not main, not saved yet)
            .map(img => img.url);
        galleryUrls.forEach((url, index) => data.append(`images[${index}]`, url));
        
        if (isEditMode) {
            data.append('_method', 'PUT');
            try {
                await api.post(`/admin/products/${id}`, data, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                toast.success('Produto atualizado com sucesso!');
                navigate('/admin/products');
            } catch (error) {
                console.error(error);
                handleSubmitError(error);
            }
        } else {
            try {
                await api.post('/admin/products', data, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                toast.success('Produto criado com sucesso!');
                navigate('/admin/products');
            } catch (error) {
                console.error(error);
                handleSubmitError(error);
            }
        }
    } else {
        // Standard JSON payload
        const payload = {
            ...formData,
            price: parseCurrency(formData.price),
            promotional_price: formData.promotional_price ? parseCurrency(formData.promotional_price) : null,
            category_id: formData.category_id ? parseInt(formData.category_id) : null,
            attributes: attributesPayload,
            variations: variationsPayload.length > 0 ? variationsPayload : undefined,
            main_image_url: null,
            action_type: formData.action_type,
            affiliate_link: formData.action_type === 'affiliate_link' ? formData.affiliate_link : null,
            whatsapp_message: formData.action_type === 'whatsapp_contact' ? (formData.whatsapp_message || null) : null,
            button_label: formData.action_type === 'whatsapp_contact' ? (formData.button_label || null) : null,
            stock_management_enabled: formData.stock_management_enabled,
            // Send all images in order (main first, then gallery)
            // Backend will handle syncing - existing images with IDs are kept, new URLs are added
            images: productImages
                .filter(img => !img.id) // Only new images (not saved yet)
                .map(img => img.url)
        };

        try {
            if (isEditMode) {
                await api.put(`/admin/products/${id}`, payload);
                toast.success('Produto atualizado com sucesso!');
            } else {
                await api.post('/admin/products', payload);
                toast.success('Produto criado com sucesso!');
            }
            navigate('/admin/products');
        } catch (error) {
            console.error(error);
            handleSubmitError(error);
        }
    }
    setLoading(false);
  };

  if (loading && isEditMode) {
    return (
      <div className="max-w-4xl mx-auto mb-10">
        <div className="bg-white rounded-lg shadow p-6">
          <div className="text-center py-8">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
            <p className="mt-4 text-gray-600">Carregando produto...</p>
          </div>
        </div>
      </div>
    );
  }

  // Early return if we're in edit mode but don't have an ID
  if (isEditMode && !id) {
    return (
      <div className="max-w-4xl mx-auto mb-10">
        <div className="bg-white rounded-lg shadow p-6">
          <div className="text-center py-8">
            <p className="text-red-600">ID do produto não encontrado.</p>
            <button
              onClick={() => navigate('/admin/products')}
              className="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
            >
              Voltar para Produtos
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto mb-10">
      <SEOHead title={isEditMode ? 'Editar Produto — VendaPop' : 'Novo Produto — VendaPop'} noIndex />
      <h1 className="text-2xl font-bold text-gray-800 mb-6">
        {isEditMode ? 'Editar Produto' : 'Novo Produto'}
      </h1>

      <div className="bg-white rounded-lg shadow p-6">
        <form onSubmit={handleSubmit}>
          <div className="flex flex-col gap-5 md:grid md:grid-cols-2 md:gap-6 mb-6">
            
            {/* Nome */}
            <div className="col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-2">Nome do Produto</label>
              <input
                type="text"
                name="name"
                value={formData.name}
                onChange={handleChange}
                required
                className="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              />
            </div>

            {/* Preço */}
            <div className="w-full pb-0">
              <label className="block text-sm font-medium text-gray-700 mb-2">Preço (R$)</label>
              <div className="flex">
                <span className="inline-flex items-center px-3 text-sm text-gray-500 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md">R$</span>
                <input
                  type="text"
                  name="price"
                  placeholder="0,00"
                  value={formData.price}
                  onChange={(e) => setFormData(prev => ({ ...prev, price: formatCurrency(e.target.value) }))}
                  className="flex-1 min-w-0 rounded-none rounded-r-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                  required
                />
              </div>
            </div>

            {/* Preço Promocional */}
            <div className="w-full pt-0">
              <label className="block text-sm font-medium text-gray-700 mb-2">Preço Promocional (Opcional)</label>
              <div className="flex">
                <span className="inline-flex items-center px-3 text-sm text-gray-500 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md">R$</span>
                <input
                  type="text"
                  name="promotional_price"
                  placeholder="0,00"
                  value={formData.promotional_price}
                  onChange={(e) => setFormData(prev => ({ ...prev, promotional_price: formatCurrency(e.target.value) }))}
                  className="flex-1 min-w-0 rounded-none rounded-r-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                />
              </div>
              <p className="text-xs text-gray-500 mt-1">Se preenchido, o preço original aparecerá riscado.</p>
            </div>

            {/* Categoria */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
              <select
                name="category_id"
                value={formData.category_id}
                onChange={handleChange}
                className="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              >
                <option value="">Selecione uma categoria...</option>
                {categories.map(cat => (
                  <option key={cat.id} value={cat.id}>{cat.name}</option>
                ))}
              </select>
            </div>

            {/* Atributos do Produto */}
            <div className="col-span-2 border-t pt-4">
              <h3 className="text-lg font-semibold text-gray-800 mb-4">Atributos</h3>
              
              {/* Select estilo Select2 para adicionar atributos */}
              <div className="mb-6 relative">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Adicionar Atributo
                </label>
                <div className="relative">
                  <div className="flex gap-2">
                    <div className="flex-1 relative">
                      <input
                        type="text"
                        value={attributeSearchTerm}
                        onChange={(e) => {
                          setAttributeSearchTerm(e.target.value);
                          setShowAttributeSelect(true);
                        }}
                        onFocus={() => setShowAttributeSelect(true)}
                        onKeyDown={(e) => {
                          if (e.key === 'Enter') {
                            e.preventDefault();
                            handleAddAttribute();
                          }
                        }}
                        placeholder="Busque um atributo existente ou digite um novo nome"
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                      />
                      {showAttributeSelect && (
                        <div className="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                          {availableAttributes
                            .filter(attr => 
                              attr.name.toLowerCase().includes(attributeSearchTerm.toLowerCase()) &&
                              !productAttributes.some(pa => pa.attributeId === attr.id)
                            )
                            .map(attr => (
                              <div
                                key={attr.id}
                                onClick={() => handleSelectExistingAttribute(attr.id)}
                                className="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                              >
                                {attr.name}
                              </div>
                            ))}
                          {attributeSearchTerm.trim() && 
                           !availableAttributes.some(attr => 
                             attr.name.toLowerCase() === attributeSearchTerm.trim().toLowerCase()
                           ) && (
                            <div
                              onClick={handleAddAttribute}
                              className="px-4 py-2 hover:bg-purple-50 cursor-pointer border-t border-gray-200 text-purple-600 font-medium"
                            >
                              + Criar "{attributeSearchTerm.trim()}"
                            </div>
                          )}
                          {availableAttributes.filter(attr => 
                            attr.name.toLowerCase().includes(attributeSearchTerm.toLowerCase()) &&
                            !productAttributes.some(pa => pa.attributeId === attr.id)
                          ).length === 0 && 
                          (!attributeSearchTerm.trim() || availableAttributes.some(attr => 
                            attr.name.toLowerCase() === attributeSearchTerm.trim().toLowerCase()
                          )) && (
                            <div className="px-4 py-2 text-gray-500 text-sm">
                              Nenhum resultado encontrado
                            </div>
                          )}
                        </div>
                      )}
                    </div>
                    <button
                      type="button"
                      onClick={handleAddAttribute}
                      disabled={!attributeSearchTerm.trim()}
                      className="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition disabled:bg-gray-400 disabled:cursor-not-allowed"
                    >
                      Adicionar
                    </button>
                  </div>
                </div>
                {/* Click outside para fechar dropdown */}
                {showAttributeSelect && (
                  <div 
                    className="fixed inset-0 z-0" 
                    onClick={() => setShowAttributeSelect(false)}
                  />
                )}
              </div>

              {/* Lista de atributos adicionados */}
              {(() => {
                console.log('Renderizando atributos. Total:', productAttributes.length, productAttributes);
                return null;
              })()}
              {productAttributes.length === 0 ? (
                <p className="text-sm text-gray-500 italic mb-4">Nenhum atributo adicionado ainda.</p>
              ) : (
                <div className="space-y-4">
                  {productAttributes.map((productAttr, index) => (
                    <div key={index} className="border border-gray-200 rounded-lg p-4 bg-gray-50">
                      <div className="flex items-center justify-between mb-3">
                        <label className="block text-sm font-medium text-gray-700">
                          {productAttr.attributeName}
                        </label>
                        <button
                          type="button"
                          onClick={() => handleRemoveAttribute(index)}
                          className="px-3 py-1 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm"
                        >
                          Remover
                        </button>
                      </div>

                      {/* Valores do Atributo (Tags) */}
            <div>
                        <label className="block text-xs text-gray-600 mb-2">
                          Valores (digite e pressione Enter)
                        </label>
                        <div className="flex flex-wrap gap-2 p-2 border border-gray-300 rounded-lg bg-white min-h-[50px]">
                          {productAttr.values.map((value, valueIndex) => (
                            <span
                              key={valueIndex}
                              className="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm"
                            >
                              {value}
                              <button
                                type="button"
                                onClick={() => handleRemoveAttributeValue(index, valueIndex)}
                                className="text-purple-600 hover:text-purple-800 font-bold"
                              >
                                ×
                              </button>
                            </span>
                          ))}
              <input
                type="text"
                            onKeyDown={(e) => handleAddAttributeValue(index, e)}
                            placeholder="Digite e pressione Enter"
                            className="flex-1 min-w-[150px] border-0 focus:ring-0 focus:outline-none"
              />
            </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}

              {/* Tabela de Variações - Mostrar apenas se houver variações geradas */}
              {variations.length > 0 && (
                <div className="mt-6 border-t pt-6">
                  <h3 className="text-lg font-semibold text-gray-800 mb-4">Variações do Produto</h3>
                  <p className="text-sm text-gray-600 mb-4">
                    Configure o estoque, preço e SKU para cada combinação de atributos. 
                    Se o preço estiver vazio, será usado o preço base do produto.
                  </p>
                  
                  {/* Mobile: Cards */}
                  <div className="md:hidden space-y-4">
                    {variations.map((variation, index) => (
                      <div key={index} className="bg-white border border-gray-200 rounded-lg p-4 space-y-3">
                        <span className="inline-block px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">
                          {formatVariationAttributes(variation)}
                        </span>
                        <div className="grid grid-cols-2 gap-3">
                          <div>
                            <label className="block text-xs text-gray-500 mb-1">Estoque</label>
                            <input
                              type="number"
                              min="0"
                              value={variation.stock ?? ''}
                              onChange={(e) => handleVariationChange(index, 'stock', e.target.value ? parseInt(e.target.value) : null)}
                              placeholder="0"
                              className="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            />
                          </div>
                          <div>
                            <label className="block text-xs text-gray-500 mb-1">Preço (R$)</label>
                            <CurrencyInput
                              placeholder="0,00"
                              value={variation.price?.toString() || ''}
                              decimalsLimit={2}
                              onValueChange={(value) => handleVariationChange(index, 'price', value ? parseFloat(value.replace(',', '.')) : null)}
                              prefix="R$ "
                              className="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            />
                          </div>
                          <div className="col-span-2">
                            <label className="block text-xs text-gray-500 mb-1">SKU</label>
                            <input
                              type="text"
                              value={variation.sku || ''}
                              onChange={(e) => handleVariationChange(index, 'sku', e.target.value || null)}
                              placeholder="SKU"
                              className="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            />
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>

                  {/* Desktop: Tabela */}
                  <div className="hidden md:block overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200 border border-gray-300 rounded-lg">
                      <thead className="bg-gray-50">
                        <tr>
                          <th className="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-200">
                            Variação
                          </th>
                          <th className="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-200">
                            Estoque
                          </th>
                          <th className="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-200">
                            Preço (R$)
                          </th>
                          <th className="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                            SKU
                          </th>
                        </tr>
                      </thead>
                      <tbody className="bg-white divide-y divide-gray-200">
                        {variations.map((variation, index) => (
                          <tr key={index} className="hover:bg-gray-50">
                            <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                              <span className="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">
                                {formatVariationAttributes(variation)}
                              </span>
                            </td>
                            <td className="px-4 py-3 whitespace-nowrap border-r border-gray-200">
                              <input
                                type="number"
                                min="0"
                                value={variation.stock ?? ''}
                                onChange={(e) => handleVariationChange(index, 'stock', e.target.value ? parseInt(e.target.value) : null)}
                                placeholder="0"
                                className="w-24 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                              />
                            </td>
                            <td className="px-4 py-3 whitespace-nowrap border-r border-gray-200">
                              <CurrencyInput
                                placeholder="R$ 0,00"
                                value={variation.price?.toString() || ''}
                                decimalsLimit={2}
                                onValueChange={(value) => handleVariationChange(index, 'price', value ? parseFloat(value.replace(',', '.')) : null)}
                                prefix="R$ "
                                className="w-32 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                              />
                            </td>
                            <td className="px-4 py-3 whitespace-nowrap">
                              <input
                                type="text"
                                value={variation.sku || ''}
                                onChange={(e) => handleVariationChange(index, 'sku', e.target.value || null)}
                                placeholder="SKU"
                                className="w-32 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                              />
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </div>
              )}
            </div>

            {/* Imagem Principal */}
            <div className="col-span-2">
                <ImageUploader
                    aspectRatio="2:3"
                    currentImageUrl={previewUrl ?? undefined}
                    onImageReady={(file) => {
                        setImageFile(file);
                        setPreviewUrl(URL.createObjectURL(file));
                    }}
                    label="Foto principal do produto"
                />
            </div>

            {/* Galeria de Imagens */}
            <div className="col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    Imagens do Produto
                    <span className="text-xs text-gray-500 ml-2">(Arraste para reordenar, clique em "Principal" para definir a imagem principal)</span>
                </label>
                <div className="flex gap-2 mb-4">
                    <input 
                        type="url" 
                        placeholder="https://..." 
                        value={newGalleryUrl}
                        onChange={(e) => setNewGalleryUrl(e.target.value)}
                        onKeyPress={(e) => {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                addGalleryImage();
                            }
                        }}
                        className="flex-grow rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    />
                    <button 
                        type="button" 
                        onClick={addGalleryImage}
                        className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    >
                        Adicionar URL
                    </button>
                </div>
                
                {/* Lista de Imagens com Drag and Drop */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {productImages.map((image, index) => (
                        <div
                            key={image.id || image.url}
                            draggable
                            onDragStart={() => handleDragStart(index)}
                            onDragOver={handleDragOver}
                            onDrop={(e) => handleDrop(e, index)}
                            className={`relative group border-2 rounded bg-gray-50 overflow-hidden aspect-square cursor-move ${
                                image.is_main ? 'border-indigo-500 ring-2 ring-indigo-300' : 'border-gray-200'
                            } ${draggedIndex === index ? 'opacity-50' : ''}`}
                        >
                            <img 
                                src={image.url} 
                                alt={`Product image ${index}`} 
                                className="w-full h-full object-cover" 
                            />
                            
                            {/* Badge Principal */}
                            {image.is_main && (
                                <div className="absolute top-2 left-2 bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded shadow-lg">
                                    Principal
                                </div>
                            )}
                            
                            {/* Botões de Ação */}
                            <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all flex items-center justify-center gap-2">
                                {!image.is_main && (
                                    <button
                                        type="button"
                                        onClick={() => setAsMain(image)}
                                        className="bg-indigo-600 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-indigo-700"
                                        title="Definir como Principal"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                    </button>
                                )}
                                <button
                                    type="button"
                                    onClick={() => removeImage(image)}
                                    className="bg-red-500 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-red-600"
                                    title="Remover Imagem"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            
                            {/* Ícone de arrastar */}
                            <div className="absolute bottom-2 right-2 bg-gray-800 bg-opacity-50 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 8h16M4 16h16" />
                                </svg>
                            </div>
                        </div>
                    ))}
                    {productImages.length === 0 && (
                        <div className="col-span-full text-center py-8 text-gray-400 border-2 border-dashed rounded">
                            Nenhuma imagem adicionada. Adicione URLs de imagens acima.
                        </div>
                    )}
                </div>
            </div>

            {/* Descrição */}
            <div className="col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
              <textarea
                name="description"
                rows={4}
                value={formData.description}
                onChange={handleChange}
                className="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              />
            </div>

            {/* Ação do Botão (CTA) */}
            <div className="col-span-2 border-t pt-4">
              <h3 className="text-lg font-semibold text-gray-800 mb-4">Ação do Botão</h3>
              
              <div className="space-y-4">
                {/* Tipo de Ação */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Tipo de Ação *
                  </label>
                  <div className="space-y-2">
                    <label className="flex items-center cursor-pointer">
                      <input
                        type="radio"
                        name="action_type"
                        value="add_to_cart"
                        checked={formData.action_type === 'add_to_cart'}
                        onChange={handleChange}
                        className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                      />
                      <span className="ml-2 text-sm text-gray-700">Adicionar ao Carrinho (padrão)</span>
                    </label>
                    <label className="flex items-center cursor-pointer">
                      <input
                        type="radio"
                        name="action_type"
                        value="affiliate_link"
                        checked={formData.action_type === 'affiliate_link'}
                        onChange={handleChange}
                        className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                      />
                      <span className="ml-2 text-sm text-gray-700">Link de Afiliado (abre link externo)</span>
                    </label>
                    <label className="flex items-center cursor-pointer">
                      <input
                        type="radio"
                        name="action_type"
                        value="whatsapp_contact"
                        checked={formData.action_type === 'whatsapp_contact'}
                        onChange={handleChange}
                        className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                      />
                      <span className="ml-2 text-sm text-gray-700">Contato WhatsApp (abre WhatsApp do vendedor)</span>
                    </label>
                  </div>
                </div>

                {/* Link de Afiliado (se action_type = affiliate_link) */}
                {formData.action_type === 'affiliate_link' && (
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Link de Afiliado *
                    </label>
                    <input
                      type="url"
                      name="affiliate_link"
                      value={formData.affiliate_link}
                      onChange={handleChange}
                      placeholder="https://exemplo.com/produto?ref=123"
                      className="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    />
                    <p className="text-xs text-gray-500 mt-1">
                      URL para onde o cliente será redirecionado ao clicar no botão
                    </p>
                  </div>
                )}

                {/* Mensagem WhatsApp (se action_type = whatsapp_contact) */}
                {formData.action_type === 'whatsapp_contact' && (
                  <>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Mensagem Personalizada do WhatsApp
                      </label>
                      <textarea
                        name="whatsapp_message"
                        value={formData.whatsapp_message}
                        onChange={handleChange}
                        rows={3}
                        placeholder="Olá! Tenho interesse em {nome do produto}. Poderia me enviar mais informações?"
                        className="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                      />
                      <p className="text-xs text-gray-500 mt-1">
                        Mensagem que será enviada ao abrir o WhatsApp. Deixe em branco para usar a mensagem padrão da loja.
                      </p>
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Label do Botão (opcional)
                      </label>
                      <input
                        type="text"
                        name="button_label"
                        value={formData.button_label}
                        onChange={handleChange}
                        placeholder="Ex: Fale com um Corretor, Fale com um Vendedor"
                        className="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                      />
                      <p className="text-xs text-gray-500 mt-1">
                        Texto personalizado do botão. Se deixar em branco, será usado "Fale com um Vendedor"
                      </p>
                    </div>
                  </>
                )}
              </div>
            </div>

            {/* Tags e Status */}
            <div className="col-span-2 flex flex-wrap gap-6">
                {/* Ativo */}
                <div className="flex items-center">
                <input
                    type="checkbox"
                    name="is_active"
                    id="is_active"
                    checked={formData.is_active}
                    onChange={handleCheckboxChange}
                    className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label htmlFor="is_active" className="ml-2 block text-sm text-gray-900 cursor-pointer">
                    Produto Ativo (Visível na loja)
                </label>
                </div>

                {/* Hot */}
                <div className="flex items-center">
                <input
                    type="checkbox"
                    name="is_hot"
                    id="is_hot"
                    checked={formData.is_hot}
                    onChange={handleCheckboxChange}
                    className="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                />
                <label htmlFor="is_hot" className="ml-2 block text-sm text-gray-900 cursor-pointer font-medium text-red-600 flex items-center gap-1">
                    🔥 Produto HOT / Destaque
                </label>
                </div>

                {/* Controle de Estoque */}
                <div className="flex items-center">
                <input
                    type="checkbox"
                    name="stock_management_enabled"
                    id="stock_management_enabled"
                    checked={formData.stock_management_enabled}
                    onChange={handleCheckboxChange}
                    className="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                />
                <label htmlFor="stock_management_enabled" className="ml-2 block text-sm text-gray-900 cursor-pointer">
                    📦 Controlar Estoque (verificar disponibilidade nas variações)
                </label>
                </div>
            </div>
          </div>

          <div className="flex justify-end gap-3">
            <button
              type="button"
              onClick={() => navigate('/admin/products')}
              className="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300 transition"
            >
              Cancelar
            </button>
            <button
              type="submit"
              disabled={loading}
              className="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 transition disabled:opacity-50"
            >
              {loading ? 'Salvando...' : (isEditMode ? 'Atualizar' : 'Criar Produto')}
            </button>
          </div>
        </form>
      </div>

      <UpgradeModal
        isOpen={upgradeInfo !== null}
        planType={upgradeInfo?.planType || 'free'}
        current={upgradeInfo?.current || 0}
        limit={upgradeInfo?.limit || 0}
        upgradeUrl="/admin/planos"
        onClose={() => {
          setUpgradeInfo(null);
          navigate('/admin/products');
        }}
      />
    </div>
  );
};

export default ProductForm;
