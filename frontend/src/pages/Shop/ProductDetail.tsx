import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../../services/api';

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

const ProductDetail: React.FC = () => {
  const { storeSlug, productId } = useParams();
  const navigate = useNavigate();
  const [product, setProduct] = useState<Product | null>(null);
  const [selectedSize, setSelectedSize] = useState<string>('');
  const [selectedColor, setSelectedColor] = useState<string>('');

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

  const addToCart = () => {
      if (!product) return;
      if (!selectedSize && product.sizes?.length > 0) {
          alert('Selecione um tamanho');
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

      navigate(`/${storeSlug}/cart`);
  };

  if (!product) return <div>Carregando...</div>;

  return (
    <div className="bg-white rounded-lg shadow p-4">
        <div className="aspect-square w-full bg-gray-200 rounded overflow-hidden mb-4">
             {product.main_image_url && <img src={product.main_image_url} alt={product.name} className="w-full h-full object-cover" />}
        </div>

        <h1 className="text-2xl font-bold mb-2">{product.name}</h1>
        <p className="text-xl text-purple-600 font-bold mb-4">R$ {parseFloat(product.price).toFixed(2).replace('.',',')}</p>

        <div className="mb-4">
            <p className="text-sm text-gray-500 mb-2">Tamanho:</p>
            <div className="flex gap-2">
                {product.sizes.map(s => (
                    <button
                        key={s}
                        onClick={() => setSelectedSize(s)}
                        className={`px-4 py-2 border rounded ${selectedSize === s ? 'bg-black text-white border-black' : 'border-gray-300'}`}
                    >
                        {s}
                    </button>
                ))}
            </div>
        </div>

        {product.colors && product.colors.length > 0 && (
            <div className="mb-6">
                <p className="text-sm text-gray-500 mb-2">Cor:</p>
                <div className="flex gap-2">
                    {product.colors.map(c => (
                        <button
                            key={c}
                            onClick={() => setSelectedColor(c)}
                            className={`px-4 py-2 border rounded ${selectedColor === c ? 'bg-black text-white border-black' : 'border-gray-300'}`}
                        >
                            {c}
                        </button>
                    ))}
                </div>
            </div>
        )}

        <p className="text-gray-600 mb-8">{product.description}</p>

        <button onClick={addToCart} className="w-full bg-green-600 text-white py-4 rounded font-bold text-lg shadow-lg hover:bg-green-700 transition sticky bottom-4">
            Adicionar ao Carrinho
        </button>
    </div>
  );
};

export default ProductDetail;
