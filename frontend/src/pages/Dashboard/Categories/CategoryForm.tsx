import React, { useEffect, useState, useCallback } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../../../services/api';
import { SEOHead } from '../../../components/common/SEOHead';
import { toast } from 'react-toastify';
import { useDropzone } from 'react-dropzone';

const CategoryForm: React.FC = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEditMode = !!id;

  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    image_url: '',
    is_active: true,
  });

  const [imageMode, setImageMode] = useState<'url' | 'file'>('url');
  const [imageFile, setImageFile] = useState<File | null>(null);
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);

  useEffect(() => {
    if (isEditMode) {
      loadCategory(id);
    }
  }, [id]);

  const loadCategory = async (categoryId: string) => {
    setLoading(true);
    try {
      const { data } = await api.get(`/admin/categories/${categoryId}`);
      setFormData({
        name: data.name,
        image_url: data.image_url || '',
        is_active: !!data.is_active,
      });
      if (data.image_url) {
        setPreviewUrl(data.image_url);
        setImageMode(data.image_url.includes('http') ? 'url' : 'file'); // Or just default to URL for edit
        setImageMode('url');
      }
    } catch (error) {
      console.error('Erro ao carregar categoria', error);
      toast.error('Erro ao carregar detalhes da categoria.');
      navigate('/admin/categories');
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };

  // Dropzone
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

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
        if (imageMode === 'file' && imageFile) {
            const data = new FormData();
            data.append('name', formData.name);
            data.append('is_active', formData.is_active ? '1' : '0');
            data.append('image', imageFile);
            
            if (isEditMode) {
                data.append('_method', 'PUT');
                await api.post(`/admin/categories/${id}`, data, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                toast.success('Categoria atualizada com sucesso!');
            } else {
                await api.post('/admin/categories', data, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                toast.success('Categoria criada com sucesso!');
            }
        } else {
            const payload = {
                ...formData,
                image_url: imageMode === 'url' ? formData.image_url : null
            };

            if (isEditMode) {
                await api.put(`/admin/categories/${id}`, payload);
                toast.success('Categoria atualizada com sucesso!');
            } else {
                await api.post('/admin/categories', payload);
                toast.success('Categoria criada com sucesso!');
            }
        }
        navigate('/admin/categories');
    } catch (error) {
        console.error(error);
        toast.error('Erro ao salvar categoria.');
    } finally {
        setLoading(false);
    }
  };

  return (
    <div className="max-w-2xl mx-auto mb-10">
      <SEOHead title={isEditMode ? 'Editar Categoria — VendaPop' : 'Nova Categoria — VendaPop'} noIndex />
      <h1 className="text-2xl font-bold text-gray-800 mb-6">
        {isEditMode ? 'Editar Categoria' : 'Nova Categoria'}
      </h1>

      <div className="bg-white rounded-lg shadow p-6">
        <form onSubmit={handleSubmit}>
          <div className="space-y-6">
            
            {/* Nome */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Nome da Categoria</label>
              <input
                type="text"
                name="name"
                value={formData.name}
                onChange={handleChange}
                required
                className="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              />
            </div>

            {/* Imagem */}
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Imagem de Capa</label>
                
                <div className="flex items-center gap-4 mb-3">
                    <label className="inline-flex items-center cursor-pointer">
                        <input 
                            type="radio" 
                            className="form-radio text-blue-600" 
                            name="imageMode" 
                            value="url" 
                            checked={imageMode === 'url'} 
                            onChange={() => setImageMode('url')}
                        />
                        <span className="ml-2">URL da Imagem</span>
                    </label>
                    <label className="inline-flex items-center cursor-pointer">
                        <input 
                            type="radio" 
                            className="form-radio text-blue-600" 
                            name="imageMode" 
                            value="file" 
                            checked={imageMode === 'file'} 
                            onChange={() => setImageMode('file')}
                        />
                        <span className="ml-2">Upload de Arquivo</span>
                    </label>
                </div>

                {imageMode === 'url' ? (
                    <input
                        type="url"
                        name="image_url"
                        value={formData.image_url}
                        onChange={(e) => {
                            handleChange(e);
                            setPreviewUrl(e.target.value);
                        }}
                        placeholder="https://..."
                        className="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    />
                ) : (
                    <div {...getRootProps()} className={`border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-colors ${isDragActive ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-blue-400'}`}>
                        <input {...getInputProps()} />
                        {isDragActive ? (
                            <p className="text-blue-500">Solte a imagem aqui...</p>
                        ) : (
                            <p className="text-gray-500">Arraste e solte uma imagem aqui, ou clique para selecionar</p>
                        )}
                        <p className="text-xs text-gray-400 mt-2">PNG, JPG, GIF até 2MB</p>
                    </div>
                )}

                {/* Preview */}
                {previewUrl && (
                    <div className="mt-4 h-48 w-full rounded-lg border bg-gray-50 overflow-hidden relative">
                        <img src={previewUrl} alt="Preview" className="h-full w-full object-cover" />
                    </div>
                )}
            </div>

            {/* Ativo */}
            <div className="flex items-center">
              <input
                type="checkbox"
                name="is_active"
                id="is_active"
                checked={formData.is_active}
                onChange={handleChange}
                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label htmlFor="is_active" className="ml-2 block text-sm text-gray-900 cursor-pointer">
                Categoria Ativa
              </label>
            </div>

          </div>

          <div className="flex justify-end gap-3 mt-6">
            <button
              type="button"
              onClick={() => navigate('/admin/categories')}
              className="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300 transition"
            >
              Cancelar
            </button>
            <button
              type="submit"
              disabled={loading}
              className="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 transition disabled:opacity-50"
            >
              {loading ? 'Salvando...' : (isEditMode ? 'Atualizar' : 'Criar Categoria')}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default CategoryForm;

