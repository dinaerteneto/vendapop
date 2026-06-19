import { useScrollReveal } from '../../hooks/useScrollReveal';

interface Plan {
  name: string
  price: string
  period: string
  description: string
  features: string[]
  highlighted?: boolean
  badge?: string
}

const plans: Plan[] = [
  {
    name: 'Grátis',
    price: 'R$ 0',
    period: 'para sempre',
    description: 'Ideal pra quem está começando a vender online.',
    features: [
      'Até 6 produtos',
      'Variações (tamanho, cor)',
      'Link da loja personalizado',
      'Pedidos pelo WhatsApp',
      'PWA — instala como app',
    ],
  },
  {
    name: 'Básico',
    price: 'R$ 29,90',
    period: '/mês',
    description: 'Pra quem já tem um volume bacana de produtos.',
    badge: '90 dias grátis para convidados',
    features: [
      'Até 30 produtos',
      'Tudo do plano Grátis',
      'Chave PIX no checkout',
      'Sem anúncios na loja',
      'Pedidos ilimitados',
    ],
  },
  {
    name: 'Profissional',
    price: 'R$ 59,90',
    period: '/mês',
    description: 'O plano mais escolhido por lojistas que vendem todo dia.',
    highlighted: true,
    features: [
      'Até 100 produtos',
      'Tudo do plano Básico',
      'Controle de estoque',
      'Cupons de desconto',
      'Carrinho abandonado',
      'Relatórios de vendas',
      'Suporte prioritário',
    ],
  },
  {
    name: 'Premium',
    price: 'R$ 99,90',
    period: '/mês',
    description: 'Sua loja com domínio próprio e visual totalmente personalizado.',
    features: [
      'Produtos ilimitados',
      'Tudo do plano Profissional',
      'Domínio próprio',
      'Temas premium',
      'API e webhooks',
      'Remoção marca VendaPop',
    ],
  },
]

const PlansSection: React.FC = () => {
  const { ref, inView } = useScrollReveal();

  return (
    <section ref={ref} id="planos" className="bg-gray-100 py-16">
      <div className="container mx-auto px-4">
        <div className={`text-center mb-8 transition-all duration-700 ${inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'}`}>
          <span className="inline-block px-3 py-1 text-xs font-semibold text-purple-700 bg-purple-100 rounded-full mb-4">
            Somente para convidados
          </span>
          <h2 className="text-2xl md:text-3xl font-bold text-gray-900 mb-3">
            Planos que cabem no seu bolso
          </h2>
          <p className="text-gray-500 max-w-xl mx-auto">
            Do gratuito ao premium, você escolhe o plano ideal pro tamanho da sua loja.
          </p>
        </div>

        <div className={`max-w-2xl mx-auto mb-8 transition-all duration-500 ${inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'}`} style={{ transitionDelay: '150ms' }}>
          <div className="bg-green-50 border border-green-200 rounded-xl px-5 py-3 flex items-center gap-3">
            <svg className="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p className="text-sm text-green-800">
              <strong>Convidados ganham 90 dias de Básico grátis.</strong>
              {' '}Depois é só escolher o plano que quiser — sem surpresa, sem aumento.
            </p>
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 max-w-5xl mx-auto">
          {plans.map((plan, i) => (
            <div
              key={plan.name}
              className={`relative bg-white rounded-xl border-2 p-6 flex flex-col transition-all duration-500 ${
                plan.highlighted
                  ? 'border-purple-500 shadow-lg scale-[1.02] z-10'
                  : 'border-gray-200 hover:border-purple-300 hover:shadow-md'
              } ${inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'}`}
              style={{ transitionDelay: `${300 + i * 100}ms` }}
            >
              {plan.highlighted && (
                <span className="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-purple-600 text-white text-xs font-semibold rounded-full whitespace-nowrap">
                  Mais popular
                </span>
              )}
              {plan.badge && (
                <span className="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded-full whitespace-nowrap">
                  {plan.badge}
                </span>
              )}

              <div className="mb-4">
                <h3 className="text-lg font-bold text-gray-900">{plan.name}</h3>
                <div className="flex items-baseline gap-1 mt-2">
                  <span className="text-3xl font-bold text-gray-900">{plan.price}</span>
                  <span className="text-sm text-gray-400">{plan.period}</span>
                </div>
                <p className="text-sm text-gray-500 mt-2">{plan.description}</p>
              </div>

              <ul className="flex-1 space-y-2 mb-6">
                {plan.features.map((feat) => (
                  <li key={feat} className="flex items-start gap-2 text-sm text-gray-600">
                    <svg className="w-4 h-4 text-green-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                    </svg>
                    {feat}
                  </li>
                ))}
              </ul>

              <a
                href="#waitlist"
                className="block w-full text-center px-4 py-2.5 rounded-lg font-semibold text-sm bg-gray-100 text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition"
              >
                Quero ser convidado(a)
              </a>
            </div>
          ))}
        </div>

        <p className={`text-center text-xs text-gray-400 mt-6 transition-all duration-500 ${inView ? 'opacity-100' : 'opacity-0'}`} style={{ transitionDelay: '700ms' }}>
          Somente convidados podem acessar. Deixe seu email abaixo para ser avisado das próximas vagas.
        </p>
      </div>
    </section>
  )
}

export default PlansSection
