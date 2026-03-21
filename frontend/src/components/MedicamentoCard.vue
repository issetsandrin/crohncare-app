<script setup>
import { computed } from 'vue'
import EstoqueIndicador from './EstoqueIndicador.vue'
import { useEstoque } from '../composables/useEstoque'

const props = defineProps({
  medicamento: { type: Object, required: true }
})

const emit = defineEmits(['click'])

const { nivelAlerta } = useEstoque()

const dias = computed(() => props.medicamento.dias_restantes ?? 0)
const nivel = computed(() => nivelAlerta(dias.value))

const diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']

const periodicidadeTexto = computed(() => {
  const tipo = props.medicamento.periodicidade_tipo || 'diario'
  const valor = props.medicamento.periodicidade_valor

  if (tipo === 'diario') return 'Diário'
  if (tipo === 'sob_demanda') return 'Sob demanda'
  if (tipo === 'intervalo' && valor?.intervalo) {
    return `A cada ${valor.intervalo} dia${valor.intervalo > 1 ? 's' : ''}`
  }
  if (tipo === 'dias_semana' && valor?.dias?.length) {
    return valor.dias.map(d => diasSemana[d]).join(', ')
  }
  return 'Diário'
})
</script>

<template>
  <div class="medicamento-card clickable" @click="emit('click', medicamento)">
    <div class="card-header">
      <div class="card-title-row">
        <h3 class="card-nome">{{ medicamento.nome }}</h3>
        <span class="card-dose">{{ medicamento.dose }}</span>
      </div>
      <span v-if="nivel !== 'ok'" class="card-alerta-badge" :class="nivel">
        {{ dias === Infinity ? '—' : dias }}d
      </span>
    </div>

    <span class="card-periodicidade">{{ periodicidadeTexto }}</span>

    <div v-if="medicamento.periodicidade_tipo !== 'sob_demanda' && medicamento.horarios && medicamento.horarios.length" class="card-horarios">
      <span v-for="h in medicamento.horarios" :key="h" class="horario-badge">{{ h.substring(0, 5) }}</span>
    </div>

    <div class="card-estoque">
      <EstoqueIndicador :dias-restantes="dias === Infinity ? 0 : dias" />
    </div>
  </div>
</template>

<style scoped>
.medicamento-card {
  background: #fff;
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  animation: fadeInUp 0.4s ease both;
}

.medicamento-card.clickable {
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.15s;
}

.medicamento-card.clickable:active {
  transform: scale(0.98);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title-row {
  display: flex;
  align-items: baseline;
  gap: 8px;
  flex: 1;
  min-width: 0;
}

.card-nome {
  font-family: var(--font-titulo);
  font-size: 1.1rem;
  color: var(--texto);
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.card-dose {
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--texto-light);
  white-space: nowrap;
}

.card-alerta-badge {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 8px;
  flex-shrink: 0;
}

.card-alerta-badge.urgente {
  background: rgba(229, 115, 115, 0.15);
  color: var(--intensidade-5);
}

.card-alerta-badge.atencao {
  background: rgba(212, 160, 60, 0.15);
  color: var(--ambar);
}

.card-periodicidade {
  display: inline-block;
  margin-top: 6px;
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
}

.card-horarios {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 8px;
}

.horario-badge {
  background: var(--branco-quente);
  color: var(--verde-salvia);
  font-family: var(--font-corpo);
  font-size: 12px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 16px;
  border: 1px solid rgba(127, 168, 50, 0.2);
}

.card-estoque {
  margin-top: 10px;
}
</style>
