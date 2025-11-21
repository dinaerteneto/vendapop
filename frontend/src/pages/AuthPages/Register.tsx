import React, { useState, useRef } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import ReCAPTCHA from 'react-google-recaptcha';
import api from '../../services/api';
import { toast } from 'react-toastify';

const Register: React.FC = () => {
  const [formData, setFormData] = useState({
    store_name: '',
    store_slug: '',
    whatsapp_number: '',
    email: '',
  });
  const [loading, setLoading] = useState(false);
  const [recaptchaToken, setRecaptchaToken] = useState<string | null>(null);
  const recaptchaRef = useRef<ReCAPTCHA>(null);
  const navigate = useNavigate();

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!recaptchaToken) {
      toast.error('Por favor, complete a verificação reCAPTCHA.');
      return;
    }
    
    setLoading(true);

    try {
      await api.post('/admin/register', {
        ...formData,
        recaptcha_token: recaptchaToken,
      });
      toast.success('Loja cadastrada com sucesso! Verifique seu e-mail para ativar sua conta e receber sua senha.');
      navigate('/admin/login');
    } catch (err: any) {
      console.error(err);
      const errorMessage = err.response?.data?.message || 'Erro ao cadastrar loja.';
      const errors = err.response?.data?.errors;
      
      // Reset reCAPTCHA on error
      if (recaptchaRef.current) {
        recaptchaRef.current.reset();
        setRecaptchaToken(null);
      }
      
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
      <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
        <h2 className="mb-6 text-center text-2xl font-bold text-gray-800">Cadastre sua Loja</h2>
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
            <label className="mb-2 block font-medium text-gray-700">
              URL da Loja (ex: nomeDaLoja)
            </label>
            <div className="flex items-center">
              <span className="rounded-l border border-r-0 border-gray-300 bg-gray-50 px-3 py-2 text-gray-600">
                vestezap/
              </span>
              <input
                type="text"
                name="store_slug"
                value={formData.store_slug}
                onChange={handleChange}
                placeholder="nomeDaLoja"
                className="w-full rounded-r border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none"
                required
                pattern="[a-z0-9-]+"
                title="Apenas letras minúsculas, números e hífens"
              />
            </div>
            <p className="mt-1 text-xs text-gray-500">
              A URL será: vestezap/{formData.store_slug || 'nomeDaLoja'}
            </p>
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

          <div className="mb-4">
            <label className="mb-2 block font-medium text-gray-700">E-mail</label>
            <input
              type="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              className="w-full rounded border px-3 py-2 focus:border-blue-500 focus:outline-none"
              required
            />
            <p className="mt-1 text-xs text-gray-500">
              Você receberá sua senha por e-mail após a verificação
            </p>
          </div>

          <div className="mb-6 flex justify-center">
            <ReCAPTCHA
              ref={recaptchaRef}
              sitekey={import.meta.env.VITE_RECAPTCHA_SITE_KEY || '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'} // Test key for development
              onChange={(token: string | null) => setRecaptchaToken(token)}
              onExpired={() => setRecaptchaToken(null)}
            />
          </div>

          <button
            type="submit"
            disabled={loading || !recaptchaToken}
            className="w-full rounded bg-blue-600 py-2 text-white transition hover:bg-blue-700 disabled:opacity-50"
          >
            {loading ? 'Cadastrando...' : 'Cadastrar'}
          </button>
        </form>

        <p className="mt-4 text-center text-sm text-gray-600">
          Já tem uma conta?{' '}
          <Link to="/admin/login" className="text-blue-600 hover:underline">
            Fazer login
          </Link>
        </p>
      </div>
    </div>
  );
};

export default Register;

