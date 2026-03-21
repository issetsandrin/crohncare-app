import { defineStore } from 'pinia'
import api from '../composables/useApi'
import { useDiarioStore } from './diario'

export const useCrisesStore = defineStore('crises', {
  state: () => ({
    lista: [],
    loading: false
  }),

  actions: {
    async fetchAll() {
      this.loading = true
      try {
        const res = await api.get('/crises')
        this.lista = res.data.data
      } catch (err) {
        console.error('Erro ao buscar crises:', err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async create(crise) {
      try {
        const res = await api.post('/crises', crise)
        const item = typeof res.data.data === 'object' && res.data.data ? res.data.data : res.data
        this.lista.unshift(item)
        useDiarioStore().fetchTags()
        return item
      } catch (err) {
        console.error('Erro ao criar crise:', err)
        throw err
      }
    },

    async update(id, crise) {
      try {
        const res = await api.put(`/crises/${id}`, crise)
        const item = typeof res.data.data === 'object' && res.data.data ? res.data.data : res.data
        const index = this.lista.findIndex((c) => c.id === id)
        if (index !== -1) this.lista[index] = item
        useDiarioStore().fetchTags()
        return item
      } catch (err) {
        console.error('Erro ao atualizar crise:', err)
        throw err
      }
    },

    async remove(id) {
      try {
        await api.delete(`/crises/${id}`)
        this.lista = this.lista.filter((c) => c.id !== id)
      } catch (err) {
        console.error('Erro ao remover crise:', err)
        throw err
      }
    }
  }
})
