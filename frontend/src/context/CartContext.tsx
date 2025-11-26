import React, { createContext, useContext, useEffect, useState } from 'react';

export interface CartItem {
  product_id: number;
  name: string;
  price: number;
  size?: string; // Deprecated, usar attributes
  color?: string; // Deprecated, usar attributes
  attributes?: { [key: string]: string }; // { attributeId: value }
  quantity: number;
  main_image_url?: string;
}

interface CartContextType {
  cart: CartItem[];
  addToCart: (item: CartItem) => void;
  removeFromCart: (index: number) => void;
  updateQuantity: (index: number, quantity: number) => void;
  clearCart: () => void;
  cartCount: number;
  totalValue: number;
}

const CartContext = createContext<CartContextType | undefined>(undefined);

export const CartProvider: React.FC<{ children: React.ReactNode; storeSlug?: string }> = ({ children, storeSlug }) => {
  const [cart, setCart] = useState<CartItem[]>([]);

  const getCartKey = () => `cart_${storeSlug}`;

  useEffect(() => {
    if (storeSlug) {
      const savedCart = localStorage.getItem(getCartKey());
      if (savedCart) {
        try {
          setCart(JSON.parse(savedCart));
        } catch (e) {
          console.error("Failed to parse cart", e);
        }
      } else {
        setCart([]);
      }
    }
  }, [storeSlug]);

  useEffect(() => {
    if (storeSlug) {
      localStorage.setItem(getCartKey(), JSON.stringify(cart));
    }
  }, [cart, storeSlug]);

  const addToCart = (item: CartItem) => {
    setCart((prev) => {
      // Comparar por product_id e atributos (ou size/color para compatibilidade)
      const existingItemIndex = prev.findIndex((i) => {
        if (i.product_id !== item.product_id) return false;
        
        // Normalizar atributos para comparação (tratar undefined, null e objetos vazios)
        const itemAttrs = item.attributes || {};
        const iAttrs = i.attributes || {};
        const itemHasAttrs = item.attributes !== undefined && Object.keys(itemAttrs).length > 0;
        const iHasAttrs = i.attributes !== undefined && Object.keys(iAttrs).length > 0;
        
        // Se o produto tem atributos definidos (não undefined), sempre comparar por attributes
        // Isso garante que produtos sem atributos não sejam agrupados incorretamente
        if (item.attributes !== undefined || i.attributes !== undefined) {
          // Se ambos têm atributos definidos, comparar strings
          if (itemHasAttrs && iHasAttrs) {
            const itemAttrsStr = JSON.stringify(itemAttrs);
            const iAttrsStr = JSON.stringify(iAttrs);
            return itemAttrsStr === iAttrsStr;
          }
          // Se um tem e outro não, são diferentes
          return false;
        }
        
        // Se nenhum tem atributos definidos, usar fallback para size/color (compatibilidade)
        return i.size === item.size && i.color === item.color;
      });

      if (existingItemIndex >= 0) {
        const newCart = [...prev];
        // Somar a quantidade nova à existente
        newCart[existingItemIndex].quantity += item.quantity;
        return newCart;
      }
      
      // Adicionar novo item
      return [...prev, item];
    });
  };

  const removeFromCart = (index: number) => {
    setCart((prev) => prev.filter((_, i) => i !== index));
  };

  const updateQuantity = (index: number, quantity: number) => {
    if (quantity < 1) return;
    setCart((prev) => {
        const newCart = [...prev];
        if (newCart[index]) {
            newCart[index].quantity = quantity;
        }
        return newCart;
    });
  };

  const clearCart = () => {
    setCart([]);
  };

  const cartCount = cart.reduce((acc, item) => acc + item.quantity, 0);
  const totalValue = cart.reduce((acc, item) => acc + item.price * item.quantity, 0);

  return (
    <CartContext.Provider value={{ cart, addToCart, removeFromCart, updateQuantity, clearCart, cartCount, totalValue }}>
      {children}
    </CartContext.Provider>
  );
};

export const useCart = () => {
  const context = useContext(CartContext);
  if (context === undefined) {
    throw new Error('useCart must be used within a CartProvider');
  }
  return context;
};
