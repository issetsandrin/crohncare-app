<script setup>
defineProps({
  valor: { type: Number, default: 1 }
})

const labels = ['Leve', 'Baixa', 'Moderada', 'Alta', 'Severa']
</script>

<template>
  <div class="intensidade-dots" :title="labels[valor - 1]">
    <span
      v-for="n in 5"
      :key="n"
      class="dot"
      :class="[`cor-${n}`, { ativo: n <= valor, inativo: n > valor }]"
    />
    <span class="intensidade-label" :class="`label-${valor}`">{{ labels[valor - 1] }}</span>
  </div>
</template>

<style scoped>
.intensidade-dots {
  display: flex;
  align-items: center;
  gap: 3px;
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  transition: all 0.3s var(--ease-smooth);
}

.dot.inativo {
  background: #e0e0e0;
}

.dot.ativo.cor-1 { background: var(--intensidade-1); }
.dot.ativo.cor-2 { background: var(--intensidade-2); }
.dot.ativo.cor-3 { background: var(--intensidade-3); }
.dot.ativo.cor-4 { background: var(--intensidade-4); }
.dot.ativo.cor-5 { background: var(--intensidade-5); }

.intensidade-label {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 600;
  margin-left: 5px;
}

.label-1 { color: var(--intensidade-1); }
.label-2 { color: var(--intensidade-2); }
.label-3 { color: var(--intensidade-3); }
.label-4 { color: var(--intensidade-4); }
.label-5 { color: var(--intensidade-5); }
</style>
