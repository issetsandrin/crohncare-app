import { defineStore } from 'pinia'
import api from '../composables/useApi'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    token: localStorage.getItem('token') || null,
    loading: false,
    errors: {}
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    userName: (state) => state.user?.nome || ''
  },

  actions: {
    setAuth(user, token) {
      this.user = user
      this.token = token
      localStorage.setItem('user', JSON.stringify(user))
      localStorage.setItem('token', token)
      api.defaults.headers.common['Authorization'] = `Bearer ${token}`
    },

    clearAuth() {
      this.user = null
      this.token = null
      this.errors = {}
      localStorage.removeItem('user')
      localStorage.removeItem('token')
      delete api.defaults.headers.common['Authorization']
    },

    initAuth() {
      if (this.token) {
        api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
      }
    },

    async login(email, password) {
      this.loading = true
      this.errors = {}
      try {
        const res = await api.post('/login', { email, password })
        this.setAuth(res.data.user, res.data.token)
        return true
      } catch (err) {
        if (err.response?.status === 422) {
          this.errors = err.response.data.errors || {}
        } else {
          this.errors = { email: ['Erro ao fazer login. Tente novamente.'] }
        }
        return false
      } finally {
        this.loading = false
      }
    },

    async register(nome, email, password, password_confirmation) {
      this.loading = true
      this.errors = {}
      try {
        const res = await api.post('/register', { nome, email, password, password_confirmation })
        this.setAuth(res.data.user, res.data.token)
        return true
      } catch (err) {
        if (err.response?.status === 422) {
          this.errors = err.response.data.errors || {}
        } else {
          this.errors = { email: ['Erro ao criar conta. Tente novamente.'] }
        }
        return false
      } finally {
        this.loading = false
      }
    },

    async updateProfile(data) {
      try {
        const res = await api.put('/me', data)
        this.user = res.data
        localStorage.setItem('user', JSON.stringify(res.data))
        return true
      } catch (err) {
        if (err.response?.status === 422) {
          this.errors = err.response.data.errors || {}
        }
        return false
      }
    },

    marcarOnboardingCompleto() {
      if (this.user) {
        this.user.onboarding_completo = true
        localStorage.setItem('user', JSON.stringify(this.user))
      }
    },

    async logout() {
      try {
        await api.post('/logout')
      } catch (e) {
        // ignore
      }
      this.clearAuth()
    }
  }
})
