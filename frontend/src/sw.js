// Service Worker para PWA com suporte a Push Notifications (futuro)
// Este arquivo será processado pelo vite-plugin-pwa

// Import workbox precaching
import { precacheAndRoute } from 'workbox-precaching';

// Precaching automático de assets do Vite
precacheAndRoute(self.__WB_MANIFEST || []);

// Preparação para Push Notifications (futuro)
self.addEventListener('push', (event) => {
  console.log('[Service Worker] Push notification received');
  
  const data = event.data ? event.data.json() : { title: 'VendaPop', body: 'Nova notificação' };
  
  const options = {
    body: data.body || 'Nova notificação',
    icon: data.icon || '/icon-192x192.png',
    badge: data.badge || '/icon-192x192.png',
    vibrate: [200, 100, 200],
    tag: self.location.hostname + '-notification',
    requireInteraction: false,
    data: data.url || '/'
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'VendaPop', options)
  );
});

// Clique em notificação
self.addEventListener('notificationclick', (event) => {
  console.log('[Service Worker] Notification clicked');
  event.notification.close();

  const urlToOpen = event.notification.data || '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      // Se já existe uma janela aberta, focar nela
      for (const client of clientList) {
        if (client.url === urlToOpen && 'focus' in client) {
          return client.focus();
        }
      }
      // Caso contrário, abrir nova janela
      if (clients.openWindow) {
        return clients.openWindow(urlToOpen);
      }
    })
  );
});
