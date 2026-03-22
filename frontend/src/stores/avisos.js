import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useApi } from '../composables/useApi'

export const useAvisosStore = defineStore('avisos', () => {
  const api = useApi()
  const avisos = ref([])
  const loading = ref(false)

  const naoLidos = computed(() => avisos.value.filter(a => !a.lido).length)

  async function fetchAll() {
    loading.value = true
    try {
      const { data } = await api.get('/avisos')
      avisos.value = data
    } catch (e) {
      // silent
    } finally {
      loading.value = false
    }
  }

  async function fetchNaoLidos() {
    try {
      const { data } = await api.get('/avisos/nao-lidos')
      return data.count
    } catch (e) {
      return 0
    }
  }

  async function marcarLido(id) {
    try {
      await api.put(`/avisos/${id}/lido`)
      const aviso = avisos.value.find(a => a.id === id)
      if (aviso) aviso.lido = true
    } catch (e) {
      // silent
    }
  }

  async function marcarTodosLidos() {
    try {
      await api.put('/avisos/marcar-todos')
      avisos.value.forEach(a => a.lido = true)
    } catch (e) {
      // silent
    }
  }

  return { avisos, loading, naoLidos, fetchAll, fetchNaoLidos, marcarLido, marcarTodosLidos }
})
