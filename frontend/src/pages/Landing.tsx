import { Link } from 'react-router-dom';

const Landing = () => {
  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-600 via-purple-700 to-indigo-800 relative overflow-hidden">
      {/* Background Decorative Elements */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        {/* Large circles */}
        <div className="absolute top-20 -left-20 w-96 h-96 bg-purple-500 rounded-full opacity-20 blur-3xl"></div>
        <div className="absolute top-40 right-10 w-80 h-80 bg-indigo-500 rounded-full opacity-20 blur-3xl"></div>
        <div className="absolute bottom-20 left-1/4 w-72 h-72 bg-pink-500 rounded-full opacity-15 blur-3xl"></div>
        
        {/* Grid pattern overlay */}
        <div 
          className="absolute inset-0 opacity-5"
          style={{
            backgroundImage: `linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px)`,
            backgroundSize: '50px 50px'
          }}
        ></div>
      </div>

      {/* Header */}
      <header className="container mx-auto px-4 py-6 relative z-10">
        <nav className="flex items-center justify-between">
          <div className="text-2xl font-bold text-white">
            🛍️ VesteZap
          </div>
          <div className="flex gap-4">
            <Link
              to="/admin/login"
              className="px-4 py-2 text-white hover:text-purple-200 transition"
            >
              Entrar
            </Link>
            <Link
              to="/admin/register"
              className="px-6 py-2 bg-white text-purple-700 rounded-lg font-semibold hover:bg-purple-50 transition shadow-lg"
            >
              Começar Grátis
            </Link>
          </div>
        </nav>
      </header>

      {/* Hero Section */}
      <section className="container mx-auto px-4 py-20 text-center relative z-10">
        {/* Decorative elements in hero */}
        <div className="absolute top-10 left-10 w-32 h-32 bg-white rounded-full opacity-10 blur-2xl hidden md:block"></div>
        <div className="absolute bottom-10 right-10 w-40 h-40 bg-purple-300 rounded-full opacity-10 blur-2xl hidden md:block"></div>
        
        <h1 className="text-5xl md:text-6xl font-bold text-white mb-6 leading-tight relative z-10">
          Seu Catálogo Online
          <br />
          <span className="text-purple-200">Integrado com Instagram</span>
        </h1>
        <p className="text-xl md:text-2xl text-purple-100 mb-8 max-w-3xl mx-auto">
          A solução perfeita para lojistas que estão migrando do físico para o digital.
          Gerencie seus produtos, receba pedidos e venda direto pelo WhatsApp.
        </p>
        <div className="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
          <Link
            to="/admin/register"
            className="px-8 py-4 bg-white text-purple-700 rounded-lg font-bold text-lg hover:bg-purple-50 transition shadow-xl hover:scale-105"
          >
            Criar Minha Loja Grátis
          </Link>
          <Link
            to="/admin/login"
            className="px-8 py-4 bg-transparent border-2 border-white text-white rounded-lg font-bold text-lg hover:bg-white hover:text-purple-700 transition"
          >
            Já tenho uma conta
          </Link>
        </div>
        
        {/* Link para loja de exemplo */}
        <div className="mb-12 relative z-10">
          <a
            href="https://vestezap.com.br/modachic"
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center gap-2 px-6 py-3 bg-purple-500/20 backdrop-blur-sm border-2 border-white/30 text-white rounded-lg font-semibold hover:bg-purple-500/30 hover:border-white/50 transition shadow-lg hover:scale-105"
          >
            <span>👁️</span>
            <span>Ver Loja de Exemplo</span>
            <span>→</span>
          </a>
        </div>

        {/* Visual Preview Section */}
        <div className="mt-16 relative z-10">
          <div className="max-w-5xl mx-auto bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/20 shadow-2xl">
            <div className="grid md:grid-cols-3 gap-6">
              {/* Mockup Card 1 - Mobile */}
              <div className="bg-white rounded-xl p-6 shadow-xl transform hover:scale-105 transition">
                <div className="w-12 h-12 bg-purple-600 rounded-lg mb-4 mx-auto flex items-center justify-center text-2xl">
                  📱
                </div>
                <h3 className="font-bold text-gray-800 mb-2">Loja Mobile</h3>
                <p className="text-sm text-gray-600">Totalmente responsiva</p>
              </div>
              
              {/* Mockup Card 2 - Products */}
              <div className="bg-white rounded-xl p-6 shadow-xl transform hover:scale-105 transition">
                <div className="w-12 h-12 bg-indigo-600 rounded-lg mb-4 mx-auto flex items-center justify-center text-2xl">
                  🛍️
                </div>
                <h3 className="font-bold text-gray-800 mb-2">Catálogo Completo</h3>
                <p className="text-sm text-gray-600">Produtos organizados</p>
              </div>
              
              {/* Mockup Card 3 - WhatsApp */}
              <div className="bg-white rounded-xl p-6 shadow-xl transform hover:scale-105 transition">
                <div className="w-12 h-12 bg-green-600 rounded-lg mb-4 mx-auto flex items-center justify-center text-2xl">
                  💬
                </div>
                <h3 className="font-bold text-gray-800 mb-2">WhatsApp Direto</h3>
                <p className="text-sm text-gray-600">Pedidos instantâneos</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Example Store Section */}
      <section className="bg-gradient-to-br from-indigo-50 to-purple-50 py-16 relative overflow-hidden">
        <div className="container mx-auto px-4 text-center relative z-10">
          <h2 className="text-3xl font-bold text-gray-800 mb-4">
            Veja como funciona na prática
          </h2>
          <p className="text-lg text-gray-600 mb-6 max-w-2xl mx-auto">
            Explore nossa loja de exemplo e veja como seus clientes vão interagir com seus produtos
          </p>
          <a
            href="https://vestezap.com.br/modachic"
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-bold text-lg hover:from-purple-700 hover:to-indigo-700 transition shadow-xl hover:scale-105"
          >
            <span>🛍️</span>
            <span>Visitar Loja de Exemplo</span>
            <span>→</span>
          </a>
        </div>
      </section>

      {/* Features Section */}
      <section className="bg-white py-20 relative overflow-hidden">
        {/* Decorative background shapes */}
        <div className="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
          <div className="absolute -top-20 -right-20 w-96 h-96 bg-purple-100 rounded-full opacity-30 blur-3xl"></div>
          <div className="absolute bottom-0 left-0 w-80 h-80 bg-indigo-100 rounded-full opacity-20 blur-3xl"></div>
        </div>
        
        <div className="container mx-auto px-4 relative z-10">
          <h2 className="text-4xl font-bold text-gray-800 text-center mb-12">
            Tudo que você precisa para vender online
          </h2>
          <div className="grid md:grid-cols-3 gap-8">
            {/* Feature 1 */}
            <div className="text-center p-6 rounded-lg hover:shadow-lg transition">
              <div className="text-5xl mb-4">📱</div>
              <h3 className="text-xl font-bold text-gray-800 mb-2">Catálogo Online</h3>
              <p className="text-gray-600">
                Crie seu catálogo de produtos de forma simples e intuitiva. 
                Adicione fotos, descrições e organize por categorias.
              </p>
            </div>

            {/* Feature 2 */}
            <div className="text-center p-6 rounded-lg hover:shadow-lg transition">
              <div className="text-5xl mb-4">💬</div>
              <h3 className="text-xl font-bold text-gray-800 mb-2">WhatsApp Integration</h3>
              <p className="text-gray-600">
                Seus clientes finalizam pedidos direto pelo WhatsApp. 
                Receba notificações instantâneas de novos pedidos.
              </p>
            </div>

            {/* Feature 3 */}
            <div className="text-center p-6 rounded-lg hover:shadow-lg transition">
              <div className="text-5xl mb-4">📸</div>
              <h3 className="text-xl font-bold text-gray-800 mb-2">Integração Instagram</h3>
              <p className="text-gray-600">
                Coloque o link do VesteZap na sua bio do Instagram. 
                Em breve: postagens automáticas de produtos.
              </p>
            </div>

            {/* Feature 4 */}
            <div className="text-center p-6 rounded-lg hover:shadow-lg transition">
              <div className="text-5xl mb-4">🎨</div>
              <h3 className="text-xl font-bold text-gray-800 mb-2">Personalização</h3>
              <p className="text-gray-600">
                Customize cores, logo e identidade visual da sua loja. 
                Deixe sua marca única e reconhecível.
              </p>
            </div>

            {/* Feature 5 */}
            <div className="text-center p-6 rounded-lg hover:shadow-lg transition">
              <div className="text-5xl mb-4">📊</div>
              <h3 className="text-xl font-bold text-gray-800 mb-2">Painel Administrativo</h3>
              <p className="text-gray-600">
                Gerencie produtos, categorias, pedidos e clientes em um só lugar. 
                Tudo de forma simples e organizada.
              </p>
            </div>

            {/* Feature 6 */}
            <div className="text-center p-6 rounded-lg hover:shadow-lg transition">
              <div className="text-5xl mb-4">🔔</div>
              <h3 className="text-xl font-bold text-gray-800 mb-2">Notificações</h3>
              <p className="text-gray-600">
                Receba notificações por email, push e WhatsApp quando houver novos pedidos. 
                Nunca perca uma venda.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* How it Works */}
      <section className="bg-gray-50 py-20 relative overflow-hidden">
        {/* Decorative dots pattern */}
        <div 
          className="absolute inset-0 opacity-5"
          style={{
            backgroundImage: `radial-gradient(circle, #7c3aed 1px, transparent 1px)`,
            backgroundSize: '30px 30px'
          }}
        ></div>
        
        <div className="container mx-auto px-4 relative z-10">
          <h2 className="text-4xl font-bold text-gray-800 text-center mb-12">
            Como Funciona
          </h2>
          <div className="max-w-4xl mx-auto">
            <div className="grid md:grid-cols-3 gap-8">
              <div className="text-center">
                <div className="w-16 h-16 bg-purple-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                  1
                </div>
                <h3 className="text-lg font-bold text-gray-800 mb-2">Cadastre-se</h3>
                <p className="text-gray-600">
                  Crie sua conta gratuitamente e configure sua loja em minutos
                </p>
              </div>
              <div className="text-center">
                <div className="w-16 h-16 bg-purple-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                  2
                </div>
                <h3 className="text-lg font-bold text-gray-800 mb-2">Adicione Produtos</h3>
                <p className="text-gray-600">
                  Faça upload de fotos, defina preços e organize por categorias
                </p>
              </div>
              <div className="text-center">
                <div className="w-16 h-16 bg-purple-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                  3
                </div>
                <h3 className="text-lg font-bold text-gray-800 mb-2">Compartilhe e Venda</h3>
                <p className="text-gray-600">
                  Coloque o link na sua bio do Instagram e comece a vender
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="bg-gradient-to-r from-purple-600 to-indigo-700 py-20 relative overflow-hidden">
        {/* Animated background shapes */}
        <div className="absolute inset-0 overflow-hidden pointer-events-none">
          <div className="absolute top-10 left-1/4 w-64 h-64 bg-white rounded-full opacity-10 blur-3xl animate-pulse"></div>
          <div className="absolute bottom-10 right-1/4 w-56 h-56 bg-purple-300 rounded-full opacity-15 blur-3xl animate-pulse" style={{ animationDelay: '1s' }}></div>
          <div className="absolute top-1/2 left-10 w-48 h-48 bg-indigo-300 rounded-full opacity-10 blur-3xl animate-pulse" style={{ animationDelay: '2s' }}></div>
        </div>
        
        <div className="container mx-auto px-4 text-center relative z-10">
          <h2 className="text-4xl font-bold text-white mb-6">
            Pronto para começar a vender online?
          </h2>
          <p className="text-xl text-purple-100 mb-8 max-w-2xl mx-auto">
            Junte-se a centenas de lojistas que já estão vendendo mais com o VesteZap
          </p>
          <Link
            to="/admin/register"
            className="inline-block px-10 py-4 bg-white text-purple-700 rounded-lg font-bold text-lg hover:bg-purple-50 transition shadow-xl hover:scale-105"
          >
            Criar Minha Loja Agora
          </Link>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-gray-900 text-gray-400 py-12">
        <div className="container mx-auto px-4 text-center">
          <p className="mb-4">© 2025 VesteZap. Todos os direitos reservados.</p>
          <div className="flex justify-center gap-6">
            <Link to="/admin/login" className="hover:text-white transition">
              Entrar
            </Link>
            <Link to="/admin/register" className="hover:text-white transition">
              Registrar
            </Link>
          </div>
        </div>
      </footer>
    </div>
  );
};

export default Landing;

