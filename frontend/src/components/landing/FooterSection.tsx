import { Link } from 'react-router-dom';

const FooterSection: React.FC = () => {
  return (
    <footer className="bg-gray-900 text-gray-400 py-8">
      <div className="container mx-auto px-4 text-center">
        <div className="flex flex-wrap justify-center gap-4 mb-4 text-sm">
          <Link to="/privacidade" className="hover:text-white transition">Privacidade</Link>
          <Link to="/termos" className="hover:text-white transition">Termos de Uso</Link>
          <Link to="/cookies" className="hover:text-white transition">Cookies</Link>
          <Link to="/lgpd" className="hover:text-white transition">Seus Direitos LGPD</Link>
        </div>
        <p className="text-xs">
          © {new Date().getFullYear()} PopVenda. Desenvolvido por{' '}
          <a href="https://dynasolutions.com.br" target="_blank" rel="noopener noreferrer" className="text-purple-400 hover:text-purple-300">
            Dyna Solutions
          </a>
        </p>
      </div>
    </footer>
  );
};

export default FooterSection;
