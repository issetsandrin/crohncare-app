<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: { type: String, required: true }
})
const emit = defineEmits(['update:modelValue'])

const open = ref(false)
const triggerRef = ref(null)
const dropdownRef = ref(null)

const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']
const mesesCompletos = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro']

const anoAtual = ref(Number(props.modelValue.split('-')[0]))
const mesAtual = computed(() => Number(props.modelValue.split('-')[1]) - 1)

const labelAtual = computed(() => {
  const [y, m] = props.modelValue.split('-').map(Number)
  return `${mesesCompletos[m - 1]} ${y}`
})

function toggle() {
  open.value = !open.value
  if (open.value) {
    anoAtual.value = Number(props.modelValue.split('-')[0])
  }
}

function selecionar(indice) {
  const m = String(indice + 1).padStart(2, '0')
  emit('update:modelValue', `${anoAtual.value}-${m}`)
  open.value = false
}

function handleOutside(e) {
  if (
    triggerRef.value && !triggerRef.value.contains(e.target) &&
    dropdownRef.value && !dropdownRef.value.contains(e.target)
  ) {
    open.value = false
  }
}

onMounted(() => document.addEventListener('mousedown', handleOutside))
onUnmounted(() => document.removeEventListener('mousedown', handleOutside))
</script>

<template>
  <div class="month-picker">
    <button ref="triggerRef" class="mp-trigger" @click="toggle" :title="labelAtual">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
        <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/>
        <path d="M3 9h18" stroke="currentColor" stroke-width="1.8"/>
        <path d="M8 2v4M16 2v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
      </svg>
    </button>

    <Transition name="mp-drop">
      <div v-if="open" ref="dropdownRef" class="mp-dropdown">
        <!-- Navegação de ano -->
        <div class="mp-year-row">
          <button class="mp-arrow" @click="anoAtual--">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="none">
              <path d="M12 4l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
          <span class="mp-year">{{ anoAtual }}</span>
          <button class="mp-arrow" @click="anoAtual++">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="none">
              <path d="M8 4l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>

        <!-- Grade de meses -->
        <div class="mp-grid">
          <button
            v-for="(mes, i) in meses"
            :key="i"
            class="mp-month"
            :class="{ active: i === mesAtual && anoAtual === Number(modelValue.split('-')[0]) }"
            @click="selecionar(i)"
          >
            {{ mes }}
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.month-picker {
  position: relative;
}

.mp-trigger {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid rgba(127, 168, 50, 0.4);
  background: rgba(127, 168, 50, 0.08);
  color: var(--verde-salvia);
  cursor: pointer;
  transition: background 0.15s, border-color 0.15s;
}

.mp-trigger:hover {
  background: rgba(127, 168, 50, 0.16);
  border-color: var(--verde-salvia);
}

.mp-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background: #fff;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  padding: 12px;
  z-index: 200;
  width: 220px;
}

.mp-year-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
  padding: 0 2px;
}

.mp-year {
  font-family: var(--font-titulo);
  font-size: 14px;
  font-weight: 700;
  color: var(--texto);
}

.mp-arrow {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  border-radius: 6px;
  border: none;
  background: none;
  color: var(--texto-light);
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}

.mp-arrow:hover {
  background: rgba(127, 168, 50, 0.1);
  color: var(--verde-salvia);
}

.mp-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 4px;
}

.mp-month {
  padding: 8px 4px;
  border-radius: 8px;
  border: none;
  background: none;
  font-family: var(--font-corpo);
  font-size: 12.5px;
  font-weight: 500;
  color: var(--texto-light);
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  text-align: center;
}

.mp-month:hover {
  background: rgba(127, 168, 50, 0.1);
  color: var(--verde-salvia);
}

.mp-month.active {
  background: var(--verde-salvia);
  color: #fff;
  font-weight: 600;
}

.mp-drop-enter-active,
.mp-drop-leave-active {
  transition: opacity 0.15s, transform 0.15s;
}

.mp-drop-enter-from,
.mp-drop-leave-to {
  opacity: 0;
  transform: translateY(-6px) scale(0.97);
}
</style>
