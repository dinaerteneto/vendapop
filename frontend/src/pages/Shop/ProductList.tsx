import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../../services/api';
import CategoryList from '../../components/ecommerce/CategoryList';

interface Product {
  id: number;
  slug: string;
  name: string;
  price: string;
  promotional_price?: string | null;
  main_image_url: string | null;
  category_id: number;
  is_hot?: boolean;
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
  const [isOneColumn, setIsOneColumn] = useState(false);

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
                className="w-full px-4 py-3 rounded-full bg-gray-100 border-none focus:ring-2 focus:ring-purple-200 text-gray-700 outline-none transition-all"
             />
         </div>
         <button 
             onClick={() => setIsOneColumn(!isOneColumn)}
             className="md:hidden w-12 h-12 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 shadow-sm hover:bg-gray-200 transition-colors"
             aria-label="Alternar visualização"
         >
             {isOneColumn ? (
                 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-6 h-6">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                 </svg>
             ) : (
                 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-6 h-6">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
                 </svg>
             )}
         </button>
      </div>

      <h2 className="text-lg font-bold text-red-600 mb-4 flex items-center">
          Promoções
      </h2>

      {loading ? (
          <div className={`grid gap-4 ${isOneColumn ? 'grid-cols-1' : 'grid-cols-2'} md:grid-cols-3 lg:grid-cols-4`}>
              {[1,2,3,4].map(i => (
                  <div key={i} className="animate-pulse bg-gray-200 h-64 rounded-lg"></div>
              ))}
          </div>
      ) : products.length === 0 ? (
          <p className="text-center text-gray-500 py-10">Nenhum produto encontrado.</p>
      ) : (
        <div className={`grid gap-4 ${isOneColumn ? 'grid-cols-1' : 'grid-cols-2'} md:grid-cols-3 lg:grid-cols-4`}>
            {products.map((p) => (
                <Link to={`/${storeSlug}/product/${p.slug}`} key={p.id} className="group block overflow-hidden rounded-xl bg-gray-50 shadow-sm hover:shadow-md transition-all">
                    <div className="aspect-[3/4] w-full overflow-hidden bg-gray-200 relative">
                        {p.main_image_url ? (
                            <img src={p.main_image_url} alt={p.name} className="h-full w-full object-cover transition duration-500 group-hover:scale-110" />
                        ) : (
                            <div className="flex h-full items-center justify-center text-gray-400">Sem foto</div>
                        )}
                        
                        {!!p.is_hot && (
                            <div className="absolute top-2 right-2 bg-yellow-400 text-xs font-bold px-2 py-1 rounded text-black shadow-sm z-10">
                                HOT 🔥
                            </div>
                        )}
                    </div>
                    <div className="p-3">
                        <p className="text-xs text-gray-500 uppercase tracking-wider mb-1 truncate">
                            {categories.find(c => c.id === p.category_id)?.name || 'Coleção'}
                        </p>
                        <h3 className="text-sm font-medium text-gray-900 truncate leading-tight mb-2">{p.name}</h3>
                        
                        <div className="mb-3 flex flex-wrap items-baseline gap-2">
                            {p.promotional_price && parseFloat(p.promotional_price) > 0 ? (
                                <>
                                    <span className="text-xs text-gray-400 line-through">
                                        R$ {parseFloat(p.price).toFixed(2).replace('.',',')}
                                    </span>
                                    <span 
                                        className="text-lg font-extrabold"
                                        style={{ color: 'var(--theme-primary)' }}
                                    >
                                        R$ {parseFloat(p.promotional_price).toFixed(2).replace('.',',')}
                                    </span>
                                </>
                            ) : (
                                <span 
                                    className="text-lg font-extrabold"
                                    style={{ color: 'var(--theme-primary)' }}
                                >
                                    R$ {parseFloat(p.price).toFixed(2).replace('.',',')}
                                </span>
                            )}
                        </div>

                        <button 
                            className="w-full rounded-full py-2 text-sm font-bold text-white shadow hover:opacity-90 transition-colors uppercase tracking-wide"
                            style={{ backgroundColor: 'var(--theme-primary)' }}
                        >
                            Comprar
                        </button>
                    </div>
                </Link>
            ))}
        </div>
      )}
    </div>
  );
};

export default ProductList;
