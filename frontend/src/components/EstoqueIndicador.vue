<script setup>
import { computed } from 'vue'

const props = defineProps({
  diasRestantes: { type: Number, required: true },
  max: { type: Number, default: 30 }
})

const porcentagem = computed(() => {
  if (props.diasRestantes >= props.max) return 100
  if (props.diasRestantes <= 0) return 2
  return Math.round((props.diasRestantes / props.max) * 100)
})

const nivel = computed(() => {
  if (props.diasRestantes <= 3) return 'urgente'
  if (props.diasRestantes <= 7) return 'atencao'
  return 'ok'
})

const corBarra = computed(() => {
  if (nivel.value === 'urgente') return 'var(--intensidade-5)'
  if (nivel.value === 'atencao') return 'var(--ambar)'
  return 'var(--verde-salvia)'
})
</script>

<template>
  <div class="estoque-indicador">
    <div class="estoque-bar-track">
      <div
        class="estoque-bar-fill"
        :class="{ pulse: nivel === 'urgente' }"
        :style="{ width: porcentagem + '%', background: corBarra }"
      />
    </div>
    <span class="estoque-text" :class="nivel">
      {{ diasRestantes === Infinity ? '—' : diasRestantes }} dias restantes
    </span>
  </div>
</template>

<style scoped>
.estoque-indicador {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.estoque-bar-track {
  width: 100%;
  height: 6px;
  background: #eee;
  border-radius: 3px;
  overflow: hidden;
}

.estoque-bar-fill {
  height: 100%;
  border-radius: 3px;
  transition: width 0.6s var(--ease-out-smooth), background 0.4s var(--ease-smooth);
}

.estoque-bar-fill.pulse {
  animation: pulse 2s var(--ease-smooth) infinite;
}

.estoque-text {
  font-family: var(--font-corpo);
  font-size: 12px;
  font-weight: 500;
}

.estoque-text.ok {
  color: var(--verde-salvia);
}

.estoque-text.atencao {
  color: var(--ambar);
}

.estoque-text.urgente {
  color: var(--intensidade-5);
}
</style>
