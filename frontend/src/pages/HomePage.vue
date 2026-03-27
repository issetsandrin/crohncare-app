<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useMedicamentosStore } from '../stores/medicamentos'
import { useAuthStore } from '../stores/auth'
import { useNotificacoesStore } from '../stores/notificacoes'
import { useAvisosStore } from '../stores/avisos'
import LoadingDots from '../components/LoadingDots.vue'

const router = useRouter()
const medStore = useMedicamentosStore()
const authStore = useAuthStore()
const notifStore = useNotificacoesStore()
const avisosStore = useAvisosStore()

const loading = ref(true)

// Usa diretamente o estado reativo do store (não ref local)
const naoLidosCount = computed(() => avisosStore.naoLidosCount)

function abrirAvisos() {
  router.push('/avisos')
}

const dicasSaude = [
  'Beber bastante água ajuda na digestão e reduz inflamações',
  'Manter uma rotina de sono regular fortalece o sistema imunológico',
  'Alimentos ricos em ômega-3 podem ajudar a reduzir inflamações',
  'Registrar seus sintomas ajuda o médico a ajustar o tratamento',
  'Evite pular doses — a constância é a chave do tratamento',
  'Práticas de respiração podem ajudar a controlar o estresse',
  'Pequenas refeições frequentes são mais gentis com o intestino',
  'Caminhadas leves melhoram a motilidade intestinal',
  'Probióticos podem ser aliados — converse com seu médico',
  'Ouvir seu corpo é o primeiro passo para cuidar da sua saúde',
  'Manter um diário alimentar ajuda a identificar gatilhos',
  'O estresse pode agravar os sintomas — cuide da sua mente também'
]

const dicaAtual = ref(0)
let dicaInterval = null
let pollingInterval = null

function shuffleDicas() {
  const shuffled = [...dicasSaude].sort(() => Math.random() - 0.5)
  dicasSaude.splice(0, dicasSaude.length, ...shuffled)
}

shuffleDicas()

onMounted(async () => {
  try {
    await medStore.fetchAll()
    notifStore.inicializar()
    avisosStore.fetchNaoLidos()
  } catch (e) {
    // stores already log errors
  } finally {
    loading.value = false
  }

  dicaInterval = setInterval(() => {
    dicaAtual.value = (dicaAtual.value + 1) % dicasSaude.length
  }, 6000)

  // Polling a cada 30s para manter badge atualizado
  pollingInterval = setInterval(() => {
    avisosStore.fetchNaoLidos()
  }, 30000)
})

onUnmounted(() => {
  if (dicaInterval) clearInterval(dicaInterval)
  if (pollingInterval) clearInterval(pollingInterval)
})

const firstName = computed(() => {
  const nome = authStore.userName
  return nome ? nome.split(' ')[0] : 'Usuário'
})

const saudacao = computed(() => {
  const hora = new Date().getHours()
  if (hora < 12) return 'Bom dia'
  if (hora < 18) return 'Boa tarde'
  return 'Boa noite'
})

const proximoAlarme = computed(() => {
  const proximos = medStore.proximosHorarios.filter(h => !h.passado)
  return proximos.length > 0 ? proximos[0] : null
})

const proximosRemedios = computed(() => medStore.proximosHorarios.slice(0, 8))

function statusRemedio(item) {
  if (item.passado) return 'perdido'
  const agora = new Date()
  const horaAtual = agora.getHours() * 60 + agora.getMinutes()
  const diff = item.minutos - horaAtual
  if (diff >= 0 && diff <= 30) return 'proximo'
  return 'pendente'
}
const alertasEstoque = computed(() => medStore.comAlerta)
const totalRemediosHoje = computed(() => medStore.proximosHorarios.length)
const remediosTomados = computed(() => medStore.proximosHorarios.filter(h => h.passado).length)
</script>

<template>
  <main class="home-page">
    <!-- Cabeçalho desktop (substitui o hero) -->
    <div class="desktop-page-header">
      <div class="dph-text">
        <h1 class="dph-title">{{ saudacao }}, {{ firstName }}</h1>
        <p class="dph-subtitle">Veja o resumo do seu dia e os próximos medicamentos</p>
      </div>
      <button class="dph-alerts-btn" :class="{ 'has-alerts': naoLidosCount > 0 }" @click="abrirAvisos">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" :class="{ 'bell-ring': naoLidosCount > 0 }">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span v-if="naoLidosCount > 0" class="dph-badge">{{ naoLidosCount }}</span>
        Avisos
      </button>
    </div>

    <!-- Hero Header (mobile only) -->
    <header class="hero">
      <div class="hero-bg"></div>
      <div class="hero-content">
        <div class="hero-top">
          <div class="avatar">{{ firstName.charAt(0) }}</div>
          <div class="hero-text">
            <span class="hero-greeting">{{ saudacao }}, {{ firstName }}</span>
            <div class="hero-dica">
              <Transition name="dica" mode="out-in">
                <span class="hero-dica-texto" :key="dicaAtual">{{ dicasSaude[dicaAtual] }}</span>
              </Transition>
            </div>
          </div>
          <button class="hero-alerts-btn" :class="{ 'has-alerts': naoLidosCount > 0 }" @click="abrirAvisos">
            <svg
              width="22" height="22" viewBox="0 0 24 24" fill="none"
              :class="{ 'bell-ring': naoLidosCount > 0 }"
            >
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span v-if="naoLidosCount > 0" class="alerts-badge">{{ naoLidosCount }}</span>
          </button>
        </div>

        <!-- Próximo remédio highlight -->
        <div v-if="!loading && proximoAlarme" class="proximo-card">
          <div class="proximo-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
            </svg>
          </div>
          <div class="proximo-info">
            <span class="proximo-label">Próximo remédio</span>
            <span class="proximo-detail">
              <strong>{{ proximoAlarme.horario }}</strong> — {{ proximoAlarme.nome }} {{ proximoAlarme.dose }}
            </span>
          </div>
        </div>
      </div>
    </header>

    <!-- Quick Stats -->
    <div v-if="!loading" class="stats-row">
      <div class="stat-card">
        <span class="stat-number">{{ remediosTomados }}/{{ totalRemediosHoje }}</span>
        <span class="stat-label">Doses hoje</span>
      </div>
      <div class="stat-card" :class="{ 'stat-alert': alertasEstoque.length > 0 }">
        <span class="stat-number">{{ alertasEstoque.length }}</span>
        <span class="stat-label">{{ alertasEstoque.length === 1 ? 'Alerta estoque' : 'Alertas estoque' }}</span>
      </div>
    </div>


    <!-- Remédios do dia -->
    <LoadingDots v-if="loading" />

    <div v-else class="remedios-section">
      <h3 class="section-title">Remédios de hoje</h3>
      <div v-if="proximosRemedios.length === 0" class="empty-state">
        <svg class="empty-icon" width="40" height="40" viewBox="0 0 24 24" fill="none">
          <rect x="6" y="2" width="12" height="20" rx="6" stroke="currentColor" stroke-width="1.5"/>
          <line x1="6" y1="12" x2="18" y2="12" stroke="currentColor" stroke-width="1.5"/>
        </svg>
        <p>Nenhum horário cadastrado</p>
        <p class="empty-hint">Adicione medicamentos para ver seus horários aqui</p>
      </div>
      <div v-else class="remedios-list">
        <div
          v-for="(item, i) in proximosRemedios"
          :key="item.medicamentoId + '-' + item.horario"
          class="remedio-card"
          :class="[statusRemedio(item), { passado: item.passado }]"
          :style="{ animationDelay: i * 0.04 + 's' }"
        >
          <div class="remedio-accent" :class="statusRemedio(item)" />
          <div class="remedio-hora-box" :class="statusRemedio(item)">
            <span class="remedio-hora">{{ item.horario }}</span>
          </div>
          <div class="remedio-info">
            <div class="remedio-nome-row">
              <span class="remedio-nome">{{ item.nome }}</span>
              <span v-if="item.diaCiclo" class="ciclo-badge">Dia {{ item.diaCiclo }}/{{ item.totalCiclo }}</span>
            </div>
            <span class="remedio-dose">Tomar {{ item.dose }}</span>
          </div>
          <div class="remedio-status-icon" :class="statusRemedio(item)">
            <svg v-if="statusRemedio(item) === 'proximo'" width="16" height="16" viewBox="0 0 18 18" fill="none">
              <circle cx="9" cy="9" r="7" stroke="currentColor" stroke-width="1.8"/>
              <path d="M9 5.5V9l2.5 1.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <svg v-else width="16" height="16" viewBox="0 0 18 18" fill="none">
              <circle cx="9" cy="9" r="7" stroke="currentColor" stroke-width="1.8"/>
              <path d="M9 5.5V9l2.5 1.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
.home-page {
  padding-bottom: calc(80px + env(safe-area-inset-bottom, 0px));
  height: 100dvh;
  overflow: hidden;
}

.desktop-page-header {
  display: none;
}

/* Hero */
.hero {
  position: relative;
  padding: 28px 20px 20px;
  margin-bottom: 0;
  overflow: hidden;
}

.hero-bg {
  position: absolute;
  inset: 0;
  background: var(--verde-salvia);
  border-radius: 0 0 28px 28px;
}

.hero-content {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.hero-top {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar {
  width: 44px;
  min-width: 44px;
  height: 44px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(4px);
  color: #fff;
  font-family: var(--font-titulo);
  font-size: 18px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  text-transform: uppercase;
  flex-shrink: 0;
}

.hero-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.hero-greeting {
  font-family: var(--font-titulo);
  font-size: 18px;
  font-weight: 700;
  color: #fff;
}

.hero-dica {
  overflow: hidden;
  min-height: 16px;
}

.hero-dica-texto {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.4;
  display: block;
}

/* Alerts button */
.hero-alerts-btn {
  position: relative;
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(4px);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.85);
  flex-shrink: 0;
  margin-left: auto;
  transition: background 0.3s var(--ease-smooth);
}

.hero-alerts-btn:active {
  background: rgba(255, 255, 255, 0.25);
}

.hero-alerts-btn.has-alerts {
  color: #fff;
}

.alerts-badge {
  position: absolute;
  top: -3px;
  right: -3px;
  min-width: 18px;
  height: 18px;
  border-radius: 9px;
  background: var(--terracota);
  color: #fff;
  font-family: var(--font-corpo);
  font-size: 10px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 5px;
  box-shadow: 0 1px 4px rgba(196, 120, 74, 0.4);
}

/* Próximo remédio card dentro do hero */
.proximo-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 14px;
}

.proximo-icon {
  color: #fff;
  display: flex;
  align-items: center;
  flex-shrink: 0;
  opacity: 0.9;
}

.proximo-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.proximo-label {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.7);
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.proximo-detail {
  font-family: var(--font-corpo);
  font-size: 14px;
  color: #fff;
}

.proximo-detail strong {
  font-weight: 700;
}

/* Quick Stats */
.stats-row {
  display: flex;
  gap: 8px;
  padding: 16px 16px 0;
  margin-top: 4px;
  position: relative;
  z-index: 2;
}

.stat-card {
  flex: 1;
  background: #fff;
  border-radius: 14px;
  padding: 14px 10px;
  text-align: center;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  display: flex;
  flex-direction: column;
  gap: 4px;
  animation: fadeInUp 0.5s var(--ease-out-smooth) both;
}

.stat-card:nth-child(2) { animation-delay: 0.05s; }
.stat-card:nth-child(3) { animation-delay: 0.1s; }

.stat-alert {
  border: 1.5px solid rgba(196, 120, 74, 0.3);
}

.stat-number {
  font-family: var(--font-titulo);
  font-size: 22px;
  font-weight: 700;
  color: var(--verde-salvia);
  line-height: 1;
}

.stat-alert .stat-number {
  color: var(--terracota);
}

.stat-label {
  font-family: var(--font-corpo);
  font-size: 10px;
  font-weight: 600;
  color: var(--texto-light);
  text-transform: uppercase;
  letter-spacing: 0.2px;
}

.dica-enter-active,
.dica-leave-active {
  transition: all 0.4s var(--ease-smooth);
}

.dica-enter-from {
  opacity: 0;
  transform: translateY(6px);
}

.dica-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* Remédios section */
.remedios-section {
  padding: 16px 16px 0;
}

.section-title {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--texto-light);
  margin: 0 0 10px;
}


/* Loading */
.loading-state {
  display: flex;
  justify-content: center;
  padding: 48px 0;
}

.loading-dots {
  display: flex;
  gap: 6px;
}

.loading-dots span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--verde-salvia);
  animation: loadingBounce 1s ease infinite;
}

.loading-dots span:nth-child(2) { animation-delay: 0.15s; }
.loading-dots span:nth-child(3) { animation-delay: 0.3s; }

@keyframes loadingBounce {
  0%, 80%, 100% { opacity: 0.3; transform: scale(0.8); }
  40% { opacity: 1; transform: scale(1); }
}

/* Empty state */
.empty-state {
  text-align: center;
  padding: 48px 16px;
  color: var(--texto-light);
  font-family: var(--font-corpo);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.empty-icon {
  color: #ccc;
  margin-bottom: 4px;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}

.empty-hint {
  font-size: 13px;
  margin-top: 4px;
}

/* Remédios list */
.remedios-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.remedio-card {
  display: flex;
  align-items: center;
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  animation: fadeInUp 0.4s var(--ease-out-smooth) both;
  transition: opacity 0.4s var(--ease-smooth), transform 0.2s var(--ease-smooth);
}

.remedio-card:active {
  transform: scale(0.985);
}

.remedio-card.passado {
  opacity: 0.5;
}

/* Accent bar esquerda */
.remedio-accent {
  width: 4px;
  align-self: stretch;
  flex-shrink: 0;
  background: var(--verde-salvia);
}
.remedio-accent.proximo { background: var(--ambar); }
.remedio-accent.perdido { background: rgba(91, 147, 199, 0.6); }
.remedio-accent.pendente { background: var(--verde-salvia); }

/* Hora box */
.remedio-hora-box {
  width: 52px;
  height: 42px;
  border-radius: 10px;
  background: rgba(127, 168, 50, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin: 12px 0 12px 12px;
}
.remedio-hora-box.proximo { background: rgba(212, 160, 60, 0.12); }
.remedio-hora-box.perdido { background: rgba(91, 147, 199, 0.1); }
.remedio-hora-box.pendente { background: rgba(127, 168, 50, 0.1); }

.remedio-hora {
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 700;
  color: var(--verde-salvia);
}
.remedio-hora-box.proximo .remedio-hora { color: var(--ambar); }
.remedio-hora-box.perdido .remedio-hora { color: #5B93C7; }

/* Info */
.remedio-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 0;
  padding: 12px;
}

.remedio-nome-row {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.remedio-nome {
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  color: var(--texto);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ciclo-badge {
  font-family: var(--font-corpo);
  font-size: 10px;
  font-weight: 700;
  color: var(--verde-salvia);
  background: rgba(127, 168, 50, 0.12);
  border-radius: 20px;
  padding: 1px 7px;
  white-space: nowrap;
  flex-shrink: 0;
}

.remedio-dose {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
}

/* Status icon */
.remedio-status-icon {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-right: 12px;
  color: rgba(0,0,0,0.2);
  background: rgba(0,0,0,0.03);
}
.remedio-status-icon.proximo {
  color: var(--ambar);
  background: rgba(212, 160, 60, 0.12);
  animation: pulseAmbar 2s ease infinite;
}
.remedio-status-icon.perdido {
  color: #5B93C7;
  background: rgba(91, 147, 199, 0.1);
}

@keyframes pulseAmbar {
  0%, 100% { box-shadow: 0 0 0 0 rgba(212, 160, 60, 0.3); }
  50% { box-shadow: 0 0 0 5px rgba(212, 160, 60, 0); }
}

/* Bell ring animation — ativa somente quando há avisos não lidos */
@keyframes bell-ring {
  0%, 75%, 100% { transform: rotate(0); }
  78%  { transform: rotate(18deg); }
  82%  { transform: rotate(-15deg); }
  86%  { transform: rotate(12deg); }
  90%  { transform: rotate(-8deg); }
  93%  { transform: rotate(5deg); }
  96%  { transform: rotate(-2deg); }
}

.bell-ring {
  transform-origin: top center;
  animation: bell-ring 3.5s ease infinite;
}

/* ── Desktop ── */
@media (min-width: 769px) {
  .home-page {
    padding-bottom: 0;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    height: 100%;
  }

  /* Cabeçalho desktop */
  .desktop-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 28px 40px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.07);
    background: transparent;
    flex-shrink: 0;
  }

  .dph-text {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .dph-title {
    font-family: var(--font-titulo);
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--texto);
    margin: 0;
    line-height: 1.2;
  }

  .dph-subtitle {
    font-family: var(--font-corpo);
    font-size: 13px;
    color: var(--texto-light);
    margin: 0;
  }

  .dph-alerts-btn {
    position: relative;
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 9px 16px;
    border-radius: 10px;
    background: rgba(0, 0, 0, 0.05);
    color: var(--texto-light);
    font-family: var(--font-corpo);
    font-size: 13px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: background 0.2s var(--ease-smooth);
  }

  .dph-alerts-btn:hover {
    background: rgba(0, 0, 0, 0.08);
  }

  .dph-alerts-btn.has-alerts {
    background: rgba(229, 115, 115, 0.1);
    color: var(--terracota);
  }

  .dph-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    background: var(--terracota);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 5px;
  }

  /* Esconder hero (mobile only) */
  .hero {
    display: none;
  }

  .stats-row {
    padding: 24px 40px 0;
    gap: 16px;
  }

  .stat-card {
    padding: 20px 24px;
    border-radius: 16px;
    background: transparent;
    box-shadow: none;
    border: 1px solid rgba(0, 0, 0, 0.08);
  }

  .stat-number {
    font-size: 28px;
  }

  .stat-label {
    font-size: 11px;
  }

  .remedios-section {
    padding: 24px 40px 40px;
    flex: 1;
  }

  .section-title {
    font-size: 13px;
    margin-bottom: 16px;
  }

  .remedios-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 12px;
  }

  .remedio-card {
    cursor: default;
    background: transparent;
    box-shadow: none;
    border: 1px solid rgba(0, 0, 0, 0.08);
    transition: border-color 0.2s var(--ease-smooth);
  }

  .remedio-card:hover {
    border-color: var(--verde-salvia);
    transform: none;
  }

  .remedio-nome {
    font-size: 15px;
  }
}
</style>
