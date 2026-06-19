import { Link } from 'react-router-dom';
import PhoneSlideshow from './PhoneSlideshow';

interface HeroSectionProps {
  spotsRemaining: number | null;
  spotsLoading: boolean;
  spotsError: boolean;
}

const HeroSection: React.FC<HeroSectionProps> = ({ spotsRemaining, spotsLoading, spotsError }) => {
  const loaded = !spotsLoading && !spotsError;
  const spotsAvailable = loaded && spotsRemaining !== null && spotsRemaining > 0;
  const spotsExhausted = loaded && spotsRemaining === 0;

  const badgeText = spotsAvailable
    ? `Beta exclusivo — ${spotsRemaining} vagas restantes`
    : spotsExhausted
      ? 'Beta exclusivo — vagas esgotadas'
      : 'Beta exclusivo';

  const scrollToWaitlist = () => {
    const el = document.querySelector('#waitlist');
    if (el) el.scrollIntoView({ behavior: 'smooth' });
  };

  return (
    <section className="min-h-[85vh] flex flex-col pt-14" style={{ backgroundColor: '#FDF8F6' }}>
      <div className="flex-1 flex items-center">
      <div className="container mx-auto px-4 py-16">
        <div className="grid md:grid-cols-2 gap-12 items-center">
          <div>
            <span className="inline-block px-3 py-1 text-xs font-semibold text-purple-700 bg-purple-100 rounded-full mb-4">
              {badgeText}
            </span>
            <h1 className="text-4xl md:text-5xl font-bold text-gray-900 leading-tight mb-4">
              Seu catálogo online.
              <br />
              <span className="text-purple-600">O pedido cai pronto no seu WhatsApp.</span>
            </h1>
            <p className="text-lg text-gray-500 mb-8">
              Monte sua loja em 5 minutos. Seus clientes navegam, escolhem variações e o pedido chega organizado no seu WhatsApp — sem calcular total na mão, sem conversa perdida.
            </p>
            <div className="flex flex-col sm:flex-row gap-3">
              {spotsAvailable ? (
                <Link
                  to="/admin/register"
                  className="px-6 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition text-center"
                >
                  Criar minha loja grátis
                </Link>
              ) : spotsExhausted ? (
                <button
                  onClick={scrollToWaitlist}
                  className="px-6 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition text-center"
                >
                  Entrar na lista de espera
                </button>
              ) : (
                <>
                  <Link
                    to="/admin/register"
                    className="px-6 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition text-center"
                  >
                    Tenho um convite
                  </Link>
                  <button
                    onClick={scrollToWaitlist}
                    className="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:border-purple-400 hover:text-purple-600 transition text-center"
                  >
                    Quero ser convidado(a)
                  </button>
                </>
              )}
            </div>
          </div>
          <PhoneSlideshow />
        </div>
      </div>
      </div>
    </section>
  );
};

export default HeroSection;
