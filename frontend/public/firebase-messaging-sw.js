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
    icon: '/icons/icon-192x192.svg',
    badge: '/icons/icon-192x192.svg',
    vibrate: [200, 100, 200, 100, 200],
    requireInteraction: true
  })
})
