import { Helmet } from 'react-helmet-async'
import { SEOHead } from '../components/common/SEOHead'
import Navbar from '../components/landing/Navbar';
import HeroSection from '../components/landing/HeroSection';
import CaseSection from '../components/landing/CaseSection';
import HowItWorksSection from '../components/landing/HowItWorksSection';
import FeatureGrid from '../components/landing/FeatureGrid';
import GoogleIndexSection from '../components/landing/GoogleIndexSection';
import PlansSection from '../components/landing/PlansSection';
import FAQSection from '../components/landing/FAQSection';
import WaitlistSection from '../components/landing/WaitlistSection';
import FooterSection from '../components/landing/FooterSection';

const jsonLd = {
  '@context': 'https://schema.org',
  '@graph': [
    {
      '@type': 'Organization',
      name: 'VendaPop',
      url: 'https://vendapop.com.br',
      logo: 'https://vendapop.com.br/og-image.png',
      description: 'Plataforma para criar catálogo online com pedidos finalizados pelo WhatsApp. Monte sua loja em 5 minutos.',
      founder: {
        '@type': 'Person',
        name: 'Dinaerte Neto',
      },
      sameAs: [
        'https://dynasolutions.com.br',
      ],
    },
    {
      '@type': 'WebSite',
      name: 'VendaPop',
      url: 'https://vendapop.com.br',
      description: 'Crie sua loja online e receba pedidos organizados no WhatsApp. Grátis para até 6 produtos.',
      inLanguage: 'pt-BR',
    },
    {
      '@type': 'HowTo',
      name: 'Como criar sua loja no VendaPop',
      description: 'Monte seu catálogo online em 3 passos.',
      step: [
        {
          '@type': 'HowToStep',
          position: 1,
          name: 'Monte sua loja',
          text: 'Cadastre seus produtos com fotos, preços, tamanhos e cores em minutos.',
        },
        {
          '@type': 'HowToStep',
          position: 2,
          name: 'Compartilhe o link',
          text: 'Cole o link na bio do Instagram e em grupos de WhatsApp.',
        },
        {
          '@type': 'HowToStep',
          position: 3,
          name: 'Receba pedidos',
          text: 'O cliente monta o carrinho sozinho e o pedido chega organizado no seu WhatsApp.',
        },
      ],
    },
    {
      '@type': 'FAQPage',
      mainEntity: [
        {
          '@type': 'Question',
          name: 'O VendaPop é gratuito?',
          acceptedAnswer: {
            '@type': 'Answer',
            text: 'Sim! O plano Grátis permite até 6 produtos, para sempre. Você pode testar sem compromisso e fazer upgrade quando quiser.',
          },
        },
        {
          '@type': 'Question',
          name: 'Precisa de cartão de crédito para usar?',
          acceptedAnswer: {
            '@type': 'Answer',
            text: 'Não. Seus clientes pagam via PIX — a chave aparece na página de confirmação do pedido. Você não precisa de maquininha nem gateway.',
          },
        },
        {
          '@type': 'Question',
          name: 'Funciona no celular?',
          acceptedAnswer: {
            '@type': 'Answer',
            text: 'Sim! O VendaPop é um PWA — você instala no celular como se fosse um app nativo, e seus clientes acessam a loja pelo navegador sem baixar nada.',
          },
        },
        {
          '@type': 'Question',
          name: 'Preciso ter CNPJ?',
          acceptedAnswer: {
            '@type': 'Answer',
            text: 'Não. Você pode usar como pessoa física. O VendaPop é feito para microempreendedores, autônomos e pequenos negócios.',
          },
        },
        {
          '@type': 'Question',
          name: 'Como recebo os pedidos?',
          acceptedAnswer: {
            '@type': 'Answer',
            text: 'Quando o cliente finaliza a compra, o pedido chega organizado no seu WhatsApp — com produto, variações, quantidade e total já calculado.',
          },
        },
      ],
    },
  ],
}

const Landing = () => {
  return (
    <div className="min-h-screen bg-white">
      <Helmet>
        <script type="application/ld+json">
          {JSON.stringify(jsonLd)}
        </script>
      </Helmet>
      <SEOHead
        title="VendaPop — Monte sua loja online e receba pedidos no WhatsApp"
        description="Crie seu catálogo online grátis em 5 minutos. Seus clientes navegam, escolhem variações e o pedido chega organizado no seu WhatsApp — sem calcular total na mão."
        path="/"
      />
      <Navbar />
      <HeroSection />
      <CaseSection />
      <HowItWorksSection />
      <FeatureGrid />
      <GoogleIndexSection />
      <PlansSection />
      <FAQSection />
      <WaitlistSection />
      <FooterSection />
    </div>
  );
};

export default Landing;
