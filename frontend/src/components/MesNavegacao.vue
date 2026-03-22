<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: String, required: true }
})

const emit = defineEmits(['update:modelValue'])

const meses = [
  'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
]

const label = computed(() => {
  const [year, month] = props.modelValue.split('-').map(Number)
  return `${meses[month - 1]} ${year}`
})

function mudarMes(delta) {
  const [year, month] = props.modelValue.split('-').map(Number)
  const d = new Date(year, month - 1 + delta, 1)
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  emit('update:modelValue', `${y}-${m}`)
}
</script>

<template>
  <div class="mes-navegacao">
    <button class="nav-arrow" @click="mudarMes(-1)" aria-label="Mês anterior">
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
        <path d="M12 4l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
    <span class="mes-label">{{ label }}</span>
    <button class="nav-arrow" @click="mudarMes(1)" aria-label="Próximo mês">
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
        <path d="M8 4l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
  </div>
</template>

<style scoped>
.mes-navegacao {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 0;
}

.nav-arrow {
  background: none;
  border: none;
  cursor: pointer;
  color: var(--verde-salvia);
  padding: 8px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s var(--ease-smooth);
}

.nav-arrow:hover {
  background: rgba(127, 168, 50, 0.1);
}

.nav-arrow:active {
  transform: scale(0.92);
}

.mes-label {
  font-family: var(--font-titulo);
  font-size: 1.2rem;
  color: var(--texto);
}
</style>
