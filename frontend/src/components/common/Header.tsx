import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useCart } from '../../context/CartContext';

interface HeaderProps {
  storeName?: string;
  storeSlug?: string;
  primaryColor?: string;
  logoUrl?: string | null;
}

const Header: React.FC<HeaderProps> = ({ storeName, storeSlug, primaryColor, logoUrl }) => {
  const { cartCount } = useCart();
  const [logoError, setLogoError] = useState(false);
  const [isAdminLoggedIn, setIsAdminLoggedIn] = useState(false);

  // Reset error when logoUrl changes
  useEffect(() => {
    console.log('Header: logoUrl prop changed:', logoUrl);
    setLogoError(false);
    // Debug: log logo URL changes
    if (logoUrl) {
      console.log('Header: Logo URL received:', logoUrl);
    } else {
      console.log('Header: No logo URL provided');
    }
  }, [logoUrl]);

  // Check if admin is logged in
  useEffect(() => {
    const checkAdminLogin = () => {
      const token = localStorage.getItem('admin_token');
      setIsAdminLoggedIn(!!token);
    };
    
    // Check immediately
    checkAdminLogin();
    
    // Listen for storage changes (e.g., login/logout in another tab)
    const handleStorageChange = (e: StorageEvent) => {
      if (e.key === 'admin_token') {
        checkAdminLogin();
      }
    };
    
    window.addEventListener('storage', handleStorageChange);
    
    // Also listen for custom events (for same-tab login/logout)
    const handleCustomStorageChange = () => {
      checkAdminLogin();
    };
    
    window.addEventListener('localStorageChange', handleCustomStorageChange);
    
    return () => {
      window.removeEventListener('storage', handleStorageChange);
      window.removeEventListener('localStorageChange', handleCustomStorageChange);
    };
  }, []);

  // Get initials from store name for fallback
  const getInitials = (name: string) => {
    return name
      .split(' ')
      .map(word => word[0])
      .join('')
      .toUpperCase()
      .substring(0, 2);
  };

  return (
    <header className="bg-white shadow-sm sticky top-0 z-30">
      <div className="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
        <Link to={`/${storeSlug}`} className="hover:opacity-80 transition-opacity flex items-center gap-3">
          {/* Logo circle - sempre mostra, com imagem ou iniciais */}
          <div
            className="w-12 h-12 rounded-full flex items-center justify-center overflow-hidden flex-shrink-0"
            style={{ backgroundColor: logoUrl && !logoError ? 'transparent' : (primaryColor || '#6A040F') }}
          >
            {logoUrl && !logoError ? (
              <img
                src={logoUrl}
                alt={storeName || 'Logo da loja'}
                className="w-full h-full object-cover rounded-full"
                onError={() => {
                  console.error('Header: Failed to load logo image:', logoUrl);
                  setLogoError(true);
                }}
                onLoad={() => {
                  console.log('Header: Logo image loaded successfully:', logoUrl);
                }}
                key={`logo-${logoUrl}`} // Force re-render when logoUrl changes
              />
            ) : (
              <span className="text-white font-bold text-sm">
                {storeName ? getInitials(storeName) : (storeSlug?.substring(0, 2).toUpperCase() || 'LO')}
              </span>
            )}
          </div>
          
          {/* Nome da loja - sempre mostra */}
          <h1 
            className="text-xl font-bold capitalize"
            style={{ color: primaryColor || '#6A040F' }}
          >
            {storeName || storeSlug?.replace(/-/g, ' ') || 'Loja'}
          </h1>
        </Link>
        
        <div className="flex items-center gap-2">
          {/* Admin Link */}
          {isAdminLoggedIn && (
            <Link
              to="/admin"
              className="p-2 hover:bg-gray-100 rounded-full transition-colors"
              title="Ir para o painel administrativo"
            >
              <svg className="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </Link>
          )}
          
          {/* Cart Widget */}
          <Link to={`/${storeSlug}/cart`} className="relative p-2 hover:bg-gray-100 rounded-full transition-colors">
            <span className="text-2xl">🛒</span>
            {cartCount > 0 && (
              <span 
                  className="absolute -top-1 -right-1 text-white text-xs font-bold px-2 py-0.5 rounded-full"
                  style={{ backgroundColor: primaryColor || '#6A040F' }}
              >
                {cartCount}
              </span>
            )}
          </Link>
        </div>
      </div>
    </header>
  );
};

export default Header;

