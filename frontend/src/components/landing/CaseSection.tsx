import { Link } from 'react-router-dom';
import { useScrollReveal } from '../../hooks/useScrollReveal';

const cases = [
  {
    name: 'Moda Chic',
    slug: 'modachic',
    sector: 'Moda Feminina',
    products: 59,
    image: '/images/stores/modachic.png',
    avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=80&h=80&fit=crop&crop=face',
  },
  {
    name: 'Casa & Lar Imóveis',
    slug: 'casa-lar-imoveis',
    sector: 'Imobiliária',
    products: 6,
    image: '/images/stores/casa-lar-imoveis.png',
    avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=80&h=80&fit=crop&crop=face',
  },
  {
    name: 'TechStore Brasil',
    slug: 'techstore-brasil',
    sector: 'Eletrônicos',
    products: 6,
    image: '/images/stores/techstore-brasil.png',
    avatar: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=80&h=80&fit=crop&crop=face',
  },
  {
    name: 'Boa Massa',
    slug: 'boa-massa',
    sector: 'Alimentação',
    products: 6,
    image: '/images/stores/pizzaria-boa-massa.png',
    avatar: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=80&h=80&fit=crop&crop=face',
  },
];

const CaseSection: React.FC = () => {
  const { ref, inView } = useScrollReveal();

  return (
    <section ref={ref} id="cases" className="bg-gray-100 py-16">
      <div className="container mx-auto px-4 text-center">
        <div className={`transition-all duration-700 ${inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'}`}>
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Lojas que já estão vendendo mais</h2>
          <p className="text-gray-500 mb-8">Do delivery de pizza aos eletrônicos — qualquer negócio cabe aqui.</p>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {cases.map((c, i) => (
            <Link
              key={c.slug}
              to={`/${c.slug}`}
              target="_blank"
              className={`bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-500 group ${
                inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
              }`}
              style={{ transitionDelay: `${200 + i * 120}ms` }}
            >
              <div className="h-96 bg-gray-100 overflow-hidden">
                <img
                  src={c.image}
                  alt={`Loja ${c.name}`}
                  style={{ objectPosition: '0 -140px' }}
                />
              </div>
              <div className="p-4 text-left flex items-center gap-3">
                <img
                  src={c.avatar}
                  alt=""
                  className="w-10 h-10 rounded-full object-cover shrink-0 ring-2 ring-gray-100"
                />
                <div>
                  <h3 className="font-semibold text-gray-900 group-hover:text-purple-600 transition">
                    {c.name}
                  </h3>
                  <p className="text-xs text-gray-400 mt-0.5">
                    {c.sector} · {c.products} produtos
                  </p>
                </div>
              </div>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
};

export default CaseSection;
