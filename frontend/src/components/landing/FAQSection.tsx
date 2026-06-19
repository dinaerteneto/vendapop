import { useState } from 'react'
import { useScrollReveal } from '../../hooks/useScrollReveal'

const faqs = [
  {
    q: 'O VendaPop é gratuito?',
    a: 'Sim! O plano Grátis permite até 6 produtos, sem prazo de expiração. Você testa sem compromisso e faz upgrade quando seu negócio crescer.',
  },
  {
    q: 'Precisa de cartão de crédito para usar?',
    a: 'Não. Seus clientes pagam via PIX — a chave aparece na confirmação do pedido. Você não precisa de maquininha, gateway nem conta bancária especial.',
  },
  {
    q: 'Funciona no celular?',
    a: 'Sim! O VendaPop é um PWA — você instala no celular como app nativo. Seus clientes acessam a loja pelo navegador, sem baixar nada.',
  },
  {
    q: 'Preciso ter CNPJ para criar uma loja?',
    a: 'Não. Pode usar como pessoa física. O VendaPop é feito para autônomos, microempreendedores e pequenos negócios.',
  },
  {
    q: 'Como os pedidos chegam até mim?',
    a: 'Quando o cliente finaliza a compra, o pedido chega organizado no seu WhatsApp — com produto, tamanho, cor, quantidade e total já calculado.',
  },
]

const FAQSection: React.FC = () => {
  const [open, setOpen] = useState<number | null>(null)
  const { ref, inView } = useScrollReveal()

  return (
    <section ref={ref} className="bg-white py-16">
      <div className="container mx-auto px-4 max-w-2xl">
        <div className={`text-center mb-10 transition-all duration-700 ${inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'}`}>
          <span className="inline-block px-3 py-1 text-xs font-semibold text-purple-700 bg-purple-100 rounded-full mb-4">
            Perguntas frequentes
          </span>
          <h2 className="text-2xl font-bold text-gray-900">
            Tire suas dúvidas
          </h2>
        </div>

        <div className="space-y-3">
          {faqs.map((faq, i) => (
            <div
              key={i}
              className={`bg-gray-50 rounded-xl border border-gray-200 overflow-hidden transition-all duration-500 ${
                inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'
              }`}
              style={{ transitionDelay: `${150 + i * 80}ms` }}
            >
              <button
                onClick={() => setOpen(open === i ? null : i)}
                className="w-full px-5 py-4 text-left flex items-center justify-between gap-3"
              >
                <span className="text-sm font-medium text-gray-900">{faq.q}</span>
                <svg
                  className={`w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200 ${
                    open === i ? 'rotate-180' : ''
                  }`}
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div
                className={`px-5 overflow-hidden transition-all duration-200 ${
                  open === i ? 'pb-4 max-h-40' : 'max-h-0'
                }`}
              >
                <p className="text-sm text-gray-500">{faq.a}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}

export default FAQSection
