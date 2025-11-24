import React, { useEffect, useState, useCallback } from 'react';
import { useDropzone } from 'react-dropzone';
import api from '../../../services/api';
import { toast } from 'react-toastify';
import ImageCropper from '../../../components/ui/ImageCropper';

interface Social {
  id?: number;
  name: string;
  url: string;
  icon?: string;
}

// Base URLs for social networks
const SOCIAL_BASE_URLS: Record<string, string> = {
  'Instagram': 'https://instagram.com/',
  'Facebook': 'https://facebook.com/',
  'TikTok': 'https://tiktok.com/@',
  'YouTube': 'https://youtube.com/@',
  'Twitter': 'https://twitter.com/',
  'LinkedIn': 'https://linkedin.com/in/',
  'Pinterest': 'https://pinterest.com/',
  'Outro': '', // Custom URL
};

interface StoreSettings {
  id: number;
  name: string;
  slug: string;
  whatsapp_number: string;
  whatsapp_message: string | null;
  store_url: string | null;
  logo_url: string | null;
  primary_color: string | null;
  secondary_color: string | null;
  description: string | null;
  banner_message: string | null;
  banner_text_color_1: string | null;
  banner_text_color_2: string | null;
  banner_background_color: string | null;
  address: string | null;
  email_contact: string | null;
  socials: Social[];
}

const StoreSettings: React.FC = () => {
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [storeSettings, setStoreSettings] = useState<StoreSettings | null>(null);

  const [formData, setFormData] = useState({
    name: '',
    whatsapp_number: '',
    whatsapp_message: '',
    store_url: '',
    logo_url: '',
    primary_color: '#7c3aed',
    secondary_color: '#a855f7',
    description: '',
    banner_message: '',
    banner_text_color_1: '#ffffff',
    banner_text_color_2: '#fbbf24',
    banner_background_color: '#000000',
    address: '',
    email_contact: '',
  });

  const [socials, setSocials] = useState<Social[]>([]);
  const [newSocial, setNewSocial] = useState<{ name: string; username: string; url: string }>({ name: '', username: '', url: '' });

  // Logo upload states
  const [logoMode, setLogoMode] = useState<'url' | 'file'>('url');
  const [logoFile, setLogoFile] = useState<File | null>(null);
  const [logoPreview, setLogoPreview] = useState<string | null>(null);
  const [showLogoCropper, setShowLogoCropper] = useState(false);
  const [imageToCrop, setImageToCrop] = useState<string | null>(null);

  useEffect(() => {
    loadStoreSettings();
  }, []);

  const loadStoreSettings = async () => {
    try {
      setLoading(true);
      const response = await api.get('/admin/store');
      const data = response.data;
      setStoreSettings(data);
      
      setFormData({
        name: data.name || '',
        whatsapp_number: data.whatsapp_number || '',
        whatsapp_message: data.whatsapp_message || '',
        store_url: data.store_url || '',
        logo_url: data.logo_url || '',
        primary_color: data.primary_color || '#7c3aed',
        secondary_color: data.secondary_color || '#a855f7',
        description: data.description || '',
        banner_message: data.banner_message || '',
        banner_text_color_1: data.banner_text_color_1 || '#ffffff',
        banner_text_color_2: data.banner_text_color_2 || '#fbbf24',
        banner_background_color: data.banner_background_color || '#000000',
        address: data.address || '',
        email_contact: data.email_contact || '',
      });

      if (data.logo_url) {
        setLogoPreview(data.logo_url);
        setLogoMode('url');
      } else {
        setLogoPreview(null);
        setLogoFile(null);
      }

      setSocials(data.socials || []);
    } catch (error) {
      console.error('Erro ao carregar configurações', error);
      toast.error('Erro ao carregar configurações da loja.');
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleAddSocial = () => {
    if (!newSocial.name || (!newSocial.username && newSocial.name !== 'Outro')) {
      toast.error('Preencha o nome e o usuário da rede social.');
      return;
    }
    
    // Build URL based on social network
    let url = '';
    if (newSocial.name === 'Outro') {
      url = newSocial.username; // For "Outro", username is the full URL
    } else {
      const baseUrl = SOCIAL_BASE_URLS[newSocial.name] || '';
      url = baseUrl + newSocial.username.replace(/^@/, '').replace(/^https?:\/\//, '');
    }
    
    setSocials([...socials, { name: newSocial.name, url, icon: '' }]);
    setNewSocial({ name: '', username: '', url: '' });
  };

  const handleRemoveSocial = (index: number) => {
    setSocials(socials.filter((_, i) => i !== index));
  };

  // Logo upload handlers
  const onLogoDrop = useCallback((acceptedFiles: File[]) => {
    const file = acceptedFiles[0];
    if (file) {
      setImageToCrop(URL.createObjectURL(file));
      setShowLogoCropper(true);
      setLogoMode('file');
    }
  }, []);

  const onLogoCropComplete = (croppedBlob: Blob) => {
    const croppedFile = new File([croppedBlob], 'logo.jpg', { type: 'image/jpeg' });
    setLogoFile(croppedFile);
    setLogoPreview(URL.createObjectURL(croppedFile));
    setFormData(prev => ({ ...prev, logo_url: '' }));
    setShowLogoCropper(false);
    setImageToCrop(null);
  };

  const onCancelLogoCrop = () => {
    setShowLogoCropper(false);
    setImageToCrop(null);
  };

  const handleRemoveLogo = async () => {
    if (!confirm('Tem certeza que deseja remover o logo?')) {
      return;
    }

    try {
      setSaving(true);
      // Send null logo_url to remove the logo
      const payload = {
        ...formData,
        logo_url: null,
      };

      await api.put('/admin/store', payload);
      toast.success('Logo removido com sucesso!');
      
      // Clear logo preview and file
      setLogoPreview(null);
      setLogoFile(null);
      setFormData(prev => ({ ...prev, logo_url: '' }));
      
      await loadStoreSettings();
    } catch (error: any) {
      console.error('Erro ao remover logo', error);
      toast.error(error.response?.data?.message || 'Erro ao remover logo.');
    } finally {
      setSaving(false);
    }
  };

  const { getRootProps: getLogoRootProps, getInputProps: getLogoInputProps, isDragActive: isLogoDragActive } = useDropzone({
    onDrop: onLogoDrop,
    accept: { 'image/*': [] },
    multiple: false
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    // Validate required fields
    if (!formData.name || !formData.whatsapp_number) {
      toast.error('Por favor, preencha o nome da loja e o WhatsApp.');
      return;
    }
    
    try {
      setSaving(true);

      // If logo file is present, use FormData
      if (logoMode === 'file' && logoFile) {
        // Debug: log formData state before creating FormData
        console.log('FormData creation - formData state:', formData);
        console.log('FormData creation - name:', formData.name);
        console.log('FormData creation - whatsapp_number:', formData.whatsapp_number);
        
        const formDataToSend = new FormData();
        
        // Required fields - must always be sent with valid values
        // Get values from formData state, not from trimmed strings
        const nameValue = (formData.name || '').trim();
        const whatsappValue = (formData.whatsapp_number || '').trim();
        
        console.log('FormData creation - nameValue (trimmed):', nameValue);
        console.log('FormData creation - whatsappValue (trimmed):', whatsappValue);
        
        // Validate before creating FormData
        if (!nameValue || nameValue.length === 0) {
          toast.error('Por favor, preencha o nome da loja.');
          setSaving(false);
          return;
        }
        
        if (!whatsappValue || whatsappValue.length === 0) {
          toast.error('Por favor, preencha o número do WhatsApp.');
          setSaving(false);
          return;
        }
        
        // Always append required fields first - use explicit values
        // IMPORTANT: Append required fields BEFORE the file to ensure they're processed correctly
        formDataToSend.append('name', nameValue);
        formDataToSend.append('whatsapp_number', whatsappValue);
        
        // Debug: verify FormData contents
        console.log('FormData - name entry:', formDataToSend.get('name'));
        console.log('FormData - whatsapp_number entry:', formDataToSend.get('whatsapp_number'));
        
        // Append all fields (required and optional) to ensure they're sent
        // Laravel validation works better when all fields are present in FormData
        if (formData.store_url) formDataToSend.append('store_url', formData.store_url.trim());
        if (formData.primary_color) formDataToSend.append('primary_color', formData.primary_color.trim());
        if (formData.secondary_color) formDataToSend.append('secondary_color', formData.secondary_color.trim());
        if (formData.description) formDataToSend.append('description', formData.description.trim());
        if (formData.banner_message) formDataToSend.append('banner_message', formData.banner_message.trim());
        if (formData.banner_text_color_1) formDataToSend.append('banner_text_color_1', formData.banner_text_color_1.trim());
        if (formData.banner_text_color_2) formDataToSend.append('banner_text_color_2', formData.banner_text_color_2.trim());
        if (formData.banner_background_color) formDataToSend.append('banner_background_color', formData.banner_background_color.trim());
        if (formData.address) formDataToSend.append('address', formData.address.trim());
        if (formData.email_contact) formDataToSend.append('email_contact', formData.email_contact.trim());
        
        // Logo file and URL - append AFTER required fields
        formDataToSend.append('logo', logoFile);
        formDataToSend.append('logo_url', ''); // Clear URL when uploading file

        // Add socials as JSON string
        if (socials.length > 0) {
          formDataToSend.append('socials', JSON.stringify(socials.map(s => ({
            name: s.name,
            url: s.url,
            icon: s.icon || null,
          }))));
        } else {
          formDataToSend.append('socials', JSON.stringify([]));
        }

        // Use POST for file uploads (Laravel handles PUT better with POST + _method)
        formDataToSend.append('_method', 'PUT');
        await api.post('/admin/store', formDataToSend);
      } else {
        // Use JSON for URL-based updates
        const payload = {
          ...formData,
          logo_url: logoMode === 'url' ? formData.logo_url : null,
          socials: socials.map(s => ({
            name: s.name,
            url: s.url,
            icon: s.icon || null,
          })),
        };

        await api.put('/admin/store', payload);
      }

      toast.success('Configurações salvas com sucesso!');
      await loadStoreSettings();
    } catch (error: any) {
      console.error('Erro ao salvar configurações', error);
      toast.error(error.response?.data?.message || 'Erro ao salvar configurações.');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-gray-500">Carregando configurações...</div>
      </div>
    );
  }

  return (
    <div>
      {showLogoCropper && imageToCrop && (
        <ImageCropper
          imageSrc={imageToCrop}
          onCropComplete={onLogoCropComplete}
          onCancel={onCancelLogoCrop}
          targetWidth={200}
          targetHeight={200}
        />
      )}

      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Minha Loja</h1>
        <p className="text-gray-600 mt-1">Configure as informações e aparência da sua loja</p>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        {/* Informações Básicas */}
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Informações Básicas</h2>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">
                Nome da Loja *
              </label>
              <input
                type="text"
                id="name"
                name="name"
                value={formData.name}
                onChange={handleChange}
                required
                className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div>
              <label htmlFor="whatsapp_number" className="block text-sm font-medium text-gray-700 mb-2">
                WhatsApp *
              </label>
              <input
                type="text"
                id="whatsapp_number"
                name="whatsapp_number"
                value={formData.whatsapp_number}
                onChange={handleChange}
                required
                placeholder="5511999999999"
                className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div>
              <label htmlFor="store_url" className="block text-sm font-medium text-gray-700 mb-2">
                URL da Loja (ou Slug)
              </label>
              <input
                type="text"
                id="store_url"
                name="store_url"
                value={formData.store_url}
                onChange={handleChange}
                placeholder="exemplo ou https://exemplo.com"
                className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
              <p className="text-xs text-gray-500 mt-1">
                Pode ser um slug simples (ex: minha-loja) ou uma URL completa
              </p>
            </div>
            
            <div>
              <label htmlFor="whatsapp_message" className="block text-sm font-medium text-gray-700 mb-2">
                Mensagem Padrão do WhatsApp
              </label>
              <textarea
                id="whatsapp_message"
                name="whatsapp_message"
                value={formData.whatsapp_message}
                onChange={handleChange}
                placeholder="Olá! Gostaria de saber mais sobre seus produtos."
                rows={3}
                className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
              <p className="text-xs text-gray-500 mt-1">
                Esta mensagem será enviada automaticamente quando o cliente clicar no botão do WhatsApp
              </p>
            </div>

            <div>
              <label htmlFor="email_contact" className="block text-sm font-medium text-gray-700 mb-2">
                Email de Contato
              </label>
              <input
                type="email"
                id="email_contact"
                name="email_contact"
                value={formData.email_contact}
                onChange={handleChange}
                placeholder="contato@sualoja.com.br"
                className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div className="md:col-span-2">
              <label htmlFor="address" className="block text-sm font-medium text-gray-700 mb-2">
                Endereço
              </label>
              <input
                type="text"
                id="address"
                name="address"
                value={formData.address}
                onChange={handleChange}
                placeholder="Rua, número, bairro, cidade - CEP"
                className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div className="md:col-span-2">
              <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-2">
                Descrição da Loja
              </label>
              <textarea
                id="description"
                name="description"
                value={formData.description}
                onChange={handleChange}
                rows={3}
                placeholder="Descreva sua loja..."
                className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div className="md:col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Logo da Loja
              </label>
              
              <div className="mb-4 flex gap-4">
                <label className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="logoMode"
                    checked={logoMode === 'url'}
                    onChange={() => {
                      setLogoMode('url');
                      setLogoFile(null);
                      if (storeSettings?.logo_url) {
                        setLogoPreview(storeSettings.logo_url);
                      }
                    }}
                    className="text-indigo-600 focus:ring-indigo-500"
                  />
                  <span>Usar URL</span>
                </label>
                <label className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="logoMode"
                    checked={logoMode === 'file'}
                    onChange={() => {
                      setLogoMode('file');
                      setFormData(prev => ({ ...prev, logo_url: '' }));
                    }}
                    className="text-indigo-600 focus:ring-indigo-500"
                  />
                  <span>Fazer Upload</span>
                </label>
              </div>

              {logoMode === 'url' ? (
                <div>
                  <input
                    type="url"
                    id="logo_url"
                    name="logo_url"
                    value={formData.logo_url}
                    onChange={(e) => {
                      handleChange(e);
                      setLogoPreview(e.target.value);
                    }}
                    placeholder="https://exemplo.com/logo.png"
                    className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                  />
                  {logoPreview && (
                    <div className="mt-2 relative">
                      <img
                        src={logoPreview}
                        alt="Logo preview"
                        className="h-20 object-contain border border-gray-200 rounded"
                        onError={() => setLogoPreview(null)}
                      />
                      <button
                        type="button"
                        onClick={handleRemoveLogo}
                        className="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
                        title="Remover logo"
                      >
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                  )}
                </div>
              ) : (
                <div>
                  <div
                    {...getLogoRootProps()}
                    className={`border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-colors ${
                      isLogoDragActive
                        ? 'border-indigo-500 bg-indigo-50'
                        : 'border-gray-300 hover:border-indigo-400'
                    }`}
                  >
                    <input {...getLogoInputProps()} />
                    {logoPreview ? (
                      <div className="space-y-2 relative">
                        <img
                          src={logoPreview}
                          alt="Logo preview"
                          className="h-32 mx-auto object-contain border border-gray-200 rounded"
                        />
                        <div className="flex justify-center gap-2">
                          <p className="text-sm text-gray-600">Clique ou arraste para substituir</p>
                          <button
                            type="button"
                            onClick={() => {
                              setLogoPreview(null);
                              setLogoFile(null);
                              setFormData(prev => ({ ...prev, logo_url: '' }));
                            }}
                            className="text-sm text-red-600 hover:text-red-700 underline"
                          >
                            Remover
                          </button>
                        </div>
                      </div>
                    ) : (
                      <div>
                        <p className="text-gray-600">
                          {isLogoDragActive
                            ? 'Solte a imagem aqui'
                            : 'Clique ou arraste uma imagem aqui'}
                        </p>
                        <p className="text-xs text-gray-500 mt-1">PNG, JPG até 2MB</p>
                      </div>
                    )}
                  </div>
                  {logoFile && (
                    <button
                      type="button"
                      onClick={() => {
                        setLogoFile(null);
                        setLogoPreview(null);
                      }}
                      className="mt-2 text-sm text-red-600 hover:text-red-700"
                    >
                      Remover logo
                    </button>
                  )}
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Cores */}
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Cores da Loja</h2>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label htmlFor="primary_color" className="block text-sm font-medium text-gray-700 mb-2">
                Cor Primária
              </label>
              <div className="flex gap-2">
                <input
                  type="color"
                  id="primary_color"
                  name="primary_color"
                  value={formData.primary_color}
                  onChange={handleChange}
                  className="h-10 w-20 border border-gray-300 rounded cursor-pointer"
                />
                <input
                  type="text"
                  value={formData.primary_color}
                  onChange={handleChange}
                  name="primary_color"
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                  placeholder="#7c3aed"
                />
              </div>
            </div>

            <div>
              <label htmlFor="secondary_color" className="block text-sm font-medium text-gray-700 mb-2">
                Cor Secundária
              </label>
              <div className="flex gap-2">
                <input
                  type="color"
                  id="secondary_color"
                  name="secondary_color"
                  value={formData.secondary_color}
                  onChange={handleChange}
                  className="h-10 w-20 border border-gray-300 rounded cursor-pointer"
                />
                <input
                  type="text"
                  value={formData.secondary_color}
                  onChange={handleChange}
                  name="secondary_color"
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                  placeholder="#a855f7"
                />
              </div>
            </div>
          </div>
        </div>

        {/* Banner Promocional */}
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Banner Promocional</h2>
          
          <div className="space-y-4">
            <div>
              <label htmlFor="banner_message" className="block text-sm font-medium text-gray-700 mb-2">
                Mensagem do Banner
              </label>
              <input
                type="text"
                id="banner_message"
                name="banner_message"
                value={formData.banner_message}
                onChange={handleChange}
                placeholder="Ex: Promoção Black Friday 50% OFF"
                className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label htmlFor="banner_background_color" className="block text-sm font-medium text-gray-700 mb-2">
                  Cor de Fundo
                </label>
                <div className="flex gap-2">
                  <input
                    type="color"
                    id="banner_background_color"
                    name="banner_background_color"
                    value={formData.banner_background_color}
                    onChange={handleChange}
                    className="h-10 w-20 border border-gray-300 rounded cursor-pointer"
                  />
                  <input
                    type="text"
                    value={formData.banner_background_color}
                    onChange={handleChange}
                    name="banner_background_color"
                    className="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
              </div>

              <div>
                <label htmlFor="banner_text_color_1" className="block text-sm font-medium text-gray-700 mb-2">
                  Cor do Texto 1
                </label>
                <div className="flex gap-2">
                  <input
                    type="color"
                    id="banner_text_color_1"
                    name="banner_text_color_1"
                    value={formData.banner_text_color_1}
                    onChange={handleChange}
                    className="h-10 w-20 border border-gray-300 rounded cursor-pointer"
                  />
                  <input
                    type="text"
                    value={formData.banner_text_color_1}
                    onChange={handleChange}
                    name="banner_text_color_1"
                    className="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
              </div>

              <div>
                <label htmlFor="banner_text_color_2" className="block text-sm font-medium text-gray-700 mb-2">
                  Cor do Texto 2
                </label>
                <div className="flex gap-2">
                  <input
                    type="color"
                    id="banner_text_color_2"
                    name="banner_text_color_2"
                    value={formData.banner_text_color_2}
                    onChange={handleChange}
                    className="h-10 w-20 border border-gray-300 rounded cursor-pointer"
                  />
                  <input
                    type="text"
                    value={formData.banner_text_color_2}
                    onChange={handleChange}
                    name="banner_text_color_2"
                    className="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
              </div>
            </div>

            {formData.banner_message && (
              <div className="mt-4 p-4 rounded-lg" style={{ backgroundColor: formData.banner_background_color }}>
                <p className="text-center text-lg font-bold">
                  {formData.banner_message.split(' ').map((word, i) => (
                    <span key={i} style={{ color: i % 2 === 0 ? formData.banner_text_color_1 : formData.banner_text_color_2 }}>
                      {word}{i < formData.banner_message.split(' ').length - 1 ? ' ' : ''}
                    </span>
                  ))}
                </p>
              </div>
            )}
          </div>
        </div>

        {/* Redes Sociais */}
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Redes Sociais</h2>
          
          <div className="space-y-4">
            {socials.map((social, index) => {
              // Extract username from URL for display
              const baseUrl = Object.values(SOCIAL_BASE_URLS).find(base => social.url.startsWith(base));
              const username = baseUrl && baseUrl !== '' 
                ? social.url.replace(baseUrl, '').replace(/\/$/, '')
                : social.url;
              const socialName = Object.keys(SOCIAL_BASE_URLS).find(key => 
                social.url.startsWith(SOCIAL_BASE_URLS[key]) || (key === 'Outro' && !Object.values(SOCIAL_BASE_URLS).some(b => social.url.startsWith(b)))
              ) || 'Outro';
              
              return (
                <div key={index} className="flex gap-2 items-start p-3 bg-gray-50 rounded-lg">
                  <div className="flex-1 grid grid-cols-1 md:grid-cols-2 gap-2">
                    <select
                      value={socialName}
                      onChange={(e) => {
                        const updated = [...socials];
                        const newName = e.target.value;
                        const baseUrl = SOCIAL_BASE_URLS[newName] || '';
                        if (newName === 'Outro') {
                          updated[index].url = username;
                        } else {
                          updated[index].url = baseUrl + username.replace(/^@/, '').replace(/^https?:\/\//, '');
                        }
                        updated[index].name = newName;
                        setSocials(updated);
                      }}
                      className="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                      {Object.keys(SOCIAL_BASE_URLS).map(name => (
                        <option key={name} value={name}>{name}</option>
                      ))}
                    </select>
                    <input
                      type="text"
                      value={username}
                      onChange={(e) => {
                        const updated = [...socials];
                        const username = e.target.value;
                        const baseUrl = SOCIAL_BASE_URLS[socialName] || '';
                        if (socialName === 'Outro') {
                          updated[index].url = username;
                        } else {
                          updated[index].url = baseUrl + username.replace(/^@/, '').replace(/^https?:\/\//, '');
                        }
                        setSocials(updated);
                      }}
                      placeholder={socialName === 'Outro' ? 'URL completa (ex: https://exemplo.com)' : 'Usuário (ex: loja)'}
                      className="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    />
                  </div>
                  <button
                    type="button"
                    onClick={() => handleRemoveSocial(index)}
                    className="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition"
                  >
                    Remover
                  </button>
                </div>
              );
            })}

            <div className="flex gap-2 items-start p-3 bg-gray-50 rounded-lg">
              <div className="flex-1 grid grid-cols-1 md:grid-cols-2 gap-2">
                <select
                  value={newSocial.name}
                  onChange={(e) => {
                    const name = e.target.value;
                    setNewSocial({ ...newSocial, name, url: '' });
                  }}
                  className="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="">Selecione a rede social</option>
                  {Object.keys(SOCIAL_BASE_URLS).map(name => (
                    <option key={name} value={name}>{name}</option>
                  ))}
                </select>
                <input
                  type="text"
                  value={newSocial.username}
                  onChange={(e) => setNewSocial({ ...newSocial, username: e.target.value })}
                  placeholder={newSocial.name === 'Outro' || !newSocial.name ? 'URL completa ou usuário' : `Usuário (ex: ${newSocial.name === 'Instagram' ? 'loja' : 'loja'})`}
                  className="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>
              <button
                type="button"
                onClick={handleAddSocial}
                className="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition"
              >
                Adicionar
              </button>
            </div>
          </div>
        </div>

        {/* Botões de Ação */}
        <div className="flex justify-end gap-4">
          <button
            type="submit"
            disabled={saving}
            className="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {saving ? 'Salvando...' : 'Salvar Configurações'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default StoreSettings;

