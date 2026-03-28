<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useTour } from '../composables/useTour'
import { useBreakpoint } from '../composables/useBreakpoint'

const router = useRouter()
const auth = useAuthStore()
const { verificarTourInicial } = useTour()
const { isDesktop } = useBreakpoint()

const email = ref('')
const password = ref('')
const showPassword = ref(false)

async function handleLogin() {
  const success = await auth.login(email.value, password.value)
  if (success) {
    if (isDesktop.value) verificarTourInicial()
    router.push('/')
  }
}

function getError(field) {
  return auth.errors[field]?.[0] || ''
}
</script>

<template>
  <main class="login-page">
    <!-- Decorative top -->
    <div class="decor-top">
      <div class="decor-circle c1"></div>
      <div class="decor-circle c2"></div>
    </div>

    <div class="login-container">
      <!-- Logo + Welcome -->
      <div class="welcome">
        <div class="logo-wrap">
          <img src="/icons/favicon_logo.png" alt="CrohnCare" class="logo-img" />
        </div>
        <h1 class="welcome-title">CrohnCare</h1>
        <p class="welcome-text">Seu companheiro no cuidado com a saude intestinal</p>
      </div>

      <!-- Form card -->
      <div class="form-card">
        <form @submit.prevent="handleLogin">
          <div class="form-group">
            <label class="form-label">E-mail</label>
            <div class="input-wrap" :class="{ error: getError('email'), focused: false }">
              <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="1.8"/>
                <path d="M22 6l-10 7L2 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <input
                v-model="email"
                type="email"
                class="form-input"
                placeholder="seu@email.com"
                required
                autocomplete="email"
              />
            </div>
            <span v-if="getError('email')" class="form-error">{{ getError('email') }}</span>
          </div>

          <div class="form-group">
            <label class="form-label">Senha</label>
            <div class="input-wrap" :class="{ error: getError('password') }">
              <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none">
                <rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="1.8"/>
                <path d="M7 11V7a5 5 0 0110 0v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
              </svg>
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                class="form-input"
                placeholder="Sua senha"
                required
                autocomplete="current-password"
              />
              <button type="button" class="toggle-password" @click="showPassword = !showPassword">
                <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z" stroke="currentColor" stroke-width="1.8"/>
                  <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                </svg>
                <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                  <path d="M1 1l22 22" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
              </button>
            </div>
            <span v-if="getError('password')" class="form-error">{{ getError('password') }}</span>
          </div>

          <button type="submit" class="btn-login" :disabled="auth.loading">
            <span v-if="!auth.loading">Entrar</span>
            <span v-else class="btn-loading">
              <span class="btn-dot"></span>
              <span class="btn-dot"></span>
              <span class="btn-dot"></span>
            </span>
          </button>
        </form>
      </div>

      <!-- Footer -->
      <div class="login-footer">
        <span>Ainda nao tem conta?</span>
        <router-link to="/cadastro" class="link-cadastro">Criar conta</router-link>
      </div>
    </div>
  </main>
</template>

<style scoped>
.login-page {
  min-height: 100dvh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 24px 20px;
  position: relative;
  overflow: hidden;
}

/* Decorative circles */
.decor-top {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 260px;
  overflow: hidden;
  pointer-events: none;
}

.decor-circle {
  position: absolute;
  border-radius: 50%;
}

.decor-circle.c1 {
  width: 300px;
  height: 300px;
  top: -150px;
  right: -80px;
  background: radial-gradient(circle, rgba(127, 168, 50, 0.12) 0%, transparent 70%);
}

.decor-circle.c2 {
  width: 200px;
  height: 200px;
  top: -60px;
  left: -60px;
  background: radial-gradient(circle, rgba(127, 168, 50, 0.08) 0%, transparent 70%);
}

/* Container */
.login-container {
  width: 100%;
  max-width: 380px;
  position: relative;
  z-index: 1;
}

/* Welcome */
.welcome {
  text-align: center;
  margin-bottom: 32px;
}

.logo-wrap {
  display: flex;
  justify-content: center;
  margin-bottom: 16px;
}

.logo-img {
  width: 220px;
  height: 220px;
  border-radius: 48px;
  object-fit: cover;
  box-shadow: 0 8px 32px rgba(0,0,0,0.18);
}

.welcome-title {
  font-family: var(--font-titulo);
  font-size: 2rem;
  color: var(--verde-salvia);
  margin: 0 0 6px;
  letter-spacing: -0.5px;
}

.welcome-text {
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto-light);
  margin: 0;
  line-height: 1.4;
}

/* Form card */
.form-card {
  background: #fff;
  border-radius: 20px;
  padding: 28px 24px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
}

.form-group {
  margin-bottom: 16px;
}

.form-group:last-of-type {
  margin-bottom: 24px;
}

.form-label {
  display: block;
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: var(--texto);
  margin-bottom: 6px;
}

.input-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
  border: 1.5px solid #e0e0e0;
  border-radius: 12px;
  padding: 0 14px;
  background: #fafafa;
  transition: all 0.3s ease;
}

.input-wrap:focus-within {
  border-color: var(--verde-salvia);
  background: #fff;
  box-shadow: 0 0 0 3px rgba(127, 168, 50, 0.1);
}

.input-wrap.error {
  border-color: var(--terracota);
}

.input-wrap.error:focus-within {
  box-shadow: 0 0 0 3px rgba(196, 120, 74, 0.1);
}

.input-icon {
  color: var(--texto-light);
  flex-shrink: 0;
  opacity: 0.5;
}

.input-wrap:focus-within .input-icon {
  color: var(--verde-salvia);
  opacity: 1;
}

.form-input {
  flex: 1;
  border: none;
  background: transparent;
  padding: 14px 0;
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  outline: none;
  width: 100%;
}

.form-input::placeholder {
  color: #bbb;
}

.toggle-password {
  background: none;
  border: none;
  padding: 4px;
  cursor: pointer;
  color: var(--texto-light);
  opacity: 0.5;
  display: flex;
  align-items: center;
  transition: opacity 0.2s;
}

.toggle-password:hover {
  opacity: 0.8;
}

.form-error {
  display: block;
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--terracota);
  margin-top: 4px;
  padding-left: 2px;
}

/* Button */
.btn-login {
  width: 100%;
  padding: 15px;
  border: none;
  border-radius: 12px;
  background: var(--verde-salvia);
  color: #fff;
  font-family: var(--font-corpo);
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.btn-login:active:not(:disabled) {
  transform: scale(0.98);
}

.btn-login:disabled {
  opacity: 0.8;
  cursor: not-allowed;
}

.btn-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
}

.btn-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #fff;
  animation: btnBounce 1s ease infinite;
}

.btn-dot:nth-child(2) { animation-delay: 0.15s; }
.btn-dot:nth-child(3) { animation-delay: 0.3s; }

@keyframes btnBounce {
  0%, 80%, 100% { opacity: 0.4; transform: scale(0.8); }
  40% { opacity: 1; transform: scale(1); }
}

/* Footer */
.login-footer {
  text-align: center;
  margin-top: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.login-footer span {
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto-light);
}

.link-cadastro {
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 700;
  color: var(--verde-salvia);
  text-decoration: none;
  transition: opacity 0.2s;
}

.link-cadastro:active {
  opacity: 0.7;
}
</style>
