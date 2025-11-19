import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../../services/api';
import ImageCarousel from '../../components/ui/ImageCarousel';
import Toast from '../../components/ui/Toast';

interface Product {
  id: number;
  name: string;
  description: string;
  price: string;
  sizes: string[];
  colors: string[] | null;
  main_image_url: string | null;
  images: string[] | null;
}

interface ToastState {
  isVisible: boolean;
  message: string;
  type: 'success' | 'error' | 'warning';
}

const ProductDetail: React.FC = () => {
  const { storeSlug, productId } = useParams();
  const navigate = useNavigate();
  const [product, setProduct] = useState<Product | null>(null);
  const [selectedSize, setSelectedSize] = useState<string>('');
  const [selectedColor, setSelectedColor] = useState<string>('');
  
  // Toast State
  const [toast, setToast] = useState<ToastState>({
    isVisible: false,
    message: '',
    type: 'warning'
  });

  useEffect(() => {
     const fetch = async () => {
         try {
             const { data } = await api.get(`/${storeSlug}/products/${productId}`);
             setProduct(data);
         } catch (e) {
             console.error(e);
         }
     };
     if (storeSlug && productId) fetch();
  }, [storeSlug, productId]);

  const showToast = (message: string, type: ToastState['type'] = 'warning') => {
    setToast({ isVisible: true, message, type });
  };

  const addToCart = () => {
      if (!product) return;
      
      if (!selectedSize && product.sizes?.length > 0) {
          showToast('Por favor, selecione um tamanho.', 'warning');
          return;
      }

      if (!selectedColor && product.colors && product.colors.length > 0) {
          // Optional: Force color selection if colors exist? 
          // Assuming colors might be optional if not strictly enforced, 
          // but usually if listed, user should pick one. 
          // Let's warn if colors exist.
          showToast('Por favor, selecione uma cor.', 'warning');
          return;
      }

      // Logic to add to cart (LocalStorage)
      const cartKey = `cart_${storeSlug}`;
      const cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
      cart.push({
          product_id: product.id,
          name: product.name,
          price: parseFloat(product.price),
          size: selectedSize,
          color: selectedColor,
          quantity: 1
      });
      localStorage.setItem(cartKey, JSON.stringify(cart));

      showToast('Produto adicionado ao carrinho!', 'success');
      
      // Delay navigation slightly for better UX
      setTimeout(() => {
          navigate(`/${storeSlug}/cart`);
      }, 1000);
  };

  if (!product) return <div className="p-8 text-center">Carregando...</div>;

  // Prepare images array for carousel
  const carouselImages = [
    product.main_image_url,
    ...(product.images || [])
  ].filter((img): img is string => !!img);

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
            <div className="aspect-square w-full bg-gray-100 rounded-xl overflow-hidden shadow-inner">
                <ImageCarousel images={carouselImages} alt={product.name} />
            </div>

            {/* Product Info Section */}
            <div>
                <h1 className="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{product.name}</h1>
                
                <div className="mb-6">
                    <span className="text-3xl font-extrabold text-purple-700">
                        R$ {parseFloat(product.price).toFixed(2).replace('.',',')}
                    </span>
                    <span className="text-sm text-gray-500 ml-2">/ unidade</span>
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

                    {/* Add to Cart Button */}
                    <button 
                        onClick={addToCart} 
                        className="w-full bg-green-600 hover:bg-green-700 text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-green-600/30 transition-all active:scale-95 flex items-center justify-center gap-2"
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