import React, { useState } from 'react';
import { useNavigate, useSearchParams, Link } from 'react-router-dom';
import api from '../../services/api';
import { toast } from 'react-toastify';

const GoogleOnboarding: React.FC = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    store_name: '',
    store_slug: '',
    whatsapp_number: '',
  });
  const [loading, setLoading] = useState(false);

  const temporaryToken = searchParams.get('temporary_token');

  if (!temporaryToken) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-gray-100">
        <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-lg text-center">
          <h2 className="mb-4 text-xl font-bold text-red-600">Link Inválido</h2>
          <p className="mb-6 text-gray-600">Token temporário não encontrado. Faça login novamente.</p>
          <Link to="/admin/login" className="text-blue-600 hover:underline">
            Voltar para o login
          </Link>
        </div>
      </div>
    );
  }

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      const { data } = await api.post('/admin/onboarding', {
        ...formData,
        temporary_token: temporaryToken,
      });
      localStorage.setItem('admin_token', data.token);
      localStorage.setItem('user', JSON.stringify(data.user));
      if (data.tenant_slug) localStorage.setItem('tenant_slug', data.tenant_slug);
      window.dispatchEvent(new Event('localStorageChange'));
      toast.success('Loja criada com sucesso!');
      navigate('/admin');
    } catch (err: any) {
      console.error(err);
      const errors = err.response?.data?.errors;
      const message = err.response?.data?.message;
      if (errors) {
        Object.keys(errors).forEach((key) => {
          const errorText = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
          toast.error(errorText);
        });
      } else if (message) {
        toast.error(message);
      } else {
        toast.error('Erro ao criar loja.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex min-h-screen items-center justify-center bg-gray-100">
      <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
        <h2 className="mb-6 text-center text-2xl font-bold text-gray-800">Complete seu Cadastro</h2>
        <p className="mb-6 text-center text-gray-600">
          Bem-vindo! Para finalizar, precisamos de algumas informações da sua loja.
        </p>
        <form onSubmit={handleSubmit}>
          <div className="mb-4">
            <label className="mb-2 block font-medium text-gray-700">Nome da Loja</label>
            <input
              type="text"
              name="store_name"
              value={formData.store_name}
              onChange={handleChange}
              className="w-full rounded border px-3 py-2 focus:border-blue-500 focus:outline-none"
              required
            />
          </div>
          <div className="mb-4">
            <label className="mb-2 block font-medium text-gray-700">URL da Loja</label>
            <div className="flex items-center">
              <span className="rounded-l border border-r-0 border-gray-300 bg-gray-50 px-3 py-2 text-gray-600">
                {window.location.hostname}/
              </span>
              <input
                type="text"
                name="store_slug"
                value={formData.store_slug}
                onChange={handleChange}
                placeholder="minha-loja"
                className="w-full rounded-r border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none"
                required
                pattern="[a-z0-9-]+"
                title="Apenas letras minúsculas, números e hífens"
              />
            </div>
          </div>
          <div className="mb-4">
            <label className="mb-2 block font-medium text-gray-700">WhatsApp (com DDD)</label>
            <input
              type="text"
              name="whatsapp_number"
              value={formData.whatsapp_number}
              onChange={handleChange}
              placeholder="5511999999999"
              className="w-full rounded border px-3 py-2 focus:border-blue-500 focus:outline-none"
              required
            />
          </div>
          <button
            type="submit"
            disabled={loading}
            className="w-full rounded bg-blue-600 py-2 text-white transition hover:bg-blue-700 disabled:opacity-50"
          >
            {loading ? 'Criando...' : 'Finalizar Cadastro'}
          </button>
        </form>
      </div>
    </div>
  );
};

export default GoogleOnboarding;
