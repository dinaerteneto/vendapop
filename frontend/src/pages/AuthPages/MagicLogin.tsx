import React, { useEffect, useState } from 'react';
import { useNavigate, useSearchParams, Link } from 'react-router-dom';
import api from '../../services/api';
import { SEOHead } from '../../components/common/SEOHead';
import { toast } from 'react-toastify';

const MagicLogin: React.FC = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const email = searchParams.get('email');
    const token = searchParams.get('token');

    if (!email || !token) {
      setError('Link inválido. Solicite um novo código de acesso.');
      return;
    }

    const processMagicLink = async () => {
      try {
        const { data } = await api.get('/admin/magic-login', {
          params: { email, token },
        });
        localStorage.setItem('admin_token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        if (data.tenant_slug) {
          localStorage.setItem('tenant_slug', data.tenant_slug);
        }
        window.dispatchEvent(new Event('localStorageChange'));
        toast.success('Login realizado com sucesso!');
        navigate('/admin');
      } catch (err: any) {
        console.error(err);
        setError(err.response?.data?.message || 'Link inválido ou expirado.');
        toast.error('Link inválido ou expirado.');
      }
    };

    processMagicLink();
  }, [searchParams, navigate]);

  return (
    <div className="flex min-h-screen items-center justify-center bg-gray-100">
      <SEOHead title="Acesso rápido — VendaPop" noIndex />
      <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-lg text-center">
        {!error ? (
          <>
            <div className="mb-4">
              <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            </div>
            <p className="text-gray-600">Autenticando...</p>
          </>
        ) : (
          <>
            <div className="mb-4">
              <svg
                className="mx-auto h-12 w-12 text-red-500"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </div>
            <h2 className="mb-4 text-xl font-bold text-red-600">Erro</h2>
            <p className="mb-6 text-gray-600">{error}</p>
            <Link
              to="/admin/login"
              className="text-blue-600 hover:underline"
            >
              Voltar para o login
            </Link>
          </>
        )}
      </div>
    </div>
  );
};

export default MagicLogin;
