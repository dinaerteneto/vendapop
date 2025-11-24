import React, { useEffect, useState, useCallback } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../../../services/api';
import { toast } from 'react-toastify';
import { useDropzone } from 'react-dropzone';

const BannerForm: React.FC = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEditMode = !!id;

  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    link_url: '',
    order: 0,
    is_active: true,
    image_url: '',
  });

  const [imageMode, setImageMode] = useState<'url' | 'file'>('url');
  const [imageFile, setImageFile] = useState<File | null>(null);
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);

  useEffect(() => {
    if (isEditMode) {
      loadBanner(id);
    }
  }, [id]);

  const loadBanner = async (bannerId: string) => {
    setLoading(true);
    try {
      const { data } = await api.get(`/admin/banners/${bannerId}`);
      setFormData({
        title: data.title || '',
        description: data.description || '',
        link_url: data.link_url || '',
        order: data.order || 0,
        is_active: !!data.is_active,
        image_url: data.image_url || '',
      });
      if (data.image_url) {
        setPreviewUrl(data.image_url);
        // Se for URL externa, usar modo URL, senão modo file
        setImageMode(data.is_external ? 'url' : 'file');
      }
    } catch (error) {
      console.error('Erro ao carregar banner', error);
      toast.error('Erro ao carregar detalhes do banner.');
      navigate('/admin/banners');
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value, type } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? (e.target as HTMLInputElement).checked : 
              name === 'order' ? parseInt(value) || 0 : value
    }));

    // Atualizar preview quando image_url mudar
    if (name === 'image_url' && imageMode === 'url' && value) {
      setPreviewUrl(value);
    }
  };

  const onDrop = useCallback((acceptedFiles: File[]) => {
    const file = acceptedFiles[0];
    if (file) {
      setImageFile(file);
      setPreviewUrl(URL.createObjectURL(file));
      setFormData(prev => ({ ...prev, image_url: '' }));
      setImageMode('file');
    }
  }, []);

  const { getRootProps, getInputProps, isDragActive } = useDropzone({ 
    onDrop,
    accept: { 'image/*': [] },
    multiple: false
  });

  const handleImageModeChange = (mode: 'url' | 'file') => {
    setImageMode(mode);
    if (mode === 'url') {
      setImageFile(null);
      if (formData.image_url) {
        setPreviewUrl(formData.image_url);
      }
    } else {
      setFormData(prev => ({ ...prev, image_url: '' }));
      if (imageFile) {
        setPreviewUrl(URL.createObjectURL(imageFile));
      } else {
        setPreviewUrl(null);
      }
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    // Validar que pelo menos uma imagem foi fornecida
    if (imageMode === 'file' && !imageFile && !isEditMode) {
      toast.error('Por favor, selecione uma imagem para o banner.');
      return;
    }
    if (imageMode === 'url' && !formData.image_url && !isEditMode) {
      toast.error('Por favor, informe a URL da imagem.');
      return;
    }

    setLoading(true);

    try {
      const data = new FormData();
      data.append('title', formData.title);
      data.append('description', formData.description);
      data.append('link_url', formData.link_url);
      data.append('order', formData.order.toString());
      data.append('is_active', formData.is_active ? '1' : '0');
      
      if (imageMode === 'file' && imageFile) {
        data.append('image', imageFile);
      } else if (imageMode === 'url' && formData.image_url) {
        data.append('image_url', formData.image_url);
      }

      if (isEditMode) {
        if (imageMode === 'file' && imageFile) {
          data.append('_method', 'PUT');
          await api.post(`/admin/banners/${id}`, data, {
            headers: { 'Content-Type': 'multipart/form-data' }
          });
        } else {
          // Se não há arquivo, enviar como JSON
          await api.put(`/admin/banners/${id}`, {
            image_url: imageMode === 'url' ? formData.image_url : undefined,
            link_url: formData.link_url,
            title: formData.title,
            description: formData.description,
            order: formData.order,
            is_active: formData.is_active,
          });
        }
        toast.success('Banner atualizado com sucesso!');
      } else {
        await api.post('/admin/banners', data, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        toast.success('Banner criado com sucesso!');
      }
      
      navigate('/admin/banners');
    } catch (error: any) {
      console.error('Erro ao salvar banner', error);
      const errorMessage = error.response?.data?.message || 'Erro ao salvar banner.';
      toast.error(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  if (loading && isEditMode) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="text-gray-500">Carregando...</div>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-800">
          {isEditMode ? 'Editar Banner' : 'Novo Banner'}
        </h1>
        <button
          onClick={() => navigate('/admin/banners')}
          className="px-4 py-2 text-gray-600 hover:text-gray-800"
        >
          ← Voltar
        </button>
      </div>

      <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow-md p-6 space-y-6">
        {/* Modo de Imagem (URL ou Upload) */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Tipo de Imagem
          </label>
          <div className="flex gap-4">
            <button
              type="button"
              onClick={() => handleImageModeChange('url')}
              className={`px-4 py-2 rounded-lg transition ${
                imageMode === 'url'
                  ? 'bg-purple-600 text-white'
                  : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
              }`}
            >
              URL Externa
            </button>
            <button
              type="button"
              onClick={() => handleImageModeChange('file')}
              className={`px-4 py-2 rounded-lg transition ${
                imageMode === 'file'
                  ? 'bg-purple-600 text-white'
                  : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
              }`}
            >
              Upload de Arquivo
            </button>
          </div>
        </div>

        {/* Upload de Imagem ou URL */}
        {imageMode === 'file' ? (
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Imagem do Banner *
            </label>
            <div
              {...getRootProps()}
              className={`border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition ${
                isDragActive
                  ? 'border-purple-500 bg-purple-50'
                  : 'border-gray-300 hover:border-purple-400'
              }`}
            >
              <input {...getInputProps()} />
              {previewUrl ? (
                <div className="space-y-4">
                  <img
                    src={previewUrl}
                    alt="Preview"
                    className="max-w-full max-h-64 mx-auto rounded-lg"
                  />
                  <p className="text-sm text-gray-600">
                    Clique ou arraste uma nova imagem para substituir
                  </p>
                </div>
              ) : (
                <div>
                  <p className="text-gray-600">
                    {isDragActive
                      ? 'Solte a imagem aqui'
                      : 'Clique ou arraste uma imagem aqui'}
                  </p>
                  <p className="text-sm text-gray-500 mt-2">
                    PNG, JPG até 5MB
                  </p>
                </div>
              )}
            </div>
          </div>
        ) : (
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              URL da Imagem *
            </label>
            <input
              type="url"
              name="image_url"
              value={formData.image_url}
              onChange={handleChange}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              placeholder="https://exemplo.com/imagem.jpg"
            />
            {previewUrl && (
              <div className="mt-4">
                <img
                  src={previewUrl}
                  alt="Preview"
                  className="max-w-full max-h-64 rounded-lg"
                />
              </div>
            )}
            <button
              type="button"
              onClick={() => {
                if (formData.image_url) {
                  setPreviewUrl(formData.image_url);
                }
              }}
              className="mt-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm"
            >
              Visualizar Imagem
            </button>
          </div>
        )}

        {/* Título */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Título (opcional)
          </label>
          <input
            type="text"
            name="title"
            value={formData.title}
            onChange={handleChange}
            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            placeholder="Ex: Promoção de Verão"
          />
        </div>

        {/* Descrição */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Descrição (opcional)
          </label>
          <textarea
            name="description"
            value={formData.description}
            onChange={handleChange}
            rows={3}
            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            placeholder="Ex: Aproveite nossos descontos especiais"
          />
        </div>

        {/* Link URL */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Link ao clicar (opcional)
          </label>
          <input
            type="url"
            name="link_url"
            value={formData.link_url}
            onChange={handleChange}
            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            placeholder="https://exemplo.com"
          />
          <p className="text-xs text-gray-500 mt-1">
            URL para onde o usuário será redirecionado ao clicar no banner
          </p>
        </div>

        {/* Ordem */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Ordem de Exibição
          </label>
          <input
            type="number"
            name="order"
            value={formData.order}
            onChange={handleChange}
            min="0"
            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
          />
          <p className="text-xs text-gray-500 mt-1">
            Banners com menor número aparecem primeiro
          </p>
        </div>

        {/* Ativo */}
        <div className="flex items-center">
          <input
            type="checkbox"
            name="is_active"
            id="is_active"
            checked={formData.is_active}
            onChange={handleChange}
            className="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
          />
          <label htmlFor="is_active" className="ml-2 text-sm font-medium text-gray-700">
            Banner ativo
          </label>
        </div>

        {/* Botões */}
        <div className="flex gap-4 pt-4">
          <button
            type="submit"
            disabled={loading}
            className="flex-1 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition disabled:opacity-50"
          >
            {loading ? 'Salvando...' : isEditMode ? 'Atualizar Banner' : 'Criar Banner'}
          </button>
          <button
            type="button"
            onClick={() => navigate('/admin/banners')}
            className="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"
          >
            Cancelar
          </button>
        </div>
      </form>
    </div>
  );
};

export default BannerForm;
