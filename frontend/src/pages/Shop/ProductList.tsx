import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../../services/api';

interface Product {
  id: number;
  name: string;
  price: string;
  main_image_url: string | null;
}

const ProductList: React.FC = () => {
  const { storeSlug } = useParams();
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchProducts = async () => {
      try {
        const { data } = await api.get(`/${storeSlug}/products`);
        setProducts(data);
      } catch (error) {
        console.error("Erro ao buscar produtos", error);
      } finally {
        setLoading(false);
      }
    };
    if (storeSlug) fetchProducts();
  }, [storeSlug]);

  if (loading) return <div className="p-8 text-center">Carregando catálogo...</div>;

  return (
    <div>
      <div className="mb-6 flex justify-between items-center">
         <h2 className="text-lg font-semibold">Produtos</h2>
         {/* Filtro? */}
      </div>

      {products.length === 0 ? (
          <p className="text-center text-gray-500">Nenhum produto encontrado.</p>
      ) : (
        <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
            {products.map((p) => (
                <Link to={`/${storeSlug}/product/${p.id}`} key={p.id} className="group block overflow-hidden rounded-lg bg-white shadow-md transition hover:shadow-lg">
                    <div className="aspect-square w-full overflow-hidden bg-gray-200">
                        {p.main_image_url ? (
                            <img src={p.main_image_url} alt={p.name} className="h-full w-full object-cover transition group-hover:scale-105" />
                        ) : (
                            <div className="flex h-full items-center justify-center text-gray-400">Sem foto</div>
                        )}
                    </div>
                    <div className="p-3">
                        <h3 className="text-sm font-medium text-gray-900 truncate">{p.name}</h3>
                        <p className="mt-1 text-base font-bold text-purple-600">R$ {parseFloat(p.price).toFixed(2).replace('.',',')}</p>
                        <button className="mt-3 w-full rounded bg-purple-600 py-1.5 text-xs font-medium text-white opacity-90 hover:opacity-100">
                            Ver Detalhes
                        </button>
                    </div>
                </Link>
            ))}
        </div>
      )}

      {/* Botão flutuante do carrinho */}
      <div className="fixed bottom-4 right-4">
          <Link to={`/${storeSlug}/cart`} className="flex h-14 w-14 items-center justify-center rounded-full bg-green-500 text-white shadow-lg shadow-green-500/30">
              🛒
          </Link>
      </div>
    </div>
  );
};

export default ProductList;
