import { useState } from 'react';
import { useScrollReveal } from '../../hooks/useScrollReveal';
import api from '../../services/api';

interface WaitlistSectionProps {
  spotsExhausted?: boolean;
}

const WaitlistSection: React.FC<WaitlistSectionProps> = ({ spotsExhausted }) => {
  const [email, setEmail] = useState('');
  const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');
  const [message, setMessage] = useState('');
  const { ref, inView } = useScrollReveal(0.15);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email.trim()) return;

    setStatus('loading');
    try {
      const res = await api.post('/admin/waitlist', { email });
      window.gtag?.('event', 'waitlist_signup', {
        spots_exhausted: spotsExhausted ?? false,
        source: new URLSearchParams(window.location.search).get('utm_source') || 'direct',
      });
      setStatus('success');
      setMessage(res.data.message);
      setEmail('');
    } catch (err: any) {
      if (err?.response?.status === 422) {
        setStatus('success');
        setMessage('Você já está na lista de espera!');
      } else {
        setStatus('error');
        setMessage('Erro ao cadastrar. Tente novamente.');
      }
    }
  };

  return (
    <section ref={ref} id="waitlist" className="bg-purple-600 py-16">
      <div className={`container mx-auto px-4 text-center max-w-lg transition-all duration-700 ${inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'}`}>
        {spotsExhausted ? (
          <>
            <h2 className="text-2xl font-bold text-white mb-2">Vagas esgotadas</h2>
            <p className="text-purple-200 mb-6">
              Vagas esgotadas no momento. Novas vagas toda segunda-feira. Deixe seu email para ser avisado.
            </p>
          </>
        ) : (
          <>
            <h2 className="text-2xl font-bold text-white mb-2">Quero ser convidado(a)</h2>
            <p className="text-purple-200 mb-6">
              Deixe seu email para entrar na lista de espera. As vagas são limitadas.
            </p>
          </>
        )}
        {status === 'success' ? (
          <div className="bg-white/20 rounded-lg p-4">
            <p className="text-white font-medium">{message}</p>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="flex gap-2">
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="seu@email.com"
              required
              className="flex-1 px-4 py-3 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-300"
            />
            <button
              type="submit"
              disabled={status === 'loading'}
              className="px-6 py-3 bg-white text-purple-700 font-semibold rounded-lg hover:bg-purple-50 transition disabled:opacity-50"
            >
              {status === 'loading' ? '...' : 'Quero ser convidado(a)'}
            </button>
          </form>
        )}
        {status === 'error' && <p className="text-red-200 mt-2 text-sm">{message}</p>}
      </div>
    </section>
  );
};

export default WaitlistSection;
