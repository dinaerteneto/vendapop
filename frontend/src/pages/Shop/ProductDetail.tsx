import React, { useEffect, useState } from 'react';
import { useParams, useOutletContext } from 'react-router-dom';
import api from '../../services/api';
import ImageCarousel from '../../components/ui/ImageCarousel';
import Toast from '../../components/ui/Toast';
import { useCart } from '../../context/CartContext';
import ProductActionButton from '../../components/ecommerce/ProductActionButton';
import { formatCurrency } from '../../utils/currency';

interface ProductImage {
    id: number;
    url: string;
    is_main: boolean;
}

interface ProductAttribute {
  attributeId: number;
  attributeName: string;
  values: string[];
}

interface Product {
  id: number;
  uuid?: string;
  slug: string;
  name: string;
  description: string;
  short_description?: string;
  price: string;
  promotional_price?: string | null;
  sizes?: string[]; // Apenas para produtos antigos sem variações
  colors?: string[] | null; // Apenas para produtos antigos sem variações
  variations?: Array<{
    id: number;
    attributes: { [key: string]: string }; // { attributeId: value }
    attribute_names?: { [key: string]: string }; // { attributeId: name }
    stock?: number | null;
    price?: number | null;
    sku?: string | null;
    is_active?: boolean;
  }>;
  attributes_map?: Array<{
    id: number;
    name: string;
    slug: string;
  }>;
  main_image_url: string | null;
  images: ProductImage[] | null;
  is_hot?: boolean;
  is_active?: boolean;
  stock_management_enabled?: boolean;
  action_type?: 'add_to_cart' | 'affiliate_link' | 'whatsapp_contact';
  affiliate_link?: string | null;
  whatsapp_message?: string | null;
  button_label?: string | null;
  category_id?: number;
  category?: {
    id: number;
    name: string;
    slug: string;
  };
}

interface ToastState {
  isVisible: boolean;
  message: string;
  type: 'success' | 'error' | 'warning';
}

const ProductDetail: React.FC = () => {
  const { storeSlug, productSlug } = useParams();
  const { addToCart: addToCartContext, cart } = useCart();
  const [product, setProduct] = useState<Product | null>(null);
  const [productAttributes, setProductAttributes] = useState<ProductAttribute[]>([]);
  const [selectedAttributes, setSelectedAttributes] = useState<{ [key: number]: string }>({});
  const [quantity, setQuantity] = useState<number>(1);
  const [selectedVariation, setSelectedVariation] = useState<any>(null); // Variação selecionada atual
  const context = useOutletContext<{ storeInfo: any }>();
  const primaryColor = context?.storeInfo?.primary_color || '#7c3aed';
  
  // Toast State
  const [toast, setToast] = useState<ToastState>({
    isVisible: false,
    message: '',
    type: 'warning'
  });

  useEffect(() => {
     const fetch = async () => {
         try {
             const response = await api.get(`/${storeSlug}/products/${productSlug}`);
             // Laravel Resource retorna diretamente o objeto (sem wrapper { data: {...} })
             const productData = response.data;
             
             if (!productData || !productData.id) {
                 console.error('Product data is invalid:', response.data);
                 showToast('Produto não encontrado ou inválido.', 'error');
                 return;
             }
             
             setProduct(productData);
             
             // Extrair atributos das variações
             if (productData?.variations && Array.isArray(productData.variations) && productData.variations.length > 0) {
                 const attributes = extractAttributesFromVariations(productData.variations, productData.attributes_map || []);
                 setProductAttributes(attributes);
                 
                 // Selecionar primeiro valor de cada atributo por padrão
                 const defaultSelections: { [key: number]: string } = {};
                 attributes.forEach(attr => {
                     if (attr.values.length > 0) {
                         defaultSelections[attr.attributeId] = attr.values[0];
                     }
                 });
                 setSelectedAttributes(defaultSelections);
             }
         } catch (e: any) {
             console.error('Erro ao carregar produto:', e);
             const errorMessage = e.response?.data?.message || 'Produto não encontrado.';
             showToast(errorMessage, 'error');
         }
     };
     if (storeSlug && productSlug) fetch();
  }, [storeSlug, productSlug]);

  // Função para extrair atributos únicos das variações
  const extractAttributesFromVariations = (
    variations: Array<{ 
      attributes: { [key: string]: string };
      attribute_names?: { [key: string]: string };
    }>,
    attributesMap: Array<{ id: number; name: string; slug: string }> = []
  ): ProductAttribute[] => {
    const attributesById: { [key: number]: { attributeId: number; attributeName: string; values: Set<string> } } = {};
    
    // Criar mapeamento rápido de IDs para nomes
    const nameMap: { [key: number]: string } = {};
    attributesMap.forEach(attr => {
      nameMap[attr.id] = attr.name;
    });

    variations.forEach((variation) => {
        if (variation.attributes && typeof variation.attributes === 'object') {
            Object.keys(variation.attributes).forEach((attrIdStr) => {
                const attrId = parseInt(attrIdStr, 10);
                const attrValue = variation.attributes[attrIdStr];
                
                if (!attributesById[attrId]) {
                    // Buscar nome do atributo: primeiro em attribute_names, depois em attributesMap, depois fallback
                    const attributeName = variation.attribute_names?.[attrIdStr] 
                        || nameMap[attrId] 
                        || `Atributo ${attrId}`;
                    
                    attributesById[attrId] = {
                        attributeId: attrId,
                        attributeName: attributeName,
                        values: new Set<string>(),
                    };
                }
                
                if (attrValue && typeof attrValue === 'string' && attrValue.trim()) {
                    attributesById[attrId].values.add(attrValue.trim());
                }
            });
        }
    });

    // Converter para array
    return Object.values(attributesById).map((attr) => ({
        attributeId: attr.attributeId,
        attributeName: attr.attributeName,
        values: Array.from(attr.values).sort(),
    }));
  };

  // Função para encontrar a variação baseada nos atributos selecionados
  const findVariationByAttributes = (attributes: { [key: number]: string }) => {
    if (!product?.variations || product.variations.length === 0) {
      return null;
    }

    // Comparar os atributos selecionados com as variações
    for (const variation of product.variations) {
      if (!variation.attributes || !variation.is_active) continue;

      // Verificar se todos os atributos da variação batem com os selecionados
      const variationAttrs = Object.keys(variation.attributes).length;
      const selectedAttrs = Object.keys(attributes).length;

      if (variationAttrs !== selectedAttrs) continue;

      let matches = true;
      for (const [attrId, value] of Object.entries(attributes)) {
        if (variation.attributes[attrId] !== value) {
          matches = false;
          break;
        }
      }

      if (matches) {
        return variation;
      }
    }

    return null;
  };

  // Atualizar variação selecionada quando atributos mudarem
  useEffect(() => {
    if (product && productAttributes.length > 0 && Object.keys(selectedAttributes).length === productAttributes.length) {
      const variation = findVariationByAttributes(selectedAttributes);
      setSelectedVariation(variation);
    } else {
      setSelectedVariation(null);
    }
  }, [selectedAttributes, product, productAttributes]);

  const showToast = (message: string, type: ToastState['type'] = 'warning') => {
    setToast({ isVisible: true, message, type });
  };

  const handleQuantityChange = (delta: number) => {
      setQuantity(prev => {
        const newQuantity = Math.max(1, prev + delta);
        
        // Se controle de estoque está habilitado e há variação selecionada, limitar pela quantidade em estoque
        if (product?.stock_management_enabled && selectedVariation && selectedVariation.stock !== null && selectedVariation.stock !== undefined) {
          return Math.min(newQuantity, selectedVariation.stock);
        }
        
        return newQuantity;
      });
  };

  const addToCart = () => {
      if (!product) return;
      
      // Preparar atributos para o carrinho (precisa ser criado antes para usar na validação)
      const attributesPayload: { [key: string]: string } = {};
      if (productAttributes.length > 0) {
          Object.keys(selectedAttributes).forEach(attrId => {
              attributesPayload[attrId] = selectedAttributes[parseInt(attrId)];
          });
      }
      
      // Validar se todos os atributos obrigatórios foram selecionados (apenas se houver variações)
      if (productAttributes.length > 0) {
          for (const attr of productAttributes) {
              if (!selectedAttributes[attr.attributeId]) {
                  showToast(`Por favor, selecione ${attr.attributeName.toLowerCase()}.`, 'warning');
                  return;
              }
          }

          // Validar estoque se controle está habilitado
          if (product.stock_management_enabled) {
              if (!selectedVariation) {
                  showToast('Variação não encontrada. Por favor, selecione novamente os atributos.', 'warning');
                  return;
              }

              // Verificar se está disponível
              const variationIsAvailable = selectedVariation.stock === null || selectedVariation.stock === undefined || selectedVariation.stock > 0;
              if (!variationIsAvailable) {
                  showToast('Produto indisponível no momento.', 'warning');
                  return;
              }

              // Verificar quantidade no estoque considerando o que já está no carrinho
              if (selectedVariation.stock !== null && selectedVariation.stock !== undefined) {
                  // Buscar quantidade já no carrinho para esta variação específica
                  const hasAttributes = Object.keys(attributesPayload).length > 0;
                  const existingCartItem = cart.find((item) => {
                      if (item.product_id !== product.id) return false;
                      
                      // Normalizar atributos para comparação
                      const itemAttrs = item.attributes || {};
                      const payloadAttrs = attributesPayload || {};
                      const itemHasAttrs = Object.keys(itemAttrs).length > 0;
                      
                      // Se ambos têm atributos ou ambos não têm, comparar
                      if (hasAttributes || itemHasAttrs) {
                          const itemAttrsStr = JSON.stringify(itemAttrs);
                          const payloadAttrsStr = JSON.stringify(payloadAttrs);
                          return itemAttrsStr === payloadAttrsStr;
                      }
                      
                      // Se nenhum tem atributos, é a mesma variação
                      return true;
                  });
                  
                  const quantityInCart = existingCartItem?.quantity || 0;
                  const totalQuantity = quantityInCart + quantity;
                  
                  if (totalQuantity > selectedVariation.stock) {
                      const availableQuantity = selectedVariation.stock - quantityInCart;
                      if (availableQuantity <= 0) {
                          showToast(`Você já possui ${quantityInCart} unidade(s) no carrinho. Não há mais estoque disponível.`, 'warning');
                      } else {
                          showToast(`Quantidade indisponível. Você já tem ${quantityInCart} no carrinho. Restam apenas ${availableQuantity} disponível(is).`, 'warning');
                      }
                      return;
                  }
              }
          }
      }

      const finalPrice = getCurrentPrice();

      // Criar o item do carrinho
      const cartItem: any = {
          product_id: product.id,
          name: product.name,
          price: finalPrice,
          size: '', // Deprecated, usar attributes
          color: '', // Deprecated, usar attributes
          quantity: quantity,
          main_image_url: product.main_image_url || undefined
      };
      
      // Só adicionar attributes se realmente houver atributos selecionados
      if (productAttributes.length > 0 && Object.keys(attributesPayload).length > 0) {
          cartItem.attributes = attributesPayload;
      }
      
      addToCartContext(cartItem);

      showToast('Produto adicionado ao carrinho!', 'success');
  };

  if (!product) return <div className="p-8 text-center">Carregando...</div>;

  // Prepare images array for carousel
  let carouselImages: string[] = [];
  if (product.images && Array.isArray(product.images) && product.images.length > 0) {
      // Sort: Main image first
      const sorted = [...product.images].sort((a, b) => {
          if (typeof a === 'object' && typeof b === 'object') {
              return (b.is_main ? 1 : 0) - (a.is_main ? 1 : 0);
          }
          return 0;
      });
      carouselImages = sorted.map(img => typeof img === 'object' ? img.url : img);
  } else if (product.main_image_url) {
      carouselImages = [product.main_image_url];
  }

  // Price Logic - usar preço da variação se disponível, senão usar preço base
  const getCurrentPrice = () => {
    if (!product) return 0;
    
    // Se há variação selecionada e ela tem preço próprio, usar esse preço
    if (selectedVariation && selectedVariation.price !== null && selectedVariation.price !== undefined && selectedVariation.price !== '') {
      return parseFloat(selectedVariation.price.toString());
    }
    
    // Caso contrário, usar preço base do produto
    const hasPromo = product.promotional_price && parseFloat(product.promotional_price) > 0;
    return hasPromo ? parseFloat(product.promotional_price!) : parseFloat(product.price);
  };

  const getOriginalPrice = () => {
    if (!product) return null;
    
    // Se há variação selecionada com preço próprio, não mostrar preço original riscado
    if (selectedVariation && selectedVariation.price !== null && selectedVariation.price !== undefined && selectedVariation.price !== '') {
      return null;
    }

    // Para produto base, verificar se tem promoção
    const hasPromo = product.promotional_price && parseFloat(product.promotional_price) > 0;
    return hasPromo ? parseFloat(product.price) : null;
  };

  // Verificar disponibilidade
  const isAvailable = () => {
    if (!product) return true;
    
    // Se controle de estoque não está habilitado, sempre disponível
    if (!product.stock_management_enabled) {
      return true;
    }

    // Se não há variação selecionada, não podemos verificar estoque
    if (!selectedVariation) {
      return true; // Permitir seleção, mas validar depois
    }

    // Verificar estoque da variação
    if (selectedVariation.stock === null || selectedVariation.stock === undefined) {
      return true; // Sem controle de estoque para esta variação
    }

    return selectedVariation.stock > 0;
  };

  const currentPrice = getCurrentPrice();
  const originalPrice = getOriginalPrice();
  const isVariationAvailable = isAvailable();
  const stockQuantity = selectedVariation?.stock ?? null;

  return (
    <div className="bg-white rounded-2xl shadow-sm p-4 md:p-6">
        <Toast 
            isVisible={toast.isVisible} 
            message={toast.message} 
            type={toast.type} 
            onClose={() => setToast(prev => ({ ...prev, isVisible: false }))} 
        />

        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            {/* Image Carousel Section */}
            <div className="aspect-square w-full bg-gray-100 rounded-xl overflow-hidden shadow-inner relative">
                <ImageCarousel images={carouselImages} alt={product.name} />
                
                {!!product.is_hot && (
                    <div className="absolute top-4 right-4 bg-red-500 text-white text-sm font-bold px-3 py-1 rounded-full shadow-md z-10 animate-pulse">
                        HOT 🔥
                    </div>
                )}
            </div>

            {/* Product Info Section */}
            <div>
                <h1 className="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{product.name}</h1>
                
                <div className="mb-6 flex items-baseline gap-3 flex-wrap">
                    {originalPrice && (
                        <span className="text-lg text-gray-400 line-through">
                            {formatCurrency(originalPrice)}
                        </span>
                    )}
                    <span className="text-3xl font-extrabold text-purple-700" style={{ color: primaryColor }}>
                        {formatCurrency(currentPrice)}
                    </span>
                    <span className="text-sm text-gray-500">/ unidade</span>
                    {!isVariationAvailable && product.stock_management_enabled && (
                        <span className="ml-auto text-red-600 font-semibold text-sm">
                            Indisponível
                        </span>
                    )}
                </div>

                <div className="space-y-6">
                    {/* Atributos Dinâmicos - Apenas se houver variações */}
                    {productAttributes.length > 0 && (
                        productAttributes.map((attr) => (
                            <div key={attr.attributeId}>
                                <p className="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wider">
                                    {attr.attributeName}
                                </p>
                        <div className="flex flex-wrap gap-3">
                                    {attr.values.map((value) => (
                                <button
                                            key={value}
                                            onClick={() => setSelectedAttributes(prev => ({
                                                ...prev,
                                                [attr.attributeId]: value
                                            }))}
                                        className={`px-4 py-2 rounded-lg font-medium border transition-all duration-200 ${
                                                selectedAttributes[attr.attributeId] === value
                                                    ? 'bg-black text-white border-black shadow-md scale-105'
                                            : 'bg-white border-gray-200 text-gray-700 hover:border-black'
                                        }`}
                                    >
                                            {value}
                                    </button>
                                ))}
                            </div>
                        </div>
                        ))
                    )}

                    {/* Description */}
                    <div className="py-6 border-t border-gray-100">
                        <p className="text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wider">Descrição</p>
                        <p className="text-gray-600 leading-relaxed">{product.description}</p>
                    </div>

                    {/* Quantity Selector */}
                    <div className="mb-6">
                        <p className="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wider">Quantidade</p>
                        <div className="flex items-center border border-gray-300 rounded-lg w-max">
                            <button 
                                onClick={() => handleQuantityChange(-1)}
                                disabled={quantity <= 1}
                                className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-l-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                -
                            </button>
                            <span className="px-4 py-2 font-semibold text-gray-900 min-w-[3rem] text-center">
                                {quantity}
                            </span>
                            <button 
                                onClick={() => handleQuantityChange(1)}
                                disabled={
                                    product.stock_management_enabled && 
                                    selectedVariation && 
                                    selectedVariation.stock !== null && 
                                    selectedVariation.stock !== undefined &&
                                    quantity >= selectedVariation.stock
                                }
                                className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-r-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                +
                            </button>
                        </div>
                        {product.stock_management_enabled && stockQuantity !== null && stockQuantity !== undefined && (
                            <p className="text-xs text-gray-500 mt-2">
                                {stockQuantity > 0 ? `${stockQuantity} disponível${stockQuantity > 1 ? 'eis' : ''}` : 'Indisponível'}
                            </p>
                        )}
                    </div>

                    {/* Action Button (Add to Cart, WhatsApp, or Affiliate) */}
                    {!isVariationAvailable && product.stock_management_enabled ? (
                        <button
                            disabled
                            className="w-full bg-gray-300 text-gray-500 py-3 rounded-xl font-medium text-md cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <span>Indisponível</span>
                        </button>
                    ) : (
                        <ProductActionButton
                            actionType={product.action_type || 'add_to_cart'}
                            affiliateLink={product.affiliate_link}
                            whatsappMessage={product.whatsapp_message}
                            whatsappNumber={context?.storeInfo?.whatsapp_number}
                            buttonLabel={product.button_label}
                            onAddToCart={addToCart}
                            primaryColor={primaryColor}
                            productName={product.name}
                        />
                    )}

                    {/* Share Button */}
                    <button 
                        onClick={() => {
                            if (navigator.share) {
                                navigator.share({
                                    title: product.name,
                                    text: product.description,
                                    url: window.location.href,
                                }).catch(console.error);
                            } else {
                                navigator.clipboard.writeText(window.location.href);
                                showToast('Link copiado!', 'success');
                            }
                        }}
                        className="w-full mt-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 py-3 rounded-xl font-medium text-md transition-all flex items-center justify-center gap-2"
                    >
                        <span>🔗</span> Compartilhar
                    </button>
                </div>
            </div>
        </div>
    </div>
  );
};

export default ProductDetail;
