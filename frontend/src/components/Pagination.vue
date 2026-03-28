<script setup>
import { computed } from 'vue'

const props = defineProps({
  total: { type: Number, required: true },
  perPage: { type: Number, default: 10 },
  modelValue: { type: Number, default: 1 }
})
const emit = defineEmits(['update:modelValue'])

const totalPages = computed(() => Math.max(1, Math.ceil(props.total / props.perPage)))

const visiblePages = computed(() => {
  const total = totalPages.value
  const current = props.modelValue
  if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1)
  if (current <= 3) return [1, 2, 3, 4, null, total]
  if (current >= total - 2) return [1, null, total - 3, total - 2, total - 1, total]
  return [1, null, current - 1, current, current + 1, null, total]
})

function go(page) {
  if (page && page !== props.modelValue) emit('update:modelValue', page)
}
function prev() {
  if (props.modelValue > 1) emit('update:modelValue', props.modelValue - 1)
}
function next() {
  if (props.modelValue < totalPages.value) emit('update:modelValue', props.modelValue + 1)
}
</script>

<template>
  <div v-if="totalPages > 1" class="pagination">
    <button class="nav-btn" :disabled="modelValue <= 1" @click="prev" aria-label="Anterior">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>

    <div class="pages">
      <template v-for="(page, i) in visiblePages" :key="i">
        <span v-if="page === null" class="ellipsis">…</span>
        <button
          v-else
          class="page-num"
          :class="{ active: page === modelValue }"
          @click="go(page)"
        >{{ page }}</button>
      </template>
    </div>

    <button class="nav-btn" :disabled="modelValue >= totalPages" @click="next" aria-label="Próxima">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
        <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
  </div>
</template>

<style scoped>
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 16px 0 4px;
}

.pages {
  display: flex;
  align-items: center;
  gap: 2px;
}

.nav-btn {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: var(--texto-light);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  flex-shrink: 0;
}

.nav-btn:hover:not(:disabled) {
  background: rgba(127, 168, 50, 0.1);
  color: var(--verde-salvia);
}

.nav-btn:disabled {
  opacity: 0.25;
  cursor: not-allowed;
}

.page-num {
  min-width: 30px;
  height: 30px;
  padding: 0 6px;
  border-radius: 8px;
  border: none;
  background: transparent;
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 500;
  color: var(--texto-light);
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}

.page-num:hover:not(.active) {
  background: rgba(127, 168, 50, 0.08);
  color: var(--verde-salvia);
}

.page-num.active {
  background: var(--verde-salvia);
  color: #fff;
  font-weight: 700;
  cursor: default;
}

.ellipsis {
  min-width: 24px;
  text-align: center;
  font-size: 13px;
  color: var(--texto-light);
  opacity: 0.5;
  user-select: none;
}
</style>
