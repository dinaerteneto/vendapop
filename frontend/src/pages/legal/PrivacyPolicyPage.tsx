import LegalPage from '../../components/legal/LegalPage';
import type { LegalSection } from '../../components/legal/LegalPage';

const sections: LegalSection[] = [
  {
    id: 'introducao',
    title: '1. Introdução',
    content: (
      <>
        <p>
          Esta Política de Privacidade descreve como o <strong>VendaPop</strong> ("nós", "nosso" ou "plataforma") coleta, usa, armazena e protege seus dados pessoais em conformidade com a Lei Geral de Proteção de Dados Pessoais (LGPD - Lei nº 13.709/2018).
        </p>
        <p>
          Ao utilizar nossos serviços, você concorda com as práticas descritas nesta política. Se você não concordar com algum aspecto desta política, por favor, não utilize nossos serviços.
        </p>
      </>
    ),
  },
  {
    id: 'controlador',
    title: '2. Controlador de Dados',
    content: (
      <>
        <p>
          O controlador dos seus dados pessoais é o <strong>VendaPop</strong>.
        </p>
        <p>
          Para dúvidas sobre proteção de dados ou para exercer seus direitos, entre em contato através do e-mail: <strong>privacidade@vendapop.com.br</strong>
        </p>
      </>
    ),
  },
  {
    id: 'dados-coletados',
    title: '3. Dados Pessoais Coletados',
    content: (
      <>
        <p>Coletamos os seguintes dados pessoais:</p>
        <h3 className="text-base font-semibold mt-4 mb-2">3.1 Dados fornecidos diretamente por você:</h3>
        <ul className="list-disc pl-6 space-y-1">
          <li><strong>Nome completo</strong> do lojista</li>
          <li><strong>Nome da loja</strong></li>
          <li><strong>Endereço de e-mail</strong></li>
          <li><strong>Número de WhatsApp</strong></li>
          <li><strong>Senha</strong> (armazenada de forma criptografada)</li>
          <li><strong>Dados da loja</strong>: endereço, descrição, cores, logo, redes sociais</li>
          <li><strong>Dados de produtos</strong>: nomes, descrições, preços, imagens, variações</li>
          <li><strong>Dados de clientes</strong>: nome, telefone, endereço de entrega, histórico de pedidos</li>
        </ul>
        <h3 className="text-base font-semibold mt-4 mb-2">3.2 Dados coletados automaticamente:</h3>
        <ul className="list-disc pl-6 space-y-1">
          <li><strong>Dados de uso</strong>: data e hora de acesso, funcionalidades utilizadas</li>
          <li><strong>Informações do dispositivo</strong>: tipo de navegador, sistema operacional</li>
          <li><strong>Endereço IP</strong> (anonimizado para fins de segurança)</li>
        </ul>
      </>
    ),
  },
  {
    id: 'finalidade',
    title: '4. Finalidade do Tratamento',
    content: (
      <>
        <p>Seus dados pessoais são tratados para as seguintes finalidades:</p>
        <ul className="list-disc pl-6 space-y-1">
          <li><strong>Execução do contrato</strong>: fornecer a plataforma de loja virtual e processar pedidos</li>
          <li><strong>Consentimento</strong>: enviar comunicações relacionadas à sua conta (quando aplicável)</li>
          <li><strong>Interesse legítimo</strong>: melhorar nossos serviços, prevenir fraudes e garantir a segurança da plataforma</li>
          <li><strong>Obrigação legal</strong>: cumprir exigências legais e regulatórias aplicáveis</li>
        </ul>
      </>
    ),
  },
  {
    id: 'base-legal',
    title: '5. Base Legal para o Tratamento',
    content: (
      <>
        <p>O tratamento de seus dados pessoais é realizado com base nos seguintes fundamentos legais previstos na LGPD:</p>
        <ul className="list-disc pl-6 space-y-1">
          <li><strong>Consentimento</strong> (Art. 7º, I): para finalidades específicas informadas no momento da coleta</li>
          <li><strong>Execução de contrato</strong> (Art. 7º, V): para fornecer os serviços solicitados</li>
          <li><strong>Interesse legítimo</strong> (Art. 7º, IX): para segurança, prevenção de fraudes e melhoria dos serviços</li>
          <li><strong>Cumprimento de obrigação legal</strong> (Art. 7º, II): para atender exigências regulatórias</li>
        </ul>
      </>
    ),
  },
  {
    id: 'armazenamento',
    title: '6. Armazenamento e Segurança dos Dados',
    content: (
      <>
        <h3 className="text-base font-semibold mt-4 mb-2">6.1 Criptografia</h3>
        <p>
          Seus dados sensíveis são <strong>criptografados em repouso</strong> utilizando algoritmos de criptografia avançados. A senha é armazenada exclusivamente em formato hash (bcrypt), sendo impossível recuperá-la em texto puro.
        </p>
        <h3 className="text-base font-semibold mt-4 mb-2">6.2 Infraestrutura</h3>
        <p>
          Os dados são armazenados em servidores seguros com acesso restrito, protegidos por firewalls e sistemas de detecção de intrusão.
        </p>
        <h3 className="text-base font-semibold mt-4 mb-2">6.3 Período de Retenção</h3>
        <p>
          Mantemos seus dados pessoais enquanto sua conta estiver ativa ou conforme necessário para fornecer os serviços. Você pode solicitar a exclusão de seus dados a qualquer momento (ver seção "Seus Direitos").
        </p>
      </>
    ),
  },
  {
    id: 'compartilhamento',
    title: '7. Compartilhamento de Dados',
    content: (
      <>
        <p>
          <strong>Não vendemos, alugamos ou compartilhamos seus dados pessoais com terceiros</strong> para fins de marketing ou comerciais.
        </p>
        <p>Seus dados podem ser compartilhados apenas nas seguintes situações:</p>
        <ul className="list-disc pl-6 space-y-1">
          <li><strong>Prestadores de serviço</strong>: empresas que nos auxiliam na operação da plataforma (hospedagem, e-mail, analytics), sob rigorosos contratos de confidencialidade e proteção de dados</li>
          <li><strong>Exigências legais</strong>: quando necessário para cumprir obrigações legais, ordens judiciais ou processos legais</li>
          <li><strong>Proteção de direitos</strong>: para proteger nossos direitos, privacidade, segurança ou propriedade, ou a de terceiros</li>
        </ul>
      </>
    ),
  },
  {
    id: 'seus-direitos',
    title: '8. Seus Direitos (LGPD)',
    content: (
      <>
        <p>
          Em conformidade com o Art. 18 da LGPD, você tem direito a obter do controlador:
        </p>
        <ul className="list-disc pl-6 space-y-1">
          <li><strong>Confirmação da existência de tratamento</strong> de dados</li>
          <li><strong>Acesso</strong> aos seus dados pessoais</li>
          <li><strong>Correção</strong> de dados incompletos, inexatos ou desatualizados</li>
          <li><strong>Anonimização, bloqueio ou eliminação</strong> de dados desnecessários ou tratados em desconformidade</li>
          <li><strong>Portabilidade</strong> dos dados a outro fornecedor de serviço</li>
          <li><strong>Eliminação</strong> dos dados tratados com seu consentimento</li>
          <li><strong>Informação</strong> sobre entidades públicas e privadas com as quais compartilhamos dados</li>
          <li><strong>Informação sobre a possibilidade</strong> de não fornecer consentimento e suas consequências</li>
          <li><strong>Revogação do consentimento</strong></li>
        </ul>
        <p className="mt-4">
          Para exercer seus direitos, entre em contato através de: <strong>privacidade@vendapop.com.br</strong>
        </p>
        <p>
          Sua solicitação será atendida sem custos, no prazo máximo de 15 dias úteis.
        </p>
      </>
    ),
  },
  {
    id: 'cookies',
    title: '9. Cookies',
    content: (
      <>
        <p>
          Utilizamos cookies e tecnologias semelhantes para melhorar sua experiência. Para mais informações, consulte nossa <a href="/cookies" className="text-blue-600 hover:underline">Política de Cookies</a>.
        </p>
      </>
    ),
  },
  {
    id: 'transferencia-internacional',
    title: '10. Transferência Internacional de Dados',
    content: (
      <>
        <p>
          Atualmente, todos os seus dados são armazenados em servidores localizados no Brasil. Caso haja transferência internacional de dados no futuro, garantiremos que o país de destino ofereça nível adequado de proteção de dados ou que existam salvaguardas apropriadas conforme a LGPD.
        </p>
      </>
    ),
  },
  {
    id: 'menores',
    title: '11. Menores de Idade',
    content: (
      <>
        <p>
          Nossos serviços não se destinam a menores de 18 anos. Não coletamos intencionalmente dados pessoais de menores de idade. Se tomarmos conhecimento de que coletamos dados de um menor sem verificação do consentimento parental, excluiremos essas informações o mais rápido possível.
        </p>
      </>
    ),
  },
  {
    id: 'alteracoes',
    title: '12. Alterações nesta Política',
    content: (
      <>
        <p>
          Podemos atualizar esta Política de Privacidade periodicamente. Quando fizermos alterações significativas, notificaremos você por e-mail ou através de um aviso destacado em nossa plataforma antes que as alterações entrem em vigor.
        </p>
        <p>
          Recomendamos que você revise esta política regularmente para se manter informado sobre como protegemos seus dados.
        </p>
      </>
    ),
  },
  {
    id: 'contato',
    title: '13. Contato',
    content: (
      <>
        <p>
          Se você tiver dúvidas sobre esta Política de Privacidade ou sobre o tratamento de seus dados pessoais, entre em contato conosco:
        </p>
        <ul className="list-none pl-0 mt-4 space-y-2">
          <li><strong>E-mail (geral):</strong> contato@vendapop.com.br</li>
          <li><strong>E-mail (DPO):</strong> privacidade@vendapop.com.br</li>
          <li><strong>Assunto:</strong> Proteção de Dados / LGPD</li>
        </ul>
        <p className="mt-4">
          Nosso Encarregado de Dados (DPO) está disponível para tratar de todas as questões relacionadas à proteção de seus dados pessoais.
        </p>
      </>
    ),
  },
];

export default function PrivacyPolicyPage() {
  return (
    <LegalPage
      title="Política de Privacidade"
      lastUpdated="12 de junho de 2026"
      sections={sections}
    />
  );
}
