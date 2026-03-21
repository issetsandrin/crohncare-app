import { defineStore } from 'pinia'
import api from '../composables/useApi'

function mesAtualFormatado() {
  const now = new Date()
  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  return `${year}-${month}`
}

export const useDiarioStore = defineStore('diario', {
  state: () => ({
    entradas: [],
    mesAtual: mesAtualFormatado(),
    loading: false,
    tagsAnotacao: [],
    tagsCrise: []
  }),

  actions: {
    async fetchTags() {
      try {
        const [anotacao, crise] = await Promise.all([
          api.get('/tags', { params: { tipo: 'anotacao' } }),
          api.get('/tags', { params: { tipo: 'crise' } })
        ])
        this.tagsAnotacao = anotacao.data.data
        this.tagsCrise = crise.data.data
      } catch (err) {
        console.error('Erro ao buscar tags:', err)
      }
    },

    async fetchMes(mes) {
      this.loading = true
      if (mes) this.mesAtual = mes
      try {
        const res = await api.get('/diarios', { params: { mes: this.mesAtual } })
        this.entradas = res.data.data
      } catch (err) {
        console.error('Erro ao buscar entradas do diário:', err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async create(entrada) {
      try {
        const res = await api.post('/diarios', entrada)
        const item = typeof res.data.data === 'object' && res.data.data ? res.data.data : res.data
        this.entradas.unshift(item)
        this.fetchTags()
        return item
      } catch (err) {
        console.error('Erro ao criar entrada:', err)
        throw err
      }
    },

    async update(id, entrada) {
      try {
        const res = await api.put(`/diarios/${id}`, entrada)
        const item = typeof res.data.data === 'object' && res.data.data ? res.data.data : res.data
        const index = this.entradas.findIndex((e) => e.id === id)
        if (index !== -1) this.entradas[index] = item
        this.fetchTags()
        return item
      } catch (err) {
        console.error('Erro ao atualizar entrada:', err)
        throw err
      }
    },

    async remove(id) {
      try {
        await api.delete(`/diarios/${id}`)
        this.entradas = this.entradas.filter((e) => e.id !== id)
      } catch (err) {
        console.error('Erro ao remover entrada:', err)
        throw err
      }
    },

    async exportar(mes) {
      try {
        const res = await api.get('/diarios/exportar', {
          params: { mes: mes || this.mesAtual },
          responseType: 'blob'
        })
        return res.data
      } catch (err) {
        console.error('Erro ao exportar diário:', err)
        throw err
      }
    }
  }
})
