import { Link } from 'react-router-dom';

const HeroSection: React.FC = () => {
  return (
    <section className="bg-white min-h-[85vh] flex items-center">
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
                to="/admin/login"
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
          <div className="hidden md:flex justify-center">
            <div className="relative">
              <div className="w-72 h-[500px] bg-gray-900 rounded-[3rem] p-3 shadow-2xl">
                <div className="w-full h-full bg-white rounded-[2.5rem] overflow-hidden">
                  <img
                    src="/images/stores/modachic.png"
                    alt="Exemplo de loja no PopVenda"
                    className="w-full h-full object-cover object-top"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default HeroSection;
