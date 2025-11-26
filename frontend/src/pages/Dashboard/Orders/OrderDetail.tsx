import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../../../services/api';
import { toast } from 'react-toastify';

interface OrderItem {
  id: number;
  product_name: string;
  quantity: number;
  unit_price: string;
  subtotal: string;
  size?: string;
  color?: string;
  attributes?: { [key: string]: string }; // { attributeId: value }
  product?: {
    images?: Array<{ url: string; is_main: boolean }>;
    main_image_url?: string | null;
  };
}

interface Order {
  id: number;
  uuid: string;
  order_number: string;
  status: string;
  total_amount: string;
  created_at: string;
  notes?: string | null;
  customer: {
    name: string;
    email: string;
    phone: string;
  };
  items: OrderItem[];
}

const OrderDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [order, setOrder] = useState<Order | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [updatingStatus, setUpdatingStatus] = useState(false);

  useEffect(() => {
    if (id) {
      fetchOrder();
    }
  }, [id]);

  const fetchOrder = async () => {
    try {
      setLoading(true);
      const response = await api.get(`/admin/orders/${id}`);
      setOrder(response.data);
      setError('');
    } catch (err: any) {
      console.error(err);
      setError('Erro ao carregar pedido.');
    } finally {
      setLoading(false);
    }
  };

  const getStatusColor = (status: string) => {
    const statusUpper = status.toUpperCase();
    if (statusUpper === 'NEW') return 'bg-yellow-100 text-yellow-800';
    if (statusUpper === 'PREPARING') return 'bg-blue-100 text-blue-800';
    if (statusUpper === 'SENT') return 'bg-purple-100 text-purple-800';
    if (statusUpper === 'DONE') return 'bg-green-100 text-green-800';
    if (statusUpper === 'CANCELED') return 'bg-red-100 text-red-800';
    return 'bg-gray-100 text-gray-800';
  };

  const getStatusLabel = (status: string) => {
    const statusUpper = status.toUpperCase();
    if (statusUpper === 'NEW') return 'Novo';
    if (statusUpper === 'PREPARING') return 'Em Separação';
    if (statusUpper === 'SENT') return 'Enviado';
    if (statusUpper === 'DONE') return 'Concluído';
    if (statusUpper === 'CANCELED') return 'Cancelado';
    return status;
  };

  const handleStatusChange = async (newStatus: string) => {
    if (!order || newStatus === order.status) return;
    
    try {
      setUpdatingStatus(true);
      const response = await api.put(`/admin/orders/${order.uuid}`, { status: newStatus });
      setOrder(response.data);
      toast.success('Status do pedido atualizado com sucesso!');
    } catch (err: any) {
      console.error(err);
      toast.error('Erro ao atualizar status do pedido.');
    } finally {
      setUpdatingStatus(false);
    }
  };

  const getProductImage = (item: OrderItem): string | null => {
    if (item.product?.images && item.product.images.length > 0) {
      const mainImage = item.product.images.find(img => img.is_main);
      return mainImage ? mainImage.url : item.product.images[0].url;
    }
    return item.product?.main_image_url || null;
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-gray-500">Carregando pedido...</div>
      </div>
    );
  }

  if (error || !order) {
    return (
      <div className="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
        {error || 'Pedido não encontrado.'}
      </div>
    );
  }

  return (
    <div>
      <div className="mb-6 flex items-center justify-between">
        <div>
          <button
            onClick={() => navigate('/admin/orders')}
            className="text-gray-600 hover:text-gray-900 mb-2 flex items-center gap-2"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
            Voltar para Pedidos
          </button>
          <h1 className="text-2xl font-bold text-gray-800">Pedido #{order.order_number}</h1>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow overflow-hidden">
        {/* Header com Status */}
        <div className="bg-gradient-to-r from-indigo-600 to-indigo-700 p-6 text-white">
          <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
              <h2 className="text-xl font-bold mb-1">Pedido #{order.order_number}</h2>
              <p className="text-indigo-100">Cliente: {order.customer.name}</p>
            </div>
            <div className="flex items-center gap-3">
              <select
                value={order.status}
                onChange={(e) => handleStatusChange(e.target.value)}
                disabled={updatingStatus}
                className={`px-4 py-2 text-sm font-semibold rounded-full border-0 cursor-pointer focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed ${getStatusColor(order.status)}`}
              >
                <option value="NEW">Novo</option>
                <option value="PREPARING">Em Separação</option>
                <option value="SENT">Enviado</option>
                <option value="DONE">Concluído</option>
                <option value="CANCELED">Cancelado</option>
              </select>
              {updatingStatus && (
                <span className="text-indigo-100 text-sm">Salvando...</span>
              )}
            </div>
          </div>
        </div>

        <div className="p-6">
          {/* Informações do Pedido */}
          <div className="mb-6">
            <h2 className="text-lg font-bold text-gray-800 mb-3 border-b pb-2">Informações do Pedido</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <p className="text-gray-500">Data do Pedido</p>
                <p className="font-bold text-gray-900">
                  {new Date(order.created_at).toLocaleDateString('pt-BR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                  })}
                </p>
              </div>
              <div>
                <p className="text-gray-500">Status</p>
                <p className="font-bold text-gray-900">{getStatusLabel(order.status)}</p>
              </div>
            </div>
          </div>

          {/* Informações do Cliente */}
          <div className="mb-6">
            <h2 className="text-lg font-bold text-gray-800 mb-3 border-b pb-2">Informações do Cliente</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <p className="text-gray-500">Nome</p>
                <p className="font-bold text-gray-900">{order.customer.name}</p>
              </div>
              <div>
                <p className="text-gray-500">Email</p>
                <p className="font-bold text-gray-900">{order.customer.email}</p>
              </div>
              <div>
                <p className="text-gray-500">Telefone</p>
                <p className="font-bold text-gray-900">{order.customer.phone}</p>
              </div>
            </div>
          </div>

          {/* Itens do Pedido */}
          <div className="mb-6">
            <h2 className="text-lg font-bold text-gray-800 mb-3 border-b pb-2">Itens do Pedido</h2>
            <div className="space-y-4">
              {order.items.map((item) => {
                const imageUrl = getProductImage(item);
                return (
                  <div key={item.id} className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 p-4 bg-gray-50 rounded-lg">
                    <div className="flex items-center gap-4 flex-1">
                      {imageUrl ? (
                        <img
                          src={imageUrl}
                          alt={item.product_name}
                          className="w-20 h-20 object-cover rounded-md bg-gray-100 border border-gray-200"
                        />
                      ) : (
                        <div className="w-20 h-20 bg-gray-100 rounded-md flex items-center justify-center text-xs text-gray-400 border border-gray-200">
                          Sem foto
                        </div>
                      )}
                      <div className="flex-1">
                        <p className="font-medium text-gray-900">{item.product_name}</p>
                        <p className="text-sm text-gray-500 mt-1">
                          {/* Exibir atributos se existirem */}
                          {item.attributes && Object.keys(item.attributes).length > 0 ? (
                            <>
                              {Object.entries(item.attributes).map(([attrId, value]) => (
                                <span key={attrId} className="mr-2">
                                  {value}
                                </span>
                              ))}
                            </>
                          ) : (
                            <>
                              {item.size && `Tamanho: ${item.size} `}
                              {item.color && `Cor: ${item.color} `}
                            </>
                          )}
                          <span className="ml-1">Qtd: {item.quantity}</span>
                        </p>
                        <p className="text-sm text-gray-500 mt-1">
                          Unit: R$ {parseFloat(item.unit_price).toFixed(2).replace('.', ',')}
                        </p>
                      </div>
                    </div>
                    <div className="text-right">
                      <p className="font-bold text-gray-900 text-lg">
                        R$ {parseFloat(item.subtotal).toFixed(2).replace('.', ',')}
                      </p>
                    </div>
                  </div>
                );
              })}
            </div>
            <div className="mt-6 pt-4 border-t flex justify-between items-center">
              <span className="text-lg font-bold text-gray-900">Total do Pedido</span>
              <span className="text-2xl font-extrabold text-indigo-600">
                R$ {parseFloat(order.total_amount).toFixed(2).replace('.', ',')}
              </span>
            </div>
          </div>

          {/* Observações do Pedido */}
          {order.notes && (
            <div className="mb-6">
              <h2 className="text-lg font-bold text-gray-800 mb-3 border-b pb-2">Observações do Cliente</h2>
              <div className="bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
                <p className="text-gray-700 whitespace-pre-wrap">{order.notes}</p>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default OrderDetail;

