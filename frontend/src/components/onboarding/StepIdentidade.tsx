import React, { useState, useEffect } from 'react';
import api from '../../services/api';
import ImageUploader from '../ui/ImageUploader';

interface StepIdentidadeProps {
  onNext: () => void;
}

const PRESET_COLORS = [
  { hex: '#7c3aed', label: 'Roxo' },
  { hex: '#2563eb', label: 'Azul' },
  { hex: '#16a34a', label: 'Verde' },
  { hex: '#dc2626', label: 'Vermelho' },
  { hex: '#d97706', label: 'Âmbar' },
  { hex: '#db2777', label: 'Rosa' },
  { hex: '#0891b2', label: 'Ciano' },
  { hex: '#374151', label: 'Grafite' },
];

const StepIdentidade: React.FC<StepIdentidadeProps> = ({ onNext }) => {
  const [logoFile, setLogoFile] = useState<File | null>(null);
  const [logoPreview, setLogoPreview] = useState<string | null>(null);
  const [primaryColor, setPrimaryColor] = useState('#7c3aed');
  const [saving, setSaving] = useState(false);
  const [storeName, setStoreName] = useState('');
  const [storeWhatsapp, setStoreWhatsapp] = useState('');

  useEffect(() => {
    api.get('/admin/store').then(res => {
      const d = res.data;
      if (d?.primary_color) setPrimaryColor(d.primary_color);
      if (d?.logo_url) setLogoPreview(d.logo_url);
      if (d?.name) setStoreName(d.name);
      if (d?.whatsapp_number) setStoreWhatsapp(d.whatsapp_number);
    }).catch(() => {});
  }, []);

  const handleNext = async () => {
    setSaving(true);
    try {
      const formData = new FormData();
      formData.append('name', storeName);
      formData.append('whatsapp_number', storeWhatsapp);
      formData.append('primary_color', primaryColor);
      formData.append('_method', 'PUT');
      if (logoFile) {
        formData.append('logo', logoFile);
      }
      await api.post('/admin/store', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
      await api.put('/admin/onboarding-status', { onboarding_step: 1 });
      onNext();
    } catch (err: any) {
      console.error('Erro ao salvar identidade', err);
    } finally {
      setSaving(false);
    }
  };

  return (
    <div data-testid="step-identidade">
      <h2 className="text-2xl font-bold text-gray-900 mb-1">Como é a identidade da sua loja?</h2>
      <p className="text-sm text-gray-500 mb-6">Você pode mudar isso depois nas configurações.</p>

      <ImageUploader
        aspectRatio="1:1"
        currentImageUrl={logoPreview ?? undefined}
        onImageReady={(file) => {
          setLogoFile(file);
          setLogoPreview(URL.createObjectURL(file));
        }}
        label="Logo da loja"
      />

      <div className="mt-6">
        <label className="block text-sm font-medium text-gray-700 mb-2">Cor principal</label>
        <div className="grid grid-cols-4 gap-3">
          {PRESET_COLORS.map(({ hex, label }) => (
            <button
              key={hex}
              type="button"
              title={label}
              onClick={() => setPrimaryColor(hex)}
              className={`w-8 h-8 rounded-full transition-all hover:scale-110 ${
                primaryColor === hex ? 'ring-2 ring-offset-2 ring-purple-500 scale-110' : ''
              }`}
              style={{ backgroundColor: hex }}
            />
          ))}
        </div>
        <div className="flex gap-2 mt-3">
          <input
            type="color"
            value={primaryColor}
            onChange={(e) => setPrimaryColor(e.target.value)}
            className="w-10 h-10 border rounded cursor-pointer"
          />
          <input
            type="text"
            value={primaryColor}
            onChange={(e) => setPrimaryColor(e.target.value)}
            className="flex-1 px-3 py-2 border rounded text-sm font-mono"
          />
        </div>
        {primaryColor && (
          <div
            className="mt-2 h-8 rounded flex items-center justify-center text-white text-xs font-medium"
            style={{ backgroundColor: primaryColor }}
          >
            Sua cor primária
          </div>
        )}
      </div>

      <div className="mt-6 space-y-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Nome da loja</label>
          <input
            type="text"
            value={storeName}
            onChange={(e) => setStoreName(e.target.value)}
            placeholder="Minha Loja"
            className="w-full px-3 py-2 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
          />
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
          <input
            type="text"
            value={storeWhatsapp}
            onChange={(e) => setStoreWhatsapp(e.target.value)}
            placeholder="5511999999999"
            className="w-full px-3 py-2 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
          />
        </div>
      </div>

      <div className="flex items-center justify-between mt-8">
        <button
          type="button"
          onClick={onNext}
          className="text-sm text-gray-500 hover:text-gray-700"
        >
          Pular este passo
        </button>
        <button
          type="button"
          onClick={handleNext}
          disabled={saving}
          className="px-6 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 disabled:opacity-50 transition"
        >
          {saving ? 'Salvando...' : 'Próximo →'}
        </button>
      </div>
    </div>
  );
};

export default StepIdentidade;
