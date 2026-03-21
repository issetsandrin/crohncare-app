import { initializeApp } from 'firebase/app'
import { getMessaging, getToken, onMessage } from 'firebase/messaging'

const firebaseConfig = {
  apiKey: 'AIzaSyDy7_4YbNDfkcIaHDgBMHinLukZ4hviNkI',
  authDomain: 'chroncare-5bc8f.firebaseapp.com',
  projectId: 'chroncare-5bc8f',
  storageBucket: 'chroncare-5bc8f.firebasestorage.app',
  messagingSenderId: '647060476559',
  appId: '1:647060476559:web:f56a7c8f606a4ca34056fa',
  measurementId: 'G-CJ9PRQPV75'
}

const app = initializeApp(firebaseConfig)
const messaging = getMessaging(app)

const VAPID_KEY = 'BNA2LDTCzUiE9q-n0ObvpCy8_UoprgqVkCGaeQrOGhYNTXQOmg4uW9HwGE_XAq-Jnk_wRLbi6hNCvyEv8IUELXI'

export async function getFcmToken() {
  try {
    const token = await getToken(messaging, { vapidKey: VAPID_KEY })
    return token
  } catch (err) {
    console.error('Erro ao obter token FCM:', err)
    return null
  }
}

export function onForegroundMessage(callback) {
  return onMessage(messaging, (payload) => {
    callback(payload)
  })
}

export { messaging }
