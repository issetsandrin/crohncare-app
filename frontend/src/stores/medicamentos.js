import { defineStore } from 'pinia'
import api from '../composables/useApi'

function unwrap(res) {
  const d = res.data
  return (d && typeof d.data === 'object' && d.data !== null) ? d.data : d
}

export const useMedicamentosStore = defineStore('medicamentos', {
  state: () => ({
    lista: [],
    loading: false
  }),

  getters: {
    comAlerta(state) {
      return state.lista.filter((med) => {
        return med.dias_restantes !== null && med.dias_restantes <= 7
      })
    },

    proximosHorarios(state) {
      const agora = new Date()
      const horaAtual = agora.getHours() * 60 + agora.getMinutes()

      const horarios = []

      state.lista.forEach((med) => {
        if (!med.ativo || !med.horarios || med.periodicidade_tipo === 'sob_demanda') return

        let diaCiclo = null
        let totalCiclo = null
        if (med.periodicidade_tipo === 'ciclo') {
          const ciclo = med.periodicidade_valor?.ciclo
          if (ciclo?.length) {
            const inicio = new Date(med.created_at)
            inicio.setHours(0, 0, 0, 0)
            const hoje = new Date()
            hoje.setHours(0, 0, 0, 0)
            const diasDecorridos = Math.floor((hoje - inicio) / (1000 * 60 * 60 * 24))
            diaCiclo = (diasDecorridos % ciclo.length) + 1
            totalCiclo = ciclo.length
          }
        }

        med.horarios.forEach((h) => {
          const partes = h.substring(0, 5)
          const [hh, mm] = partes.split(':').map(Number)
          const minutos = hh * 60 + mm
          horarios.push({
            medicamentoId: med.id,
            nome: med.nome,
            dose: med.dose_hoje || med.dose,
            horario: partes,
            minutos,
            passado: minutos < horaAtual,
            periodicidade_tipo: med.periodicidade_tipo,
            diaCiclo,
            totalCiclo
          })
        })
      })

      horarios.sort((a, b) => a.minutos - b.minutos)

      const proximos = horarios.filter((h) => !h.passado)
      const passados = horarios.filter((h) => h.passado)

      return [...proximos, ...passados]
    }
  },

  actions: {
    async fetchAll() {
      this.loading = true
      try {
        const res = await api.get('/medicamentos')
        this.lista = unwrap(res)
      } catch (err) {
        console.error('Erro ao buscar medicamentos:', err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async create(medicamento) {
      try {
        const res = await api.post('/medicamentos', medicamento)
        const item = unwrap(res)
        this.lista.push(item)
        return item
      } catch (err) {
        console.error('Erro ao criar medicamento:', err)
        throw err
      }
    },

    async update(id, medicamento) {
      try {
        const res = await api.put(`/medicamentos/${id}`, medicamento)
        const item = unwrap(res)
        const index = this.lista.findIndex((m) => m.id === id)
        if (index !== -1) this.lista[index] = item
        return item
      } catch (err) {
        console.error('Erro ao atualizar medicamento:', err)
        throw err
      }
    },

    async remove(id) {
      try {
        await api.delete(`/medicamentos/${id}`)
        this.lista = this.lista.filter((m) => m.id !== id)
      } catch (err) {
        console.error('Erro ao remover medicamento:', err)
        throw err
      }
    },

    async updateEstoque(id, dados) {
      try {
        const res = await api.put(`/medicamentos/${id}/estoque`, dados)
        const index = this.lista.findIndex((m) => m.id === id)
        if (index !== -1) this.lista[index] = unwrap(res)
        return unwrap(res)
      } catch (err) {
        console.error('Erro ao atualizar estoque:', err)
        throw err
      }
    },

    async reabastecer(id, quantidade) {
      try {
        const res = await api.post(`/medicamentos/${id}/reabastecer`, { quantidade })
        const index = this.lista.findIndex((m) => m.id === id)
        if (index !== -1) this.lista[index] = unwrap(res)
        return unwrap(res)
      } catch (err) {
        console.error('Erro ao reabastecer medicamento:', err)
        throw err
      }
    }
  }
})
