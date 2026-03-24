import { defineStore } from 'pinia'
import api from '../composables/useApi'

function unwrap(res) {
  const d = res.data
  return (d && typeof d.data === 'object' && d.data !== null) ? d.data : d
}

export const useExamesStore = defineStore('exames', {
  state: () => ({
    lista: [],
    loading: false
  }),

  getters: {
    proximos(state) {
      const agora = new Date().toISOString().slice(0, 16)
      return state.lista
        .filter(e => e.data >= agora && e.status === 'agendado')
        .sort((a, b) => a.data.localeCompare(b.data))
    },
    historico(state) {
      const agora = new Date().toISOString().slice(0, 16)
      return state.lista
        .filter(e => e.data < agora || e.status !== 'agendado')
        .sort((a, b) => b.data.localeCompare(a.data))
    }
  },

  actions: {
    async fetchAll() {
      this.loading = true
      try {
        const res = await api.get('/exames')
        this.lista = unwrap(res)
      } catch (err) {
        console.error('Erro ao buscar exames:', err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async create(exame) {
      try {
        const res = await api.post('/exames', exame)
        const item = unwrap(res)
        this.lista.push(item)
        return item
      } catch (err) {
        console.error('Erro ao criar exame:', err)
        throw err
      }
    },

    async update(id, exame) {
      try {
        const res = await api.put(`/exames/${id}`, exame)
        const item = unwrap(res)
        const idx = this.lista.findIndex(e => e.id === id)
        if (idx !== -1) this.lista[idx] = item
        return item
      } catch (err) {
        console.error('Erro ao atualizar exame:', err)
        throw err
      }
    }
  }
})
