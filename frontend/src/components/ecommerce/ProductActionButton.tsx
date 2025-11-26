import React from 'react';
import { formatWhatsAppNumber } from '../../utils/whatsapp';

interface ProductActionButtonProps {
  actionType: 'add_to_cart' | 'affiliate_link' | 'whatsapp_contact';
  affiliateLink?: string | null;
  whatsappMessage?: string | null;
  whatsappNumber?: string | null;
  buttonLabel?: string | null;
  onAddToCart?: () => void;
  primaryColor?: string;
  productName?: string;
  className?: string;
}

const ProductActionButton: React.FC<ProductActionButtonProps> = ({
  actionType,
  affiliateLink,
  whatsappMessage,
  whatsappNumber,
  buttonLabel,
  onAddToCart,
  primaryColor = '#7c3aed',
  productName = '',
  className = '',
}) => {
  const baseClasses = `w-full text-white py-4 rounded-xl font-bold text-lg shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2 hover:opacity-90 ${className}`;
  const buttonStyle = {
    backgroundColor: primaryColor,
    boxShadow: `0 10px 15px -3px ${primaryColor}40`,
  };

  // WhatsApp Contact
  if (actionType === 'whatsapp_contact' && whatsappNumber) {
    const formattedNumber = formatWhatsAppNumber(whatsappNumber);
    const message = whatsappMessage || `Olá! Tenho interesse em ${productName}. Poderia me enviar mais informações?`;
    const whatsappUrl = `https://wa.me/${formattedNumber}?text=${encodeURIComponent(message)}`;
    const label = buttonLabel || 'Fale com um Vendedor';

    return (
      <a
        href={whatsappUrl}
        target="_blank"
        rel="noopener noreferrer"
        className={baseClasses}
        style={buttonStyle}
      >
        <span>💬</span> {label}
      </a>
    );
  }

  // Affiliate Link
  if (actionType === 'affiliate_link' && affiliateLink) {
    return (
      <a
        href={affiliateLink}
        target="_blank"
        rel="noopener noreferrer"
        className={baseClasses}
        style={buttonStyle}
      >
        <span>🛒</span> Comprar Agora
      </a>
    );
  }

  // Default: Add to Cart
  return (
    <button
      onClick={onAddToCart}
      className={baseClasses}
      style={buttonStyle}
    >
      <span>🛒</span> Adicionar ao Carrinho
    </button>
  );
};

export default ProductActionButton;

