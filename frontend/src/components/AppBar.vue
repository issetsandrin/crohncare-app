<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' }
})
</script>

<template>
  <header class="app-bar">
    <div class="app-bar-text">
      <h1 class="app-bar-title">{{ title }}</h1>
      <p v-if="subtitle" class="app-bar-subtitle">{{ subtitle }}</p>
    </div>
    <div v-if="$slots.default" class="app-bar-actions">
      <slot />
    </div>
  </header>
</template>

<style scoped>
.app-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: calc(env(safe-area-inset-top, 24px) + 8px) 20px 16px;
  background: var(--verde-salvia);
  margin-bottom: 16px;
}

.app-bar-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.app-bar-title {
  font-family: var(--font-titulo);
  font-size: 1.25rem;
  font-weight: 700;
  color: #fff;
  margin: 0;
}

.app-bar-subtitle {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
}

.app-bar-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}
</style>
