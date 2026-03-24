import { defineStore } from 'pinia'
import { getFcmToken, onForegroundMessage } from '../firebase'
import api from '../composables/useApi'
import { useRegistrosUsoStore } from './registrosUso'

export const useNotificacoesStore = defineStore('notificacoes', {
  state: () => ({
    permissao: typeof Notification !== 'undefined' ? Notification.permission : 'default',
    ativa: localStorage.getItem('notificacoes_ativa') === 'true',
    fcmToken: null,
    foregroundUnsub: null
  }),

  actions: {
    verificarPermissao() {
      if (typeof Notification !== 'undefined') {
        this.permissao = Notification.permission
        if (this.permissao !== 'granted') {
          this.ativa = false
          localStorage.setItem('notificacoes_ativa', 'false')
        }
      }
    },

    async ativar() {
      if (typeof Notification === 'undefined') {
        this.permissao = 'denied'
        return 'denied'
      }

      const result = await Notification.requestPermission()
      this.permissao = result

      if (result === 'granted') {
        this.ativa = true
        localStorage.setItem('notificacoes_ativa', 'true')
        await this.registrarToken()
        this.ouvirForeground()
      }

      return result
    },

    desativar() {
      this.ativa = false
      localStorage.setItem('notificacoes_ativa', 'false')
      if (this.fcmToken) {
        api.delete('/device-tokens', { data: { token: this.fcmToken } }).catch(() => {})
      }
      if (this.foregroundUnsub) {
        this.foregroundUnsub()
        this.foregroundUnsub = null
      }
    },

    async registrarToken() {
      try {
        const token = await getFcmToken()
        if (token) {
          this.fcmToken = token
          await api.post('/device-tokens', { token })
        }
      } catch (err) {
        console.error('Erro ao registrar token FCM:', err)
      }
    },

    ouvirForeground() {
      if (this.foregroundUnsub) return

      this.foregroundUnsub = onForegroundMessage((payload) => {
        const msgData = payload.notification || payload.data || {}
        const title = msgData.title
        const body = msgData.body || ''
        if (title && Notification.permission === 'granted') {
          new Notification(title, {
            body,
            icon: '/icons/icon-192x192.svg'
          })
        }

        // Se for notificação de medicamento, sinalizar para o modal de tomada
        const data = payload.data || {}
        if (data.medicamento_id) {
          const registrosUsoStore = useRegistrosUsoStore()
          registrosUsoStore.pendente = {
            medicamentoId:    Number(data.medicamento_id),
            nome:             data.medicamento_nome || title,
            exige_comprovacao: data.exige_comprovacao === 'true',
          }
        }
      })
    },

    async inicializar() {
      this.verificarPermissao()
      if (this.ativa && this.permissao === 'granted') {
        await this.registrarToken()
        this.ouvirForeground()
      }
    }
  }
})
