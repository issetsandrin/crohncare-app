<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSidebar } from '../composables/useSidebar'


const route = useRoute()
const router = useRouter()
const { collapsed } = useSidebar()

const items = [
  { path: '/', label: 'Início' },
  { path: '/medicamentos', label: 'Remédios' },
  { path: '/diario', label: 'Diário' },
  { path: '/consultas', label: 'Consultas' },
  { path: '/perfil', label: 'Perfil' },
]

const clickKeys = ref({})

function navTo(path) {
  clickKeys.value[path] = (clickKeys.value[path] || 0) + 1
  if (route.path === path) {
    router.replace({ path, query: { _r: Date.now() } })
  } else {
    router.push(path)
  }
}
</script>

<template>
  <aside class="sidebar-nav" :class="{ collapsed }">

    <nav class="sidebar-items">
      <span class="sidebar-section-label">Menu</span>
      <button
        v-for="item in items"
        :key="item.path"
        class="sidebar-item"
        :class="{ active: route.path === item.path }"
        :title="collapsed ? item.label : ''"
        @click="navTo(item.path)"
      >
        <div class="sidebar-active-bar" v-if="route.path === item.path" />

        <!-- Início -->
        <svg
          v-if="item.path === '/'"
          :key="'home-' + (clickKeys[item.path] || 0)"
          class="sidebar-icon"
          :class="{ 'anim-home': route.path === item.path }"
          width="20" height="20" viewBox="0 0 24 24" fill="none"
        >
          <path d="M3 12l9-8 9 8M5 10v9a1 1 0 001 1h3v-5h6v5h3a1 1 0 001-1v-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>

        <!-- Remédios -->
        <svg
          v-else-if="item.path === '/medicamentos'"
          :key="'med-' + (clickKeys[item.path] || 0)"
          class="sidebar-icon"
          :class="{ 'anim-pill': route.path === item.path }"
          width="20" height="20" viewBox="0 0 24 24" fill="none"
        >
          <rect x="6" y="2" width="12" height="20" rx="6" stroke="currentColor" stroke-width="1.8"/>
          <line x1="6" y1="12" x2="18" y2="12" stroke="currentColor" stroke-width="1.8"/>
        </svg>

        <!-- Diário -->
        <svg
          v-else-if="item.path === '/diario'"
          :key="'diario-' + (clickKeys[item.path] || 0)"
          class="sidebar-icon"
          :class="{ 'anim-diario': route.path === item.path }"
          width="20" height="20" viewBox="0 0 24 24" fill="none"
        >
          <path d="M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1zM3 8h18M8 4v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M7 13h4M7 16h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>

        <!-- Consultas -->
        <svg
          v-else-if="item.path === '/consultas'"
          :key="'consult-' + (clickKeys[item.path] || 0)"
          class="sidebar-icon"
          :class="{ 'anim-consult': route.path === item.path }"
          width="20" height="20" viewBox="0 0 24 24" fill="none"
        >
          <rect x="3" y="6" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
          <path d="M3 10h18" stroke="currentColor" stroke-width="1.8"/>
          <path d="M8 2v4M16 2v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>

        <!-- Perfil -->
        <svg
          v-else
          :key="'perfil-' + (clickKeys[item.path] || 0)"
          class="sidebar-icon"
          :class="{ 'anim-perfil': route.path === item.path }"
          width="20" height="20" viewBox="0 0 24 24" fill="none"
        >
          <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/>
        </svg>

        <span class="sidebar-label">{{ item.label }}</span>
      </button>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-footer-info">
        <span class="sidebar-footer-text">Desenvolvido por</span>
        <span class="sidebar-footer-author">Vitor Sandrin</span>
        <span class="sidebar-footer-version">CrohnCare v1.0.0</span>
      </div>
    </div>
  </aside>
</template>

<style scoped>
.sidebar-nav {
  width: 230px;
  min-width: 230px;
  background: #fff;
  border-right: 1px solid rgba(0, 0, 0, 0.07);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: width 0.25s ease, min-width 0.25s ease;
  flex-shrink: 0;
}

.sidebar-nav.collapsed {
  width: 70px;
  min-width: 70px;
}

.sidebar-items {
  display: flex;
  flex-direction: column;
  padding: 16px 8px;
  flex: 1;
  gap: 2px;
}

.sidebar-section-label {
  font-family: var(--font-corpo);
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: var(--texto-light);
  opacity: 0.5;
  padding: 0 10px;
  margin-bottom: 6px;
  white-space: nowrap;
  overflow: hidden;
  transition: opacity 0.15s ease;
}

.sidebar-nav.collapsed .sidebar-section-label {
  opacity: 0;
}

.sidebar-item {
  position: relative;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 10px;
  border-radius: 10px;
  transition: background 0.15s var(--ease-smooth), color 0.15s var(--ease-smooth);
  color: var(--texto-light);
  background: none;
  border: none;
  cursor: pointer;
  width: 100%;
  text-align: left;
  white-space: nowrap;
  overflow: hidden;
}

.sidebar-item:hover {
  background: rgba(127, 168, 50, 0.08);
  color: var(--verde-salvia);
}

.sidebar-item.active {
  background: rgba(127, 168, 50, 0.1);
  color: var(--verde-salvia);
}

.sidebar-active-bar {
  display: none;
}

.sidebar-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.sidebar-label {
  font-family: var(--font-corpo);
  font-size: 13.5px;
  font-weight: 500;
  opacity: 1;
  transition: opacity 0.15s ease;
}

.sidebar-nav.collapsed .sidebar-label {
  opacity: 0;
  pointer-events: none;
}

/* Footer */
.sidebar-footer {
  padding: 12px 8px 16px;
  border-top: 1px solid rgba(0, 0, 0, 0.07);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.sidebar-footer-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  overflow: hidden;
  opacity: 1;
  transition: opacity 0.15s ease;
}

.sidebar-nav.collapsed .sidebar-footer-info {
  opacity: 0;
  pointer-events: none;
}

.sidebar-footer-text {
  font-family: var(--font-corpo);
  font-size: 10px;
  color: var(--texto-light);
  opacity: 0.5;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
}

.sidebar-footer-author {
  font-family: var(--font-corpo);
  font-size: 12px;
  font-weight: 600;
  color: var(--texto-light);
  white-space: nowrap;
}

.sidebar-footer-version {
  font-family: var(--font-corpo);
  font-size: 10px;
  color: var(--texto-light);
  opacity: 0.4;
  margin-top: 2px;
  white-space: nowrap;
}

/* ── Animações ── */
@keyframes bounce-home {
  0%   { transform: translateY(0) scale(1); }
  30%  { transform: translateY(-5px) scale(1.12); }
  55%  { transform: translateY(2px) scale(0.94); }
  75%  { transform: translateY(-2px) scale(1.04); }
  100% { transform: translateY(0) scale(1); }
}

@keyframes wiggle-pill {
  0%   { transform: rotate(0deg); }
  15%  { transform: rotate(-18deg); }
  40%  { transform: rotate(18deg); }
  65%  { transform: rotate(-10deg); }
  82%  { transform: rotate(6deg); }
  100% { transform: rotate(0deg); }
}

@keyframes flip-diario {
  0%   { transform: scaleY(1); }
  25%  { transform: scaleY(0.6) scaleX(1.1); }
  50%  { transform: scaleY(1.15) scaleX(0.95); }
  75%  { transform: scaleY(0.92); }
  100% { transform: scaleY(1) scaleX(1); }
}

@keyframes pulse-consult {
  0%   { transform: scale(1); }
  20%  { transform: scale(1.22); }
  45%  { transform: scale(0.88); }
  70%  { transform: scale(1.1); }
  100% { transform: scale(1); }
}

@keyframes pop-perfil {
  0%   { transform: scale(1) translateY(0); }
  35%  { transform: scale(1.2) translateY(-3px); }
  60%  { transform: scale(0.9) translateY(1px); }
  80%  { transform: scale(1.06) translateY(-1px); }
  100% { transform: scale(1) translateY(0); }
}

.anim-home    { animation: bounce-home   0.45s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
.anim-pill    { animation: wiggle-pill   0.5s  ease-in-out forwards; }
.anim-diario  { animation: flip-diario   0.4s  ease-in-out forwards; }
.anim-consult { animation: pulse-consult 0.45s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
.anim-perfil  { animation: pop-perfil    0.45s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
</style>
