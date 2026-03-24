import { defineStore } from 'pinia'
import api from '../composables/useApi'

export const useRegistrosUsoStore = defineStore('registrosUso', {
  state: () => ({
    pendente: null, // { medicamentoId, nome, exige_comprovacao }
    loading: false,
  }),

  actions: {
    async registrar(medicamentoId, foto = null) {
      this.loading = true
      try {
        const formData = new FormData()
        formData.append('medicamento_id', medicamentoId)
        if (foto) {
          formData.append('foto', foto)
        }
        await api.post('/registros-uso', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })
        this.pendente = null
      } finally {
        this.loading = false
      }
    },

    dispensar() {
      this.pendente = null
    },
  },
})
