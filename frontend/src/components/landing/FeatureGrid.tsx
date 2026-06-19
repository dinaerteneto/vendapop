import { useScrollReveal } from '../../hooks/useScrollReveal';

const features = [
  { icon: '📦', title: 'Variações', desc: 'Tamanho, cor e estoque por combinação' },
  { icon: '📱', title: 'PWA', desc: 'Instala no celular como um app nativo' },
  { icon: '📊', title: 'Estoque', desc: 'Controle simples ou avançado por atributo' },
  { icon: '💳', title: 'PIX', desc: 'Chave PIX na página de confirmação' },
  { icon: '💬', title: 'WhatsApp', desc: 'Pedido finaliza direto no seu WhatsApp' },
  { icon: '🎨', title: 'Sua marca', desc: 'Logo, cores e identidade personalizada' },
];

const FeatureGrid: React.FC = () => {
  const { ref, inView } = useScrollReveal();

  return (
    <section ref={ref} className="bg-gray-100 py-16">
      <div className="container mx-auto px-4">
        <div className={`transition-all duration-700 ${inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'}`}>
          <h2 className="text-2xl font-bold text-gray-900 text-center mb-12">Tudo que sua loja precisa</h2>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
          {features.map((f, i) => (
            <div
              key={f.title}
              className={`bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-all duration-500 ${
                inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'
              }`}
              style={{ transitionDelay: `${100 + i * 100}ms` }}
            >
              <div className="text-2xl mb-3">{f.icon}</div>
              <h3 className="font-semibold text-gray-800 mb-1">{f.title}</h3>
              <p className="text-sm text-gray-500">{f.desc}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default FeatureGrid;
