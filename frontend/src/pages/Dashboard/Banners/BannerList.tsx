import React, { useEffect, useState } from 'react';
import api from '../../../services/api';
import { Link } from 'react-router-dom';
import { toast } from 'react-toastify';
import ConfirmationModal from '../../../components/ui/ConfirmationModal';

interface Banner {
  id: number;
  image_url: string;
  link_url?: string | null;
  title?: string | null;
  description?: string | null;
  order: number;
  is_active: boolean;
}

const BannerList: React.FC = () => {
  const [banners, setBanners] = useState<Banner[]>([]);
  const [loading, setLoading] = useState(true);
  const [deleteId, setDeleteId] = useState<number | null>(null);
  const [draggedIndex, setDraggedIndex] = useState<number | null>(null);
  const [isReordering, setIsReordering] = useState(false);

  useEffect(() => {
    loadBanners();
  }, []);

  const loadBanners = async () => {
    try {
      setLoading(true);
      const response = await api.get('/admin/banners');
      setBanners(response.data);
    } catch (error) {
      console.error('Erro ao carregar banners', error);
      toast.error('Erro ao carregar banners.');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async () => {
    if (!deleteId) return;

    try {
      await api.delete(`/admin/banners/${deleteId}`);
      toast.success('Banner removido com sucesso!');
      loadBanners();
      setDeleteId(null);
    } catch (error) {
      console.error('Erro ao remover banner', error);
      toast.error('Erro ao remover banner.');
    }
  };

  const handleToggleActive = async (banner: Banner) => {
    try {
      await api.put(`/admin/banners/${banner.id}`, {
        is_active: !banner.is_active,
      });
      toast.success(`Banner ${!banner.is_active ? 'ativado' : 'desativado'} com sucesso!`);
      loadBanners();
    } catch (error) {
      console.error('Erro ao atualizar banner', error);
      toast.error('Erro ao atualizar banner.');
    }
  };

  const handleDragStart = (index: number) => {
    setDraggedIndex(index);
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
  };

  const handleDrop = async (e: React.DragEvent, dropIndex: number) => {
    e.preventDefault();
    if (draggedIndex === null || draggedIndex === dropIndex) {
      setDraggedIndex(null);
      return;
    }

    const newBanners = [...banners];
    const dragged = newBanners[draggedIndex];
    newBanners.splice(draggedIndex, 1);
    newBanners.splice(dropIndex, 0, dragged);

    // Update order values
    const updatedBanners = newBanners.map((banner, index) => ({
      ...banner,
      order: index,
    }));

    setBanners(updatedBanners);
    setDraggedIndex(null);

    // Save to backend
    try {
      setIsReordering(true);
      await api.post('/admin/banners/update-order', {
        banners: updatedBanners.map(b => ({ id: b.id, order: b.order })),
      });
      toast.success('Ordem dos banners atualizada!');
    } catch (error) {
      console.error('Erro ao atualizar ordem', error);
      toast.error('Erro ao atualizar ordem dos banners.');
      // Reload on error
      loadBanners();
    } finally {
      setIsReordering(false);
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="text-gray-500">Carregando banners...</div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-800">Banners Rotativos</h1>
        <Link
          to="/admin/banners/new"
          className="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition"
        >
          + Adicionar Banner
        </Link>
      </div>

      {banners.length === 0 ? (
        <div className="text-center py-12 bg-gray-50 rounded-lg">
          <p className="text-gray-500 mb-4">Nenhum banner cadastrado ainda.</p>
          <Link
            to="/admin/banners/new"
            className="inline-block px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition"
          >
            Criar Primeiro Banner
          </Link>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {banners.map((banner, index) => (
            <div
              key={banner.id}
              draggable
              onDragStart={() => handleDragStart(index)}
              onDragOver={handleDragOver}
              onDrop={(e) => handleDrop(e, index)}
              className={`bg-white rounded-lg shadow-md overflow-hidden border-2 transition-all cursor-move ${
                draggedIndex === index 
                  ? 'border-blue-500 opacity-50' 
                  : 'border-gray-200 hover:border-blue-300'
              } ${isReordering ? 'opacity-75' : ''}`}
            >
              <div className="relative" style={{ aspectRatio: '16/9' }}>
                <img
                  src={banner.image_url}
                  alt={banner.title || 'Banner'}
                  className="w-full h-full object-cover"
                />
                <div className="absolute top-2 right-2">
                  <span
                    className={`px-2 py-1 rounded text-xs font-semibold ${
                      banner.is_active
                        ? 'bg-green-500 text-white'
                        : 'bg-gray-400 text-white'
                    }`}
                  >
                    {banner.is_active ? 'Ativo' : 'Inativo'}
                  </span>
                </div>
              </div>
              <div className="p-4">
                {banner.title && (
                  <h3 className="font-semibold text-gray-800 mb-1">{banner.title}</h3>
                )}
                {banner.link_url && (
                  <p className="text-sm text-gray-600 mb-2 truncate">
                    Link: {banner.link_url}
                  </p>
                )}
                <div className="flex items-center gap-2 mb-3">
                  <span className="text-xs text-gray-500">Ordem: {banner.order}</span>
                  <span className="text-xs text-blue-600 font-medium">🔄 Arraste para reordenar</span>
                </div>
                <div className="flex gap-2">
                  <Link
                    to={`/admin/banners/${banner.id}`}
                    className="flex-1 px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition text-center text-sm"
                  >
                    Editar
                  </Link>
                  <button
                    onClick={() => handleToggleActive(banner)}
                    className={`flex-1 px-3 py-2 rounded transition text-sm ${
                      banner.is_active
                        ? 'bg-yellow-500 text-white hover:bg-yellow-600'
                        : 'bg-green-500 text-white hover:bg-green-600'
                    }`}
                  >
                    {banner.is_active ? 'Desativar' : 'Ativar'}
                  </button>
                  <button
                    onClick={() => setDeleteId(banner.id)}
                    className="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition text-sm"
                  >
                    Excluir
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      <ConfirmationModal
        isOpen={deleteId !== null}
        onCancel={() => setDeleteId(null)}
        onConfirm={handleDelete}
        title="Confirmar Exclusão"
        message="Tem certeza que deseja remover este banner? Esta ação não pode ser desfeita."
      />
    </div>
  );
};

export default BannerList;

