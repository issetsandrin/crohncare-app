import { precacheAndRoute } from 'workbox-precaching'
import { registerRoute } from 'workbox-routing'
import { StaleWhileRevalidate } from 'workbox-strategies'
import { ExpirationPlugin } from 'workbox-expiration'

// Precache dos assets gerados pelo Vite
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

// Firebase Cloud Messaging
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js')
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js')

firebase.initializeApp({
  apiKey: 'AIzaSyDy7_4YbNDfkcIaHDgBMHinLukZ4hviNkI',
  authDomain: 'chroncare-5bc8f.firebaseapp.com',
  projectId: 'chroncare-5bc8f',
  storageBucket: 'chroncare-5bc8f.firebasestorage.app',
  messagingSenderId: '647060476559',
  appId: '1:647060476559:web:f56a7c8f606a4ca34056fa'
})

const messaging = firebase.messaging()

messaging.onBackgroundMessage((payload) => {
  const { title, body } = payload.notification || {}
  if (!title) return

  self.registration.showNotification(title, {
    body: body || '',
    icon: '/icons/icon-192x192.png',
    badge: '/icons/icon-192x192.png',
    vibrate: [200, 100, 200, 100, 200],
    requireInteraction: true
  })
})
