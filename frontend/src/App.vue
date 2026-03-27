<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import BottomNav from './components/BottomNav.vue'
import SidebarNav from './components/SidebarNav.vue'
import AppToast from './components/AppToast.vue'
import { useBreakpoint } from './composables/useBreakpoint'

const route = useRoute()
const { isDesktop } = useBreakpoint()

const showNav = computed(() => {
  return !['login', 'cadastro'].includes(route.name)
})
</script>

<template>
  <div v-if="isDesktop && showNav" class="app-layout-desktop">
    <SidebarNav />
    <main class="desktop-content">
      <router-view :key="route.fullPath" />
    </main>
  </div>
  <template v-else>
    <router-view :key="route.fullPath" />
    <BottomNav v-if="showNav" />
  </template>
  <AppToast />
</template>
