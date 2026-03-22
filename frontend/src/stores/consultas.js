import { defineStore } from 'pinia'
import api from '../composables/useApi'

function unwrap(res) {
  const d = res.data
  return (d && typeof d.data === 'object' && d.data !== null) ? d.data : d
}

export const useConsultasStore = defineStore('consultas', {
  state: () => ({
    lista: [],
    loading: false
  }),

  getters: {
    proximas(state) {
      const agora = new Date().toISOString().slice(0, 16)
      return state.lista
        .filter(c => c.data_hora >= agora && c.status === 'agendada')
        .sort((a, b) => a.data_hora.localeCompare(b.data_hora))
    },
    passadas(state) {
      const agora = new Date().toISOString().slice(0, 16)
      return state.lista
        .filter(c => c.data_hora < agora || c.status !== 'agendada')
        .sort((a, b) => b.data_hora.localeCompare(a.data_hora))
    }
  },

  actions: {
    async fetchAll() {
      this.loading = true
      try {
        const res = await api.get('/consultas')
        this.lista = unwrap(res)
      } catch (err) {
        console.error('Erro ao buscar consultas:', err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async create(consulta) {
      try {
        const res = await api.post('/consultas', consulta)
        const item = unwrap(res)
        this.lista.push(item)
        return item
      } catch (err) {
        console.error('Erro ao criar consulta:', err)
        throw err
      }
    },

    async update(id, consulta) {
      try {
        const res = await api.put(`/consultas/${id}`, consulta)
        const item = unwrap(res)
        const idx = this.lista.findIndex(c => c.id === id)
        if (idx !== -1) this.lista[idx] = item
        return item
      } catch (err) {
        console.error('Erro ao atualizar consulta:', err)
        throw err
      }
    },

    async remove(id) {
      try {
        await api.delete(`/consultas/${id}`)
        this.lista = this.lista.filter(c => c.id !== id)
      } catch (err) {
        console.error('Erro ao remover consulta:', err)
        throw err
      }
    }
  }
})
