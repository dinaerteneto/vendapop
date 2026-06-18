import React, { useEffect, useState } from 'react';
import { Link, useSearchParams, useNavigate } from 'react-router-dom';
import api from '../../services/api';
import { SEOHead } from '../../components/common/SEOHead';
import { toast } from 'react-toastify';

const GoogleCallback: React.FC = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [status, setStatus] = useState<'loading' | 'error' | 'link_required'>('loading');
  const [error, setError] = useState('');

  useEffect(() => {
    const errorParam = searchParams.get('error');
    if (errorParam) {
      setError('Falha na autenticação com Google. Tente novamente.');
      setStatus('error');
      return;
    }

    const authStatus = searchParams.get('status');

    if (authStatus === 'verified') {
      const token = searchParams.get('token');
      const tenantSlug = searchParams.get('tenant_slug');
      const userName = searchParams.get('user_name');
      const userEmail = searchParams.get('user_email');
      if (token) {
        localStorage.setItem('admin_token', token);
        if (tenantSlug) localStorage.setItem('tenant_slug', tenantSlug);
        localStorage.setItem('user', JSON.stringify({
          name: userName || 'Admin',
          email: userEmail || '',
        }));
        window.dispatchEvent(new Event('localStorageChange'));
        toast.success('Login realizado com sucesso!');
        navigate('/admin');
      }
    } else if (authStatus === 'link_required') {
      setStatus('link_required');
    } else {
      setError('Resposta inválida do Google. Tente novamente.');
      setStatus('error');
    }
  }, [searchParams, navigate]);

  const handleLink = async () => {
    try {
      const { data } = await api.post('/admin/auth/google/link', {
        email: searchParams.get('email'),
        google_id: searchParams.get('google_id'),
        google_token: searchParams.get('google_token'),
        google_refresh_token: searchParams.get('google_refresh_token'),
      });
      localStorage.setItem('admin_token', data.token);
      localStorage.setItem('user', JSON.stringify(data.user));
      if (data.tenant_slug) localStorage.setItem('tenant_slug', data.tenant_slug);
      window.dispatchEvent(new Event('localStorageChange'));
      toast.success('Conta vinculada com sucesso!');
      navigate('/admin');
    } catch (err: any) {
      console.error(err);
      toast.error(err.response?.data?.message || 'Erro ao vincular conta.');
    }
  };

  if (status === 'loading') {
    return (
      <div className="flex min-h-screen items-center justify-center bg-gray-100">
        <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-lg text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Processando login...</p>
        </div>
      </div>
    );
  }

  if (status === 'link_required') {
    return (
      <div className="flex min-h-screen items-center justify-center bg-gray-100">
        <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-lg text-center">
          <h2 className="mb-4 text-xl font-bold text-gray-800">Vincular Conta Google</h2>
          <p className="mb-6 text-gray-600">
            Uma conta com o e-mail <strong>{searchParams.get('email')}</strong> já existe mas
            ainda não foi verificada. Deseja vincular sua conta Google e verificar automaticamente?
          </p>
          <div className="space-y-3">
            <button
              onClick={handleLink}
              className="w-full rounded bg-blue-600 py-2 text-white transition hover:bg-blue-700"
            >
              Sim, vincular e verificar
            </button>
            <Link
              to="/admin/login"
              className="block text-sm text-gray-500 hover:text-gray-700"
            >
              Não, usar outro e-mail
            </Link>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-gray-100">
      <SEOHead title="VendaPop" noIndex />
      <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-lg text-center">
        <h2 className="mb-4 text-xl font-bold text-red-600">Erro</h2>
        <p className="mb-6 text-gray-600">{error}</p>
        <Link to="/admin/login" className="text-blue-600 hover:underline">
          Voltar para o login
        </Link>
      </div>
    </div>
  );
};

export default GoogleCallback;
