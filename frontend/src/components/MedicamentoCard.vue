<script setup>
import { computed } from 'vue'
import { useEstoque } from '../composables/useEstoque'

const props = defineProps({
  medicamento: { type: Object, required: true }
})

const emit = defineEmits(['click'])

const { nivelAlerta } = useEstoque()

const dias = computed(() => props.medicamento.dias_restantes ?? 0)
const quantidade = computed(() => props.medicamento.estoque?.quantidade_atual ?? 0)
const nivel = computed(() => nivelAlerta(dias.value))

const diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']

const periodicidadeTexto = computed(() => {
  const tipo = props.medicamento.periodicidade_tipo || 'diario'
  const valor = props.medicamento.periodicidade_valor
  if (tipo === 'diario') return 'Diário'
  if (tipo === 'sob_demanda') return 'Sob demanda'
  if (tipo === 'ciclo' && valor?.ciclo?.length) return `Ciclo ${valor.ciclo.length} dias`
  if (tipo === 'intervalo' && valor?.intervalo) return `A cada ${valor.intervalo} dia${valor.intervalo > 1 ? 's' : ''}`
  if (tipo === 'dias_semana' && valor?.dias?.length) return valor.dias.map(d => diasSemana[d]).join(', ')
  return 'Diário'
})

const doseHoje = computed(() => props.medicamento.dose_hoje || props.medicamento.dose || '')

const doseTomar = computed(() => {
  const raw = String(doseHoje.value).trim()
  if (!raw) return ''
  if (props.medicamento.periodicidade_tipo === 'ciclo' && /^\d+$/.test(raw)) {
    const n = parseInt(raw)
    return `Tomar ${n} comprimido${n !== 1 ? 's' : ''}`
  }
  return `Tomar ${raw}`
})

const estoqueLabel = computed(() => {
  if (quantidade.value <= 0) return 'Sem estoque'
  if (nivel.value === 'urgente') return `${quantidade.value} un restantes`
  if (nivel.value === 'atencao') return `${quantidade.value} un restantes`
  return null
})
</script>

<template>
  <div class="medicamento-card" @click="emit('click', medicamento)">
    <div class="card-accent" :class="nivel" />
    <div class="card-body">
      <div class="card-top">
        <div class="card-icon-wrap" :class="nivel">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <rect x="6" y="2" width="12" height="20" rx="6" stroke="currentColor" stroke-width="1.8"/>
            <line x1="6" y1="12" x2="18" y2="12" stroke="currentColor" stroke-width="1.8"/>
          </svg>
        </div>
        <div class="card-main">
          <div class="card-title-row">
            <h3 class="card-nome">{{ medicamento.nome }}</h3>
            <span class="card-dose-badge">{{ doseTomar }}</span>
          </div>
          <span class="card-periodicidade">{{ periodicidadeTexto }}</span>
        </div>
        <div v-if="nivel !== 'ok'" class="card-alerta-pill" :class="nivel">{{ quantidade }} un</div>
      </div>

      <div v-if="medicamento.periodicidade_tipo !== 'sob_demanda' && medicamento.horarios?.length" class="card-bottom">
        <div class="card-horarios">
          <span v-for="h in medicamento.horarios" :key="h" class="horario-tag">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
              <path d="M12 7v5l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            {{ h.substring(0, 5) }}
          </span>
        </div>
        <span v-if="estoqueLabel" class="estoque-label" :class="nivel">{{ estoqueLabel }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.medicamento-card {
  display: flex;
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.07);
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.2s var(--ease-smooth), box-shadow 0.2s var(--ease-smooth);
  animation: fadeInUp 0.4s var(--ease-out-smooth) both;
}

.medicamento-card:active {
  transform: scale(0.98);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
}

.card-accent {
  width: 4px;
  flex-shrink: 0;
  background: var(--verde-salvia);
  border-radius: 14px 0 0 14px;
}

.card-accent.atencao { background: var(--ambar); }
.card-accent.urgente { background: var(--intensidade-5); }

.card-body {
  flex: 1;
  padding: 14px 14px 12px;
  min-width: 0;
}

.card-top {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.card-icon-wrap {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: rgba(127, 168, 50, 0.12);
  color: var(--verde-salvia);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.card-icon-wrap.atencao {
  background: rgba(212, 160, 60, 0.12);
  color: var(--ambar);
}

.card-icon-wrap.urgente {
  background: rgba(229, 115, 115, 0.12);
  color: var(--intensidade-5);
}

.card-main {
  flex: 1;
  min-width: 0;
}

.card-title-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.card-nome {
  font-family: var(--font-titulo);
  font-size: 1rem;
  color: var(--texto);
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.card-dose-badge {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 600;
  background: rgba(127, 168, 50, 0.12);
  color: var(--verde-salvia);
  padding: 2px 8px;
  border-radius: 20px;
  white-space: nowrap;
}

.card-periodicidade {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
  margin-top: 2px;
  display: block;
}

.card-alerta-pill {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 20px;
  flex-shrink: 0;
}

.card-alerta-pill.urgente {
  background: rgba(229, 115, 115, 0.15);
  color: var(--intensidade-5);
}

.card-alerta-pill.atencao {
  background: rgba(212, 160, 60, 0.15);
  color: var(--ambar);
}

.card-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.card-horarios {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}

.horario-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: var(--branco-quente);
  color: var(--verde-salvia);
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 600;
  padding: 3px 9px;
  border-radius: 20px;
  border: 1px solid rgba(127, 168, 50, 0.2);
}

.estoque-label {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
  flex-shrink: 0;
}

.estoque-label.urgente { color: var(--intensidade-5); }
.estoque-label.atencao { color: var(--ambar); }
</style>
