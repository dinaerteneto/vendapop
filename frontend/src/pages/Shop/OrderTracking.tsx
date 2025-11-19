import React, { useEffect, useState } from 'react';
import { useParams, Link, useOutletContext } from 'react-router-dom';
import api from '../../services/api';

interface OrderItem {
  id: number;
  product_name: string;
  quantity: number;
  unit_price: string;
  subtotal: string;
  size?: string;
  color?: string;
}

interface Order {
  id: number;
  order_number: string;
  status: string;
  total_amount: string;
  created_at: string;
  customer: {
    name: string;
    email: string;
    phone: string;
  };
  items: OrderItem[];
}

const OrderTracking: React.FC = () => {
  const { storeSlug, orderUuid } = useParams();
  const [order, setOrder] = useState<Order | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const context = useOutletContext<{ storeInfo: any }>();
  const primaryColor = context?.storeInfo?.primary_color || '#7c3aed';

  useEffect(() => {
    if (storeSlug && orderUuid) {
      api.get(`/${storeSlug}/order/${orderUuid}`)
        .then(response => {
          setOrder(response.data);
          setLoading(false);
        })
        .catch(err => {
          console.error(err);
          setError('Pedido não encontrado.');
          setLoading(false);
        });
    }
  }, [storeSlug, orderUuid]);

  if (loading) return <div className="p-8 text-center">Carregando pedido...</div>;
  if (error || !order) return <div className="p-8 text-center text-red-600">{error}</div>;

  return (
    <div className="max-w-2xl mx-auto bg-white shadow-lg rounded-2xl overflow-hidden">
      <div className="p-6 text-white text-center" style={{ backgroundColor: primaryColor }}>
        <h1 className="text-2xl font-bold mb-2">Pedido #{order.order_number}</h1>
        <p className="opacity-90">Obrigado pela sua compra, {order.customer.name}!</p>
      </div>

      <div className="p-6">
        <div className="mb-6">
          <h2 className="text-lg font-bold text-gray-800 mb-3 border-b pb-2">Detalhes do Pedido</h2>
          <div className="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p className="text-gray-500">Status</p>
              <p className="font-bold capitalize text-green-600">{order.status}</p>
            </div>
            <div>
              <p className="text-gray-500">Data</p>
              <p className="font-bold">{new Date(order.created_at).toLocaleDateString('pt-BR')} {new Date(order.created_at).toLocaleTimeString('pt-BR')}</p>
            </div>
          </div>
        </div>

        <div className="mb-6">
          <h2 className="text-lg font-bold text-gray-800 mb-3 border-b pb-2">Itens</h2>
          <div className="space-y-4">
            {order.items.map(item => (
              <div key={item.id} className="flex justify-between items-center">
                <div>
                    <p className="font-medium text-gray-900">{item.product_name}</p>
                    <p className="text-xs text-gray-500">
                        {item.size && `Tam: ${item.size} `} 
                        {item.color && `Cor: ${item.color} `}
                        x{item.quantity}
                    </p>
                </div>
                <p className="font-bold text-gray-700">R$ {parseFloat(item.subtotal).toFixed(2).replace('.', ',')}</p>
              </div>
            ))}
          </div>
          <div className="mt-4 pt-4 border-t flex justify-between items-center text-lg font-extrabold text-gray-900">
            <span>Total</span>
            <span>R$ {parseFloat(order.total_amount).toFixed(2).replace('.', ',')}</span>
          </div>
        </div>

        <div className="text-center mt-8">
          <Link 
            to={`/${storeSlug}`} 
            className="inline-block px-8 py-3 rounded-full font-bold text-white transition-transform hover:scale-105 shadow-md"
            style={{ backgroundColor: primaryColor }}
          >
            Voltar para a Loja
          </Link>
        </div>
      </div>
    </div>
  );
};

export default OrderTracking;

