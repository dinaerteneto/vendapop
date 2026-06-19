import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../../services/api';
import { SEOHead } from '../../components/common/SEOHead';
import { toast } from 'react-toastify';

const ForgotPassword: React.FC = () => {
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const [sent, setSent] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      await api.post('/admin/forgot-password', { email });
      setSent(true);
      toast.success('Se o e-mail existir, um link de redefinição será enviado.');
    } catch (err: any) {
      console.error(err);
      toast.error(err.response?.data?.message || 'Erro ao enviar e-mail.');
    } finally {
      setLoading(false);
    }
  };

  if (sent) {
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
          <h2 className="mb-4 text-2xl font-bold text-gray-800">E-mail Enviado!</h2>
          <p className="mb-6 text-gray-600">
            Se o e-mail <strong>{email}</strong> existir em nosso sistema, você receberá um link
            para redefinir sua senha.
          </p>
          <p className="mb-6 text-sm text-gray-500">
            Verifique sua caixa de entrada e a pasta de spam.
          </p>
          <Link
            to="/admin/login"
            className="text-blue-600 hover:underline"
          >
            Voltar para o login
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-gray-100">
      <SEOHead title="Recuperar senha — VendaPop" noIndex />
      <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
        <h2 className="mb-6 text-center text-2xl font-bold text-gray-800">Esqueci minha Senha</h2>
        <p className="mb-6 text-center text-gray-600">
          Digite seu e-mail e enviaremos um link para redefinir sua senha.
        </p>
        <form onSubmit={handleSubmit}>
          <div className="mb-6">
            <label className="mb-2 block font-medium text-gray-700">E-mail</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="w-full rounded border px-3 py-2 focus:border-blue-500 focus:outline-none"
              required
            />
          </div>
          <button
            type="submit"
            disabled={loading}
            className="w-full rounded bg-blue-600 py-2 text-white transition hover:bg-blue-700 disabled:opacity-50"
          >
            {loading ? 'Enviando...' : 'Enviar Link de Redefinição'}
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

export default ForgotPassword;

