import React from 'react';
import { useNavigate } from 'react-router-dom';
import { SEOHead } from '../../../components/common/SEOHead';

const CheckoutError: React.FC = () => {
  const navigate = useNavigate();

  return (
    <div>
      <SEOHead title="Pagamento não concluído — VendaPop" noIndex />
      <div className="max-w-lg mx-auto mt-10 text-center">
        <div className="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100 mb-6">
          <svg className="h-10 w-10 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>

        <h1 className="text-2xl font-bold text-gray-900 mb-2">
          Pagamento não concluído
        </h1>
        <p className="text-gray-600 mb-8">
          O pagamento não foi concluído. Seu plano atual continua ativo e você pode tentar novamente quando quiser.
        </p>

        <div className="flex flex-col items-center gap-3">
          <button
            onClick={() => navigate('/admin/planos')}
            className="px-6 py-3 text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition font-medium"
          >
            Tentar novamente
          </button>
          <a
            href="mailto:suporte@vendapop.com.br"
            className="text-sm text-indigo-600 hover:text-indigo-800 underline"
          >
            Falar com suporte
          </a>
        </div>
      </div>
    </div>
  );
};

export default CheckoutError;
