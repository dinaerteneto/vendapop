import React, { useEffect, useState, useCallback } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../../../services/api';
import { toast } from 'react-toastify';
import { useDropzone } from 'react-dropzone';
import CurrencyInput from 'react-currency-input-field';
import ImageCropper from '../../../components/ui/ImageCropper';

interface Category {
  id: number;
  name: string;
}

interface ProductImage {
  id?: number;
  url: string;
  is_main: boolean;
  is_external: boolean;
  path?: string | null;
}

const ProductForm: React.FC = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEditMode = !!id;

  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(false);

  const [imageMode, setImageMode] = useState<'url' | 'file'>('url');
  const [imageFile, setImageFile] = useState<File | null>(null);
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);

  // Image Cropping
  const [showCropper, setShowCropper] = useState(false);
  const [imageToCrop, setImageToCrop] = useState<string | null>(null);

  // All product images (main + gallery)
  const [productImages, setProductImages] = useState<ProductImage[]>([]);
  const [newGalleryUrl, setNewGalleryUrl] = useState('');
  const [draggedIndex, setDraggedIndex] = useState<number | null>(null);

  const [formData, setFormData] = useState({
    name: '',
    price: '',
    promotional_price: '',
    category_id: '',
    sizes: '',
    colors: '',
    description: '',
    main_image_url: '',
    is_active: true,
    is_hot: false,
  });

  useEffect(() => {
    console.log('ProductForm useEffect - isEditMode:', isEditMode, 'id:', id);
    const fetchData = async () => {
      try {
        await loadCategories();
        if (isEditMode && id) {
          console.log('Carregando produto com ID:', id);
          await loadProduct(id);
        }
      } catch (error) {
        console.error('Erro ao inicializar formulário', error);
      }
    };
    fetchData();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id]);

  const loadCategories = async () => {
    try {
      const { data } = await api.get('/admin/categories');
      // Handle paginated response
      if (data.data && Array.isArray(data.data)) {
        setCategories(data.data);
      } else if (Array.isArray(data)) {
        setCategories(data);
      } else {
        setCategories([]);
      }
    } catch (error) {
      console.error('Erro ao carregar categorias', error);
      toast.error('Erro ao carregar categorias.');
    }
  };

  const loadProduct = async (productId: string | undefined) => {
    if (!productId) {
      console.error('ID do produto não fornecido');
      return;
    }
    
    setLoading(true);
    try {
      const { data } = await api.get(`/admin/products/${productId}`);
      
      // Load all images as objects
      const allImages: ProductImage[] = (data.images || []).map((img: any) => ({
        id: img.id,
        url: img.url,
        is_main: img.is_main || false,
        is_external: img.is_external || false,
        path: img.path || null,
      }));

      // Sort: main image first, then others
      const sortedImages = allImages.sort((a, b) => (b.is_main ? 1 : 0) - (a.is_main ? 1 : 0));
      setProductImages(sortedImages);

      const mainImage = sortedImages.find(img => img.is_main);

      setFormData({
        name: data.name || '',
        price: data.price?.toString() || '',
        promotional_price: data.promotional_price?.toString() || '',
        category_id: data.category_id?.toString() || '',
        sizes: Array.isArray(data.sizes) ? data.sizes.join(', ') : (data.sizes || ''),
        colors: Array.isArray(data.colors) ? data.colors.join(', ') : (data.colors || ''),
        description: data.description || '',
        main_image_url: mainImage ? mainImage.url : '',
        is_active: data.is_active !== undefined ? !!data.is_active : true,
        is_hot: data.is_hot !== undefined ? !!data.is_hot : false,
      });
      
      if (mainImage) {
        setPreviewUrl(mainImage.url);
        setImageMode('url');
      }

    } catch (error: any) {
      console.error('Erro ao carregar produto', error);
      const errorMessage = error.response?.data?.message || 'Erro ao carregar detalhes do produto.';
      toast.error(errorMessage);
      // Não navegar automaticamente, deixar o usuário ver o erro
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleCheckboxChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, checked } = e.target;
    setFormData(prev => ({ ...prev, [name]: checked }));
  };

  // Dropzone
  const onDrop = useCallback((acceptedFiles: File[]) => {
      const file = acceptedFiles[0];
      if (file) {
          // Create object URL for cropper
          setImageToCrop(URL.createObjectURL(file));
          setShowCropper(true);
          setImageMode('file');
      }
  }, []);

  const onCropComplete = (croppedBlob: Blob) => {
      const croppedFile = new File([croppedBlob], 'cropped-image.jpg', { type: 'image/jpeg' });
      setImageFile(croppedFile);
      setPreviewUrl(URL.createObjectURL(croppedFile));
      setFormData(prev => ({ ...prev, main_image_url: '' }));
      setShowCropper(false);
      setImageToCrop(null);
  };

  const onCancelCrop = () => {
      setShowCropper(false);
      setImageToCrop(null);
  };

  const { getRootProps, getInputProps, isDragActive } = useDropzone({ 
      onDrop,
      accept: { 'image/*': [] },
      multiple: false
  });

  // Image Handlers
  const addGalleryImage = () => {
      if (!newGalleryUrl.trim()) return;
      const newImage: ProductImage = {
        url: newGalleryUrl.trim(),
        is_main: false,
        is_external: true,
      };
      setProductImages([...productImages, newImage]);
      setNewGalleryUrl('');
  };

  const removeImage = async (image: ProductImage) => {
      if (!image.id) {
          // New image not saved yet, just remove from state
          setProductImages(productImages.filter(img => img.url !== image.url));
          if (image.is_main) {
              setPreviewUrl(null);
              setFormData(prev => ({ ...prev, main_image_url: '' }));
          }
          return;
      }

      // Existing image - delete from backend
      try {
          await api.delete(`/admin/product-images/${image.id}`);
          toast.success('Imagem removida com sucesso!');
          
          // Remove from state
          const updated = productImages.filter(img => img.id !== image.id);
          setProductImages(updated);
          
          // If it was the main image, update preview
          if (image.is_main && updated.length > 0) {
              // Make first image main
              const newMain = updated[0];
              newMain.is_main = true;
              setPreviewUrl(newMain.url);
              setFormData(prev => ({ ...prev, main_image_url: newMain.url }));
          } else if (image.is_main) {
              setPreviewUrl(null);
              setFormData(prev => ({ ...prev, main_image_url: '' }));
          }
      } catch (error: any) {
          console.error('Erro ao remover imagem', error);
          toast.error(error.response?.data?.message || 'Erro ao remover imagem.');
      }
  };

  const setAsMain = async (image: ProductImage) => {
      if (image.is_main) return;

      if (!image.id) {
          // New image - just update state
          const updated = productImages.map(img => ({
              ...img,
              is_main: img.url === image.url,
          }));
          // Sort: main first
          const sorted = updated.sort((a, b) => (b.is_main ? 1 : 0) - (a.is_main ? 1 : 0));
          setProductImages(sorted);
          setPreviewUrl(image.url);
          setFormData(prev => ({ ...prev, main_image_url: image.url }));
          return;
      }

      // Existing image - update in backend
      try {
          await api.put(`/admin/product-images/${image.id}/set-as-main`);
          toast.success('Imagem principal atualizada!');
          
          // Update state and sort
          const updated = productImages.map(img => ({
              ...img,
              is_main: img.id === image.id,
          }));
          const sorted = updated.sort((a, b) => (b.is_main ? 1 : 0) - (a.is_main ? 1 : 0));
          setProductImages(sorted);
          setPreviewUrl(image.url);
          setFormData(prev => ({ ...prev, main_image_url: image.url }));
      } catch (error: any) {
          console.error('Erro ao definir imagem principal', error);
          toast.error(error.response?.data?.message || 'Erro ao definir imagem principal.');
      }
  };

  // Drag and Drop handlers
  const handleDragStart = (index: number) => {
      setDraggedIndex(index);
  };

  const handleDragOver = (e: React.DragEvent) => {
      e.preventDefault();
  };

  const handleDrop = (e: React.DragEvent, dropIndex: number) => {
      e.preventDefault();
      if (draggedIndex === null || draggedIndex === dropIndex) return;

      const newImages = [...productImages];
      const dragged = newImages[draggedIndex];
      newImages.splice(draggedIndex, 1);
      newImages.splice(dropIndex, 0, dragged);
      
      setProductImages(newImages);
      setDraggedIndex(null);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    const sizesArray = formData.sizes.split(',').map(s => s.trim()).filter(s => s !== '');
    const colorsArray = formData.colors.split(',').map(c => c.trim()).filter(c => c !== '');

    // If file is present, use FormData
    if (imageMode === 'file' && imageFile) {
        const data = new FormData();
        data.append('name', formData.name);
        data.append('price', formData.price.toString().replace(',', '.'));
        if(formData.promotional_price) data.append('promotional_price', formData.promotional_price.toString().replace(',', '.'));
        
        if(formData.category_id) data.append('category_id', formData.category_id);
        sizesArray.forEach((size, index) => data.append(`sizes[${index}]`, size));
        colorsArray.forEach((color, index) => data.append(`colors[${index}]`, color));
        data.append('description', formData.description);
        data.append('is_active', formData.is_active ? '1' : '0');
        data.append('is_hot', formData.is_hot ? '1' : '0');
        data.append('image', imageFile);

        // Gallery Images (only non-main images that are URLs, not files)
        const galleryUrls = productImages
            .filter(img => !img.is_main && !img.id) // Only new gallery images (not main, not saved yet)
            .map(img => img.url);
        galleryUrls.forEach((url, index) => data.append(`images[${index}]`, url));
        
        if (isEditMode) {
            data.append('_method', 'PUT');
            try {
                await api.post(`/admin/products/${id}`, data, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                toast.success('Produto atualizado com sucesso!');
                navigate('/admin/products');
            } catch (error) {
                console.error(error);
                toast.error('Erro ao salvar produto.');
            }
        } else {
            try {
                await api.post('/admin/products', data, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                toast.success('Produto criado com sucesso!');
                navigate('/admin/products');
            } catch (error) {
                console.error(error);
                toast.error('Erro ao salvar produto.');
            }
        }
    } else {
        // Standard JSON payload
        const payload = {
            ...formData,
            price: parseFloat(formData.price.toString().replace(',', '.')),
            promotional_price: formData.promotional_price ? parseFloat(formData.promotional_price.toString().replace(',', '.')) : null,
            category_id: formData.category_id ? parseInt(formData.category_id) : null,
            sizes: sizesArray,
            colors: colorsArray,
            main_image_url: imageMode === 'url' ? formData.main_image_url : null,
            // Send all images in order (main first, then gallery)
            // Backend will handle syncing - existing images with IDs are kept, new URLs are added
            images: productImages
                .filter(img => !img.id) // Only new images (not saved yet)
                .map(img => img.url)
        };

        try {
            if (isEditMode) {
                await api.put(`/admin/products/${id}`, payload);
                toast.success('Produto atualizado com sucesso!');
            } else {
                await api.post('/admin/products', payload);
                toast.success('Produto criado com sucesso!');
            }
            navigate('/admin/products');
        } catch (error) {
            console.error(error);
            toast.error('Erro ao salvar produto.');
        }
    }
    setLoading(false);
  };

  if (loading && isEditMode) {
    return (
      <div className="max-w-4xl mx-auto mb-10">
        <div className="bg-white rounded-lg shadow p-6">
          <div className="text-center py-8">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
            <p className="mt-4 text-gray-600">Carregando produto...</p>
          </div>
        </div>
      </div>
    );
  }

  // Early return if we're in edit mode but don't have an ID
  if (isEditMode && !id) {
    return (
      <div className="max-w-4xl mx-auto mb-10">
        <div className="bg-white rounded-lg shadow p-6">
          <div className="text-center py-8">
            <p className="text-red-600">ID do produto não encontrado.</p>
            <button
              onClick={() => navigate('/admin/products')}
              className="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
            >
              Voltar para Produtos
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto mb-10">
      <h1 className="text-2xl font-bold text-gray-800 mb-6">
        {isEditMode ? 'Editar Produto' : 'Novo Produto'}
      </h1>

      {showCropper && imageToCrop && (
          <ImageCropper 
              imageSrc={imageToCrop} 
              onCropComplete={onCropComplete} 
              onCancel={onCancelCrop} 
          />
      )}

      <div className="bg-white rounded-lg shadow p-6">
        <form onSubmit={handleSubmit}>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            
            {/* Nome */}
            <div className="col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-2">Nome do Produto</label>
              <input
                type="text"
                name="name"
                value={formData.name}
                onChange={handleChange}
                required
                className="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              />
            </div>

            {/* Preço */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Preço (R$)</label>
              <CurrencyInput
                name="price"
                placeholder="R$ 0,00"
                value={formData.price}
                decimalsLimit={2}
                onValueChange={(value) => setFormData(prev => ({ ...prev, price: value || '' }))}
                prefix="R$ "
                className="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                required
              />
            </div>

            {/* Preço Promocional */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Preço Promocional (Opcional)</label>
              <CurrencyInput
                name="promotional_price"
                placeholder="R$ 0,00"
                value={formData.promotional_price}
                decimalsLimit={2}
                onValueChange={(value) => setFormData(prev => ({ ...prev, promotional_price: value || '' }))}
                prefix="R$ "
                className="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              />
              <p className="text-xs text-gray-500 mt-1">Se preenchido, o preço original aparecerá riscado.</p>
            </div>

            {/* Categoria */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
              <select
                name="category_id"
                value={formData.category_id}
                onChange={handleChange}
                className="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              >
                <option value="">Selecione uma categoria...</option>
                {categories.map(cat => (
                  <option key={cat.id} value={cat.id}>{cat.name}</option>
                ))}
              </select>
            </div>

            {/* Tamanhos */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Tamanhos (separados por vírgula)</label>
              <input
                type="text"
                name="sizes"
                value={formData.sizes}
                onChange={handleChange}
                placeholder="Ex: P, M, G, GG"
                required
                className="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              />
            </div>

            {/* Cores */}
            <div className="col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-2">Cores (separadas por vírgula)</label>
              <input
                type="text"
                name="colors"
                value={formData.colors}
                onChange={handleChange}
                placeholder="Ex: Azul, Vermelho, Preto"
                className="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              />
            </div>

            {/* Imagem: Toggle File vs URL */}
            <div className="col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-2">Imagem Principal</label>
                
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
                        name="main_image_url"
                        value={formData.main_image_url}
                        onChange={(e) => {
                            handleChange(e);
                            setPreviewUrl(e.target.value);
                            // If URL is set and not in productImages, add it as main
                            if (e.target.value && !productImages.find(img => img.url === e.target.value)) {
                                const newMain: ProductImage = {
                                    url: e.target.value,
                                    is_main: true,
                                    is_external: true,
                                };
                                // Remove main flag from others and add new main at the beginning
                                const updated = productImages.map(img => ({ ...img, is_main: false }));
                                const sorted = [newMain, ...updated].sort((a, b) => (b.is_main ? 1 : 0) - (a.is_main ? 1 : 0));
                                setProductImages(sorted);
                            }
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
                    <div className="mt-4 h-64 w-full rounded-lg border bg-gray-50 overflow-hidden relative group">
                        <img src={previewUrl} alt="Preview" className="h-full w-full object-contain" />
                        {isEditMode && imageMode === 'file' && !imageFile && (
                            <div className="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs p-2 text-center">
                                Imagem Atual / Recortada
                            </div>
                        )}
                    </div>
                )}
            </div>

            {/* Galeria de Imagens */}
            <div className="col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    Imagens do Produto
                    <span className="text-xs text-gray-500 ml-2">(Arraste para reordenar, clique em "Principal" para definir a imagem principal)</span>
                </label>
                <div className="flex gap-2 mb-4">
                    <input 
                        type="url" 
                        placeholder="https://..." 
                        value={newGalleryUrl}
                        onChange={(e) => setNewGalleryUrl(e.target.value)}
                        onKeyPress={(e) => {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                addGalleryImage();
                            }
                        }}
                        className="flex-grow rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    />
                    <button 
                        type="button" 
                        onClick={addGalleryImage}
                        className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    >
                        Adicionar URL
                    </button>
                </div>
                
                {/* Lista de Imagens com Drag and Drop */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {productImages.map((image, index) => (
                        <div
                            key={image.id || image.url}
                            draggable
                            onDragStart={() => handleDragStart(index)}
                            onDragOver={handleDragOver}
                            onDrop={(e) => handleDrop(e, index)}
                            className={`relative group border-2 rounded bg-gray-50 overflow-hidden aspect-square cursor-move ${
                                image.is_main ? 'border-indigo-500 ring-2 ring-indigo-300' : 'border-gray-200'
                            } ${draggedIndex === index ? 'opacity-50' : ''}`}
                        >
                            <img 
                                src={image.url} 
                                alt={`Product image ${index}`} 
                                className="w-full h-full object-cover" 
                            />
                            
                            {/* Badge Principal */}
                            {image.is_main && (
                                <div className="absolute top-2 left-2 bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded shadow-lg">
                                    Principal
                                </div>
                            )}
                            
                            {/* Botões de Ação */}
                            <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all flex items-center justify-center gap-2">
                                {!image.is_main && (
                                    <button
                                        type="button"
                                        onClick={() => setAsMain(image)}
                                        className="bg-indigo-600 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-indigo-700"
                                        title="Definir como Principal"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                    </button>
                                )}
                                <button
                                    type="button"
                                    onClick={() => removeImage(image)}
                                    className="bg-red-500 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-red-600"
                                    title="Remover Imagem"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            
                            {/* Ícone de arrastar */}
                            <div className="absolute bottom-2 right-2 bg-gray-800 bg-opacity-50 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 8h16M4 16h16" />
                                </svg>
                            </div>
                        </div>
                    ))}
                    {productImages.length === 0 && (
                        <div className="col-span-full text-center py-8 text-gray-400 border-2 border-dashed rounded">
                            Nenhuma imagem adicionada. Adicione URLs de imagens acima.
                        </div>
                    )}
                </div>
            </div>

            {/* Descrição */}
            <div className="col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
              <textarea
                name="description"
                rows={4}
                value={formData.description}
                onChange={handleChange}
                className="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              />
            </div>

            {/* Tags e Status */}
            <div className="col-span-2 flex flex-wrap gap-6">
                {/* Ativo */}
                <div className="flex items-center">
                <input
                    type="checkbox"
                    name="is_active"
                    id="is_active"
                    checked={formData.is_active}
                    onChange={handleCheckboxChange}
                    className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label htmlFor="is_active" className="ml-2 block text-sm text-gray-900 cursor-pointer">
                    Produto Ativo (Visível na loja)
                </label>
                </div>

                {/* Hot */}
                <div className="flex items-center">
                <input
                    type="checkbox"
                    name="is_hot"
                    id="is_hot"
                    checked={formData.is_hot}
                    onChange={handleCheckboxChange}
                    className="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                />
                <label htmlFor="is_hot" className="ml-2 block text-sm text-gray-900 cursor-pointer font-medium text-red-600 flex items-center gap-1">
                    🔥 Produto HOT / Destaque
                </label>
                </div>
            </div>
          </div>

          <div className="flex justify-end gap-3">
            <button
              type="button"
              onClick={() => navigate('/admin/products')}
              className="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300 transition"
            >
              Cancelar
            </button>
            <button
              type="submit"
              disabled={loading}
              className="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 transition disabled:opacity-50"
            >
              {loading ? 'Salvando...' : (isEditMode ? 'Atualizar' : 'Criar Produto')}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default ProductForm;
