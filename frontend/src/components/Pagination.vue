<script setup>
import { computed } from 'vue'

const props = defineProps({
  total: { type: Number, required: true },
  perPage: { type: Number, default: 10 },
  modelValue: { type: Number, default: 1 }
})
const emit = defineEmits(['update:modelValue'])

const totalPages = computed(() => Math.max(1, Math.ceil(props.total / props.perPage)))

function prev() {
  if (props.modelValue > 1) emit('update:modelValue', props.modelValue - 1)
}
function next() {
  if (props.modelValue < totalPages.value) emit('update:modelValue', props.modelValue + 1)
}
</script>

<template>
  <div v-if="totalPages > 1" class="pagination">
    <button class="page-btn" :disabled="modelValue <= 1" @click="prev">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      Anterior
    </button>
    <span class="page-info">{{ modelValue }} de {{ totalPages }}</span>
    <button class="page-btn" :disabled="modelValue >= totalPages" @click="next">
      Próxima
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
        <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
  </div>
</template>

<style scoped>
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 20px 0 8px;
}

.page-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  background: #fff;
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 500;
  color: var(--texto);
  cursor: pointer;
  transition: background 0.15s, border-color 0.15s;
}

.page-btn:hover:not(:disabled) {
  background: rgba(127, 168, 50, 0.06);
  border-color: var(--verde-salvia);
  color: var(--verde-salvia);
}

.page-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.page-info {
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--texto-light);
  min-width: 60px;
  text-align: center;
}
</style>
