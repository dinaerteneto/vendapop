import React, { useEffect } from 'react';
import { Link } from 'react-router-dom';

interface UpgradeModalProps {
  isOpen: boolean;
  planType: string;
  current: number;
  limit: number;
  upgradeUrl: string;
  onClose: () => void;
}

const NEXT_TIER: Record<string, { name: string; price: number }> = {
  free: { name: 'Básico', price: 29.90 },
  basic: { name: 'Profissional', price: 59.90 },
  professional: { name: 'Premium', price: 99.90 },
};

const PLAN_LABELS: Record<string, string> = {
  free: 'Grátis',
  basic: 'Básico',
  professional: 'Profissional',
  premium: 'Premium',
};

const UpgradeModal: React.FC<UpgradeModalProps> = ({ isOpen, planType, current, limit, upgradeUrl, onClose }) => {
  const nextTier = NEXT_TIER[planType];
  const planLabel = PLAN_LABELS[planType] || planType;

  useEffect(() => {
    if (isOpen && typeof window.gtag === 'function') {
      window.gtag('event', 'upgrade_modal_viewed', {
        plan_type: planType,
        current,
        limit,
      });
    }
  }, [isOpen, planType, current, limit]);

  if (!isOpen) return null;

  const formattedPrice = nextTier?.price.toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  });

  const handleCtaClick = () => {
    if (typeof window.gtag === 'function') {
      window.gtag('event', 'upgrade_modal_cta_clicked', {
        plan_type: planType,
        target_tier: nextTier?.name || '',
      });
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 animate-fade-in">
      <div className="bg-white rounded-lg shadow-xl p-8 max-w-md w-full mx-4">
        <div className="text-center">
          <div className="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 mb-4">
            <svg className="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>

          <h2 className="text-xl font-bold text-gray-800 mb-2">
            Você atingiu o limite do plano {planLabel}
          </h2>

          <p className="text-gray-600 mb-6">
            Seu plano atual permite até <strong>{limit}</strong> produto{limit !== 1 ? 's' : ''}.
            Você já cadastrou <strong>{current}</strong>.
          </p>

          {nextTier && (
            <div className="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg p-4 mb-6 border border-purple-200">
              <p className="text-sm text-gray-600 mb-1">Faça upgrade para o plano</p>
              <p className="text-2xl font-bold text-purple-700">{nextTier.name}</p>
              <p className="text-lg text-gray-700">
                por apenas <span className="font-bold text-indigo-600">{formattedPrice}</span>/mês
              </p>
            </div>
          )}

          <div className="flex flex-col gap-3">
            <Link
              to={upgradeUrl}
              onClick={handleCtaClick}
              className="w-full px-4 py-3 text-white bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg hover:from-purple-700 hover:to-indigo-700 transition font-medium text-center"
            >
              Fazer Upgrade Agora
            </Link>
            <button
              onClick={onClose}
              className="w-full px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition text-sm"
            >
              Depois
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default UpgradeModal;
