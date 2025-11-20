import React, { useState, useEffect } from 'react';
import { useNavigate, useSearchParams, Link } from 'react-router-dom';
import api from '../../services/api';
import { toast } from 'react-toastify';

const VerifyEmail: React.FC = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [verified, setVerified] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const email = searchParams.get('email');
    const token = searchParams.get('token');
    
    if (email && token) {
      verifyEmail(email, token);
    } else {
      setError('Link inválido. Solicite um novo link de verificação.');
      setLoading(false);
    }
  }, [searchParams]);

  const verifyEmail = async (email: string, token: string) => {
    try {
      await api.post('/admin/verify-email', { email, token });
      setVerified(true);
      toast.success('E-mail verificado com sucesso!');
      setTimeout(() => {
        navigate('/admin/login');
      }, 3000);
    } catch (err: any) {
      console.error(err);
      const errorMessage = err.response?.data?.message || 'Erro ao verificar e-mail.';
      setError(errorMessage);
      toast.error(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  const resendVerification = async () => {
    const email = searchParams.get('email');
    if (!email) return;

    try {
      await api.post('/admin/resend-verification', { email });
      toast.success('Novo link de verificação enviado! Verifique seu e-mail.');
    } catch (err: any) {
      console.error(err);
      toast.error(err.response?.data?.message || 'Erro ao reenviar link de verificação.');
    }
  };

  if (loading) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-gray-100">
        <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-lg text-center">
          <div className="mb-4">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          </div>
          <p className="text-gray-600">Verificando e-mail...</p>
        </div>
      </div>
    );
  }

  if (verified) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-gray-100">
        <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-lg text-center">
          <div className="mb-4">
            <svg
              className="mx-auto h-12 w-12 text-green-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M5 13l4 4L19 7"
              />
            </svg>
          </div>
          <h2 className="mb-4 text-2xl font-bold text-gray-800">E-mail Verificado!</h2>
          <p className="mb-6 text-gray-600">
            Seu e-mail foi verificado com sucesso. Você será redirecionado para o login em instantes.
          </p>
          <Link
            to="/admin/login"
            className="text-blue-600 hover:underline"
          >
            Ir para o login agora
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-gray-100">
      <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-lg text-center">
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
        <h2 className="mb-4 text-2xl font-bold text-gray-800">Erro na Verificação</h2>
        <p className="mb-6 text-gray-600">{error}</p>
        <div className="space-y-2">
          <button
            onClick={resendVerification}
            className="w-full rounded bg-blue-600 py-2 text-white transition hover:bg-blue-700"
          >
            Reenviar Link de Verificação
          </button>
          <Link
            to="/admin/login"
            className="block text-blue-600 hover:underline"
          >
            Voltar para o login
          </Link>
        </div>
      </div>
    </div>
  );
};

export default VerifyEmail;

