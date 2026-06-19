import { Link } from 'react-router-dom';
import PhoneSlideshow from './PhoneSlideshow';

const HeroSection: React.FC = () => {
  return (
    <section className="relative min-h-[85vh] flex flex-col pt-14 overflow-hidden" style={{ backgroundColor: '#FDF8F6' }}>
      <div
        className="absolute inset-0 pointer-events-none"
        style={{
          background: `
            radial-gradient(ellipse 80% 60% at 20% 80%, rgba(201,77,109,0.07) 0%, transparent 60%),
            radial-gradient(ellipse 60% 50% at 85% 15%, rgba(124,58,237,0.05) 0%, transparent 60%),
            repeating-linear-gradient(45deg, transparent, transparent 3px, rgba(201,77,109,0.015) 3px, rgba(201,77,109,0.015) 6px)
          `,
        }}
      />
      <div className="flex-1 flex items-center relative">
      <div className="container mx-auto px-4 py-16">
        <div className="grid md:grid-cols-2 gap-12 items-center">
          <div>
            <span className="inline-block px-3 py-1 text-xs font-semibold text-purple-700 bg-purple-100 rounded-full mb-4">
              Beta exclusivo — acesso apenas por convite
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
              <Link
                to="/admin/register"
                className="px-6 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition text-center"
              >
                Tenho um convite
              </Link>
              <a
                href="#waitlist"
                className="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:border-purple-400 hover:text-purple-600 transition text-center"
              >
                Quero ser avisado
              </a>
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
