import React, { useState } from 'react';
import { useNavigate, Link, useSearchParams } from 'react-router-dom';
import { GoogleReCaptchaProvider, useGoogleReCaptcha } from 'react-google-recaptcha-v3';
import api from '../../services/api';
import { toast } from 'react-toastify';

const RegisterForm: React.FC = () => {
  const [searchParams] = useSearchParams();
  const inviteFromUrl = searchParams.get('invite') || '';

  const [formData, setFormData] = useState({
    store_name: '',
    store_slug: '',
    whatsapp_number: '',
    email: '',
    invite_code: inviteFromUrl,
  });
  const [loading, setLoading] = useState(false);
  const [termsAccepted, setTermsAccepted] = useState(false);
  const { executeRecaptcha } = useGoogleReCaptcha();
  const navigate = useNavigate();

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!termsAccepted) {
      toast.error('Você precisa aceitar os Termos de Uso e a Política de Privacidade.');
      return;
    }

    if (!executeRecaptcha) {
      toast.error('reCAPTCHA não está pronto. Aguarde um momento.');
      return;
    }
    
    setLoading(true);

    try {
      // Executar reCAPTCHA v3
      const recaptchaToken = await executeRecaptcha('register');
      
      const payload: Record<string, any> = {
        ...formData,
        terms_accepted: true,
        recaptcha_token: recaptchaToken,
      };

      if (!formData.invite_code) {
        delete payload.invite_code;
      }

      await api.post('/admin/register', payload);
      toast.success('Loja cadastrada com sucesso! Verifique seu e-mail para ativar sua conta e receber sua senha.');
      navigate('/admin/login');
    } catch (err: any) {
      console.error('Erro completo:', err);
      console.error('Response:', err.response);
      console.error('Response data:', err.response?.data);
      
      let errorMessage = 'Erro ao cadastrar loja.';
      const errors = err.response?.data?.errors;
      const message = err.response?.data?.message;
      
      if (message) {
        errorMessage = message;
        toast.error(message);
      }
      
      if (errors) {
        Object.keys(errors).forEach((key) => {
          const errorText = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
          toast.error(`${key}: ${errorText}`, {
            autoClose: 5000,
          });
        });
      } else if (!message && err.response?.status) {
        if (err.response.status === 422) {
          errorMessage = 'Dados inválidos. Verifique os campos preenchidos.';
        } else if (err.response.status === 500) {
          errorMessage = 'Erro interno do servidor. Tente novamente mais tarde.';
        } else {
          errorMessage = `Erro ${err.response.status}: ${err.response.statusText || 'Erro desconhecido'}`;
        }
        toast.error(errorMessage, {
          autoClose: 5000,
        });
      } else if (err.message) {
        toast.error(`Erro: ${err.message}`, {
          autoClose: 5000,
        });
      } else {
        toast.error('Erro desconhecido. Verifique o console do navegador para mais detalhes.', {
          autoClose: 5000,
        });
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
                popvenda/
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
              A URL será: popvenda/{formData.store_slug || 'nomeDaLoja'}
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
            <label className="mb-2 block font-medium text-gray-700">
              Código de Convite
            </label>
            <input
              type="text"
              name="invite_code"
              value={formData.invite_code}
              onChange={handleChange}
              placeholder="ABC12345"
              className="w-full rounded border px-3 py-2 focus:border-purple-500 focus:outline-none font-mono uppercase"
              maxLength={8}
            />
            {formData.invite_code && (
              <p className="mt-1 text-xs text-green-600">
                Convite aplicado! Sua loja terá acesso Premium.
              </p>
            )}
            {!formData.invite_code && (
              <p className="mt-1 text-xs text-gray-400">
                Opcional — se você recebeu um código de convite
              </p>
            )}
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

          <div className="mb-4">
            <label className="flex items-start gap-2 text-sm text-gray-600">
              <input
                type="checkbox"
                checked={termsAccepted}
                onChange={(e) => setTermsAccepted(e.target.checked)}
                className="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                required
              />
              <span>
                Li e aceito os{' '}
                <Link to="/termos" target="_blank" className="text-blue-600 hover:underline">
                  Termos de Uso
                </Link>
                {' '}e a{' '}
                <Link to="/privacidade" target="_blank" className="text-blue-600 hover:underline">
                  Política de Privacidade
                </Link>
              </span>
            </label>
          </div>

          <button
            type="submit"
            disabled={loading || !executeRecaptcha}
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

const Register: React.FC = () => {
  const siteKey = import.meta.env.VITE_RECAPTCHA_SITE_KEY || '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';
  
  return (
    <GoogleReCaptchaProvider
      reCaptchaKey={siteKey}
      scriptProps={{
        async: false,
        defer: false,
        appendTo: "head",
        nonce: undefined,
      }}
    >
      <RegisterForm />
    </GoogleReCaptchaProvider>
  );
};

export default Register;

