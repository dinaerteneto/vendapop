const steps = [
  { num: '1', title: 'Monte sua loja', desc: 'Cadastre seus produtos com fotos, preços, tamanhos e cores em minutos.' },
  { num: '2', title: 'Compartilhe o link', desc: 'Cole o link na bio do Instagram e em grupos de WhatsApp.' },
  { num: '3', title: 'Receba pedidos', desc: 'O cliente monta o carrinho sozinho e o pedido chega organizado no seu WhatsApp.' },
];

const HowItWorksSection: React.FC = () => {
  return (
    <section className="bg-white py-16">
      <div className="container mx-auto px-4">
        <h2 className="text-2xl font-bold text-gray-900 text-center mb-12">Como funciona</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
          {steps.map((step) => (
            <div key={step.num} className="text-center">
              <div className="w-14 h-14 bg-purple-100 text-purple-700 rounded-2xl flex items-center justify-center text-xl font-bold mx-auto mb-4">
                {step.num}
              </div>
              <h3 className="font-semibold text-gray-800 mb-2">{step.title}</h3>
              <p className="text-sm text-gray-500">{step.desc}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default HowItWorksSection;
