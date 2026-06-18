import LegalPage from '../../components/legal/LegalPage';
import { SEOHead } from '../../components/common/SEOHead';
import type { LegalSection } from '../../components/legal/LegalPage';

const sections: LegalSection[] = [
  {
    id: 'introducao',
    title: '1. O que são Cookies?',
    content: (
      <>
        <p>
          Cookies são pequenos arquivos de texto que são armazenados em seu dispositivo (computador, tablet ou smartphone) quando você visita um site ou utiliza uma aplicação web. Eles são amplamente utilizados para fazer os sites funcionarem de maneira mais eficiente e fornecer informações aos proprietários do site.
        </p>
        <p className="mt-4">
          Esta Política de Cookies explica como o <strong>VendaPop</strong> utiliza cookies e tecnologias semelhantes.
        </p>
      </>
    ),
  },
  {
    id: 'cookies-utilizados',
    title: '2. Cookies Utilizados',
    content: (
      <>
        <p>Utilizamos as seguintes categorias de cookies:</p>

        <h3 className="text-base font-semibold mt-6 mb-3">2.1 Cookies Essenciais (Obrigatórios)</h3>
        <p>
          Estes cookies são necessários para o funcionamento básico da plataforma e não podem ser desativados. Eles geralmente são definidos em resposta a ações feitas por você, como definir suas preferências de privacidade, fazer login ou preencher formulários.
        </p>
        <div className="overflow-x-auto mt-3">
          <table className="min-w-full text-sm border border-gray-200 rounded">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-4 py-2 text-left font-semibold text-gray-700 border-b">Nome</th>
                <th className="px-4 py-2 text-left font-semibold text-gray-700 border-b">Finalidade</th>
                <th className="px-4 py-2 text-left font-semibold text-gray-700 border-b">Duração</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td className="px-4 py-2 border-b">admin_token</td>
                <td className="px-4 py-2 border-b">Armazena o token de autenticação do lojista (via localStorage)</td>
                <td className="px-4 py-2 border-b">Sessão + persistente</td>
              </tr>
              <tr>
                <td className="px-4 py-2 border-b">XSRF-TOKEN</td>
                <td className="px-4 py-2 border-b">Proteção contra ataques CSRF</td>
                <td className="px-4 py-2 border-b">Sessão</td>
              </tr>
              <tr>
                <td className="px-4 py-2">laravel_session</td>
                <td className="px-4 py-2">Identificação da sessão do servidor</td>
                <td className="px-4 py-2">Sessão</td>
              </tr>
            </tbody>
          </table>
        </div>

        <h3 className="text-base font-semibold mt-6 mb-3">2.2 Cookies de Preferências</h3>
        <p>
          Estes cookies permitem que a plataforma lembre das escolhas que você faz (como seu nome de usuário, idioma ou região) e forneça recursos aprimorados e personalizados.
        </p>
        <p className="mt-3">
          Atualmente, o VendaPop <strong>não utiliza cookies de preferências</strong> além dos essenciais.
        </p>

        <h3 className="text-base font-semibold mt-6 mb-3">2.3 Cookies de Analytics</h3>
        <p>
          Cookies de analytics nos ajudam a entender como os visitantes interagem com a plataforma, coletando e reportando informações de forma anônima.
        </p>
        <p className="mt-3">
          Atualmente, o VendaPop <strong>não utiliza cookies de analytics de terceiros</strong>. Podemos implementar analytics próprios no futuro para melhorar o serviço, sempre respeitando sua privacidade.
        </p>

        <h3 className="text-base font-semibold mt-6 mb-3">2.4 Cookies de Marketing</h3>
        <p>
          Cookies de marketing são utilizados para rastrear visitantes em sites e exibir anúncios relevantes.
        </p>
        <p className="mt-3">
          O VendaPop <strong>não utiliza cookies de marketing</strong>. Não rastreamos seu comportamento em outros sites nem exibimos anúncios personalizados.
        </p>
      </>
    ),
  },
  {
    id: 'local-storage',
    title: '3. Armazenamento Local (Local Storage)',
    content: (
      <>
        <p>
          Além de cookies, utilizamos o <strong>Local Storage</strong> do navegador para armazenar informações que precisam persistir entre sessões, como:
        </p>
        <ul className="list-disc pl-6 space-y-1">
          <li><strong>Token de autenticação</strong> (admin_token, para mantê-lo logado)</li>
          <li><strong>Slug da loja</strong> (tenant_slug, para identificar a loja atual)</li>
          <li><strong>Preferências da aplicação</strong></li>
        </ul>
        <p className="mt-4">
          O Local Storage é específico do navegador e dispositivo, e os dados não são enviados ao servidor automaticamente (diferente dos cookies).
        </p>
      </>
    ),
  },
  {
    id: 'gerenciar-cookies',
    title: '4. Como Gerenciar Cookies',
    content: (
      <>
        <p>
          Você pode controlar e/ou excluir cookies como desejar. Para mais detalhes, visite <a href="https://www.aboutcookies.org" target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">aboutcookies.org</a>.
        </p>
        <p className="mt-4">
          Você pode configurar seu navegador para bloquear ou alertá-lo sobre esses cookies, mas algumas partes do site não funcionarão sem eles. Veja como gerenciar cookies nos navegadores mais populares:
        </p>
        <ul className="list-disc pl-6 space-y-1 mt-3">
          <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">Google Chrome</a></li>
          <li><a href="https://support.mozilla.org/pt-BR/kb/gerencie-configuracoes-armazenamento-local-naveg" target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">Mozilla Firefox</a></li>
          <li><a href="https://support.apple.com/pt-br/guide/safari/sfri11471/mac" target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">Safari</a></li>
          <li><a href="https://support.microsoft.com/pt-br/microsoft-edge/excluir-cookies-no-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">Microsoft Edge</a></li>
        </ul>
      </>
    ),
  },
  {
    id: 'impacto-desativacao',
    title: '5. Impacto da Desativação de Cookies',
    content: (
      <>
        <p>
          Se você optar por desativar os cookies, algumas funcionalidades do VendaPop podem não estar disponíveis:
        </p>
        <ul className="list-disc pl-6 space-y-1">
          <li>Você não conseguirá fazer login ou manter sua sessão ativa</li>
          <li>Algumas funcionalidades de segurança podem ser comprometidas</li>
          <li>Suas preferências não serão lembradas entre sessões</li>
        </ul>
        <p className="mt-4">
          <strong>Recomendamos que você mantenha os cookies essenciais ativados</strong> para garantir o funcionamento adequado da plataforma.
        </p>
      </>
    ),
  },
  {
    id: 'cookies-terceiros',
    title: '6. Cookies de Terceiros',
    content: (
      <>
        <p>
          Em alguns casos, utilizamos cookies fornecidos por terceiros confiáveis. A seção abaixo detalha quais cookies de terceiros você pode encontrar neste site:
        </p>
        <p className="mt-4">
          Atualmente, o VendaPop <strong>não utiliza cookies de terceiros</strong>. Caso implementemos serviços de terceiros no futuro (como Google Analytics, por exemplo), atualizaremos esta política e, se necessário, solicitaremos seu consentimento.
        </p>
      </>
    ),
  },
  {
    id: 'alteracoes',
    title: '7. Alterações nesta Política',
    content: (
      <>
        <p>
          Podemos atualizar esta Política de Cookies periodicamente para refletir mudanças nos cookies que utilizamos ou para outros motivos operacionais, legais ou regulatórios.
        </p>
        <p className="mt-4">
          Recomendamos que você revise esta política regularmente para se manter informado sobre como utilizamos cookies.
        </p>
      </>
    ),
  },
  {
    id: 'contato',
    title: '8. Contato',
    content: (
      <>
        <p>
          Se você tiver dúvidas sobre nossa utilização de cookies ou sobre esta Política de Cookies, entre em contato:
        </p>
        <ul className="list-none pl-0 mt-4 space-y-2">
          <li><strong>E-mail:</strong> contato@vendapop.com.br</li>
        </ul>
      </>
    ),
  },
];

export default function CookiePolicyPage() {
  return (
    <>
      <SEOHead title="Política de Cookies — VendaPop" path="/cookies" />
      <LegalPage
      title="Política de Cookies"
      lastUpdated="12 de junho de 2026"
      sections={sections}
    />
    </>
  );
}
