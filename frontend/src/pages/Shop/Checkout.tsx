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
  const [notes, setNotes] = useState('');
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
                  size: i.size || null, // Deprecated, manter para compatibilidade
                  color: i.color || null, // Deprecated, manter para compatibilidade
                  attributes: i.attributes || null, // Novo formato
              })),
              notes: notes || null
          };

          const { data } = await api.post(`/${storeSlug}/checkout`, payload);

          // Limpar carrinho
          clearCart();

          // Redirecionar para página de confirmação do pedido
          window.location.href = `/${storeSlug}/order/${data.order_uuid}`;
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
                  <div key={idx} className="flex items-center justify-between py-4 border-b border-gray-100">
                      <div className="flex items-center gap-3">
                          {item.main_image_url ? (
                              <img src={item.main_image_url} alt={item.name} className="w-12 h-12 object-cover rounded-md bg-gray-100" />
                          ) : (
                              <div className="w-12 h-12 bg-gray-100 rounded-md flex items-center justify-center text-xs text-gray-400">Sem foto</div>
                          )}
                          <div>
                              <div className="font-medium text-gray-800">{item.name}</div>
                              <div className="text-xs text-gray-500">
                                  {item.attributes && Object.keys(item.attributes).length > 0 ? (
                                      <>
                                          {Object.values(item.attributes).join(', ')} <span className="font-semibold">x{item.quantity}</span>
                                      </>
                                  ) : (
                                      <>
                                          {item.size && `Tam: ${item.size} `} 
                                          {item.color && `Cor: ${item.color} `} 
                                          <span className="font-semibold">x{item.quantity}</span>
                                      </>
                                  )}
                              </div>
                          </div>
                      </div>
                      <div className="font-bold text-gray-700">R$ {(item.price * item.quantity).toFixed(2).replace('.',',')}</div>
                  </div>
              ))}
              <div className="flex justify-between py-4 font-extrabold text-lg border-t border-gray-200 mt-2">
                  <span>Total</span>
                  <span>R$ {totalValue.toFixed(2).replace('.',',')}</span>
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
              <textarea
                placeholder="Observações do pedido (opcional)"
                className="w-full border p-2 rounded min-h-[100px] resize-y"
                value={notes}
                onChange={e => setNotes(e.target.value)}
                rows={4}
              />
              <p className="text-xs text-gray-500">* Campos obrigatórios. Preencha Email ou Celular.</p>
          </div>

          <button
            onClick={handleFinish}
            disabled={loading}
            className="w-full bg-green-600 text-white py-3 rounded font-bold hover:bg-green-700 disabled:opacity-50"
          >
            {loading ? 'Finalizando pedido...' : 'Finalizar Pedido'}
          </button>
      </div>
  );
};

export default Checkout;
