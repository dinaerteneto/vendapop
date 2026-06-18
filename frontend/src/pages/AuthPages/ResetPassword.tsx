import React, { useState, useEffect } from 'react';
import { useNavigate, useSearchParams, Link } from 'react-router-dom';
import api from '../../services/api';
import { SEOHead } from '../../components/common/SEOHead';
import { toast } from 'react-toastify';

const ResetPassword: React.FC = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    email: '',
    token: '',
    password: '',
    password_confirmation: '',
  });
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    const email = searchParams.get('email');
    const token = searchParams.get('token');
    
    if (email && token) {
      setFormData((prev) => ({
        ...prev,
        email,
        token,
      }));
    } else {
      toast.error('Link inválido. Solicite um novo link de redefinição.');
      navigate('/admin/forgot-password');
    }
  }, [searchParams, navigate]);

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
      await api.post('/admin/reset-password', formData);
      toast.success('Senha redefinida com sucesso!');
      navigate('/admin/login');
    } catch (err: any) {
      console.error(err);
      const errorMessage = err.response?.data?.message || 'Erro ao redefinir senha.';
      const errors = err.response?.data?.errors;
      
      if (errors) {
        Object.keys(errors).forEach((key) => {
          toast.error(errors[key][0]);
        });
      } else {
        toast.error(errorMessage);
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex min-h-screen items-center justify-center bg-gray-100">
      <SEOHead title="Redefinir senha — VendaPop" noIndex />
      <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
        <h2 className="mb-6 text-center text-2xl font-bold text-gray-800">Redefinir Senha</h2>
        <form onSubmit={handleSubmit}>
          <div className="mb-4">
            <label className="mb-2 block font-medium text-gray-700">E-mail</label>
            <input
              type="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              className="w-full rounded border px-3 py-2 focus:border-blue-500 focus:outline-none"
              required
              readOnly
            />
          </div>

          <div className="mb-4">
            <label className="mb-2 block font-medium text-gray-700">Nova Senha</label>
            <input
              type="password"
              name="password"
              value={formData.password}
              onChange={handleChange}
              className="w-full rounded border px-3 py-2 focus:border-blue-500 focus:outline-none"
              required
              minLength={8}
            />
          </div>

          <div className="mb-6">
            <label className="mb-2 block font-medium text-gray-700">Confirmar Nova Senha</label>
            <input
              type="password"
              name="password_confirmation"
              value={formData.password_confirmation}
              onChange={handleChange}
              className="w-full rounded border px-3 py-2 focus:border-blue-500 focus:outline-none"
              required
              minLength={8}
            />
          </div>

          <input type="hidden" name="token" value={formData.token} />

          <button
            type="submit"
            disabled={loading}
            className="w-full rounded bg-blue-600 py-2 text-white transition hover:bg-blue-700 disabled:opacity-50"
          >
            {loading ? 'Redefinindo...' : 'Redefinir Senha'}
          </button>
        </form>

        <p className="mt-4 text-center text-sm text-gray-600">
          <Link to="/admin/login" className="text-blue-600 hover:underline">
            Voltar para o login
          </Link>
        </p>
      </div>
    </div>
  );
};

export default ResetPassword;

