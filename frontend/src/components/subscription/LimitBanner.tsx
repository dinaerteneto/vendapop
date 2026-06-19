import React from 'react';

interface LimitBannerProps {
  planType: string;
  current: number;
  limit: number;
  onDismiss: () => void;
}

const LimitBanner: React.FC<LimitBannerProps> = ({ planType, current, limit, onDismiss }) => {
  return (
    <div className="bg-amber-50 border border-amber-200 rounded-lg p-4 flex items-center justify-between mb-6">
      <div>
        <p className="font-medium text-amber-800">
          Você está quase no limite do plano {planType} ({current}/{limit} produtos).
        </p>
        <p className="text-sm text-amber-700">
          Faça upgrade para adicionar mais produtos e desbloquear recursos premium.
        </p>
      </div>
      <div className="flex gap-2 shrink-0">
        <a
          href="/admin/planos"
          className="inline-block bg-amber-500 text-white px-4 py-2 rounded text-sm hover:bg-amber-600 transition"
        >
          Ver planos
        </a>
        <button
          onClick={onDismiss}
          className="text-amber-600 hover:text-amber-800 text-sm px-2"
          aria-label="Dispensar aviso"
        >
          ✕
        </button>
      </div>
    </div>
  );
};

export default LimitBanner;
