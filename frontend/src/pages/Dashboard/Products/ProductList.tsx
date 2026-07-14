import React, { useEffect, useState } from 'react';
import api from '../../../services/api';
import { Link } from 'react-router-dom';
import { SEOHead } from '../../../components/common/SEOHead';
import { toast } from 'react-toastify';
import ConfirmationModal from '../../../components/ui/ConfirmationModal';
import Pagination from '../../../components/ui/Pagination';
import SortableHeader from '../../../components/ui/SortableHeader';

interface Category {
  id: number;
  name: string;
}

interface Product {
  id: number;
  uuid: string;
  slug: string;
  name: string;
  price: number;
  promotional_price?: number;
  is_active: boolean;
  main_image_url: string;
  category?: Category;
}

interface PaginationData {
  current_page: number;
  last_page: number;
  total: number;
  per_page: number;
  data: Product[];
}

const ProductList: React.FC = () => {
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [deleteUuid, setDeleteUuid] = useState<string | null>(null);
  const [pagination, setPagination] = useState<PaginationData | null>(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [perPage, setPerPage] = useState(20);
  const [sortBy, setSortBy] = useState('id');
  const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('desc');

  useEffect(() => {
    loadProducts(currentPage, perPage, sortBy, sortDirection);
  }, [currentPage, perPage, sortBy, sortDirection]);

  const loadProducts = async (page: number = 1, perPageValue: number = 20, sortByValue: string = 'id', sortDir: 'asc' | 'desc' = 'desc') => {
    try {
      setLoading(true);
      const response = await api.get(`/admin/products?page=${page}&per_page=${perPageValue}&sort_by=${sortByValue}&sort_direction=${sortDir}`);
      const data = response.data;
      
      if (data.data) {
        // Resposta paginada
        setProducts(data.data);
        setPagination({
          current_page: data.current_page,
          last_page: data.last_page,
          total: data.total,
          per_page: data.per_page,
          data: data.data,
        });
      } else {
        // Fallback para resposta não paginada
        setProducts(Array.isArray(data) ? data : []);
        setPagination(null);
      }
    } catch (error) {
      console.error('Erro ao carregar produtos', error);
      toast.error('Erro ao carregar produtos.');
    } finally {
      setLoading(false);
    }
  };

  const handlePageChange = (page: number) => {
    setCurrentPage(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleSort = (key: string, direction: 'asc' | 'desc') => {
    setSortBy(key);
    setSortDirection(direction);
    setCurrentPage(1); // Resetar para primeira página ao ordenar
  };

  const handlePerPageChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const newPerPage = parseInt(e.target.value);
    setPerPage(newPerPage);
    setCurrentPage(1); // Resetar para primeira página ao mudar tamanho
  };

  const confirmDelete = (uuid: string) => {
      setDeleteUuid(uuid);
  };

  const executeDelete = async () => {
      if (!deleteUuid) return;
      try {
          await api.delete(`/admin/products/${deleteUuid}`);
          toast.success('Produto excluído com sucesso!');
          // Recarregar produtos na página atual
          await loadProducts(currentPage, perPage, sortBy, sortDirection);
      } catch (error) {
          console.error(error);
          toast.error('Erro ao excluir produto.');
      } finally {
          setDeleteUuid(null);
      }
  }

  if (loading) return <div>Carregando...</div>;

  return (
    <div>
      <SEOHead title="Produtos — VendaPop" noIndex />
      <ConfirmationModal 
        isOpen={!!deleteUuid}
        title="Excluir Produto"
        message="Tem certeza que deseja excluir este produto? Esta ação não pode ser desfeita."
        onConfirm={executeDelete}
        onCancel={() => setDeleteUuid(null)}
      />

      <div className="flex flex-col gap-3 mb-6">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-bold text-gray-900">Produtos</h1>
          <Link 
            to="/admin/products/new" 
            className="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors shadow-sm"
          >
            + Novo Produto
          </Link>
        </div>
        <div className="flex items-center gap-2">
          <label htmlFor="perPage" className="text-sm text-gray-600">Itens por página:</label>
          <select
            id="perPage"
            value={perPage}
            onChange={handlePerPageChange}
            className="border border-gray-300 rounded-md px-3 py-1 text-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
          </select>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div className="divide-y divide-gray-200 md:hidden">
          {products.map((product) => (
            <div key={product.id} className="flex gap-3 p-4">
              <div className="h-16 w-16 shrink-0 rounded bg-gray-100 flex items-center justify-center overflow-hidden">
                {product.main_image_url ? (
                  <img src={product.main_image_url} alt={product.name} className="h-full w-full object-cover" />
                ) : (
                  <span className="text-xs text-gray-400">Sem IMG</span>
                )}
              </div>
              <div className="min-w-0 flex-1">
                <div className="flex items-start justify-between gap-2">
                  <p className="font-medium text-gray-900">{product.name}</p>
                  <span className={`shrink-0 px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${product.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                    {product.is_active ? 'Ativo' : 'Inativo'}
                  </span>
                </div>
                <p className="text-sm text-gray-500">{product.category?.name || '-'}</p>
                <div className="mt-1">
                  {product.promotional_price && parseFloat(String(product.promotional_price)) > 0 ? (
                    <div className="flex items-baseline gap-2">
                      <span className="text-green-600 font-bold">R$ {parseFloat(String(product.promotional_price)).toFixed(2).replace('.',',')}</span>
                      <span className="text-xs text-gray-400 line-through">R$ {parseFloat(String(product.price)).toFixed(2).replace('.',',')}</span>
                    </div>
                  ) : (
                    <span className="font-medium">R$ {parseFloat(String(product.price)).toFixed(2).replace('.',',')}</span>
                  )}
                </div>
                <div className="mt-2 flex gap-4 text-sm font-medium">
                  <Link to={`/admin/products/${product.uuid}`} className="text-indigo-600 hover:text-indigo-900">Editar</Link>
                  <button onClick={() => confirmDelete(product.uuid)} className="text-red-600 hover:text-red-900">Excluir</button>
                </div>
              </div>
            </div>
          ))}
          {products.length === 0 && (
            <div className="px-6 py-4 text-center text-gray-500">Nenhum produto cadastrado.</div>
          )}
        </div>
        <table className="hidden min-w-full divide-y divide-gray-200 md:table">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Imagem</th>
              <SortableHeader
                label="Nome"
                sortKey="name"
                currentSort={sortBy}
                currentDirection={sortDirection}
                onSort={handleSort}
              />
              <SortableHeader
                label="Categoria"
                sortKey="category"
                currentSort={sortBy}
                currentDirection={sortDirection}
                onSort={handleSort}
              />
              <SortableHeader
                label="Preço"
                sortKey="price"
                currentSort={sortBy}
                currentDirection={sortDirection}
                onSort={handleSort}
              />
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {products.map((product) => (
              <tr key={product.id}>
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="h-10 w-10 rounded bg-gray-100 flex items-center justify-center overflow-hidden">
                     {product.main_image_url ? (
                        <img src={product.main_image_url} alt={product.name} className="h-full w-full object-cover" />
                     ) : (
                        <span className="text-xs text-gray-400">Sem IMG</span>
                     )}
                  </div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{product.name}</td>
                <td className="px-6 py-4 whitespace-nowrap text-gray-500">{product.category?.name || '-'}</td>
                <td className="px-6 py-4 whitespace-nowrap">
                    {product.promotional_price && parseFloat(String(product.promotional_price)) > 0 ? (
                        <div className="flex flex-col">
                            <span className="text-green-600 font-bold">R$ {parseFloat(String(product.promotional_price)).toFixed(2).replace('.',',')}</span>
                            <span className="text-xs text-gray-400 line-through">R$ {parseFloat(String(product.price)).toFixed(2).replace('.',',')}</span>
                        </div>
                    ) : (
                        <span>R$ {parseFloat(String(product.price)).toFixed(2).replace('.',',')}</span>
                    )}
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${product.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                    {product.is_active ? 'Ativo' : 'Inativo'}
                  </span>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link to={`/admin/products/${product.uuid}`} className="text-indigo-600 hover:text-indigo-900 mr-4">Editar</Link>
                  <button onClick={() => confirmDelete(product.uuid)} className="text-red-600 hover:text-red-900">Excluir</button>
                </td>
              </tr>
            ))}
            {products.length === 0 && (
                <tr>
                    <td colSpan={6} className="px-6 py-4 text-center text-gray-500">
                        Nenhum produto cadastrado.
                    </td>
                </tr>
            )}
          </tbody>
        </table>
        {pagination && (
          <Pagination
            currentPage={pagination.current_page}
            lastPage={pagination.last_page}
            total={pagination.total}
            perPage={pagination.per_page}
            onPageChange={handlePageChange}
          />
        )}
      </div>
    </div>
  );
};

export default ProductList;
