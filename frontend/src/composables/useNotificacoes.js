import { ref } from 'vue'

export function useNotificacoes() {
  const permissao = ref(typeof Notification !== 'undefined' ? Notification.permission : 'denied')

  async function requestPermission() {
    if (typeof Notification === 'undefined') {
      console.warn('Notificações não suportadas neste navegador.')
      return 'denied'
    }

    const result = await Notification.requestPermission()
    permissao.value = result
    return result
  }

  async function agendarLembrete(titulo, corpo, horario) {
    if (permissao.value !== 'granted') {
      console.warn('Permissão de notificação não concedida.')
      return
    }

    const agora = Date.now()
    const alvo = new Date(horario).getTime()
    const delay = alvo - agora

    if (delay <= 0) {
      console.warn('O horário do lembrete já passou.')
      return
    }

    const registration = await navigator.serviceWorker?.ready
    if (!registration) {
      console.warn('Service Worker não disponível.')
      return
    }

    setTimeout(async () => {
      try {
        await registration.showNotification(titulo, {
          body: corpo,
          icon: '/icons/icon-192x192.png',
          badge: '/icons/icon-192x192.png',
          vibrate: [200, 100, 200],
          tag: `lembrete-${Date.now()}`
        })
      } catch (err) {
        console.error('Erro ao exibir notificação:', err)
      }
    }, delay)
  }

  return {
    permissao,
    requestPermission,
    agendarLembrete
  }
}
