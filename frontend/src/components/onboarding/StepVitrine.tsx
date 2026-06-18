import React, { useEffect, useState } from 'react';
import api from '../../services/api';

interface DemoProduct {
  id: number;
  uuid: string;
  name: string;
  price: string;
  main_image_url: string | null;
  is_demo: boolean;
}

interface StepVitrineProps {
  onNext: () => void;
  onBack: () => void;
  onSkip: () => void;
}

const StepVitrine: React.FC<StepVitrineProps> = ({ onNext, onBack, onSkip }) => {
  const [products, setProducts] = useState<DemoProduct[]>([]);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [editName, setEditName] = useState('');
  const [saving, setSaving] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get('/admin/products', { params: { per_page: 50 } })
      .then(res => {
        const data = res.data.data || res.data;
        const all = Array.isArray(data) ? data : [];
        let demo = all.filter((p: any) => p.is_demo).slice(0, 4);
        if (demo.length === 0) {
          demo = all.filter((p: any) => p.is_active !== false).slice(0, 4);
        }
        setProducts(demo);
      })
      .catch(console.error)
      .finally(() => setLoading(false));
  }, []);

  const startEditName = (product: DemoProduct) => {
    setEditingId(product.id);
    setEditName(product.name);
  };

  const saveEditName = async () => {
    if (editingId === null || !editName.trim()) return;
    const formData = new FormData();
    formData.append('name', editName.trim());
    formData.append('_method', 'PUT');
    try {
      await api.post(`/admin/products/${editingId}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      setProducts(ps => ps.map(p => p.id === editingId ? { ...p, name: editName.trim() } : p));
    } catch (err) {
      console.error(err);
    }
    setEditingId(null);
  };

  const handleProductImage = async (productId: number, file: File) => {
    setSaving(true);
    const formData = new FormData();
    formData.append('image', file);
    formData.append('_method', 'PUT');
    try {
      const res = await api.post(`/admin/products/${productId}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      setProducts(ps => ps.map(p => p.id === productId
        ? { ...p, main_image_url: res.data.main_image_url || URL.createObjectURL(file) }
        : p
      ));
    } catch (err) {
      console.error(err);
    } finally {
      setSaving(false);
    }
  };

  const handleNext = async () => {
    try {
      await api.put('/admin/onboarding-status', { onboarding_step: 2 });
    } catch (err) {
      console.error(err);
    }
    onNext();
  };

  if (loading) {
    return (
      <div data-testid="step-vitrine" className="space-y-4">
        <h2 className="text-2xl font-bold text-gray-900 mb-1">Personalize sua vitrine</h2>
        <div className="grid grid-cols-2 gap-4">
          {[1, 2, 3, 4].map(i => (
            <div key={i} className="animate-pulse">
              <div className="bg-gray-200 rounded-lg" style={{ aspectRatio: '2/3' }} />
              <div className="h-4 bg-gray-200 rounded mt-2 w-3/4" />
              <div className="h-3 bg-gray-100 rounded mt-1 w-1/2" />
            </div>
          ))}
        </div>
      </div>
    );
  }

  return (
    <div data-testid="step-vitrine">
      <h2 className="text-2xl font-bold text-gray-900 mb-1">Personalize sua vitrine</h2>
      <p className="text-sm text-gray-500 mb-4">Edite as fotos e nomes dos produtos. Você pode adicionar mais depois.</p>

      <div className="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4 text-sm text-amber-800">
        Esses são produtos de exemplo. Substitua pelas suas peças — ou pule e faça depois.
      </div>

      <div className="grid grid-cols-2 gap-4 mb-6">
        {products.map(product => (
          <div key={product.id} className="border rounded-lg overflow-hidden bg-white">
            <label className="block cursor-pointer relative group">
              {product.main_image_url ? (
                <img
                  src={product.main_image_url}
                  alt={product.name}
                  className="w-full object-cover"
                  style={{ aspectRatio: '2/3' }}
                />
              ) : (
                <div className="w-full bg-gray-100 flex items-center justify-center text-gray-400" style={{ aspectRatio: '2/3' }}>
                  <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
              )}
              <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all flex items-center justify-center">
                <span className="text-white text-sm font-medium opacity-0 group-hover:opacity-100 bg-black bg-opacity-60 px-3 py-1 rounded">
                  Alterar foto
                </span>
              </div>
              <input
                type="file"
                accept="image/*"
                className="hidden"
                onChange={(e) => {
                  const file = e.target.files?.[0];
                  if (file) handleProductImage(product.id, file);
                  e.target.value = '';
                }}
              />
            </label>

            <div className="p-2">
              {editingId === product.id ? (
                <input
                  type="text"
                  value={editName}
                  onChange={(e) => setEditName(e.target.value)}
                  onBlur={saveEditName}
                  onKeyDown={(e) => { if (e.key === 'Enter') saveEditName(); }}
                  className="w-full px-2 py-1 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
                  autoFocus
                />
              ) : (
                <p
                  className="text-sm font-medium text-gray-800 truncate cursor-pointer hover:text-purple-600"
                  onClick={() => startEditName(product)}
                >
                  {product.name}
                </p>
              )}
              <p className="text-xs text-gray-500">R$ {product.price}</p>
            </div>
          </div>
        ))}
      </div>

      <div className="flex items-center justify-between mt-8">
        <button type="button" onClick={onBack} className="text-sm text-gray-500 hover:text-gray-700">
          ← Voltar
        </button>
        <div className="flex items-center gap-3">
          <button type="button" onClick={onSkip} className="text-sm text-gray-500 hover:text-gray-700">
            Pular
          </button>
          <button
            type="button"
            onClick={handleNext}
            disabled={saving}
            className="px-6 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 disabled:opacity-50 transition"
          >
            Próximo →
          </button>
        </div>
      </div>
    </div>
  );
};

export default StepVitrine;
