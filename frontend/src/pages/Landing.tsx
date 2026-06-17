import HeroSection from '../components/landing/HeroSection';
import CaseSection from '../components/landing/CaseSection';
import HowItWorksSection from '../components/landing/HowItWorksSection';
import FeatureGrid from '../components/landing/FeatureGrid';
import WaitlistSection from '../components/landing/WaitlistSection';
import FooterSection from '../components/landing/FooterSection';

const Landing = () => {
  return (
    <div className="min-h-screen bg-white">
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
