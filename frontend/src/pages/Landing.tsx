import { SEOHead } from '../components/common/SEOHead'
import HeroSection from '../components/landing/HeroSection';
import CaseSection from '../components/landing/CaseSection';
import HowItWorksSection from '../components/landing/HowItWorksSection';
import FeatureGrid from '../components/landing/FeatureGrid';
import WaitlistSection from '../components/landing/WaitlistSection';
import FooterSection from '../components/landing/FooterSection';

const Landing = () => {
  return (
    <div className="min-h-screen bg-white">
      <SEOHead
        title="VendaPop — Sua loja no WhatsApp"
        description="Monte sua loja em 5 minutos. Seus clientes navegam, escolhem variações e o pedido chega organizado no seu WhatsApp — sem calcular total na mão."
        path="/"
      />
      <HeroSection />
      <CaseSection />
      <HowItWorksSection />
      <FeatureGrid />
      <WaitlistSection />
      <FooterSection />
    </div>
  );
};

export default Landing;
