<script setup>
const props = defineProps({
  modelValue: { type: Number, default: 0 }
})

const emit = defineEmits(['update:modelValue'])

function select(n) {
  emit('update:modelValue', n)
}
</script>

<template>
  <div class="intensidade-escala">
    <button
      v-for="n in 5"
      :key="n"
      class="intensidade-circle"
      :class="[`nivel-${n}`, { selected: modelValue === n }]"
      @click="select(n)"
      type="button"
    >
      {{ n }}
    </button>
  </div>
</template>

<style scoped>
.intensidade-escala {
  display: flex;
  gap: 12px;
  align-items: center;
}

.intensidade-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  background: transparent;
}

.intensidade-circle.nivel-1 {
  border: 2px solid var(--intensidade-1);
  color: var(--intensidade-1);
}
.intensidade-circle.nivel-1.selected {
  background: var(--intensidade-1);
  color: #fff;
}

.intensidade-circle.nivel-2 {
  border: 2px solid var(--intensidade-2);
  color: var(--intensidade-2);
}
.intensidade-circle.nivel-2.selected {
  background: var(--intensidade-2);
  color: #fff;
}

.intensidade-circle.nivel-3 {
  border: 2px solid var(--intensidade-3);
  color: var(--intensidade-3);
}
.intensidade-circle.nivel-3.selected {
  background: var(--intensidade-3);
  color: #fff;
}

.intensidade-circle.nivel-4 {
  border: 2px solid var(--intensidade-4);
  color: var(--intensidade-4);
}
.intensidade-circle.nivel-4.selected {
  background: var(--intensidade-4);
  color: #fff;
}

.intensidade-circle.nivel-5 {
  border: 2px solid var(--intensidade-5);
  color: var(--intensidade-5);
}
.intensidade-circle.nivel-5.selected {
  background: var(--intensidade-5);
  color: #fff;
}

.intensidade-circle:active {
  transform: scale(0.92);
}
</style>
