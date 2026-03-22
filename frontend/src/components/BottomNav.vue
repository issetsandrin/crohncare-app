<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const items = [
  { path: '/', label: 'Início' },
  { path: '/medicamentos', label: 'Remédios' },
  { path: '/diario', label: 'Diário' },
  { path: '/consultas', label: 'Consultas' },
  { path: '/configuracoes', label: 'Config' }
]

const activeIndex = computed(() => {
  const idx = items.findIndex(i => i.path === route.path)
  return idx >= 0 ? idx : 0
})
</script>

<template>
  <nav class="bottom-nav">
    <div class="nav-indicator" :style="{ width: (100 / items.length) + '%', transform: `translateX(${activeIndex * 100}%)` }" />
    <router-link
      v-for="item in items"
      :key="item.path"
      :to="item.path"
      class="nav-item"
      :class="{ active: route.path === item.path }"
    >
      <!-- Início -->
      <svg v-if="item.path === '/'" class="nav-icon" width="22" height="22" viewBox="0 0 24 24" fill="none">
        <path d="M3 12l9-8 9 8M5 10v9a1 1 0 001 1h3v-5h6v5h3a1 1 0 001-1v-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <!-- Remédios -->
      <svg v-else-if="item.path === '/medicamentos'" class="nav-icon" width="22" height="22" viewBox="0 0 24 24" fill="none">
        <rect x="6" y="2" width="12" height="20" rx="6" stroke="currentColor" stroke-width="1.8"/>
        <line x1="6" y1="12" x2="18" y2="12" stroke="currentColor" stroke-width="1.8"/>
      </svg>
      <!-- Diário -->
      <svg v-else-if="item.path === '/diario'" class="nav-icon" width="22" height="22" viewBox="0 0 24 24" fill="none">
        <path d="M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1zM3 8h18M8 4v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M7 13h4M7 16h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
      </svg>
      <!-- Consultas -->
      <svg v-else-if="item.path === '/consultas'" class="nav-icon" width="22" height="22" viewBox="0 0 24 24" fill="none">
        <path d="M8 2v4m8-4v4m-9 4h10M5 6h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <!-- Config -->
      <svg v-else class="nav-icon" width="22" height="22" viewBox="0 0 24 24" fill="none">
        <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 8.6a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <span class="nav-label">{{ item.label }}</span>
    </router-link>
  </nav>
</template>

<style scoped>
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: 430px;
  display: flex;
  justify-content: space-around;
  align-items: center;
  background: #fff;
  border-top: 1px solid #e8e8e8;
  padding: 10px 0 0;
  padding-bottom: env(safe-area-inset-bottom, 8px);
  z-index: 100;
}

.nav-indicator {
  position: absolute;
  top: 0;
  left: 0;
  height: 2px;
  background: var(--verde-salvia);
  transition: transform 0.4s var(--ease-out-smooth);
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  padding: 6px 4px;
  border-radius: 8px;
  transition: color 0.3s var(--ease-smooth);
  color: var(--texto-light);
  text-decoration: none;
  flex: 1;
}

.nav-item.active {
  color: var(--verde-salvia);
}

.nav-icon {
  width: 22px;
  height: 22px;
}

.nav-label {
  font-family: var(--font-corpo);
  font-size: 10px;
  font-weight: 500;
}
</style>
