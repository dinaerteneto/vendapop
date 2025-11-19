import React from 'react';
import { Link } from 'react-router-dom';
import { useCart } from '../../context/CartContext';

interface HeaderProps {
  storeName?: string;
  storeSlug?: string;
  primaryColor?: string;
}

const Header: React.FC<HeaderProps> = ({ storeName, storeSlug, primaryColor }) => {
  const { cartCount } = useCart();

  return (
    <header className="bg-white shadow-sm sticky top-0 z-30">
      <div className="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
        <Link to={`/${storeSlug}`} className="hover:opacity-80 transition-opacity">
          <h1 
            className="text-xl font-bold capitalize"
            style={{ color: primaryColor || '#6A040F' }}
          >
            {storeName || storeSlug?.replace('-', ' ')}
          </h1>
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

