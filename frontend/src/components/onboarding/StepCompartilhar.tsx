import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../../services/api';
import confetti from 'canvas-confetti';

const StepCompartilhar: React.FC = () => {
  const navigate = useNavigate();
  const [copied, setCopied] = useState(false);
  const [completing, setCompleting] = useState(false);

  const slug = localStorage.getItem('tenant_slug') || '';
  const shopUrl = `vendapop.com.br/${slug}`;

  const handleCopyLink = () => {
    navigator.clipboard.writeText(shopUrl).then(() => {
      setCopied(true);
      setTimeout(() => setCopied(false), 3000);
    });
  };

  const handleConcluir = async () => {
    setCompleting(true);
    try {
      await api.put('/admin/onboarding-status', { onboarding_completed: true, onboarding_step: 4 });
      confetti({ particleCount: 150, spread: 80, origin: { y: 0.6 } });
      setTimeout(() => navigate('/admin'), 2500);
    } catch (err) {
      console.error('Erro ao concluir onboarding', err);
      navigate('/admin');
    }
  };

  return (
    <div data-testid="step-compartilhar" className="text-center">
      <h2 className="text-2xl font-bold text-gray-900 mb-1">Sua loja está pronta!</h2>
      <p className="text-sm text-gray-500 mb-6">Compartilhe com suas clientes e comece a vender.</p>

      <div className="bg-gray-50 rounded-lg p-4 mb-4">
        <p className="text-sm text-gray-500 mb-2">Link da sua loja</p>
        <p className="text-lg font-mono font-bold text-gray-800 break-all">{shopUrl}</p>
      </div>

      <button
        type="button"
        onClick={handleCopyLink}
        className={`w-full px-4 py-3 rounded-lg text-sm font-medium transition mb-4 ${
          copied ? 'bg-green-500 text-white' : 'bg-purple-600 text-white hover:bg-purple-700'
        }`}
      >
        {copied ? 'Link copiado!' : 'Copiar link da loja'}
      </button>

      <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
        <p className="text-xs text-blue-700 font-medium mb-2">Como compartilhar</p>
        <div className="space-y-2 text-sm text-blue-800">
          <div className="flex items-start gap-2">
            <span className="bg-blue-200 text-blue-800 rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold shrink-0">1</span>
            <span>Copie o link da sua loja</span>
          </div>
          <div className="flex items-start gap-2">
            <span className="bg-blue-200 text-blue-800 rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold shrink-0">2</span>
            <span>Cole na bio do seu Instagram</span>
          </div>
        </div>
      </div>

      <div className="space-y-3">
        <button
          type="button"
          onClick={() => window.open(`/${slug}`, '_blank')}
          className="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
        >
          Ver minha loja completa
        </button>
        <button
          type="button"
          onClick={handleConcluir}
          disabled={completing}
          className="w-full px-4 py-3 bg-purple-600 text-white rounded-lg text-sm font-bold hover:bg-purple-700 disabled:opacity-50 transition"
        >
          {completing ? 'Finalizando...' : 'Concluir configuração'}
        </button>
      </div>
    </div>
  );
};

export default StepCompartilhar;
