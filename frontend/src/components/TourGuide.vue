<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useTour } from '../composables/useTour'

const { showTour, fecharTour } = useTour()
const router = useRouter()
const route = useRoute()

const stepAtual = ref(0)
const spotlight = ref(null)   // { top, left, width, height }
const tooltip = ref(null)     // { top, left, side: 'top'|'bottom' }
const transitioning = ref(false)

const steps = [
  {
    route: '/',
    selector: '[data-tour="stats"]',
    titulo: 'Painel de Resumo',
    descricao: 'Seus indicadores em tempo real: remédios do dia, alertas de estoque, crises do mês e a próxima consulta ou exame. Tudo de uma vez só.',
    padding: 10
  },
  {
    route: '/',
    selector: '[data-tour="charts"]',
    titulo: 'Gráficos de Saúde',
    descricao: 'Visualize a intensidade das crises mês a mês e a aderência ao tratamento hoje. Use esses dados nas consultas com seu médico.',
    padding: 10
  },
  {
    route: '/medicamentos',
    selector: '[data-tour="add-med"]',
    titulo: 'Adicionar Remédio',
    descricao: 'Toque aqui para cadastrar um medicamento com nome, dosagem e horários. O app criará lembretes automáticos para cada dose do dia.',
    padding: 16
  },
  {
    route: '/medicamentos',
    selector: '[data-tour="med-list"]',
    titulo: 'Seus Medicamentos',
    descricao: 'Todos os remédios aparecem aqui com o próximo horário de dose. O indicador de estoque avisa quando estiver chegando no limite.',
    padding: 8
  },
  {
    route: '/diario',
    selector: '[data-tour="diario-tabs"]',
    titulo: 'Diário de Saúde',
    descricao: '"Anotações" para registros livres do dia a dia. "Crises" para registrar episódios com intensidade de 1 a 5, sintomas e observações detalhadas.',
    padding: 8
  },
  {
    route: '/consultas',
    selector: '[data-tour="consultas-tabs"]',
    titulo: 'Consultas & Exames',
    descricao: 'Gerencie suas consultas médicas e exames em abas separadas. O status mostra se está agendado, realizado ou cancelado.',
    padding: 8
  },
  {
    route: '/avisos',
    selector: '[data-tour="avisos-list"]',
    titulo: 'Avisos & Alertas',
    descricao: 'Central de notificações: alertas de estoque baixo, lembretes de consultas próximas e avisos do sistema. Fique sempre por dentro.',
    padding: 8
  }
]

const totalSteps = steps.length
const step = computed(() => steps[stepAtual.value])
const ehUltimo = computed(() => stepAtual.value === totalSteps - 1)

function calcularPosicao(selector, padding = 8) {
  const el = document.querySelector(selector)
  if (!el) { spotlight.value = null; tooltip.value = null; return }

  const rect = el.getBoundingClientRect()
  const pad = padding

  spotlight.value = {
    top: Math.max(0, rect.top - pad),
    left: Math.max(0, rect.left - pad),
    width: rect.width + pad * 2,
    height: rect.height + pad * 2
  }

  const TOOLTIP_W = Math.min(320, window.innerWidth - 32)
  const TOOLTIP_H = 180
  const CENTER_Y = rect.top + rect.height / 2
  const isTop = CENTER_Y > window.innerHeight / 2

  let left = rect.left + rect.width / 2 - TOOLTIP_W / 2
  left = Math.max(16, Math.min(left, window.innerWidth - TOOLTIP_W - 16))

  const top = isTop
    ? Math.max(8, rect.top - pad - TOOLTIP_H - 16)
    : rect.bottom + pad + 16

  tooltip.value = { top, left, side: isTop ? 'top' : 'bottom', width: TOOLTIP_W }
}

async function irParaStep(index) {
  transitioning.value = true
  spotlight.value = null
  tooltip.value = null

  const s = steps[index]

  if (route.path !== s.route) {
    await router.push(s.route)
    await nextTick()
    await new Promise(r => setTimeout(r, 500))
  } else {
    await nextTick()
    await new Promise(r => setTimeout(r, 100))
  }

  calcularPosicao(s.selector, s.padding ?? 8)
  transitioning.value = false
}

watch(showTour, async (val) => {
  if (val) {
    stepAtual.value = 0
    await irParaStep(0)
  }
})

async function proximo() {
  if (!ehUltimo.value && !transitioning.value) {
    stepAtual.value++
    await irParaStep(stepAtual.value)
  }
}

async function anterior() {
  if (stepAtual.value > 0 && !transitioning.value) {
    stepAtual.value--
    await irParaStep(stepAtual.value)
  }
}

function concluir() {
  spotlight.value = null
  tooltip.value = null
  stepAtual.value = 0
  fecharTour()
}
</script>

<template>
  <Teleport to="body">
    <template v-if="showTour">
      <!-- Spotlight: cria o buraco na escuridão via box-shadow -->
      <div
        v-if="spotlight"
        class="tour-spotlight"
        :style="{
          top: spotlight.top + 'px',
          left: spotlight.left + 'px',
          width: spotlight.width + 'px',
          height: spotlight.height + 'px'
        }"
      />

      <!-- Fallback overlay quando não há spotlight -->
      <div v-else class="tour-fallback-overlay" />

      <!-- Interceptor de cliques fora -->
      <div class="tour-click-guard" @click.self="() => {}" />

      <!-- Tooltip card -->
      <Transition name="tooltip-pop">
        <div
          v-if="tooltip && !transitioning"
          class="tour-tooltip"
          :class="'side-' + tooltip.side"
          :style="{
            top: tooltip.top + 'px',
            left: tooltip.left + 'px',
            width: tooltip.width + 'px'
          }"
        >
          <!-- Arrow -->
          <div class="tour-arrow" :class="'arrow-' + tooltip.side" />

          <!-- Progresso -->
          <div class="tour-progress">
            <span
              v-for="(s, i) in steps"
              :key="i"
              class="tour-dot"
              :class="{ active: i === stepAtual, done: i < stepAtual }"
            />
          </div>

          <!-- Conteúdo -->
          <Transition name="step-slide" mode="out-in">
            <div :key="stepAtual" class="tour-content">
              <h3 class="tour-titulo">{{ step.titulo }}</h3>
              <p class="tour-descricao">{{ step.descricao }}</p>
            </div>
          </Transition>

          <!-- Ações -->
          <div class="tour-actions">
            <button v-if="stepAtual > 0" class="tour-btn-ant" @click="anterior" :disabled="transitioning">
              ← Anterior
            </button>
            <div v-else class="tour-spacer" />
            <span class="tour-counter">{{ stepAtual + 1 }}/{{ totalSteps }}</span>
            <button
              v-if="!ehUltimo"
              class="tour-btn-prox"
              @click="proximo"
              :disabled="transitioning"
            >
              Próximo →
            </button>
            <button v-else class="tour-btn-concluir" @click="concluir">Concluir</button>
          </div>

          <!-- Fechar -->
          <button class="tour-fechar" @click="concluir" aria-label="Fechar tour">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="none">
              <path d="M15 5L5 15M5 5l10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
        </div>
      </Transition>

      <!-- Loading entre steps -->
      <div v-if="transitioning" class="tour-loading">
        <div class="tour-loading-dots">
          <span /><span /><span />
        </div>
      </div>
    </template>
  </Teleport>
</template>

<style scoped>
/* ── Spotlight ── */
.tour-spotlight {
  position: fixed;
  border-radius: 10px;
  box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.58);
  z-index: 9998;
  pointer-events: none;
  transition: top 0.35s cubic-bezier(0.4, 0, 0.2, 1),
              left 0.35s cubic-bezier(0.4, 0, 0.2, 1),
              width 0.35s cubic-bezier(0.4, 0, 0.2, 1),
              height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.tour-fallback-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.58);
  z-index: 9998;
  pointer-events: none;
}

.tour-click-guard {
  position: fixed;
  inset: 0;
  z-index: 9997;
}

/* ── Tooltip ── */
.tour-tooltip {
  position: fixed;
  background: #fff;
  border-radius: 14px;
  padding: 16px 16px 14px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.22);
  z-index: 9999;
  pointer-events: all;
}

.tour-arrow {
  position: absolute;
  width: 12px;
  height: 12px;
  background: #fff;
  transform: rotate(45deg);
  left: 50%;
  translate: -50% 0;
}
.tour-arrow.arrow-bottom {
  top: -6px;
  box-shadow: -2px -2px 4px rgba(0,0,0,0.06);
}
.tour-arrow.arrow-top {
  bottom: -6px;
  box-shadow: 2px 2px 4px rgba(0,0,0,0.06);
}

/* ── Progress dots ── */
.tour-progress {
  display: flex;
  gap: 5px;
  margin-bottom: 10px;
}
.tour-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.12);
  transition: background 0.2s, transform 0.2s;
}
.tour-dot.done { background: rgba(127, 168, 50, 0.4); }
.tour-dot.active { background: var(--verde-salvia); transform: scale(1.35); }

/* ── Content ── */
.tour-content { min-height: 68px; }
.tour-titulo {
  font-family: var(--font-titulo);
  font-size: 1rem;
  color: var(--texto);
  margin: 0 0 5px;
}
.tour-descricao {
  font-family: var(--font-corpo);
  font-size: 12.5px;
  color: var(--texto-light);
  line-height: 1.6;
  margin: 0;
}

/* ── Actions ── */
.tour-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-top: 12px;
}
.tour-spacer { flex: 1; }
.tour-counter {
  font-family: var(--font-corpo);
  font-size: 11px;
  color: var(--texto-light);
  opacity: 0.5;
  flex-shrink: 0;
}
.tour-btn-ant {
  flex: 1;
  background: none;
  border: 1px solid rgba(0,0,0,0.1);
  border-radius: 8px;
  padding: 7px 10px;
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
  cursor: pointer;
  transition: background 0.15s;
}
.tour-btn-ant:hover { background: rgba(0,0,0,0.04); }
.tour-btn-ant:disabled { opacity: 0.4; cursor: not-allowed; }

.tour-btn-prox,
.tour-btn-concluir {
  flex: 1;
  background: var(--verde-salvia);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 7px 10px;
  font-family: var(--font-corpo);
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.15s;
}
.tour-btn-prox:hover,
.tour-btn-concluir:hover { opacity: 0.88; }
.tour-btn-prox:disabled { opacity: 0.4; cursor: not-allowed; }

.tour-fechar {
  position: absolute;
  top: 10px;
  right: 10px;
  background: none;
  border: none;
  color: var(--texto-light);
  opacity: 0.45;
  cursor: pointer;
  padding: 4px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.15s;
}
.tour-fechar:hover { opacity: 0.9; }

/* ── Loading ── */
.tour-loading {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  pointer-events: none;
}
.tour-loading-dots {
  display: flex;
  gap: 6px;
}
.tour-loading-dots span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #fff;
  animation: tour-pulse 1.2s ease-in-out infinite;
}
.tour-loading-dots span:nth-child(2) { animation-delay: 0.2s; }
.tour-loading-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes tour-pulse {
  0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
  40% { transform: scale(1); opacity: 1; }
}

/* ── Transitions ── */
.tooltip-pop-enter-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.tooltip-pop-leave-active { transition: opacity 0.15s ease; }
.tooltip-pop-enter-from { opacity: 0; transform: scale(0.95); }
.tooltip-pop-leave-to { opacity: 0; }

.step-slide-enter-active,
.step-slide-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.step-slide-enter-from { opacity: 0; transform: translateX(10px); }
.step-slide-leave-to { opacity: 0; transform: translateX(-10px); }
</style>
