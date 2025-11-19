import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../../services/api';
import CategoryList from '../../components/ecommerce/CategoryList';

interface Product {
  id: number;
  name: string;
  price: string;
  main_image_url: string | null;
  category_id: number;
}

interface Category {
  id: number;
  name: string;
  slug: string;
  image_url?: string;
}

const ProductList: React.FC = () => {
  const { storeSlug } = useParams();
  const [products, setProducts] = useState<Product[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);
  
  // Filters
  const [selectedCategoryId, setSelectedCategoryId] = useState<number | null>(null);
  const [searchTerm, setSearchTerm] = useState('');

  // Fetch Initial Data (Categories Only)
  useEffect(() => {
    if (!storeSlug) return;
    api.get(`/${storeSlug}/categories`).then(res => setCategories(res.data)).catch(console.error);
  }, [storeSlug]);

  // Fetch Products (Initial + Filtered)
  useEffect(() => {
      const fetchProducts = async () => {
          if (!storeSlug) return;
          setLoading(true);
          try {
              const params: { category_id?: number; search?: string } = {};
              if (selectedCategoryId) params.category_id = selectedCategoryId;
              if (searchTerm) params.search = searchTerm;

              const { data } = await api.get(`/${storeSlug}/products`, { params });
              setProducts(data);
          } catch (error) {
              console.error("Erro ao buscar produtos", error);
          } finally {
              setLoading(false);
          }
      };
      
      // Debounce search
      const timeoutId = setTimeout(() => {
          fetchProducts();
      }, 300);

      return () => clearTimeout(timeoutId);
  }, [storeSlug, selectedCategoryId, searchTerm]);


  return (
    <div>
      {/* Categories Section */}
      <CategoryList 
         categories={categories} 
         selectedCategoryId={selectedCategoryId}
         onSelectCategory={setSelectedCategoryId}
      />

      {/* Search & Filter Bar */}
      <div className="mb-6 flex gap-3">
         <div className="relative flex-grow">
             <input 
                type="text" 
                placeholder="Faça sua busca" 
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-4 pr-10 py-3 rounded-full bg-gray-100 border-none focus:ring-2 focus:ring-purple-200 text-gray-700 outline-none transition-all"
             />
             <div className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                 🔍
             </div>
         </div>
         <button className="w-12 h-12 flex items-center justify-center rounded-full bg-purple-900 text-white shadow-md hover:bg-purple-800 transition-colors">
             <span className="text-xl">⚙️</span> {/* Filter Icon */}
         </button>
      </div>

      <h2 className="text-lg font-bold text-red-600 mb-4 flex items-center">
          Promoções
      </h2>

      {loading ? (
          <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
              {[1,2,3,4].map(i => (
                  <div key={i} className="animate-pulse bg-gray-200 h-64 rounded-lg"></div>
              ))}
          </div>
      ) : products.length === 0 ? (
          <p className="text-center text-gray-500 py-10">Nenhum produto encontrado.</p>
      ) : (
        <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
            {products.map((p) => (
                <Link to={`/${storeSlug}/product/${p.id}`} key={p.id} className="group block overflow-hidden rounded-xl bg-gray-50 shadow-sm hover:shadow-md transition-all">
                    <div className="aspect-[3/4] w-full overflow-hidden bg-gray-200 relative">
                        {p.main_image_url ? (
                            <img src={p.main_image_url} alt={p.name} className="h-full w-full object-cover transition duration-500 group-hover:scale-110" />
                        ) : (
                            <div className="flex h-full items-center justify-center text-gray-400">Sem foto</div>
                        )}
                        {/* Discount Badge Mock */}
                        <div className="absolute top-2 right-2 bg-yellow-400 text-xs font-bold px-2 py-1 rounded text-black shadow-sm">
                            HOT
                        </div>
                    </div>
                    <div className="p-3">
                        <p className="text-xs text-gray-500 uppercase tracking-wider mb-1 truncate">
                            {categories.find(c => c.id === p.category_id)?.name || 'Coleção'}
                        </p>
                        <h3 className="text-sm font-medium text-gray-900 truncate leading-tight mb-2">{p.name}</h3>
                        
                        <div className="mb-3">
                            {/* Mocking original price for visual effect */}
                            <span className="text-xs text-gray-400 line-through mr-2">
                                R$ {(parseFloat(p.price) * 1.2).toFixed(2).replace('.',',')}
                            </span>
                            <span className="text-lg font-extrabold text-red-600 block sm:inline">
                                R$ {parseFloat(p.price).toFixed(2).replace('.',',')}
                            </span>
                        </div>

                        <button className="w-full rounded-full bg-red-600 py-2 text-sm font-bold text-white shadow hover:bg-red-700 transition-colors uppercase tracking-wide">
                            Comprar
                        </button>
                    </div>
                </Link>
            ))}
        </div>
      )}

      {/* Floating Cart Button (Bottom Left or adjusted) */}
      <div className="fixed bottom-4 right-4 z-40">
          <Link to={`/${storeSlug}/cart`} className="flex h-14 w-14 items-center justify-center rounded-full bg-purple-600 text-white shadow-lg shadow-purple-600/30 hover:scale-110 transition-transform">
              <span className="text-2xl">🛒</span>
          </Link>
      </div>
    </div>
  );
};

export default ProductList;