<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()

const userName = computed(() => authStore.user?.nome || 'Usuário')
const initials = computed(() => {
  const parts = userName.value.trim().split(' ')
  if (parts.length >= 2) return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  return parts[0].substring(0, 2).toUpperCase()
})
</script>

<template>
  <header class="desktop-header">
    <div class="desktop-brand">
      <svg class="brand-icon" width="26" height="26" viewBox="0 0 24 24" fill="none">
        <path d="M12 21C12 21 3 14.5 3 8.5C3 6.01 5.01 4 7.5 4C9 4 10.35 4.75 11.25 5.9C11.63 6.39 12.37 6.39 12.75 5.9C13.65 4.75 15 4 16.5 4C18.99 4 21 6.01 21 8.5C21 14.5 12 21 12 21Z" fill="white" opacity="0.9"/>
      </svg>
      <span class="brand-name">ChronCare</span>
    </div>

    <div class="header-user">
      <div class="user-info">
        <span class="user-name">{{ userName }}</span>
        <span class="user-role">Paciente</span>
      </div>
      <div class="user-avatar">{{ initials }}</div>
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

.header-user {
  display: flex;
  align-items: center;
  gap: 10px;
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
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
  font-family: var(--font-titulo);
  font-size: 13px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  letter-spacing: 0.5px;
}
</style>
