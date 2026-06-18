import React from 'react';

interface OnboardingBannerProps {
  step: number;
  onContinue: () => void;
  onDismiss: () => void;
}

const STEP_LABELS: Record<number, string> = {
  0: 'Identidade',
  1: 'Vitrine',
  2: 'WhatsApp',
  3: 'Compartilhar',
};

const OnboardingBanner: React.FC<OnboardingBannerProps> = ({ step, onContinue, onDismiss }) => {
  return (
    <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex items-center justify-between mb-6">
      <div>
        <p className="font-medium text-yellow-800">Sua loja ainda não está configurada</p>
        <p className="text-sm text-yellow-700">
          {step > 0
            ? `Você parou no passo ${step} de 4 (${STEP_LABELS[step] || ''}).`
            : 'Configure agora e compartilhe com suas clientes!'}
        </p>
      </div>
      <div className="flex gap-2 shrink-0">
        <button
          onClick={onContinue}
          className="bg-yellow-500 text-white px-4 py-2 rounded text-sm hover:bg-yellow-600 transition"
        >
          Continuar configuração
        </button>
        <button onClick={onDismiss} className="text-yellow-600 hover:text-yellow-800 text-sm px-2">
          ✕
        </button>
      </div>
    </div>
  );
};

export default OnboardingBanner;
