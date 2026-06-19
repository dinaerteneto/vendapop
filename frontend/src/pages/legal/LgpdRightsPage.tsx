import LegalPage from '../../components/legal/LegalPage';
import { SEOHead } from '../../components/common/SEOHead';
import type { LegalSection } from '../../components/legal/LegalPage';

const sections: LegalSection[] = [
  {
    id: 'introducao',
    title: '1. Introdução',
    content: (
      <>
        <p>
          A Lei Geral de Proteção de Dados Pessoais (LGPD - Lei nº 13.709/2018) garante a você uma série de direitos em relação aos seus dados pessoais tratados pelo <strong>VendaPop</strong>.
        </p>
        <p className="mt-4">
          Esta página explica cada um desses direitos e como você pode exercê-los de forma simples e gratuita.
        </p>
      </>
    ),
  },
  {
    id: 'confirmacao',
    title: '2. Confirmação da Existência de Tratamento',
    content: (
      <>
        <p>
          <strong>O que é:</strong> Você tem o direito de solicitar confirmação se o VendaPop trata ou não seus dados pessoais.
        </p>
        <h3 className="text-base font-semibold mt-4 mb-2">Como exercer:</h3>
        <ol className="list-decimal pl-6 space-y-1">
          <li>Envie um e-mail para <strong>privacidade@vendapop.com.br</strong></li>
          <li>Assunto: "LGPD - Confirmação de Tratamento"</li>
          <li>Informe seu nome completo e e-mail cadastrado</li>
        </ol>
        <p className="mt-4">
          <strong>Prazo de resposta:</strong> Imediatamente (formato simplificado) ou em até 15 dias (declaração completa).
        </p>
      </>
    ),
  },
  {
    id: 'acesso',
    title: '3. Acesso aos Dados',
    content: (
      <>
        <p>
          <strong>O que é:</strong> Você tem o direito de acessar todos os dados pessoais que o VendaPop possui sobre você.
        </p>
        <h3 className="text-base font-semibold mt-4 mb-2">O que você receberá:</h3>
        <ul className="list-disc pl-6 space-y-1">
          <li>Origem dos dados</li>
          <li>Confirmação da existência ou inexistência de registros</li>
          <li>Critérios utilizados para o tratamento</li>
          <li>Finalidades do tratamento</li>
        </ul>
        <h3 className="text-base font-semibold mt-4 mb-2">Como exercer:</h3>
        <ol className="list-decimal pl-6 space-y-1">
          <li>Envie um e-mail para <strong>privacidade@vendapop.com.br</strong></li>
          <li>Assunto: "LGPD - Acesso aos Dados"</li>
          <li>Informe seu nome completo e e-mail cadastrado</li>
        </ol>
        <p className="mt-4">
          <strong>Formato:</strong> Você pode solicitar os dados em formato eletrônico seguro ou impresso.
        </p>
        <p className="mt-2">
          <strong>Prazo de resposta:</strong> Imediatamente (formato simplificado) ou em até 15 dias (declaração completa).
        </p>
      </>
    ),
  },
  {
    id: 'correcao',
    title: '4. Correção de Dados',
    content: (
      <>
        <p>
          <strong>O que é:</strong> Você tem o direito de solicitar a correção de dados pessoais que estejam incompletos, inexatos ou desatualizados.
        </p>
        <h3 className="text-base font-semibold mt-4 mb-2">Quando utilizar:</h3>
        <ul className="list-disc pl-6 space-y-1">
          <li>Seu nome está grafado incorretamente</li>
          <li>Seu e-mail está desatualizado</li>
          <li>Informações da sua loja estão incorretas</li>
          <li>Qualquer outro dado pessoal está impreciso</li>
        </ul>
        <h3 className="text-base font-semibold mt-4 mb-2">Como exercer:</h3>
        <p className="mt-2">
          <strong>Opção 1 - Pela plataforma:</strong> Acesse as configurações da sua loja e atualize suas informações diretamente.
        </p>
        <p className="mt-2">
          <strong>Opção 2 - Por e-mail:</strong>
        </p>
        <ol className="list-decimal pl-6 space-y-1">
          <li>Envie um e-mail para <strong>privacidade@vendapop.com.br</strong></li>
          <li>Assunto: "LGPD - Correção de Dados"</li>
          <li>Informe o dado que deseja corrigir e a informação correta</li>
        </ol>
        <p className="mt-4">
          <strong>Prazo de resposta:</strong> A correção será realizada o mais breve possível.
        </p>
      </>
    ),
  },
  {
    id: 'anonimizacao-bloqueio-eliminacao',
    title: '5. Anonimização, Bloqueio ou Eliminação de Dados',
    content: (
      <>
        <p>
          <strong>O que é:</strong> Você tem o direito de solicitar:
        </p>
        <ul className="list-disc pl-6 space-y-1">
          <li><strong>Anonimização:</strong> tornar os dados anônimos, de forma que não possam mais ser associados a você</li>
          <li><strong>Bloqueio:</strong> suspensão temporária do tratamento dos dados</li>
          <li><strong>Eliminação:</strong> exclusão definitiva dos seus dados pessoais</li>
        </ul>
        <h3 className="text-base font-semibold mt-4 mb-2">Quando utilizar:</h3>
        <ul className="list-disc pl-6 space-y-1">
          <li>Dados tratados sem seu consentimento</li>
          <li>Dados desnecessários ou excessivos para a finalidade</li>
          <li>Dados tratados em desconformidade com a LGPD</li>
        </ul>
        <h3 className="text-base font-semibold mt-4 mb-2">Como exercer:</h3>
        <ol className="list-decimal pl-6 space-y-1">
          <li>Envie um e-mail para <strong>privacidade@vendapop.com.br</strong></li>
          <li>Assunto: "LGPD - Anonimização/Bloqueio/Eliminação"</li>
          <li>Especifique qual ação deseja e quais dados</li>
        </ol>
        <p className="mt-4">
          <strong>Importante:</strong> Podemos manter alguns dados quando houver obrigação legal ou regulatória. Nesse caso, informaremos os motivos.
        </p>
        <p className="mt-2">
          <strong>Prazo de resposta:</strong> Em até 15 dias.
        </p>
      </>
    ),
  },
  {
    id: 'portabilidade',
    title: '6. Portabilidade dos Dados',
    content: (
      <>
        <p>
          <strong>O que é:</strong> Você tem o direito de solicitar a portabilidade dos seus dados pessoais para outro fornecedor de serviço ou produto.
        </p>
        <h3 className="text-base font-semibold mt-4 mb-2">O que você receberá:</h3>
        <ul className="list-disc pl-6 space-y-1">
          <li>Seus dados em formato estruturado e de uso comum</li>
          <li>Arquivo que possa ser importado por outro serviço</li>
        </ul>
        <h3 className="text-base font-semibold mt-4 mb-2">Como exercer:</h3>
        <ol className="list-decimal pl-6 space-y-1">
          <li>Envie um e-mail para <strong>privacidade@vendapop.com.br</strong></li>
          <li>Assunto: "LGPD - Portabilidade de Dados"</li>
          <li>Informe seu nome completo e e-mail cadastrado</li>
        </ol>
        <p className="mt-4">
          <strong>Formato:</strong> Os dados serão fornecidos em formato JSON, amplamente utilizado e compatível com outros sistemas.
        </p>
        <p className="mt-2">
          <strong>Prazo de resposta:</strong> Em até 15 dias.
        </p>
      </>
    ),
  },
  {
    id: 'eliminacao-consentimento',
    title: '7. Eliminação de Dados Tratados com Consentimento',
    content: (
      <>
        <p>
          <strong>O que é:</strong> Você tem o direito de solicitar a eliminação de dados pessoais que foram tratados com base no seu consentimento.
        </p>
        <h3 className="text-base font-semibold mt-4 mb-2">Como exercer:</h3>
        <ol className="list-decimal pl-6 space-y-1">
          <li>Envie um e-mail para <strong>privacidade@vendapop.com.br</strong></li>
          <li>Assunto: "LGPD - Eliminação de Dados"</li>
          <li>Confirme que deseja a exclusão completa da sua conta e dados</li>
        </ol>
        <p className="mt-4">
          <strong>Importante:</strong> Após a eliminação, você perderá acesso à sua conta e todos os dados associados. Esta ação é irreversível.
        </p>
        <p className="mt-2">
          <strong>Exceções:</strong> Podemos manter alguns dados quando houver obrigação legal (ex: dados fiscais) ou para exercício regular de direitos.
        </p>
        <p className="mt-2">
          <strong>Prazo de resposta:</strong> Em até 15 dias.
        </p>
      </>
    ),
  },
  {
    id: 'informacao-compartilhamento',
    title: '8. Informação sobre Compartilhamento de Dados',
    content: (
      <>
        <p>
          <strong>O que é:</strong> Você tem o direito de saber com quais entidades públicas e privadas o VendaPop compartilhou seus dados pessoais.
        </p>
        <h3 className="text-base font-semibold mt-4 mb-2">Como exercer:</h3>
        <ol className="list-decimal pl-6 space-y-1">
          <li>Envie um e-mail para <strong>privacidade@vendapop.com.br</strong></li>
          <li>Assunto: "LGPD - Informação sobre Compartilhamento"</li>
          <li>Informe seu nome completo e e-mail cadastrado</li>
        </ol>
        <p className="mt-4">
          <strong>Nota:</strong> Atualmente, o VendaPop <strong>não compartilha seus dados pessoais com terceiros</strong> para fins comerciais ou de marketing.
        </p>
        <p className="mt-2">
          <strong>Prazo de resposta:</strong> Em até 15 dias.
        </p>
      </>
    ),
  },
  {
    id: 'nao-consentimento',
    title: '9. Informação sobre Possibilidade de Não Consentimento',
    content: (
      <>
        <p>
          <strong>O que é:</strong> Você tem o direito de ser informado sobre a possibilidade de não fornecer consentimento e quais são as consequências dessa negativa.
        </p>
        <h3 className="text-base font-semibold mt-4 mb-2">Consequências da negativa:</h3>
        <p>
          Se você optar por não fornecer consentimento para o tratamento de dados pessoais essenciais, <strong>não será possível utilizar os serviços do VendaPop</strong>, pois o tratamento de dados é necessário para:
        </p>
        <ul className="list-disc pl-6 space-y-1 mt-2">
          <li>Criar e manter sua conta</li>
          <li>Fornecer a plataforma de loja virtual</li>
          <li>Processar pedidos e atender clientes</li>
          <li>Garantir a segurança da plataforma</li>
        </ul>
      </>
    ),
  },
  {
    id: 'revogacao',
    title: '10. Revogação do Consentimento',
    content: (
      <>
        <p>
          <strong>O que é:</strong> Você tem o direito de revogar seu consentimento a qualquer momento, sem que isso afete a licitude do tratamento realizado anteriormente.
        </p>
        <h3 className="text-base font-semibold mt-4 mb-2">Como exercer:</h3>
        <ol className="list-decimal pl-6 space-y-1">
          <li>Envie um e-mail para <strong>privacidade@vendapop.com.br</strong></li>
          <li>Assunto: "LGPD - Revogação de Consentimento"</li>
          <li>Informe quais consentimentos deseja revogar</li>
        </ol>
        <p className="mt-4">
          <strong>Consequências:</strong> Após a revogação, deixaremos de tratar seus dados com base no consentimento anterior. Se o consentimento era essencial para o serviço, sua conta poderá ser encerrada.
        </p>
        <p className="mt-2">
          <strong>Prazo de resposta:</strong> Em até 15 dias.
        </p>
      </>
    ),
  },
  {
    id: 'revisao-decisoes',
    title: '11. Revisão de Decisões Automatizadas',
    content: (
      <>
        <p>
          <strong>O que é:</strong> Você tem o direito de solicitar a revisão de decisões tomadas unicamente com base em tratamento automatizado de dados pessoais que afetem seus interesses.
        </p>
        <h3 className="text-base font-semibold mt-4 mb-2">Decisões automatizadas no VendaPop:</h3>
        <ul className="list-disc pl-6 space-y-1">
          <li>Atualmente, o VendaPop <strong>não toma decisões automatizadas</strong> baseadas exclusivamente em perfis de usuários</li>
        </ul>
        <h3 className="text-base font-semibold mt-4 mb-2">Como exercer:</h3>
        <ol className="list-decimal pl-6 space-y-1">
          <li>Envie um e-mail para <strong>privacidade@vendapop.com.br</strong></li>
          <li>Assunto: "LGPD - Revisão de Decisão Automatizada"</li>
          <li>Descreva qual decisão deseja revisar e os motivos</li>
        </ol>
        <p className="mt-4">
          <strong>Prazo de resposta:</strong> Em até 15 dias.
        </p>
      </>
    ),
  },
  {
    id: 'anpd',
    title: '12. Direito de Reclamar junto à ANPD',
    content: (
      <>
        <p>
          Além dos direitos listados acima, você tem o direito de apresentar reclamação à <strong>Autoridade Nacional de Proteção de Dados (ANPD)</strong> caso considere que seus direitos foram violados.
        </p>
        <h3 className="text-base font-semibold mt-4 mb-2">Como reclamar à ANPD:</h3>
        <ul className="list-disc pl-6 space-y-1">
          <li>Acesse o site oficial: <a href="https://www.gov.br/anpd" target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">www.gov.br/anpd</a></li>
          <li>Utilize o canal de atendimento ao cidadão</li>
          <li>Forneça todas as informações relevantes sobre o caso</li>
        </ul>
        <p className="mt-4">
          <strong>Importante:</strong> Recomendamos que você tente resolver a questão diretamente conosco antes de recorrer à ANPD. Estamos comprometidos em atender suas solicitações de forma rápida e adequada.
        </p>
      </>
    ),
  },
  {
    id: 'gratuidade',
    title: '13. Gratuidade do Exercício dos Direitos',
    content: (
      <>
        <p>
          O exercício de todos os direitos previstos na LGPD é <strong>totalmente gratuito</strong>. Não cobraremos qualquer valor para atender suas solicitações.
        </p>
        <p className="mt-4">
          Em caso de solicitações manifestamente infundadas, excessivas ou repetitivas, poderemos:
        </p>
        <ul className="list-disc pl-6 space-y-1">
          <li>Cobrar uma taxa administrativa razoável, baseada nos custos reais; ou</li>
          <li>Recusar atender a solicitação</li>
        </ul>
        <p className="mt-4">
          Nesses casos, informaremos os motivos e você poderá reclamar junto à ANPD.
        </p>
      </>
    ),
  },
  {
    id: 'prazos',
    title: '14. Prazos de Resposta',
    content: (
      <>
        <p>
          Os prazos máximos para resposta às suas solicitações são:
        </p>
        <div className="overflow-x-auto mt-3">
          <table className="min-w-full text-sm border border-gray-200 rounded">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-4 py-2 text-left font-semibold text-gray-700 border-b">Tipo de Solicitação</th>
                <th className="px-4 py-2 text-left font-semibold text-gray-700 border-b">Prazo</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td className="px-4 py-2 border-b">Confirmação de existência de tratamento</td>
                <td className="px-4 py-2 border-b">Imediata ou 15 dias</td>
              </tr>
              <tr>
                <td className="px-4 py-2 border-b">Acesso aos dados</td>
                <td className="px-4 py-2 border-b">Imediato ou 15 dias</td>
              </tr>
              <tr>
                <td className="px-4 py-2 border-b">Correção de dados</td>
                <td className="px-4 py-2 border-b">O mais breve possível</td>
              </tr>
              <tr>
                <td className="px-4 py-2 border-b">Anonimização, bloqueio ou eliminação</td>
                <td className="px-4 py-2 border-b">15 dias</td>
              </tr>
              <tr>
                <td className="px-4 py-2 border-b">Portabilidade</td>
                <td className="px-4 py-2 border-b">15 dias</td>
              </tr>
              <tr>
                <td className="px-4 py-2 border-b">Eliminação (consentimento)</td>
                <td className="px-4 py-2 border-b">15 dias</td>
              </tr>
              <tr>
                <td className="px-4 py-2 border-b">Informação sobre compartilhamento</td>
                <td className="px-4 py-2 border-b">15 dias</td>
              </tr>
              <tr>
                <td className="px-4 py-2 border-b">Revogação de consentimento</td>
                <td className="px-4 py-2 border-b">15 dias</td>
              </tr>
              <tr>
                <td className="px-4 py-2">Revisão de decisão automatizada</td>
                <td className="px-4 py-2">15 dias</td>
              </tr>
            </tbody>
          </table>
        </div>
      </>
    ),
  },
  {
    id: 'faq',
    title: '15. Perguntas Frequentes (FAQ)',
    content: (
      <>
        <h3 className="text-base font-semibold mt-4 mb-2">Posso exercer meus direitos por telefone?</h3>
        <p>
          Para sua segurança e para mantermos um registro adequado das solicitações, atendemos exclusivamente por e-mail. Isso garante que possamos verificar sua identidade e fornecer uma resposta completa.
        </p>

        <h3 className="text-base font-semibold mt-4 mb-2">Preciso pagar algo para exercer meus direitos?</h3>
        <p>
          Não. O exercício dos direitos previstos na LGPD é gratuito, salvo em casos de solicitações manifestamente infundadas ou excessivas.
        </p>

        <h3 className="text-base font-semibold mt-4 mb-2">Posso solicitar a exclusão completa da minha conta?</h3>
        <p>
          Sim. Você pode solicitar a exclusão da sua conta e de todos os dados associados a qualquer momento. Basta enviar um e-mail para privacidade@vendapop.com.br com o assunto "LGPD - Eliminação de Dados".
        </p>

        <h3 className="text-base font-semibold mt-4 mb-2">Quanto tempo leva para meus dados serem excluídos?</h3>
        <p>
          Após sua solicitação, iniciamos o processo de exclusão imediatamente. O processo completo pode levar até 15 dias. Alguns dados podem ser mantidos quando houver obrigação legal (ex: dados fiscais).
        </p>

        <h3 className="text-base font-semibold mt-4 mb-2">Posso exportar meus dados antes de excluir a conta?</h3>
        <p>
          Sim. Recomendamos que você solicite a portabilidade dos dados antes de solicitar a exclusão. Assim, você terá uma cópia completa dos seus dados em formato JSON.
        </p>

        <h3 className="text-base font-semibold mt-4 mb-2">Meus dados estão seguros?</h3>
        <p>
          Sim. Adotamos medidas técnicas e organizacionais rigorosas para proteger seus dados, incluindo criptografia em repouso, controle de acesso, monitoramento contínuo e backups regulares. Para mais detalhes, consulte nossa Política de Privacidade.
        </p>
      </>
    ),
  },
  {
    id: 'contato',
    title: '16. Contato',
    content: (
      <>
        <p>
          Para exercer qualquer um dos seus direitos ou tirar dúvidas, entre em contato:
        </p>
        <ul className="list-none pl-0 mt-4 space-y-2">
          <li><strong>E-mail (geral):</strong> contato@vendapop.com.br</li>
          <li><strong>E-mail (DPO):</strong> privacidade@vendapop.com.br</li>
          <li><strong>Assunto sugerido:</strong> "LGPD - [Nome do Direito]"</li>
        </ul>
        <p className="mt-4">
          Nosso Encarregado de Dados (DPO) está disponível para tratar de todas as questões relacionadas à proteção de seus dados pessoais.
        </p>
      </>
    ),
  },
];

export default function LgpdRightsPage() {
  return (
    <>
      <SEOHead title="Direitos LGPD — VendaPop" path="/direitos-lgpd" />
      <LegalPage
      title="Seus Direitos (LGPD)"
      lastUpdated="12 de junho de 2026"
      sections={sections}
    />
    </>
  );
}
