<script setup>
import { ref, computed } from 'vue'
import { useTour } from '../composables/useTour'

const { showTour, fecharTour } = useTour()

const stepAtual = ref(0)

const steps = [
  {
    icon: 'home',
    color: '#7FA832',
    bg: '#7fa8321f',
    titulo: 'Início — Seu Painel',
    descricao: 'Aqui fica o resumo do seu cuidado diário: remédios do dia, alertas de estoque, crises do mês e a próxima consulta ou exame agendada. Também há gráficos de intensidade das crises ao longo do tempo e os sintomas mais frequentes.'
  },
  {
    icon: 'pill',
    color: '#7FA832',
    bg: '#7fa8321f',
    titulo: 'Remédios',
    descricao: 'Cadastre seus medicamentos com nome, dose e horários. O app cria lembretes automáticos e monitora o estoque, avisando quando estiver chegando no limite. Você também pode registrar quando tomou cada dose.'
  },
  {
    icon: 'diary',
    color: '#5b8fcc',
    bg: 'rgba(91,143,204,0.12)',
    titulo: 'Diário de Saúde',
    descricao: 'Registre anotações livres e crises com nível de intensidade (1 a 5), sintomas e observações. Esse histórico é valioso para compartilhar com seu médico e identificar padrões e gatilhos da doença.'
  },
  {
    icon: 'calendar',
    color: '#e07b39',
    bg: 'rgba(224,123,57,0.12)',
    titulo: 'Consultas & Exames',
    descricao: 'Organize suas consultas médicas e exames laboratoriais. Cadastre a data, especialidade, médico e status. O app sinaliza os agendamentos próximos para que você nunca perca um compromisso importante.'
  },
  {
    icon: 'bell',
    color: '#c46aae',
    bg: 'rgba(196,106,174,0.12)',
    titulo: 'Avisos',
    descricao: 'A central de notificações do CrohnCare. Aqui aparecem alertas de estoque baixo, lembretes de consultas próximas e avisos gerais do sistema. Fique sempre atualizado sobre o seu cuidado.'
  }
]

const totalSteps = steps.length
const step = computed(() => steps[stepAtual.value])
const ehUltimo = computed(() => stepAtual.value === totalSteps - 1)

function proximo() {
  if (!ehUltimo.value) stepAtual.value++
}
function anterior() {
  if (stepAtual.value > 0) stepAtual.value--
}
function concluir() {
  stepAtual.value = 0
  fecharTour()
}
</script>

<template>
  <Teleport to="body">
    <Transition name="tour-fade">
      <div v-if="showTour" class="tour-overlay">
        <div class="tour-card">
          <!-- Progresso -->
          <div class="tour-progress">
            <span
              v-for="(s, i) in steps"
              :key="i"
              class="tour-dot"
              :class="{ active: i === stepAtual, done: i < stepAtual }"
            />
          </div>

          <!-- Ícone -->
          <div class="tour-icon-wrap" :style="{ background: step.bg }">
            <!-- Início -->
            <svg v-if="step.icon === 'home'" width="32" height="32" viewBox="0 0 24 24" fill="none">
              <path d="M3 12l9-8 9 8M5 10v9a1 1 0 001 1h3v-5h6v5h3a1 1 0 001-1v-9" :stroke="step.color" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <!-- Remédios -->
            <svg v-else-if="step.icon === 'pill'" width="32" height="32" viewBox="0 0 24 24" fill="none">
              <rect x="6" y="2" width="12" height="20" rx="6" :stroke="step.color" stroke-width="1.8"/>
              <line x1="6" y1="12" x2="18" y2="12" :stroke="step.color" stroke-width="1.8"/>
            </svg>
            <!-- Diário -->
            <svg v-else-if="step.icon === 'diary'" width="32" height="32" viewBox="0 0 24 24" fill="none">
              <path d="M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1zM3 8h18M8 4v4" :stroke="step.color" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M7 13h4M7 16h6" :stroke="step.color" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <!-- Consultas -->
            <svg v-else-if="step.icon === 'calendar'" width="32" height="32" viewBox="0 0 24 24" fill="none">
              <rect x="3" y="6" width="18" height="14" rx="2" :stroke="step.color" stroke-width="1.8"/>
              <path d="M3 10h18" :stroke="step.color" stroke-width="1.8"/>
              <path d="M8 2v4M16 2v4" :stroke="step.color" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            <!-- Avisos -->
            <svg v-else-if="step.icon === 'bell'" width="32" height="32" viewBox="0 0 24 24" fill="none">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" :stroke="step.color" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0" :stroke="step.color" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
          </div>

          <!-- Conteúdo -->
          <div class="tour-step-counter">{{ stepAtual + 1 }} de {{ totalSteps }}</div>

          <Transition name="step-slide" mode="out-in">
            <div :key="stepAtual" class="tour-content">
              <h3 class="tour-titulo">{{ step.titulo }}</h3>
              <p class="tour-descricao">{{ step.descricao }}</p>
            </div>
          </Transition>

          <!-- Ações -->
          <div class="tour-actions">
            <button v-if="stepAtual > 0" class="tour-btn-ant" @click="anterior">Anterior</button>
            <div v-else class="tour-btn-spacer"></div>

            <button v-if="!ehUltimo" class="tour-btn-prox" @click="proximo">Próximo</button>
            <button v-else class="tour-btn-concluir" @click="concluir">Concluir tour</button>
          </div>

          <!-- Fechar -->
          <button class="tour-fechar" @click="concluir" aria-label="Fechar tour">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
              <path d="M15 5L5 15M5 5l10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.tour-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 901;
  padding: 16px;
}

.tour-card {
  background: #fff;
  border-radius: 20px;
  padding: 32px 28px 28px;
  max-width: 420px;
  width: 100%;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
  text-align: center;
  box-shadow: 0 24px 70px rgba(0,0,0,0.2);
}

.tour-progress {
  display: flex;
  gap: 6px;
}

.tour-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: rgba(0,0,0,0.12);
  transition: background 0.25s, transform 0.25s;
}

.tour-dot.done {
  background: rgba(127, 168, 50, 0.4);
}

.tour-dot.active {
  background: var(--verde-salvia);
  transform: scale(1.3);
}

.tour-icon-wrap {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s;
}

.tour-step-counter {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: var(--texto-light);
  opacity: 0.55;
}

.tour-content {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-height: 100px;
}

.tour-titulo {
  font-family: var(--font-titulo);
  font-size: 1.25rem;
  color: var(--texto);
  margin: 0;
}

.tour-descricao {
  font-family: var(--font-corpo);
  font-size: 13.5px;
  color: var(--texto-light);
  line-height: 1.65;
  margin: 0;
}

.tour-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  gap: 10px;
  margin-top: 4px;
}

.tour-btn-spacer {
  flex: 1;
}

.tour-btn-ant {
  flex: 1;
  background: none;
  border: 1px solid rgba(0,0,0,0.12);
  border-radius: 10px;
  padding: 11px 16px;
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--texto-light);
  cursor: pointer;
  transition: background 0.2s;
}

.tour-btn-ant:hover {
  background: rgba(0,0,0,0.04);
}

.tour-btn-prox,
.tour-btn-concluir {
  flex: 1;
  background: var(--verde-salvia);
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 11px 16px;
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}

.tour-btn-prox:hover,
.tour-btn-concluir:hover {
  opacity: 0.88;
}

.tour-fechar {
  position: absolute;
  top: 14px;
  right: 14px;
  background: none;
  border: none;
  color: var(--texto-light);
  opacity: 0.5;
  cursor: pointer;
  padding: 6px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s, background 0.2s;
}

.tour-fechar:hover {
  opacity: 1;
  background: rgba(0,0,0,0.06);
}

/* Transitions */
.tour-fade-enter-active,
.tour-fade-leave-active {
  transition: opacity 0.25s ease;
}
.tour-fade-enter-from,
.tour-fade-leave-to {
  opacity: 0;
}

.step-slide-enter-active,
.step-slide-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}
.step-slide-enter-from {
  opacity: 0;
  transform: translateX(14px);
}
.step-slide-leave-to {
  opacity: 0;
  transform: translateX(-14px);
}
</style>
