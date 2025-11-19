import React, { useState } from 'react';
import { useParams } from 'react-router-dom';
import api from '../../services/api';
import { useCart } from '../../context/CartContext';

const Checkout: React.FC = () => {
  const { storeSlug } = useParams();
  const { cart, clearCart, totalValue } = useCart();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [loading, setLoading] = useState(false);

  const handleFinish = async () => {
      if (!name) { alert('Nome obrigatório'); return; }
      if (!email && !phone) { alert('Email ou Telefone obrigatório'); return; }

      setLoading(true);
      try {
          const payload = {
              customer: { name, email, phone },
              items: cart.map(i => ({
                  product_id: i.product_id,
                  quantity: i.quantity,
                  size: i.size,
                  color: i.color
              }))
          };

          const { data } = await api.post(`/${storeSlug}/checkout`, payload);

          // Limpar carrinho
          clearCart();

          // Redirecionar WhatsApp
          window.location.href = data.whatsapp_link;
      } catch (e) {
          console.error(e);
          alert('Erro ao processar pedido.');
          setLoading(false);
      }
  };

  if (cart.length === 0) return <div className="p-4 text-center">Carrinho vazio</div>;

  return (
      <div className="p-4 bg-white shadow rounded-lg">
          <h2 className="text-xl font-bold mb-4">Checkout</h2>

          <div className="mb-6">
              {cart.map((item, idx) => (
                  <div key={idx} className="flex justify-between py-2 border-b">
                      <div>
                          <div className="font-medium">{item.name}</div>
                          <div className="text-xs text-gray-500">{item.size} {item.color} x{item.quantity}</div>
                      </div>
                      <div>R$ {(item.price * item.quantity).toFixed(2)}</div>
                  </div>
              ))}
              <div className="flex justify-between py-4 font-bold text-lg">
                  <span>Total</span>
                  <span>R$ {totalValue.toFixed(2)}</span>
              </div>
          </div>

          <div className="space-y-4 mb-6">
              <input
                placeholder="Seu Nome *"
                className="w-full border p-2 rounded"
                value={name} onChange={e => setName(e.target.value)}
              />
              <input
                placeholder="Seu E-mail"
                className="w-full border p-2 rounded"
                type="email"
                value={email} onChange={e => setEmail(e.target.value)}
              />
              <input
                placeholder="Seu Celular/WhatsApp"
                className="w-full border p-2 rounded"
                value={phone} onChange={e => setPhone(e.target.value)}
              />
              <p className="text-xs text-gray-500">* Campos obrigatórios. Preencha Email ou Celular.</p>
          </div>

          <button
            onClick={handleFinish}
            disabled={loading}
            className="w-full bg-green-600 text-white py-3 rounded font-bold hover:bg-green-700 disabled:opacity-50"
          >
            {loading ? 'Enviando...' : 'Finalizar no WhatsApp'}
          </button>
      </div>
  );
};

export default Checkout;
