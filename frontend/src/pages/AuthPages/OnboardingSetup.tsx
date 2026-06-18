import React, { useState } from 'react';
import WizardProgressBar from '../../components/onboarding/WizardProgressBar';
import ShopPreview from '../../components/onboarding/ShopPreview';
import StepIdentidade from '../../components/onboarding/StepIdentidade';
import StepVitrine from '../../components/onboarding/StepVitrine';
import StepWhatsapp from '../../components/onboarding/StepWhatsapp';
import StepCompartilhar from '../../components/onboarding/StepCompartilhar';

const OnboardingSetup: React.FC = () => {
  const [step, setStep] = useState<1 | 2 | 3 | 4>(() => {
    const savedStep = localStorage.getItem('onboarding_step');
    const parsed = savedStep ? parseInt(savedStep, 10) : 0;
    return (parsed > 0 && parsed <= 4) ? parsed as 1 | 2 | 3 | 4 : 1;
  });
  const [previewRefreshKey, setPreviewRefreshKey] = useState(0);
  const tenantSlug = localStorage.getItem('tenant_slug') || '';

  const handleNext = () => {
    const newStep = Math.min(step + 1, 4) as 1 | 2 | 3 | 4;
    setStep(newStep);
    setPreviewRefreshKey(k => k + 1);
    try { localStorage.setItem('onboarding_step', String(newStep)); } catch {}
  };

  const handleBack = () => {
    const newStep = Math.max(step - 1, 1) as 1 | 2 | 3 | 4;
    setStep(newStep);
    setPreviewRefreshKey(k => k + 1);
  };

  const handleSkip = () => {
    const newStep = Math.min(step + 1, 4) as 1 | 2 | 3 | 4;
    setStep(newStep);
    setPreviewRefreshKey(k => k + 1);
  };

  return (
    <div className="min-h-screen flex flex-col bg-white">
      <WizardProgressBar currentStep={step} />
      <div className="flex flex-1 overflow-hidden">
        <div className="w-2/5 p-8 border-r overflow-y-auto">
          {step === 1 && <StepIdentidade onNext={handleNext} />}
          {step === 2 && <StepVitrine onNext={handleNext} onBack={handleBack} onSkip={handleSkip} />}
          {step === 3 && <StepWhatsapp onNext={handleNext} onBack={handleBack} />}
          {step === 4 && <StepCompartilhar onBack={handleBack} />}
        </div>
        <div className="w-3/5 bg-gray-50 flex items-center justify-center p-8">
          <ShopPreview tenantSlug={tenantSlug} refreshKey={previewRefreshKey} />
        </div>
      </div>
    </div>
  );
};

export default OnboardingSetup;
