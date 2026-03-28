<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useAvisosStore } from '../stores/avisos'

const router = useRouter()
const authStore = useAuthStore()
const avisosStore = useAvisosStore()

const showDropdown = ref(false)
let pollingInterval = null

const userName = computed(() => authStore.user?.nome || 'Usuário')
const initials = computed(() => {
  const parts = userName.value.trim().split(' ')
  if (parts.length >= 2) return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  return parts[0].substring(0, 2).toUpperCase()
})
const naoLidosCount = computed(() => avisosStore.naoLidosCount)

function toggleDropdown() {
  showDropdown.value = !showDropdown.value
}

function abrirAvisos() {
  router.push('/avisos')
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

onMounted(() => {
  document.addEventListener('click', handleOutsideClick, true)
  avisosStore.fetchNaoLidos()
  pollingInterval = setInterval(() => avisosStore.fetchNaoLidos(), 30000)
})
onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick, true)
  if (pollingInterval) clearInterval(pollingInterval)
})
</script>

<template>
  <header class="desktop-header">
    <div class="desktop-brand">
      <img src="/icons/logo-branca.png" alt="ChronCare" class="brand-icon" />
      <span class="brand-name">ChronCare</span>
    </div>

    <div class="header-right">
      <!-- Bell de avisos -->
      <button class="bell-btn" :class="{ 'has-alerts': naoLidosCount > 0 }" @click="abrirAvisos">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" :class="{ 'bell-ring': naoLidosCount > 0 }">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span v-if="naoLidosCount > 0" class="bell-badge">{{ naoLidosCount }}</span>
      </button>

      <!-- Usuário -->
      <div class="header-user-area">
        <button class="user-trigger" @click.stop="toggleDropdown" :class="{ open: showDropdown }">
          <div class="user-avatar">{{ initials }}</div>
          <span class="user-name">{{ userName }}</span>
        </button>

        <Transition name="dropdown">
          <div v-if="showDropdown" class="user-dropdown">
            <button class="dropdown-logout" @click="handleLogout">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Sair da conta
            </button>
          </div>
        </Transition>
      </div>
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

.brand-icon {
  width: 36px;
  height: auto;
  object-fit: contain;
}

.brand-name {
  font-family: var(--font-titulo);
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  letter-spacing: -0.3px;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

/* Bell */
.bell-btn {
  position: relative;
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.15s;
}

.bell-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.bell-btn.has-alerts {
  background: rgba(255, 255, 255, 0.18);
  color: #fff;
}

.bell-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  min-width: 17px;
  height: 17px;
  border-radius: 9px;
  background: var(--terracota);
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
  border: 2px solid var(--verde-salvia);
}

@keyframes bell-ring {
  0%, 75%, 100% { transform: rotate(0); }
  78%  { transform: rotate(18deg); }
  82%  { transform: rotate(-15deg); }
  86%  { transform: rotate(12deg); }
  90%  { transform: rotate(-8deg); }
  93%  { transform: rotate(5deg); }
  96%  { transform: rotate(-2deg); }
}

.bell-ring {
  transform-origin: top center;
  animation: bell-ring 3.5s ease infinite;
}

/* Usuário */
.header-user-area {
  position: relative;
}

.user-trigger {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 5px 12px 5px 6px;
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

.user-avatar {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.25);
  color: #fff;
  font-family: var(--font-titulo);
  font-size: 11px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  letter-spacing: 0.5px;
  flex-shrink: 0;
}

.user-name {
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  line-height: 1;
}

.user-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background: var(--verde-bg);
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  overflow: hidden;
  min-width: 160px;
  z-index: 100;
}

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
