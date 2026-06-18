import React, { useState } from 'react';
import api from '../../services/api';

interface StepWhatsappProps {
  onNext: () => void;
}

const StepWhatsapp: React.FC<StepWhatsappProps> = ({ onNext }) => {
  const [whatsapp, setWhatsapp] = useState('');
  const [message, setMessage] = useState('Olá! Vi seu produto na loja e gostaria de mais informações.');
  const [saving, setSaving] = useState(false);

  const formatPhone = (value: string) => {
    const digits = value.replace(/\D/g, '');
    if (digits.length <= 2) return digits;
    if (digits.length <= 7) return `(${digits.slice(0, 2)}) ${digits.slice(2)}`;
    if (digits.length <= 11) return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7)}`;
    return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7, 11)}`;
  };

  const handleNext = async () => {
    setSaving(true);
    try {
      const formData = new FormData();
      formData.append('whatsapp_number', whatsapp.replace(/\D/g, ''));
      if (message) formData.append('whatsapp_message', message);
      formData.append('_method', 'PUT');

      await api.post('/admin/store', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      await api.put('/admin/onboarding-status', { onboarding_step: 3 });
      onNext();
    } catch (err) {
      console.error('Erro ao salvar WhatsApp', err);
    } finally {
      setSaving(false);
    }
  };

  return (
    <div data-testid="step-whatsapp">
      <h2 className="text-2xl font-bold text-gray-900 mb-1">Como as clientes entram em contato?</h2>
      <p className="text-sm text-gray-500 mb-6">Configure seu WhatsApp para receber pedidos.</p>

      <div className="space-y-5">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Número do WhatsApp</label>
          <input
            type="tel"
            value={whatsapp}
            onChange={(e) => setWhatsapp(formatPhone(e.target.value))}
            placeholder="(11) 99999-9999"
            className="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Mensagem padrão do pedido</label>
          <textarea
            value={message}
            onChange={(e) => setMessage(e.target.value)}
            rows={3}
            className="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
          />
        </div>

        <div className="bg-green-50 border border-green-200 rounded-lg p-4">
          <p className="text-xs text-green-700 font-medium mb-2">Prévia da conversa</p>
          <div className="space-y-2">
            <div className="flex justify-end">
              <div className="bg-green-100 rounded-lg px-3 py-2 text-xs text-gray-800 max-w-[80%]">
                {message || 'Olá! Vi seu produto na loja e gostaria de mais informações.'}
              </div>
            </div>
            <div className="flex justify-start">
              <div className="bg-white rounded-lg px-3 py-2 text-xs text-gray-500 max-w-[80%] border">
                {whatsapp ? `Você (${whatsapp})` : 'Sua resposta aqui...'}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="flex items-center justify-between mt-8">
        <button type="button" onClick={onNext} className="text-sm text-gray-500 hover:text-gray-700">
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

export default StepWhatsapp;
