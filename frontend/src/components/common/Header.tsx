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
        <Link to={`/${storeSlug}`} className="hover:opacity-80 transition-opacity flex items-center">
          {logoUrl && !logoError ? (
            <img
              src={logoUrl}
              alt={storeName || 'Logo da loja'}
              className="h-10 object-contain"
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
            <div className="flex items-center gap-2">
              {storeName ? (
                <>
                  <div
                    className="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm"
                    style={{ backgroundColor: primaryColor || '#6A040F' }}
                  >
                    {getInitials(storeName)}
                  </div>
                  <h1 
                    className="text-xl font-bold capitalize"
                    style={{ color: primaryColor || '#6A040F' }}
                  >
                    {storeName}
                  </h1>
                </>
              ) : (
                <h1 
                  className="text-xl font-bold capitalize"
                  style={{ color: primaryColor || '#6A040F' }}
                >
                  {storeSlug?.replace('-', ' ')}
                </h1>
              )}
            </div>
          )}
        </Link>
        
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
    </header>
  );
};

export default Header;

