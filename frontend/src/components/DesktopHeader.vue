<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useTheme, LISTA_TEMAS } from '../composables/useTheme'

const router = useRouter()
const authStore = useAuthStore()
const { getTema, setTema } = useTheme()

const showDropdown = ref(false)
const editNome = ref('')
const salvando = ref(false)
const temaAtual = ref(getTema())

const userName = computed(() => authStore.user?.nome || 'Usuário')
const userEmail = computed(() => authStore.user?.email || '')
const initials = computed(() => {
  const parts = userName.value.trim().split(' ')
  if (parts.length >= 2) return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  return parts[0].substring(0, 2).toUpperCase()
})

function toggleDropdown() {
  editNome.value = userName.value
  temaAtual.value = getTema()
  showDropdown.value = !showDropdown.value
}

async function handleSaveNome() {
  if (!editNome.value.trim() || salvando.value) return
  salvando.value = true
  try {
    await authStore.updateProfile({ nome: editNome.value.trim() })
  } finally {
    salvando.value = false
  }
}

function handleSelectTema(id) {
  temaAtual.value = id
  setTema(id)
}

async function handleLogout() {
  showDropdown.value = false
  await authStore.logout()
  router.push('/login')
}

function handleOutsideClick(e) {
  if (!e.target.closest('.header-user-area')) {
    showDropdown.value = false
  }
}

onMounted(() => document.addEventListener('click', handleOutsideClick, true))
onUnmounted(() => document.removeEventListener('click', handleOutsideClick, true))
</script>

<template>
  <header class="desktop-header">
    <div class="desktop-brand">
      <svg class="brand-icon" width="26" height="26" viewBox="0 0 24 24" fill="none">
        <path d="M12 21C12 21 3 14.5 3 8.5C3 6.01 5.01 4 7.5 4C9 4 10.35 4.75 11.25 5.9C11.63 6.39 12.37 6.39 12.75 5.9C13.65 4.75 15 4 16.5 4C18.99 4 21 6.01 21 8.5C21 14.5 12 21 12 21Z" fill="white" opacity="0.9"/>
      </svg>
      <span class="brand-name">ChronCare</span>
    </div>

    <!-- Área do usuário com dropdown -->
    <div class="header-user-area">
      <button class="user-trigger" @click.stop="toggleDropdown" :class="{ open: showDropdown }">
        <div class="user-info">
          <span class="user-name">{{ userName }}</span>
          <span class="user-role">Paciente</span>
        </div>
        <div class="user-avatar">{{ initials }}</div>
        <svg class="trigger-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none">
          <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>

      <!-- Dropdown -->
      <Transition name="dropdown">
        <div v-if="showDropdown" class="user-dropdown">
          <!-- Cabeçalho do perfil -->
          <div class="dropdown-header">
            <div class="dropdown-avatar">{{ initials }}</div>
            <div class="dropdown-user-info">
              <span class="dropdown-name">{{ userName }}</span>
              <span class="dropdown-email">{{ userEmail }}</span>
            </div>
          </div>

          <div class="dropdown-sep" />

          <!-- Editar nome -->
          <div class="dropdown-section">
            <span class="dropdown-section-label">Nome de exibição</span>
            <div class="dropdown-nome-form">
              <input
                v-model="editNome"
                type="text"
                class="dropdown-input"
                placeholder="Seu nome"
                @keydown.enter="handleSaveNome"
              />
              <button
                class="dropdown-save-btn"
                :disabled="salvando || !editNome.trim()"
                @click="handleSaveNome"
              >
                {{ salvando ? '...' : 'Salvar' }}
              </button>
            </div>
          </div>

          <div class="dropdown-sep" />

          <!-- Seletor de tema -->
          <div class="dropdown-section">
            <span class="dropdown-section-label">Tema de cores</span>
            <div class="dropdown-swatches">
              <button
                v-for="t in LISTA_TEMAS"
                :key="t.id"
                class="dropdown-swatch"
                :class="{ active: temaAtual === t.id }"
                :style="{ '--cor': t.cor }"
                :aria-label="t.label"
                @click="handleSelectTema(t.id)"
              >
                <span v-if="temaAtual === t.id" class="swatch-check">
                  <svg width="10" height="10" viewBox="0 0 12 12" fill="none">
                    <path d="M2 6l3 3 5-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </span>
              </button>
            </div>
          </div>

          <div class="dropdown-sep" />

          <!-- Logout -->
          <button class="dropdown-logout" @click="handleLogout">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
              <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Sair da conta
          </button>
        </div>
      </Transition>
    </div>
  </header>
</template>

<style scoped>
.desktop-header {
  width: 100%;
  height: 58px;
  background: var(--verde-salvia);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 28px;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
  z-index: 20;
  position: relative;
}

.desktop-brand {
  display: flex;
  align-items: center;
  gap: 10px;
}

.brand-name {
  font-family: var(--font-titulo);
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  letter-spacing: -0.3px;
}

/* ── Área do usuário ── */
.header-user-area {
  position: relative;
}

.user-trigger {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 5px 10px 5px 12px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.2);
  cursor: pointer;
  transition: background 0.15s;
}

.user-trigger:hover,
.user-trigger.open {
  background: rgba(255, 255, 255, 0.2);
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 1px;
}

.user-name {
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  line-height: 1;
}

.user-role {
  font-family: var(--font-corpo);
  font-size: 11px;
  color: rgba(255, 255, 255, 0.65);
  line-height: 1;
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 9px;
  background: rgba(255, 255, 255, 0.25);
  color: #fff;
  font-family: var(--font-titulo);
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  letter-spacing: 0.5px;
}

.trigger-chevron {
  color: rgba(255, 255, 255, 0.7);
  transition: transform 0.2s;
  flex-shrink: 0;
}

.user-trigger.open .trigger-chevron {
  transform: rotate(180deg);
}

/* ── Dropdown ── */
.user-dropdown {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: 280px;
  background: var(--verde-bg);
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 14px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.14);
  padding: 6px 0;
  z-index: 100;
  overflow: hidden;
}

.dropdown-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
}

.dropdown-avatar {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: var(--verde-salvia);
  color: #fff;
  font-family: var(--font-titulo);
  font-size: 15px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.dropdown-user-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.dropdown-name {
  font-family: var(--font-titulo);
  font-size: 14px;
  font-weight: 700;
  color: var(--texto);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-email {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-sep {
  height: 1px;
  background: rgba(0, 0, 0, 0.07);
  margin: 4px 0;
}

.dropdown-section {
  padding: 10px 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.dropdown-section-label {
  font-family: var(--font-corpo);
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  color: var(--texto-light);
  opacity: 0.7;
}

/* Editar nome */
.dropdown-nome-form {
  display: flex;
  gap: 8px;
}

.dropdown-input {
  flex: 1;
  padding: 8px 10px;
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 8px;
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--texto);
  background: rgba(255, 255, 255, 0.5);
  outline: none;
  transition: border-color 0.15s;
}

.dropdown-input:focus {
  border-color: var(--verde-salvia);
}

.dropdown-save-btn {
  padding: 8px 12px;
  background: var(--verde-salvia);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-family: var(--font-corpo);
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  flex-shrink: 0;
  transition: background 0.15s;
}

.dropdown-save-btn:hover:not(:disabled) {
  background: var(--verde-claro);
}

.dropdown-save-btn:disabled {
  opacity: 0.5;
  cursor: default;
}

/* Swatches de tema */
.dropdown-swatches {
  display: flex;
  gap: 10px;
}

.dropdown-swatch {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  background: var(--cor);
  border: 2px solid transparent;
  cursor: pointer;
  transition: transform 0.15s, border-color 0.15s;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  flex-shrink: 0;
}

.dropdown-swatch:hover {
  transform: scale(1.15);
}

.dropdown-swatch.active {
  border-color: rgba(0, 0, 0, 0.25);
  transform: scale(1.1);
}

.swatch-check {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Logout */
.dropdown-logout {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  border: none;
  background: none;
  cursor: pointer;
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 500;
  color: var(--terracota);
  text-align: left;
  transition: background 0.15s;
}

.dropdown-logout:hover {
  background: rgba(229, 115, 115, 0.08);
}

/* Animação do dropdown */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.15s, transform 0.15s;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-6px) scale(0.97);
}
</style>
