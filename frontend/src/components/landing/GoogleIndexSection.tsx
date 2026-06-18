import React from 'react'

const EXEMPLO_LOJA = {
  nome: 'Casa Lar Imóveis',
  slug: 'casa-lar-imoveis',
  produto: 'Apartamento 3 Quartos Vista para o Mar',
  descricao: 'Confira nosso catálogo completo. Apartamento espaçoso com 3 quartos, 2 banheiros, varanda ampla e vista para o mar...',
  imagem: '/images/stores/casa-lar-imoveis.png',
}

const GoogleIndexSection: React.FC = () => {
  return (
    <section className="bg-white py-16">
      <div className="container mx-auto px-4 max-w-4xl">
        
        <div className="text-center mb-12">
          <span className="inline-block px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full mb-4">
            Indexação orgânica gratuita
          </span>
          <h2 className="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
            Sua loja aparece no Google
          </h2>
          <p className="text-gray-500 max-w-xl mx-auto">
            Diferente de outros catálogos, as lojas VendaPop são indexadas pelo Google automaticamente.
            Seus clientes encontram seus produtos mesmo sem ter o link.
          </p>
        </div>

        <div className="max-w-2xl mx-auto">
          
          <div className="flex items-center bg-white border border-gray-300 rounded-full px-4 py-2 mb-4 shadow-sm">
            <svg className="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span className="text-gray-700 text-sm">{EXEMPLO_LOJA.produto.toLowerCase()}</span>
          </div>

          <div className="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition">
            <div className="flex items-center gap-2 mb-1">
              <div className="w-4 h-4 rounded-full bg-purple-100 flex items-center justify-center">
                <span className="text-purple-700 text-[8px] font-bold">V</span>
              </div>
              <span className="text-xs text-gray-500">
                vendapop.com.br › {EXEMPLO_LOJA.slug} › product
              </span>
            </div>

            <h3 className="text-blue-700 text-lg font-medium hover:underline cursor-pointer mb-1">
              {EXEMPLO_LOJA.produto} — {EXEMPLO_LOJA.nome}
            </h3>

            <p className="text-sm text-gray-600 leading-relaxed">
              {EXEMPLO_LOJA.descricao}
            </p>

            <div className="mt-3 flex items-center gap-2">
              <span className="text-xs text-green-600 bg-green-50 border border-green-200 rounded px-2 py-0.5">
                ✓ resultado real no Google
              </span>
            </div>
          </div>

          <p className="text-center text-xs text-gray-400 mt-4">
            Exemplo real de como sua loja aparece nas buscas do Google
          </p>
        </div>

      </div>
    </section>
  )
}

export default GoogleIndexSection
