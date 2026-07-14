import React, { useState, useCallback, useRef } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import ReCAPTCHA from 'react-google-recaptcha';
import api from '../../services/api';
import { SEOHead } from '../../components/common/SEOHead';
import { toast } from 'react-toastify';

const RECAPTCHA_SITE_KEY = import.meta.env.VITE_RECAPTCHA_SITE_KEY || '';

const SignInForm: React.FC = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [emailNotVerified, setEmailNotVerified] = useState(false);
  const [resending, setResending] = useState(false);
  const [otpMode, setOtpMode] = useState(false);
  const [otpStep, setOtpStep] = useState<'email' | 'code'>('email');
  const [otpCode, setOtpCode] = useState(['', '', '', '', '', '']);
  const [otpSending, setOtpSending] = useState(false);
  const [otpVerifying, setOtpVerifying] = useState(false);
  const [recaptchaToken, setRecaptchaToken] = useState<string | null>(null);
  const otpRefs = useRef<(HTMLInputElement | null)[]>([]);
  const recaptchaRef = useRef<ReCAPTCHA>(null);
  const navigate = useNavigate();

  const resetRecaptcha = () => {
    recaptchaRef.current?.reset();
    setRecaptchaToken(null);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setEmailNotVerified(false);
    try {
      const { data } = await api.post('/admin/login', { email, password });
      localStorage.setItem('admin_token', data.token);
      localStorage.setItem('user', JSON.stringify(data.user));
      if (data.tenant_slug) {
        localStorage.setItem('tenant_slug', data.tenant_slug);
      }
      if (data.tenant) {
        localStorage.setItem('tenant', JSON.stringify(data.tenant));
        localStorage.setItem('onboarding_step', String(data.tenant.onboarding_step || 0));
        if (!data.tenant.onboarding_completed) {
          navigate('/admin/setup');
          return;
        }
      }
      window.dispatchEvent(new Event('localStorageChange'));
      navigate('/admin');
    } catch (err: any) {
        console.error(err);
        if (err.response?.status === 403 && err.response?.data?.email_not_verified) {
            setError('E-mail não verificado. Verifique seu e-mail para ativar sua conta.');
            setEmailNotVerified(true);
        } else {
            setError('Login falhou. Verifique suas credenciais.');
        }
    }
  };

  const handleResend = useCallback(async () => {
    if (!recaptchaToken) {
      toast.error('Confirme que você não é um robô.');
      return;
    }

    setResending(true);
    try {
      await api.post('/admin/resend-verification', {
        email,
        recaptcha_token: recaptchaToken,
      });
      toast.success('Novo e-mail de verificação enviado! Verifique sua caixa de entrada.');
      setEmailNotVerified(false);
      setError('');
    } catch (err: any) {
      console.error(err);
      toast.error(err.response?.data?.message || 'Erro ao reenviar e-mail de verificação.');
    } finally {
      resetRecaptcha();
      setResending(false);
    }
  }, [email, recaptchaToken]);

  const handleOtpSend = async () => {
    if (!recaptchaToken) {
      toast.error('Confirme que você não é um robô.');
      return;
    }

    setOtpSending(true);
    try {
      await api.post('/admin/otp/send', {
        email,
        recaptcha_token: recaptchaToken,
      });
      toast.success('Código enviado para seu e-mail.');
      setOtpStep('code');
    } catch (err: any) {
      console.error(err);
      toast.error(err.response?.data?.message || 'Erro ao enviar código.');
    } finally {
      resetRecaptcha();
      setOtpSending(false);
    }
  };

  const handleOtpDigit = (index: number, value: string) => {
    if (value && !/^\d$/.test(value)) return;

    const newCode = [...otpCode];
    newCode[index] = value;
    setOtpCode(newCode);

    if (value && index < 5) {
      otpRefs.current[index + 1]?.focus();
    }

    // Auto-submit when all digits filled
    if (value && index === 5) {
      const fullCode = newCode.join('');
      if (fullCode.length === 6) {
        verifyOtp(fullCode);
      }
    }
  };

  const handleOtpKeyDown = (index: number, e: React.KeyboardEvent) => {
    if (e.key === 'Backspace' && !otpCode[index] && index > 0) {
      otpRefs.current[index - 1]?.focus();
    }
  };

  const verifyOtp = async (code: string) => {
    setOtpVerifying(true);
    try {
      const { data } = await api.post('/admin/otp/verify', { email, code });
      localStorage.setItem('admin_token', data.token);
      localStorage.setItem('user', JSON.stringify(data.user));
      if (data.tenant_slug) localStorage.setItem('tenant_slug', data.tenant_slug);
      if (data.tenant) {
        localStorage.setItem('tenant', JSON.stringify(data.tenant));
        localStorage.setItem('onboarding_step', String(data.tenant.onboarding_step || 0));
        if (!data.tenant.onboarding_completed) {
          navigate('/admin/setup');
          return;
        }
      }
      window.dispatchEvent(new Event('localStorageChange'));
      toast.success('Login realizado com sucesso!');
      navigate('/admin');
    } catch (err: any) {
      console.error(err);
      toast.error(err.response?.data?.message || 'Código inválido.');
      setOtpCode(['', '', '', '', '', '']);
      otpRefs.current[0]?.focus();
    } finally {
      setOtpVerifying(false);
    }
  };

  const resetOtpMode = () => {
    setOtpMode(false);
    setOtpStep('email');
    setOtpCode(['', '', '', '', '', '']);
  };

  return (
    <div className="flex min-h-screen items-center justify-center bg-gray-100">
      <SEOHead title="Entrar — VendaPop" noIndex />
      <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
        <h2 className="mb-6 text-center text-2xl font-bold text-gray-800">
          {otpMode ? 'Entrar com Código' : 'Login Lojista'}
        </h2>

        {error && <p className="mb-4 text-center text-red-500">{error}</p>}

        {!otpMode && emailNotVerified && (
          <div className="mb-4">
            <div className="mb-3">
              <ReCAPTCHA
                ref={recaptchaRef}
                sitekey={RECAPTCHA_SITE_KEY}
                onChange={(token) => setRecaptchaToken(token)}
                onExpired={() => setRecaptchaToken(null)}
              />
            </div>
            <button
              onClick={handleResend}
              disabled={resending || !recaptchaToken}
              className="w-full rounded bg-orange-500 py-2 text-white transition hover:bg-orange-600 disabled:opacity-50"
            >
              {resending ? 'Enviando...' : 'Reenviar e-mail de verificação'}
            </button>
          </div>
        )}

        {!otpMode ? (
          <>
            <form onSubmit={handleSubmit}>
              <div className="mb-4">
                <label className="mb-2 block font-medium text-gray-700">E-mail</label>
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="w-full rounded border px-3 py-2 focus:border-blue-500 focus:outline-none"
                  required
                />
              </div>
              <div className="mb-6">
                <label className="mb-2 block font-medium text-gray-700">Senha</label>
                <input
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="w-full rounded border px-3 py-2 focus:border-blue-500 focus:outline-none"
                  required
                />
              </div>
              <button type="submit" className="w-full rounded bg-blue-600 py-2 text-white hover:bg-blue-700 transition">
                Entrar
              </button>
            </form>

            <div className="mt-3">
              <button
                onClick={() => {
                  window.location.href = `${import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'}/admin/auth/google`;
                }}
                className="w-full rounded border border-gray-300 bg-white py-2 text-gray-700 transition hover:bg-gray-50 flex items-center justify-center gap-2"
              >
                <svg className="h-5 w-5" viewBox="0 0 24 24">
                  <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                  <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                  <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                  <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Entrar com Google
              </button>
            </div>

            <div className="mt-3 text-center">
              <button
                onClick={() => setOtpMode(true)}
                className="text-sm text-blue-600 hover:underline"
              >
                Entrar com código por e-mail
              </button>
            </div>

            <div className="mt-2 text-center">
              <Link to="/admin/forgot-password" className="text-sm text-blue-600 hover:underline">
                Esqueci minha senha
              </Link>
            </div>
          </>
        ) : (
          <>
            {otpStep === 'email' ? (
              <>
                <p className="mb-4 text-center text-gray-600">
                  Digite seu e-mail para receber um código de acesso.
                </p>
                <div className="mb-4">
                  <label className="mb-2 block font-medium text-gray-700">E-mail</label>
                  <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="w-full rounded border px-3 py-2 focus:border-blue-500 focus:outline-none"
                    required
                  />
                </div>
                <div className="mb-3">
                  <ReCAPTCHA
                    ref={recaptchaRef}
                    sitekey={RECAPTCHA_SITE_KEY}
                    onChange={(token) => setRecaptchaToken(token)}
                    onExpired={() => setRecaptchaToken(null)}
                  />
                </div>
                <button
                  onClick={handleOtpSend}
                  disabled={otpSending || !email || !recaptchaToken}
                  className="w-full rounded bg-blue-600 py-2 text-white transition hover:bg-blue-700 disabled:opacity-50"
                >
                  {otpSending ? 'Enviando...' : 'Enviar código'}
                </button>
              </>
            ) : (
              <>
                <p className="mb-4 text-center text-gray-600">
                  Digite o código de 6 dígitos enviado para <strong>{email}</strong>
                </p>
                <div className="flex justify-center gap-2 mb-6">
                  {otpCode.map((digit, index) => (
                    <input
                      key={index}
                      ref={(el) => { otpRefs.current[index] = el; }}
                      type="text"
                      inputMode="numeric"
                      pattern="[0-9]*"
                      maxLength={1}
                      value={digit}
                      onChange={(e) => handleOtpDigit(index, e.target.value)}
                      onKeyDown={(e) => handleOtpKeyDown(index, e)}
                      disabled={otpVerifying}
                      className="w-12 h-12 text-center text-xl font-bold border rounded focus:border-blue-500 focus:outline-none"
                      autoFocus={index === 0}
                    />
                  ))}
                </div>
                {otpVerifying && (
                  <p className="text-center text-gray-500">Verificando...</p>
                )}
                <div className="mt-3 mb-1">
                  <ReCAPTCHA
                    ref={recaptchaRef}
                    sitekey={RECAPTCHA_SITE_KEY}
                    onChange={(token) => setRecaptchaToken(token)}
                    onExpired={() => setRecaptchaToken(null)}
                  />
                </div>
                <button
                  onClick={handleOtpSend}
                  disabled={otpSending || !recaptchaToken}
                  className="w-full rounded border border-gray-300 bg-white py-2 text-gray-600 text-sm transition hover:bg-gray-50"
                >
                  {otpSending ? 'Enviando...' : 'Reenviar código'}
                </button>
              </>
            )}

            <div className="mt-4 text-center">
              <button
                onClick={resetOtpMode}
                className="text-sm text-blue-600 hover:underline"
              >
                Voltar para login com senha
              </button>
            </div>
          </>
        )}

        {!otpMode && (
          <div className="mt-4 border-t pt-4 text-center">
            <p className="text-sm text-gray-600">
              Não tem uma conta?{' '}
              <Link to="/admin/register" className="text-blue-600 hover:underline">
                Cadastre-se
              </Link>
            </p>
            <p className="mt-2 text-xs text-gray-500">
              Ao usar nossos serviços, você concorda com nossos{' '}
              <Link to="/termos" className="text-blue-600 hover:underline">Termos de Uso</Link>
              {' '}e{' '}
              <Link to="/privacidade" className="text-blue-600 hover:underline">Política de Privacidade</Link>.
            </p>
          </div>
        )}
      </div>
    </div>
  );
};

const SignIn: React.FC = () => <SignInForm />;

export default SignIn;
