import { precacheAndRoute, cleanupOutdatedCaches } from 'workbox-precaching'
import { registerRoute } from 'workbox-routing'
import { StaleWhileRevalidate } from 'workbox-strategies'
import { ExpirationPlugin } from 'workbox-expiration'

// Ativa o novo SW imediatamente
self.skipWaiting()
self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim())
})

// Limpa caches antigos e precache dos assets
cleanupOutdatedCaches()
precacheAndRoute(self.__WB_MANIFEST)

// Cache da API
registerRoute(
  ({ url }) => url.pathname.startsWith('/api/'),
  new StaleWhileRevalidate({
    cacheName: 'api-cache',
    plugins: [
      new ExpirationPlugin({
        maxEntries: 50,
        maxAgeSeconds: 60 * 60 * 24
      })
    ]
  })
)

// Push notifications - intercepta o evento push diretamente
self.addEventListener('push', (event) => {
  if (!event.data) return

  let data
  try {
    data = event.data.json()
  } catch {
    return
  }

  const notification = data.notification || {}
  const title = notification.title || data.data?.title || 'CrohnCare'
  const body = notification.body || data.data?.body || ''

  event.waitUntil(
    self.registration.showNotification(title, {
      body,
      icon: '/icons/icon-192x192.png',
      vibrate: [200, 100, 200, 100, 200],
      requireInteraction: true
    })
  )
})

// Ao clicar na notificação, abre o app
self.addEventListener('notificationclick', (event) => {
  event.notification.close()
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      for (const client of clientList) {
        if (client.url.includes('crohncare') && 'focus' in client) {
          return client.focus()
        }
      }
      return clients.openWindow('/')
    })
  )
})
