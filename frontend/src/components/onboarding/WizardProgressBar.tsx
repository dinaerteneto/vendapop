import React from 'react';

interface WizardProgressBarProps {
  currentStep: number;
}

const STEPS = [
  { label: 'Identidade', icon: '1' },
  { label: 'Vitrine', icon: '2' },
  { label: 'WhatsApp', icon: '3' },
  { label: 'Compartilhar', icon: '4' },
];

const WizardProgressBar: React.FC<WizardProgressBarProps> = ({ currentStep }) => {
  return (
    <div className="bg-white border-b px-8 py-4">
      <div className="max-w-4xl mx-auto flex items-center justify-between relative">
        {STEPS.map((step, index) => {
          const stepNum = index + 1;
          const isCompleted = stepNum < currentStep;
          const isActive = stepNum === currentStep;

          return (
            <React.Fragment key={step.label}>
              {index > 0 && (
                <div className="flex-1 mx-2">
                  <div className={`h-1 rounded ${stepNum <= currentStep ? 'bg-purple-600' : 'bg-gray-200'}`} />
                </div>
              )}
              <div className="flex flex-col items-center" data-active={isActive ? 'true' : undefined}>
                <div
                  className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors ${
                    isCompleted
                      ? 'bg-green-500 text-white'
                      : isActive
                      ? 'bg-purple-600 text-white ring-4 ring-purple-100'
                      : 'bg-gray-200 text-gray-500'
                  }`}
                >
                  {isCompleted ? '✓' : step.icon}
                </div>
                <span
                  className={`text-xs mt-1 whitespace-nowrap ${
                    isActive ? 'text-purple-700 font-semibold' : isCompleted ? 'text-green-600' : 'text-gray-400'
                  }`}
                >
                  {step.label}
                </span>
              </div>
            </React.Fragment>
          );
        })}
      </div>
    </div>
  );
};

export default WizardProgressBar;
