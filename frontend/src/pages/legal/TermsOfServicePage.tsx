import LegalPage from '../../components/legal/LegalPage';
import type { LegalSection } from '../../components/legal/LegalPage';

const sections: LegalSection[] = [
  {
    id: 'aceitacao',
    title: '1. Aceitação dos Termos',
    content: (
      <>
        <p>
          Ao criar uma conta e utilizar os serviços do <strong>VendaPop</strong>, você declara ter lido, compreendido e aceito integralmente estes Termos de Uso. Se você não concordar com qualquer parte destes termos, não deverá utilizar nossos serviços.
        </p>
      </>
    ),
  },
  {
    id: 'descricao-servico',
    title: '2. Descrição do Serviço',
    content: (
      <>
        <p>
          O VendaPop é uma plataforma de loja virtual que permite aos lojistas:
        </p>
        <ul className="list-disc pl-6 space-y-1">
          <li>Criar e gerenciar sua loja online</li>
          <li>Cadastrar produtos com fotos, descrições, preços e variações</li>
          <li>Organizar produtos em categorias</li>
          <li>Receber pedidos dos clientes via WhatsApp</li>
          <li>Gerenciar clientes e histórico de pedidos</li>
          <li>Personalizar a aparência da loja</li>
        </ul>
        <p className="mt-4">
          O serviço é fornecido "como está" e pode ser modificado, suspenso ou descontinuado a qualquer momento, mediante aviso prévio quando possível.
        </p>
      </>
    ),
  },
  {
    id: 'elegibilidade',
    title: '3. Elegibilidade e Cadastro',
    content: (
      <>
        <p>Para utilizar nossos serviços, você deve:</p>
        <ul className="list-disc pl-6 space-y-1">
          <li>Ser maior de 18 anos</li>
          <li>Possuir capacidade legal para contratar</li>
          <li>Fornecer informações verdadeiras, precisas e atualizadas</li>
          <li>Manter a confidencialidade de suas credenciais de acesso</li>
        </ul>
        <p className="mt-4">
          Você é responsável por todas as atividades que ocorrem em sua conta. Notifique-nos imediatamente sobre qualquer uso não autorizado.
        </p>
      </>
    ),
  },
  {
    id: 'obrigacoes-usuario',
    title: '4. Obrigações do Usuário',
    content: (
      <>
        <p>Ao utilizar o VendaPop, você se compromete a:</p>
        <ul className="list-disc pl-6 space-y-1">
          <li>Fornecer dados precisos sobre sua loja e produtos</li>
          <li>Não utilizar o serviço para fins ilegais ou não autorizados</li>
          <li>Não anunciar produtos proibidos ou que violem direitos de terceiros</li>
          <li>Não tentar acessar áreas restritas do sistema ou contas de outros usuários</li>
          <li>Não utilizar o serviço para transmitir malware, vírus ou código malicioso</li>
          <li>Não realizar engenharia reversa, descompilar ou desacoplar qualquer parte do sistema</li>
          <li>Não sobrecarregar a infraestrutura com requisições excessivas ou automatizadas</li>
          <li>Não compartilhar sua conta com terceiros</li>
        </ul>
      </>
    ),
  },
  {
    id: 'responsabilidade-produtos',
    title: '5. Responsabilidade sobre Produtos e Pedidos',
    content: (
      <>
        <p>
          O VendaPop é uma plataforma tecnológica que conecta lojistas a seus clientes. <strong>O lojista é o único responsável</strong> por:
        </p>
        <ul className="list-disc pl-6 space-y-1">
          <li>A precisão das informações dos produtos (descrição, preço, fotos)</li>
          <li>O cumprimento dos pedidos recebidos</li>
          <li>A qualidade dos produtos vendidos</li>
          <li>O envio e a entrega dos produtos</li>
          <li>O atendimento ao cliente e suporte pós-venda</li>
          <li>A conformidade com o Código de Defesa do Consumidor</li>
        </ul>
        <p className="mt-4">
          O VendaPop não se responsabiliza por disputas entre lojistas e clientes, agindo apenas como intermediário tecnológico.
        </p>
      </>
    ),
  },
  {
    id: 'propriedade-intelectual',
    title: '6. Propriedade Intelectual',
    content: (
      <>
        <p>
          Todo o conteúdo, design, código-fonte, logos, interfaces e funcionalidades do VendaPop são de nossa propriedade exclusiva ou de nossos licenciadores, estando protegidos pelas leis de direitos autorais, marcas e propriedade intelectual.
        </p>
        <p className="mt-4">
          Você não adquire qualquer direito de propriedade intelectual sobre o serviço, exceto o direito limitado de uso pessoal e comercial conforme estes termos. Os conteúdos que você publicar em sua loja continuam sendo de sua propriedade.
        </p>
      </>
    ),
  },
  {
    id: 'disponibilidade',
    title: '7. Disponibilidade e Limitações do Serviço',
    content: (
      <>
        <p>
          Buscamos manter o serviço disponível 24 horas por dia, 7 dias por semana. No entanto, não garantimos disponibilidade ininterrupta, pois podem ocorrer:
        </p>
        <ul className="list-disc pl-6 space-y-1">
          <li>Manutenções programadas (com aviso prévio quando possível)</li>
          <li>Interrupções por causas técnicas ou de força maior</li>
          <li>Falhas de comunicação, rede ou infraestrutura</li>
        </ul>
        <p className="mt-4">
          Não nos responsabilizamos por perdas decorrentes de indisponibilidade do serviço.
        </p>
      </>
    ),
  },
  {
    id: 'privacidade-dados',
    title: '8. Privacidade e Proteção de Dados',
    content: (
      <>
        <p>
          O tratamento de seus dados pessoais é regido pela nossa <a href="/privacidade" className="text-blue-600 hover:underline">Política de Privacidade</a>, que complementa estes Termos de Uso.
        </p>
        <p className="mt-4">
          Ao utilizar nossos serviços, você concorda com a coleta, uso e compartilhamento de informações conforme descrito na Política de Privacidade.
        </p>
      </>
    ),
  },
  {
    id: 'suspensao-rescisao',
    title: '9. Suspensão e Rescisão',
    content: (
      <>
        <p>Podemos suspender ou encerrar sua conta se:</p>
        <ul className="list-disc pl-6 space-y-1">
          <li>Você violar estes Termos de Uso</li>
          <li>Você utilizar o serviço para fins ilegais</li>
          <li>Houver exigência legal ou ordem judicial</li>
          <li>Você solicitar o encerramento da conta</li>
        </ul>
        <p className="mt-4">
          Você pode encerrar sua conta a qualquer momento. Após o encerramento, seus dados serão excluídos conforme nossa Política de Privacidade e obrigações legais aplicáveis.
        </p>
      </>
    ),
  },
  {
    id: 'limitacao-responsabilidade',
    title: '10. Limitação de Responsabilidade',
    content: (
      <>
        <p>
          Na máxima extensão permitida por lei, o VendaPop <strong>não será responsável</strong> por:
        </p>
        <ul className="list-disc pl-6 space-y-1">
          <li>Perdas indiretas, incidentais, especiais ou consequenciais</li>
          <li>Perda de lucros, receitas, dados ou economias</li>
          <li>Disputas entre lojistas e clientes</li>
          <li>Interrupções, erros ou atrasos no serviço</li>
          <li>Ações de terceiros ou eventos de força maior</li>
        </ul>
        <p className="mt-4">
          Esta limitação não afeta direitos que não podem ser excluídos por lei.
        </p>
      </>
    ),
  },
  {
    id: 'modificacoes',
    title: '11. Modificações dos Termos',
    content: (
      <>
        <p>
          Podemos modificar estes Termos de Uso a qualquer momento. Quando fizermos alterações significativas:
        </p>
        <ul className="list-disc pl-6 space-y-1">
          <li>Notificaremos você por e-mail ou aviso destacado na plataforma</li>
          <li>Publicaremos a versão atualizada com nova data de "última atualização"</li>
          <li>Em caso de alterações substanciais, poderemos solicitar sua aceitação expressa</li>
        </ul>
        <p className="mt-4">
          O uso continuado do serviço após as modificações constitui aceitação dos novos termos.
        </p>
      </>
    ),
  },
  {
    id: 'foro',
    title: '12. Lei Aplicável e Foro',
    content: (
      <>
        <p>
          Estes Termos de Uso são regidos pelas leis da República Federativa do Brasil, incluindo a Lei Geral de Proteção de Dados (LGPD), o Marco Civil da Internet e o Código de Defesa do Consumidor.
        </p>
        <p className="mt-4">
          Fica eleito o foro da comarca do domicílio do usuário para dirimir quaisquer controvérsias decorrentes destes termos, com renúncia expressa a qualquer outro, por mais privilegiado que seja.
        </p>
      </>
    ),
  },
  {
    id: 'disposicoes-gerais',
    title: '13. Disposições Gerais',
    content: (
      <>
        <ul className="list-disc pl-6 space-y-2">
          <li>
            <strong>Integralidade:</strong> Estes termos constituem o acordo completo entre você e o VendaPop sobre o uso do serviço.
          </li>
          <li>
            <strong>Separabilidade:</strong> Se qualquer disposição for considerada inválida, as demais permanecerão em pleno vigor.
          </li>
          <li>
            <strong>Renúncia:</strong> A não aplicação de qualquer disposição não constituirá renúncia ao direito de aplicá-la posteriormente.
          </li>
          <li>
            <strong>Cessão:</strong> Você não pode ceder estes termos a terceiros sem nosso consentimento prévio.
          </li>
        </ul>
      </>
    ),
  },
  {
    id: 'contato',
    title: '14. Contato',
    content: (
      <>
        <p>
          Para dúvidas sobre estes Termos de Uso, entre em contato:
        </p>
        <ul className="list-none pl-0 mt-4 space-y-2">
          <li><strong>E-mail:</strong> contato@vendapop.com.br</li>
        </ul>
      </>
    ),
  },
];

export default function TermsOfServicePage() {
  return (
    <LegalPage
      title="Termos de Uso"
      lastUpdated="12 de junho de 2026"
      sections={sections}
    />
  );
}
