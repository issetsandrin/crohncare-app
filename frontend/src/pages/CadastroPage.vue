<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const nome = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')

async function handleRegister() {
  const success = await auth.register(
    nome.value,
    email.value,
    password.value,
    password_confirmation.value
  )
  if (success) {
    router.push('/')
  }
}

function getError(field) {
  return auth.errors[field]?.[0] || ''
}
</script>

<template>
  <main class="auth-page">
    <div class="auth-container">
      <div class="auth-header">
        <div class="auth-logo">
          <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
            <rect width="48" height="48" rx="12" fill="var(--verde-salvia)"/>
            <text x="24" y="30" font-family="Inter, sans-serif" font-size="16" fill="#fff" text-anchor="middle" font-weight="700">CC</text>
          </svg>
        </div>
        <h1 class="auth-title">CrohnCare</h1>
        <p class="auth-subtitle">Crie sua conta para começar</p>
      </div>

      <form class="auth-form" @submit.prevent="handleRegister">
        <div class="form-group">
          <label class="form-label">Nome</label>
          <input
            v-model="nome"
            type="text"
            class="form-input"
            :class="{ error: getError('nome') }"
            placeholder="Seu nome"
            required
            autocomplete="name"
          />
          <span v-if="getError('nome')" class="form-error">{{ getError('nome') }}</span>
        </div>

        <div class="form-group">
          <label class="form-label">E-mail</label>
          <input
            v-model="email"
            type="email"
            class="form-input"
            :class="{ error: getError('email') }"
            placeholder="seu@email.com"
            required
            autocomplete="email"
          />
          <span v-if="getError('email')" class="form-error">{{ getError('email') }}</span>
        </div>

        <div class="form-group">
          <label class="form-label">Senha</label>
          <input
            v-model="password"
            type="password"
            class="form-input"
            :class="{ error: getError('password') }"
            placeholder="Mínimo 6 caracteres"
            required
            autocomplete="new-password"
          />
          <span v-if="getError('password')" class="form-error">{{ getError('password') }}</span>
        </div>

        <div class="form-group">
          <label class="form-label">Confirmar Senha</label>
          <input
            v-model="password_confirmation"
            type="password"
            class="form-input"
            placeholder="Repita a senha"
            required
            autocomplete="new-password"
          />
        </div>

        <button type="submit" class="btn-submit" :disabled="auth.loading">
          {{ auth.loading ? 'Criando conta...' : 'Criar conta' }}
        </button>
      </form>

      <div class="auth-footer">
        <p>Já tem uma conta?</p>
        <router-link to="/login" class="auth-link">Entrar</router-link>
      </div>
    </div>
  </main>
</template>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px 16px;
}

.auth-container {
  width: 100%;
  max-width: 360px;
}

.auth-header {
  text-align: center;
  margin-bottom: 32px;
}

.auth-logo {
  display: flex;
  justify-content: center;
  margin-bottom: 16px;
}

.auth-title {
  font-family: var(--font-titulo);
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--verde-salvia);
  margin: 0 0 4px;
}

.auth-subtitle {
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto-light);
  margin: 0;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-label {
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: var(--texto);
}

.form-input {
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 14px;
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  background: #fff;
  transition: border-color 0.2s;
  width: 100%;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: var(--verde-salvia);
  box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.form-input.error {
  border-color: var(--terracota);
}

.form-error {
  font-size: 12px;
  color: var(--terracota);
  font-family: var(--font-corpo);
}

.btn-submit {
  background: var(--verde-salvia);
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 14px;
  font-family: var(--font-corpo);
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s, transform 0.1s;
  margin-top: 8px;
}

.btn-submit:hover:not(:disabled) {
  opacity: 0.9;
}

.btn-submit:active:not(:disabled) {
  transform: scale(0.98);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.auth-footer {
  text-align: center;
  margin-top: 24px;
  display: flex;
  gap: 6px;
  justify-content: center;
  align-items: center;
}

.auth-footer p {
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto-light);
  margin: 0;
}

.auth-link {
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  color: var(--verde-salvia);
  text-decoration: none;
}

.auth-link:hover {
  text-decoration: underline;
}
</style>
