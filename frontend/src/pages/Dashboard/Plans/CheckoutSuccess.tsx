import React, { useEffect, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import api from '../../../services/api';
import { SEOHead } from '../../../components/common/SEOHead';

const CheckoutSuccess: React.FC = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const planType = searchParams.get('plan') || 'basic';
  const [polling, setPolling] = useState(true);

  const planLabel: Record<string, string> = {
    free: 'Grátis',
    basic: 'Básico',
    pro: 'Pro',
  };

  useEffect(() => {
    if (typeof window.gtag === 'function') {
      window.gtag('event', 'purchase', {
        plan_type: planType,
        currency: 'BRL',
      });
    }
  }, [planType]);

  useEffect(() => {
    let cancelled = false;
    let attempts = 0;

    const poll = async () => {
      while (!cancelled && attempts < 30) {
        try {
          const { data } = await api.get('/admin/subscription');
          if (data.status === 'active' || data.status === 'trialing') {
            if (!cancelled) setPolling(false);
            return;
          }
        } catch {
          // continue polling
        }
        attempts++;
        await new Promise((r) => setTimeout(r, 2000));
      }
      if (!cancelled) setPolling(false);
    };

    poll();

    return () => {
      cancelled = true;
    };
  }, []);

  return (
    <div>
      <SEOHead title="Pagamento Confirmado — VendaPop" noIndex />
      <div className="max-w-lg mx-auto mt-10 text-center">
        <div className="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-6">
          <svg className="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
          </svg>
        </div>

        <h1 className="text-2xl font-bold text-gray-900 mb-2">
          Seu plano {planLabel[planType] || planType} está ativo!
        </h1>
        <p className="text-gray-600 mb-6">
          Seu pagamento foi confirmado e você já pode aproveitar todos os recursos do plano.
        </p>

        {polling && (
          <div className="flex items-center justify-center gap-2 mb-6 text-sm text-gray-500">
            <svg className="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
              <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Aguardando confirmação da assinatura...
          </div>
        )}

        <button
          onClick={() => navigate('/admin')}
          className="px-6 py-3 text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition font-medium"
        >
          Ir para o dashboard
        </button>
      </div>
    </div>
  );
};

export default CheckoutSuccess;
