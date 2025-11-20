import React, { useEffect, useState } from 'react';
import api from '../../../services/api';
import { Link } from 'react-router-dom';
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
  const [deleteId, setDeleteId] = useState<number | null>(null);
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

  const confirmDelete = (id: number) => {
      setDeleteId(id);
  };

  const executeDelete = async () => {
      if (!deleteId) return;
      try {
          await api.delete(`/admin/products/${deleteId}`);
          toast.success('Produto excluído com sucesso!');
          // Recarregar produtos na página atual
          await loadProducts(currentPage, perPage, sortBy, sortDirection);
      } catch (error) {
          console.error(error);
          toast.error('Erro ao excluir produto.');
      } finally {
          setDeleteId(null);
      }
  }

  if (loading) return <div>Carregando...</div>;

  return (
    <div>
      <ConfirmationModal 
        isOpen={!!deleteId}
        title="Excluir Produto"
        message="Tem certeza que deseja excluir este produto? Esta ação não pode ser desfeita."
        onConfirm={executeDelete}
        onCancel={() => setDeleteId(null)}
      />

      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Produtos</h1>
        <div className="flex items-center gap-4">
          <div className="flex items-center gap-2">
            <label htmlFor="perPage" className="text-sm text-gray-600">Itens por página:</label>
            <select
              id="perPage"
              value={perPage}
              onChange={handlePerPageChange}
              className="border border-gray-300 rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
            </select>
          </div>
          <Link 
            to="/admin/products/new" 
            className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
          >
            + Novo Produto
          </Link>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow overflow-hidden">
        <table className="min-w-full divide-y divide-gray-200">
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
              <th className="px-6 py-3 text-right">Ações</th>
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
                  <Link to={`/admin/products/${product.id}`} className="text-indigo-600 hover:text-indigo-900 mr-4">Editar</Link>
                  <button onClick={() => confirmDelete(product.id)} className="text-red-600 hover:text-red-900">Excluir</button>
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
