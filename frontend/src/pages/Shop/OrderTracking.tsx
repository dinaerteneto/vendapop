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
  product?: {
    main_image_url: string | null;
  };
}

interface Order {
  id: number;
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

const OrderTracking: React.FC = () => {
  const { storeSlug, orderUuid } = useParams();
  const [order, setOrder] = useState<Order | null>(null);
  const [loading, setLoading] = useState(true);
  const [loadingWhatsApp, setLoadingWhatsApp] = useState(false);
  const [isSubscribed, setIsSubscribed] = useState(false);
  const [subscribing, setSubscribing] = useState(false);
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

    // Check if already subscribed
    if ('serviceWorker' in navigator && 'PushManager' in window) {
      navigator.serviceWorker.ready.then(registration => {
        registration.pushManager.getSubscription().then(subscription => {
          setIsSubscribed(!!subscription);
        });
      });
    }
  }, [storeSlug, orderUuid]);

  const requestNotificationPermission = async () => {
    if (!('Notification' in window)) {
      alert('Seu navegador não suporta notificações.');
      return;
    }

    if (Notification.permission === 'denied') {
      alert('As notificações foram bloqueadas. Por favor, permita notificações nas configurações do navegador.');
      return;
    }

    if (Notification.permission === 'granted') {
      await subscribeToPush();
      return;
    }

    const permission = await Notification.requestPermission();
    if (permission === 'granted') {
      await subscribeToPush();
    } else {
      alert('Permissão de notificações negada.');
    }
  };

  const subscribeToPush = async () => {
    if (!storeSlug || !orderUuid) return;

    try {
      setSubscribing(true);

      // Get VAPID public key from environment or use a default
      const vapidPublicKey = import.meta.env.VITE_VAPID_PUBLIC_KEY || '';

      if (!vapidPublicKey) {
        console.warn('VAPID_PUBLIC_KEY não configurada');
        alert('Configuração de notificações não disponível.');
        setSubscribing(false);
        return;
      }

      const registration = await navigator.serviceWorker.ready;
      const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey) as BufferSource,
      });

      // Send subscription to backend
      await api.post(`/${storeSlug}/order/${orderUuid}/push-subscriptions`, {
        endpoint: subscription.endpoint,
        keys: {
          p256dh: arrayBufferToBase64(subscription.getKey('p256dh')!),
          auth: arrayBufferToBase64(subscription.getKey('auth')!),
        },
      });

      setIsSubscribed(true);
      alert('Notificações ativadas! Você receberá atualizações sobre seu pedido.');
    } catch (err: any) {
      console.error('Erro ao inscrever-se em push notifications:', err);
      alert('Erro ao ativar notificações. Tente novamente.');
    } finally {
      setSubscribing(false);
    }
  };

  // Helper functions
  const urlBase64ToUint8Array = (base64String: string): Uint8Array => {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  };

  const arrayBufferToBase64 = (buffer: ArrayBuffer): string => {
    const bytes = new Uint8Array(buffer);
    let binary = '';
    for (let i = 0; i < bytes.byteLength; i++) {
      binary += String.fromCharCode(bytes[i]);
    }
    return window.btoa(binary);
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

  const getStatusColor = (status: string) => {
    const statusUpper = status.toUpperCase();
    if (statusUpper === 'NEW') return 'text-yellow-600';
    if (statusUpper === 'PREPARING') return 'text-blue-600';
    if (statusUpper === 'SENT') return 'text-purple-600';
    if (statusUpper === 'DONE') return 'text-green-600';
    if (statusUpper === 'CANCELED') return 'text-red-600';
    return 'text-gray-600';
  };

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
              <p className={`font-bold ${getStatusColor(order.status)}`}>{getStatusLabel(order.status)}</p>
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
                <div className="flex items-center gap-3">
                    {item.product?.main_image_url ? (
                        <img 
                            src={item.product.main_image_url} 
                            alt={item.product_name} 
                            className="w-16 h-16 object-cover rounded-md bg-gray-100 border border-gray-200" 
                        />
                    ) : (
                        <div className="w-16 h-16 bg-gray-100 rounded-md flex items-center justify-center text-xs text-gray-400 border border-gray-200">
                            Sem foto
                        </div>
                    )}
                    <div>
                        <p className="font-medium text-gray-900">{item.product_name}</p>
                        <p className="text-xs text-gray-500">
                            {item.size && `Tam: ${item.size} `} 
                            {item.color && `Cor: ${item.color} `}
                            x{item.quantity}
                        </p>
                    </div>
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

        {order.notes && (
          <div className="mb-6">
            <h2 className="text-lg font-bold text-gray-800 mb-3 border-b pb-2">Observações</h2>
            <div className="bg-gray-50 p-4 rounded-lg">
              <p className="text-gray-700 whitespace-pre-wrap">{order.notes}</p>
            </div>
          </div>
        )}

        {/* Notificações Push */}
        {('serviceWorker' in navigator && 'PushManager' in window) && (
          <div className="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="font-bold text-gray-800 mb-1">Receba atualizações do pedido</h3>
                <p className="text-sm text-gray-600">
                  {isSubscribed 
                    ? '✅ Você receberá notificações quando o pedido for enviado ou concluído.'
                    : 'Ative as notificações para ser avisado quando seu pedido for enviado ou concluído.'}
                </p>
              </div>
              {!isSubscribed && (
                <button
                  onClick={requestNotificationPermission}
                  disabled={subscribing}
                  className="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap"
                >
                  {subscribing ? 'Ativando...' : 'Ativar Notificações'}
                </button>
              )}
            </div>
          </div>
        )}

        <div className="flex flex-col gap-4 mt-8">
          <button
            onClick={async () => {
              if (!storeSlug || !orderUuid) return;
              
              setLoadingWhatsApp(true);
              try {
                const response = await api.get(`/${storeSlug}/order/${orderUuid}/whatsapp`);
                window.open(response.data.whatsapp_link, '_blank', 'noopener,noreferrer');
                setLoadingWhatsApp(false);
              } catch (err) {
                console.error(err);
                alert('Erro ao abrir WhatsApp. Tente novamente.');
                setLoadingWhatsApp(false);
              }
            }}
            disabled={loadingWhatsApp}
            className="w-full bg-green-600 hover:bg-green-700 text-white py-4 px-6 rounded-lg font-bold transition-all hover:scale-105 shadow-md flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg viewBox="0 0 24 24" fill="currentColor" className="w-6 h-6">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
            {loadingWhatsApp ? 'Abrindo WhatsApp...' : 'Acelere seu pedido'}
          </button>
          
          <Link 
            to={`/${storeSlug}`} 
            className="inline-block text-center px-8 py-3 rounded-lg font-bold text-white transition-transform hover:scale-105 shadow-md"
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

