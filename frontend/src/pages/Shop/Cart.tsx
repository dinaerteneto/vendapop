import React, { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';

const Cart: React.FC = () => {
  const { storeSlug } = useParams();
  const navigate = useNavigate();
  const [cart, setCart] = useState<any[]>([]);

  useEffect(() => {
      const cartKey = `cart_${storeSlug}`;
      setCart(JSON.parse(localStorage.getItem(cartKey) || '[]'));
  }, [storeSlug]);

  const removeItem = (index: number) => {
      const newCart = [...cart];
      newCart.splice(index, 1);
      setCart(newCart);
      localStorage.setItem(`cart_${storeSlug}`, JSON.stringify(newCart));
  };

  const total = cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);

  return (
    <div>
        <h2 className="text-2xl font-bold mb-4">Carrinho</h2>
        {cart.length === 0 ? (
            <div className="text-center py-10">
                <p className="mb-4">Seu carrinho está vazio.</p>
                <Link to={`/${storeSlug}`} className="text-purple-600 underline">Voltar para loja</Link>
            </div>
        ) : (
            <>
                <div className="bg-white rounded shadow overflow-hidden mb-6">
                    {cart.map((item, idx) => (
                        <div key={idx} className="p-4 border-b flex justify-between items-center">
                            <div>
                                <h3 className="font-bold">{item.name}</h3>
                                <p className="text-sm text-gray-500">
                                    {item.size && `Tam: ${item.size}`} {item.color && `Cor: ${item.color}`}
                                </p>
                                <p className="text-sm">R$ {item.price.toFixed(2)} x {item.quantity}</p>
                            </div>
                            <button onClick={() => removeItem(idx)} className="text-red-500 text-sm">Remover</button>
                        </div>
                    ))}
                    <div className="p-4 bg-gray-50 flex justify-between font-bold text-lg">
                        <span>Total</span>
                        <span>R$ {total.toFixed(2)}</span>
                    </div>
                </div>

                <div className="flex gap-4">
                    <Link to={`/${storeSlug}`} className="flex-1 py-3 text-center border border-purple-600 text-purple-600 rounded font-bold">
                        Continuar Comprando
                    </Link>
                    <button onClick={() => navigate(`/${storeSlug}/checkout`)} className="flex-1 py-3 text-center bg-green-600 text-white rounded font-bold shadow">
                        Fechar Pedido
                    </button>
                </div>
            </>
        )}
    </div>
  );
};

export default Cart;
