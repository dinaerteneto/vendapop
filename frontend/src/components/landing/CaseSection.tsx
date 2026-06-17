import { Link } from 'react-router-dom';

const cases = [
  {
    name: 'Moda Chic',
    slug: 'modachic',
    sector: 'Moda Feminina',
    products: 59,
    image: '/images/stores/modachic.png',
  },
  {
    name: 'Casa & Lar Imóveis',
    slug: 'casa-lar-imoveis',
    sector: 'Imobiliária',
    products: 6,
    image: '/images/stores/casa-lar-imoveis.png',
  },
  {
    name: 'TechStore Brasil',
    slug: 'techstore-brasil',
    sector: 'Eletrônicos',
    products: 6,
    image: '/images/stores/techstore-brasil.png',
  },
  {
    name: 'Boa Massa',
    slug: 'boa-massa',
    sector: 'Alimentação',
    products: 6,
    image: '/images/stores/pizzaria-boa-massa.png',
  },
];

const CaseSection: React.FC = () => {
  return (
    <section className="bg-gray-50 py-16">
      <div className="container mx-auto px-4 text-center">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Lojas que já estão vendendo mais</h2>
        <p className="text-gray-500 mb-8">Do delivery de pizza aos eletrônicos — qualquer negócio cabe aqui.</p>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {cases.map((c) => (
            <Link
              key={c.slug}
              to={`/${c.slug}`}
              target="_blank"
              className="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition group"
            >
              <div className="h-96 bg-gray-100 overflow-hidden">
                <img
                  src={c.image}
                  alt={`Loja ${c.name}`}
                  style={{ objectPosition: '0 -140px' }}
                />
              </div>
              <div className="p-4 text-left">
                <h3 className="font-semibold text-gray-900 group-hover:text-purple-600 transition">
                  {c.name}
                </h3>
                <p className="text-xs text-gray-400 mt-1">
                  {c.sector} · {c.products} produtos
                </p>
              </div>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
};

export default CaseSection;
