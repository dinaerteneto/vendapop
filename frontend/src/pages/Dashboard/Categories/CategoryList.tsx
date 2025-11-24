import React, { useEffect, useState } from 'react';
import api from '../../../services/api';
import { Link } from 'react-router-dom';
import { toast } from 'react-toastify';
import ConfirmationModal from '../../../components/ui/ConfirmationModal';
import Pagination from '../../../components/ui/Pagination';
import SortableHeader from '../../../components/ui/SortableHeader';

interface Category {
  id: number;
  uuid: string;
  slug: string;
  name: string;
  image_url?: string;
  is_active: boolean;
}

interface PaginationData {
  current_page: number;
  last_page: number;
  total: number;
  per_page: number;
  data: Category[];
}

const CategoryList: React.FC = () => {
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);
  const [deleteUuid, setDeleteUuid] = useState<string | null>(null);
  const [pagination, setPagination] = useState<PaginationData | null>(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [perPage, setPerPage] = useState(20);
  const [sortBy, setSortBy] = useState('id');
  const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('desc');

  useEffect(() => {
    loadCategories(currentPage, perPage, sortBy, sortDirection);
  }, [currentPage, perPage, sortBy, sortDirection]);

  const loadCategories = async (page: number = 1, perPageValue: number = 20, sortByValue: string = 'id', sortDir: 'asc' | 'desc' = 'desc') => {
    try {
      setLoading(true);
      const response = await api.get(`/admin/categories?page=${page}&per_page=${perPageValue}&sort_by=${sortByValue}&sort_direction=${sortDir}`);
      const data = response.data;
      
      if (data.data) {
        // Resposta paginada
        setCategories(data.data);
        setPagination({
          current_page: data.current_page,
          last_page: data.last_page,
          total: data.total,
          per_page: data.per_page,
          data: data.data,
        });
      } else {
        // Fallback para resposta não paginada
        setCategories(Array.isArray(data) ? data : []);
        setPagination(null);
      }
    } catch (error) {
      console.error('Erro ao carregar categorias', error);
      toast.error('Erro ao carregar categorias.');
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
    setCurrentPage(1);
  };

  const handlePerPageChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const newPerPage = parseInt(e.target.value);
    setPerPage(newPerPage);
    setCurrentPage(1);
  };

  const confirmDelete = (uuid: string) => setDeleteUuid(uuid);

  const executeDelete = async () => {
      if (!deleteUuid) return;
      try {
          await api.delete(`/admin/categories/${deleteUuid}`);
          toast.success('Categoria excluída com sucesso!');
          // Recarregar categorias na página atual
          await loadCategories(currentPage, perPage, sortBy, sortDirection);
      } catch (error: any) {
          console.error(error);
          const msg = error.response?.data?.message || 'Erro ao excluir categoria.';
          toast.error(msg);
      } finally {
          setDeleteUuid(null);
      }
  }

  if (loading) return <div>Carregando...</div>;

  return (
    <div>
      <ConfirmationModal 
        isOpen={!!deleteUuid}
        title="Excluir Categoria"
        message="Tem certeza que deseja excluir esta categoria? Produtos associados podem ficar sem categoria."
        onConfirm={executeDelete}
        onCancel={() => setDeleteUuid(null)}
      />

      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Categorias</h1>
        <div className="flex items-center gap-4">
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
          <Link 
            to="/admin/categories/new" 
            className="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors shadow-sm"
          >
            + Nova Categoria
          </Link>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
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
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th className="px-6 py-3 text-right">Ações</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {categories.map((category) => (
              <tr key={category.id}>
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden border">
                     {category.image_url ? (
                        <img src={category.image_url} alt={category.name} className="h-full w-full object-cover" />
                     ) : (
                        <span className="text-xs font-bold text-gray-400">{category.name.substring(0,2).toUpperCase()}</span>
                     )}
                  </div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{category.name}</td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${category.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                    {category.is_active ? 'Ativa' : 'Inativa'}
                  </span>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link to={`/admin/categories/${category.uuid}`} className="text-indigo-600 hover:text-indigo-900 mr-4">Editar</Link>
                  <button onClick={() => confirmDelete(category.uuid)} className="text-red-600 hover:text-red-900">Excluir</button>
                </td>
              </tr>
            ))}
            {categories.length === 0 && (
                <tr>
                    <td colSpan={4} className="px-6 py-4 text-center text-gray-500">
                        Nenhuma categoria cadastrada.
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

export default CategoryList;
