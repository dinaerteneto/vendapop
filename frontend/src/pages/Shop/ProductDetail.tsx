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
  const { addToCart: addToCartContext } = useCart();
  const [product, setProduct] = useState<Product | null>(null);
  const [productAttributes, setProductAttributes] = useState<ProductAttribute[]>([]);
  const [selectedAttributes, setSelectedAttributes] = useState<{ [key: number]: string }>({});
  const [quantity, setQuantity] = useState<number>(1);
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
         } catch (e) {
             console.error(e);
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

  const showToast = (message: string, type: ToastState['type'] = 'warning') => {
    setToast({ isVisible: true, message, type });
  };

  const handleQuantityChange = (delta: number) => {
      setQuantity(prev => Math.max(1, prev + delta));
  };

  const addToCart = () => {
      if (!product) return;
      
      // Validar se todos os atributos obrigatórios foram selecionados (apenas se houver variações)
      if (productAttributes.length > 0) {
          for (const attr of productAttributes) {
              if (!selectedAttributes[attr.attributeId]) {
                  showToast(`Por favor, selecione ${attr.attributeName.toLowerCase()}.`, 'warning');
          return;
      }
          }
      }

      const finalPrice = product.promotional_price && parseFloat(product.promotional_price) > 0 
        ? parseFloat(product.promotional_price) 
        : parseFloat(product.price);

      // Preparar atributos para o carrinho (compatibilidade com formato antigo)
      const attributesPayload: { [key: string]: string } = {};
      Object.keys(selectedAttributes).forEach(attrId => {
          attributesPayload[attrId] = selectedAttributes[parseInt(attrId)];
      });

      addToCartContext({
          product_id: product.id,
          name: product.name,
          price: finalPrice,
          size: '', // Deprecated, usar attributes
          color: '', // Deprecated, usar attributes
          attributes: attributesPayload, // Novo formato
          quantity: quantity,
          main_image_url: product.main_image_url || undefined
      });

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

  // Price Logic
  const hasPromo = product.promotional_price && parseFloat(product.promotional_price) > 0;
  const currentPrice = hasPromo ? parseFloat(product.promotional_price!) : parseFloat(product.price);
  const originalPrice = hasPromo ? parseFloat(product.price) : null;

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
                
                <div className="mb-6 flex items-baseline gap-3">
                    {originalPrice && (
                        <span className="text-lg text-gray-400 line-through">
                            {formatCurrency(originalPrice)}
                        </span>
                    )}
                    <span className="text-3xl font-extrabold text-purple-700" style={{ color: primaryColor }}>
                        {formatCurrency(currentPrice)}
                    </span>
                    <span className="text-sm text-gray-500">/ unidade</span>
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
                                className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-l-lg transition-colors"
                            >
                                -
                            </button>
                            <span className="px-4 py-2 font-semibold text-gray-900 min-w-[3rem] text-center">
                                {quantity}
                            </span>
                            <button 
                                onClick={() => handleQuantityChange(1)}
                                className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-r-lg transition-colors"
                            >
                                +
                            </button>
                        </div>
                    </div>

                    {/* Action Button (Add to Cart, WhatsApp, or Affiliate) */}
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
