import React, { useEffect, useState } from 'react';
import { useParams, useOutletContext } from 'react-router-dom';
import api from '../../services/api';
import ImageCarousel from '../../components/ui/ImageCarousel';
import Toast from '../../components/ui/Toast';
import { useCart } from '../../context/CartContext';

interface ProductImage {
    id: number;
    url: string;
    is_main: boolean;
}

interface Product {
  id: number;
  name: string;
  description: string;
  price: string;
  promotional_price?: string | null;
  sizes: string[];
  colors: string[] | null;
  main_image_url: string | null;
  images: ProductImage[] | null;
  is_hot?: boolean; // Added is_hot
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
  const [selectedSize, setSelectedSize] = useState<string>('');
  const [selectedColor, setSelectedColor] = useState<string>('');
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
             const { data } = await api.get(`/${storeSlug}/products/${productSlug}`);
             setProduct(data);
         } catch (e) {
             console.error(e);
         }
     };
     if (storeSlug && productSlug) fetch();
  }, [storeSlug, productSlug]);

  const showToast = (message: string, type: ToastState['type'] = 'warning') => {
    setToast({ isVisible: true, message, type });
  };

  const handleQuantityChange = (delta: number) => {
      setQuantity(prev => Math.max(1, prev + delta));
  };

  const addToCart = () => {
      if (!product) return;
      
      if (!selectedSize && product.sizes?.length > 0) {
          showToast('Por favor, selecione um tamanho.', 'warning');
          return;
      }

      if (!selectedColor && product.colors && product.colors.length > 0) {
          showToast('Por favor, selecione uma cor.', 'warning');
          return;
      }

      const finalPrice = product.promotional_price && parseFloat(product.promotional_price) > 0 
        ? parseFloat(product.promotional_price) 
        : parseFloat(product.price);

      addToCartContext({
          product_id: product.id,
          name: product.name,
          price: finalPrice,
          size: selectedSize,
          color: selectedColor,
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
                            R$ {originalPrice.toFixed(2).replace('.',',')}
                        </span>
                    )}
                    <span className="text-3xl font-extrabold text-purple-700" style={{ color: primaryColor }}>
                        R$ {currentPrice.toFixed(2).replace('.',',')}
                    </span>
                    <span className="text-sm text-gray-500">/ unidade</span>
                </div>

                <div className="space-y-6">
                    {/* Size Selection */}
                    <div>
                        <p className="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wider">Tamanho</p>
                        <div className="flex flex-wrap gap-3">
                            {product.sizes.map(s => (
                                <button
                                    key={s}
                                    onClick={() => setSelectedSize(s)}
                                    className={`min-w-[3rem] h-10 px-3 rounded-lg font-medium transition-all duration-200 ${
                                        selectedSize === s 
                                        ? 'bg-black text-white shadow-md scale-105' 
                                        : 'bg-white border border-gray-200 text-gray-700 hover:border-black'
                                    }`}
                                >
                                    {s}
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Color Selection */}
                    {product.colors && product.colors.length > 0 && (
                        <div>
                            <p className="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wider">Cor</p>
                            <div className="flex flex-wrap gap-3">
                                {product.colors.map(c => (
                                    <button
                                        key={c}
                                        onClick={() => setSelectedColor(c)}
                                        className={`px-4 py-2 rounded-lg font-medium border transition-all duration-200 ${
                                            selectedColor === c 
                                            ? 'bg-black text-white border-black shadow-md' 
                                            : 'bg-white border-gray-200 text-gray-700 hover:border-black'
                                        }`}
                                    >
                                        {c}
                                    </button>
                                ))}
                            </div>
                        </div>
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

                    {/* Add to Cart Button */}
                    <button 
                        onClick={addToCart} 
                        className="w-full text-white py-4 rounded-xl font-bold text-lg shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2 hover:opacity-90"
                        style={{ backgroundColor: primaryColor, boxShadow: `0 10px 15px -3px ${primaryColor}40` }}
                    >
                        <span>🛒</span> Adicionar ao Carrinho
                    </button>

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
