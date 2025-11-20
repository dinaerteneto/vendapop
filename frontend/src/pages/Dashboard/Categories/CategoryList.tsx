import React, { useEffect, useState } from 'react';
import api from '../../../services/api';
import { Link } from 'react-router-dom';
import { toast } from 'react-toastify';
import ConfirmationModal from '../../../components/ui/ConfirmationModal';

interface Category {
  id: number;
  name: string;
  image_url?: string;
  is_active: boolean;
}

const CategoryList: React.FC = () => {
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);
  const [deleteId, setDeleteId] = useState<number | null>(null);

  useEffect(() => {
    loadCategories();
  }, []);

  const loadCategories = async () => {
    try {
      const { data } = await api.get('/admin/categories');
      setCategories(data);
    } catch (error) {
      console.error('Erro ao carregar categorias', error);
      toast.error('Erro ao carregar categorias.');
    } finally {
        setLoading(false);
    }
  };

  const confirmDelete = (id: number) => setDeleteId(id);

  const executeDelete = async () => {
      if (!deleteId) return;
      try {
          await api.delete(`/admin/categories/${deleteId}`);
          setCategories(categories.filter(c => c.id !== deleteId));
          toast.success('Categoria excluída com sucesso!');
      } catch (error: any) {
          console.error(error);
          const msg = error.response?.data?.message || 'Erro ao excluir categoria.';
          toast.error(msg);
      } finally {
          setDeleteId(null);
      }
  }

  if (loading) return <div>Carregando...</div>;

  return (
    <div>
      <ConfirmationModal 
        isOpen={!!deleteId}
        title="Excluir Categoria"
        message="Tem certeza que deseja excluir esta categoria? Produtos associados podem ficar sem categoria."
        onConfirm={executeDelete}
        onCancel={() => setDeleteId(null)}
      />

      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Categorias</h1>
        <Link 
          to="/admin/categories/new" 
          className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
        >
          + Nova Categoria
        </Link>
      </div>

      <div className="bg-white rounded-lg shadow overflow-hidden">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Imagem</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
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
                  <Link to={`/admin/categories/${category.id}`} className="text-indigo-600 hover:text-indigo-900 mr-4">Editar</Link>
                  <button onClick={() => confirmDelete(category.id)} className="text-red-600 hover:text-red-900">Excluir</button>
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
      </div>
    </div>
  );
};

export default CategoryList;
