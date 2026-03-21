<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import BottomNav from './components/BottomNav.vue'
import AppToast from './components/AppToast.vue'

const route = useRoute()

const showNav = computed(() => {
  return !['login', 'cadastro'].includes(route.name)
})
</script>

<template>
  <router-view />
  <BottomNav v-if="showNav" />
  <AppToast />
</template>
