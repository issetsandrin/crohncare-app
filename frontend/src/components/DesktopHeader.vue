<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const showDropdown = ref(false)

const userName = computed(() => authStore.user?.nome || 'Usuário')
const initials = computed(() => {
  const parts = userName.value.trim().split(' ')
  if (parts.length >= 2) return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  return parts[0].substring(0, 2).toUpperCase()
})

function toggleDropdown() {
  showDropdown.value = !showDropdown.value
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

    <div class="header-user-area">
      <button class="user-trigger" @click.stop="toggleDropdown" :class="{ open: showDropdown }">
        <span class="user-name">{{ userName }}</span>
        <div class="user-avatar">{{ initials }}</div>
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

.header-user-area {
  position: relative;
}

.user-trigger {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 5px 8px 5px 14px;
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

.user-name {
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: #fff;
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
