<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useMedicamentosStore } from '../stores/medicamentos'
import { useAuthStore } from '../stores/auth'
import { useNotificacoesStore } from '../stores/notificacoes'
import { useAvisosStore } from '../stores/avisos'
import { useCrisesStore } from '../stores/crises'
import { useConsultasStore } from '../stores/consultas'
import { useExamesStore } from '../stores/exames'
import LoadingDots from '../components/LoadingDots.vue'
import {
  Chart as ChartJS,
  CategoryScale, LinearScale, BarElement, LineElement,
  PointElement, ArcElement, Title, Tooltip, Legend, Filler
} from 'chart.js'
import { Bar, Line, Doughnut } from 'vue-chartjs'

ChartJS.register(
  CategoryScale, LinearScale, BarElement, LineElement,
  PointElement, ArcElement, Title, Tooltip, Legend, Filler
)

const router = useRouter()
const medStore = useMedicamentosStore()
const authStore = useAuthStore()
const notifStore = useNotificacoesStore()
const avisosStore = useAvisosStore()
const crisesStore = useCrisesStore()
const consultasStore = useConsultasStore()
const examesStore = useExamesStore()

const loading = ref(true)
const naoLidosCount = computed(() => avisosStore.naoLidosCount)

function abrirAvisos() { router.push('/avisos') }

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
  dicasSaude.splice(0, dicasSaude.length, ...[...dicasSaude].sort(() => Math.random() - 0.5))
}
shuffleDicas()

onMounted(async () => {
  try {
    await Promise.all([
      medStore.fetchAll(),
      crisesStore.fetchAll(),
      consultasStore.fetchAll(),
      examesStore.fetchAll(),
    ])
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
  pollingInterval = setInterval(() => avisosStore.fetchNaoLidos(), 30000)
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
  const h = new Date().getHours()
  if (h < 12) return 'Bom dia'
  if (h < 18) return 'Boa tarde'
  return 'Boa noite'
})

const proximoAlarme = computed(() => medStore.proximosHorarios.filter(h => !h.passado)[0] || null)
const proximosRemedios = computed(() => medStore.proximosHorarios.slice(0, 8))

function statusRemedio(item) {
  if (item.passado) return 'perdido'
  const agora = new Date()
  const diff = item.minutos - (agora.getHours() * 60 + agora.getMinutes())
  return (diff >= 0 && diff <= 30) ? 'proximo' : 'pendente'
}

const alertasEstoque = computed(() => medStore.comAlerta)
const totalRemediosHoje = computed(() => medStore.proximosHorarios.length)
const remediosTomados = computed(() => medStore.proximosHorarios.filter(h => h.passado).length)

// ── Crises: últimos 30 dias ──────────────────────────────────
const crises30dias = computed(() => {
  const hoje = new Date()
  const limite = new Date(hoje); limite.setDate(limite.getDate() - 29)
  return crisesStore.lista.filter(c => {
    const d = new Date(c.data_hora)
    return d >= limite && d <= hoje
  })
})

const crisesEsteMes = computed(() => {
  const agora = new Date()
  return crisesStore.lista.filter(c => {
    const d = new Date(c.data_hora)
    return d.getMonth() === agora.getMonth() && d.getFullYear() === agora.getFullYear()
  }).length
})

// Gráfico: intensidade de crises nos últimos 14 dias
const crisesMapeadas = computed(() => {
  const dias = 14
  const labels = []
  const data = []
  const hoje = new Date()
  for (let i = dias - 1; i >= 0; i--) {
    const d = new Date(hoje)
    d.setDate(d.getDate() - i)
    const label = d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' })
    labels.push(label)
    const chave = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`
    const criseDia = crisesStore.lista.filter(c => c.data_hora?.startsWith(chave))
    const maxIntensidade = criseDia.length > 0 ? Math.max(...criseDia.map(c => c.intensidade || 0)) : 0
    data.push(maxIntensidade)
  }
  return { labels, data }
})

const chartCrisesData = computed(() => ({
  labels: crisesMapeadas.value.labels,
  datasets: [{
    label: 'Intensidade',
    data: crisesMapeadas.value.data,
    backgroundColor: crisesMapeadas.value.data.map(v =>
      v >= 4 ? 'rgba(229,115,115,0.7)' : v >= 2 ? 'rgba(212,160,60,0.65)' : 'rgba(127,168,50,0.55)'
    ),
    borderRadius: 6,
    borderSkipped: false,
  }]
}))

const chartCrisesOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label: ctx => ctx.parsed.y === 0 ? 'Sem crise' : `Intensidade ${ctx.parsed.y}`
      }
    }
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { font: { size: 10 }, maxTicksLimit: 7 }
    },
    y: {
      min: 0, max: 5,
      grid: { color: 'rgba(0,0,0,0.04)' },
      ticks: { stepSize: 1, font: { size: 10 } }
    }
  }
}

// Gráfico: aderência (donut)
const chartAderenciaData = computed(() => {
  const total = totalRemediosHoje.value
  const tomados = remediosTomados.value
  const restantes = Math.max(0, total - tomados)
  return {
    labels: ['Tomados', 'Pendentes'],
    datasets: [{
      data: total === 0 ? [1, 0] : [tomados, restantes],
      backgroundColor: ['#7FA832', 'rgba(0,0,0,0.07)'],
      borderWidth: 0,
      hoverOffset: 4
    }]
  }
})

const chartAderenciaOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '72%',
  plugins: {
    legend: { display: false },
    tooltip: { enabled: totalRemediosHoje.value > 0 }
  }
}

const aderenciaPct = computed(() => {
  if (totalRemediosHoje.value === 0) return 100
  return Math.round((remediosTomados.value / totalRemediosHoje.value) * 100)
})

// Gráfico: sintomas mais frequentes (barras horizontais)
const topSintomas = computed(() => {
  const contagem = {}
  crisesStore.lista.forEach(c => {
    if (!c.sintomas) return
    c.sintomas.split(',').forEach(s => {
      const t = s.trim()
      if (t) contagem[t] = (contagem[t] || 0) + 1
    })
  })
  return Object.entries(contagem)
    .sort((a, b) => b[1] - a[1])
    .slice(0, 6)
})

const chartSintomasData = computed(() => ({
  labels: topSintomas.value.map(([s]) => s),
  datasets: [{
    label: 'Ocorrências',
    data: topSintomas.value.map(([, n]) => n),
    backgroundColor: 'rgba(127,168,50,0.6)',
    borderRadius: 6,
    borderSkipped: false,
  }]
}))

const chartSintomasOptions = {
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: 'y',
  plugins: { legend: { display: false } },
  scales: {
    x: {
      grid: { color: 'rgba(0,0,0,0.04)' },
      ticks: { font: { size: 10 }, stepSize: 1 }
    },
    y: {
      grid: { display: false },
      ticks: { font: { size: 11 } }
    }
  }
}

// Próxima consulta/exame
const proximaConsulta = computed(() => {
  const agora = new Date()
  const proximas = consultasStore.proximas
    ?.filter(c => new Date(c.data_hora) > agora)
    ?.sort((a, b) => new Date(a.data_hora) - new Date(b.data_hora))
  return proximas?.[0] || null
})

const proximoExame = computed(() => {
  const agora = new Date()
  const proximos = examesStore.proximos
    ?.filter(e => new Date(e.data) > agora)
    ?.sort((a, b) => new Date(a.data) - new Date(b.data))
  return proximos?.[0] || null
})

function formatarProxima(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  const hoje = new Date()
  const diff = Math.ceil((d - hoje) / 86400000)
  if (diff === 0) return 'Hoje'
  if (diff === 1) return 'Amanhã'
  if (diff < 7) return `Em ${diff} dias`
  return d.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' })
}
</script>

<template>
  <main class="home-page">
    <!-- Cabeçalho desktop -->
    <div class="desktop-page-header">
      <div class="dph-text">
        <h1 class="dph-title">{{ saudacao }}, {{ firstName }}</h1>
        <p class="dph-subtitle">Resumo do seu cuidado de saúde</p>
      </div>
    </div>

    <!-- Hero (mobile only) -->
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
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" :class="{ 'bell-ring': naoLidosCount > 0 }">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span v-if="naoLidosCount > 0" class="alerts-badge">{{ naoLidosCount }}</span>
          </button>
        </div>
        <div v-if="!loading && proximoAlarme" class="proximo-card">
          <div class="proximo-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
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

    <LoadingDots v-if="loading" />

    <template v-else>
      <!-- Stats Row -->
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-icon green">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <rect x="6" y="2" width="12" height="20" rx="6" stroke="currentColor" stroke-width="1.8"/>
              <line x1="6" y1="12" x2="18" y2="12" stroke="currentColor" stroke-width="1.8"/>
            </svg>
          </div>
          <span class="stat-number">{{ remediosTomados }}/{{ totalRemediosHoje }}</span>
          <span class="stat-label">Doses hoje</span>
        </div>

        <div class="stat-card" :class="{ 'stat-alert': alertasEstoque.length > 0 }">
          <div class="stat-icon" :class="alertasEstoque.length > 0 ? 'red' : 'green'">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
              <path d="M12 9v4M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <span class="stat-number">{{ alertasEstoque.length }}</span>
          <span class="stat-label">{{ alertasEstoque.length === 1 ? 'Alerta estoque' : 'Alertas estoque' }}</span>
        </div>

        <div class="stat-card" :class="{ 'stat-alert': crisesEsteMes >= 3 }">
          <div class="stat-icon" :class="crisesEsteMes >= 3 ? 'amber' : 'green'">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M22 12h-4l-3 9L9 3l-3 9H2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <span class="stat-number">{{ crisesEsteMes }}</span>
          <span class="stat-label">Crises no mês</span>
        </div>

        <div class="stat-card">
          <div class="stat-icon blue">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/>
              <path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
          </div>
          <span class="stat-number stat-small">
            {{ proximaConsulta ? formatarProxima(proximaConsulta.data_hora) : proximoExame ? formatarProxima(proximoExame.data) : '—' }}
          </span>
          <span class="stat-label">Próx. consulta/exame</span>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="charts-row">
        <!-- Gráfico crises 14 dias -->
        <div class="chart-card chart-large">
          <div class="chart-header">
            <div>
              <span class="chart-title">Intensidade de crises</span>
              <span class="chart-sub">Últimos 14 dias</span>
            </div>
            <span class="chart-badge" :class="crisesEsteMes === 0 ? 'good' : crisesEsteMes < 3 ? 'warn' : 'bad'">
              {{ crises30dias.length }} registros
            </span>
          </div>
          <div class="chart-wrap">
            <Bar :data="chartCrisesData" :options="chartCrisesOptions" />
          </div>
          <div class="chart-legend">
            <span class="legend-dot green"></span><span>Leve (1–2)</span>
            <span class="legend-dot amber"></span><span>Moderada (3)</span>
            <span class="legend-dot red"></span><span>Intensa (4–5)</span>
          </div>
        </div>

        <!-- Aderência -->
        <div class="chart-card chart-small">
          <div class="chart-header">
            <div>
              <span class="chart-title">Aderência</span>
              <span class="chart-sub">Doses de hoje</span>
            </div>
          </div>
          <div class="chart-wrap donut-wrap">
            <Doughnut :data="chartAderenciaData" :options="chartAderenciaOptions" />
            <div class="donut-center">
              <span class="donut-pct">{{ aderenciaPct }}%</span>
              <span class="donut-lbl">tomado</span>
            </div>
          </div>
          <div class="aderencia-detail">
            <span class="ad-item">
              <span class="ad-dot green"></span>
              {{ remediosTomados }} tomado{{ remediosTomados !== 1 ? 's' : '' }}
            </span>
            <span class="ad-item">
              <span class="ad-dot gray"></span>
              {{ Math.max(0, totalRemediosHoje - remediosTomados) }} pendente{{ (totalRemediosHoje - remediosTomados) !== 1 ? 's' : '' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Sintomas + Remédios Row -->
      <div class="bottom-row">
        <!-- Sintomas frequentes -->
        <div class="chart-card">
          <div class="chart-header">
            <div>
              <span class="chart-title">Sintomas frequentes</span>
              <span class="chart-sub">Registros de crises</span>
            </div>
          </div>
          <div v-if="topSintomas.length === 0" class="chart-empty">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
              <path d="M22 12h-4l-3 9L9 3l-3 9H2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Nenhum sintoma registrado ainda</span>
          </div>
          <div v-else class="chart-wrap sintomas-wrap">
            <Bar :data="chartSintomasData" :options="chartSintomasOptions" />
          </div>
        </div>

        <!-- Remédios de hoje -->
        <div class="chart-card remedios-card">
          <div class="chart-header">
            <div>
              <span class="chart-title">Remédios de hoje</span>
              <span class="chart-sub">Horários programados</span>
            </div>
          </div>
          <div v-if="proximosRemedios.length === 0" class="chart-empty">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
              <rect x="6" y="2" width="12" height="20" rx="6" stroke="currentColor" stroke-width="1.5"/>
              <line x1="6" y1="12" x2="18" y2="12" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <span>Nenhum horário cadastrado</span>
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
                <svg v-if="item.passado" width="14" height="14" viewBox="0 0 24 24" fill="none">
                  <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
                <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none">
                  <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                  <path d="M12 7v5l3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </main>
</template>

<style scoped>
.home-page {
  padding-bottom: calc(80px + env(safe-area-inset-bottom, 0px));
  height: 100dvh;
  overflow-y: auto;
}

.desktop-page-header { display: none; }

/* ── Hero (mobile) ── */
.hero {
  position: relative;
  padding: 28px 20px 20px;
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
  width: 44px; min-width: 44px; height: 44px;
  border-radius: 14px;
  background: rgba(255,255,255,0.2);
  color: #fff;
  font-family: var(--font-titulo);
  font-size: 18px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.hero-text { display: flex; flex-direction: column; gap: 2px; }
.hero-greeting { font-family: var(--font-titulo); font-size: 18px; font-weight: 700; color: #fff; }
.hero-dica { overflow: hidden; min-height: 16px; }
.hero-dica-texto { font-family: var(--font-corpo); font-size: 12px; color: rgba(255,255,255,0.7); line-height: 1.4; display: block; }
.hero-alerts-btn {
  position: relative; width: 40px; height: 40px; border-radius: 12px;
  background: rgba(255,255,255,0.15); border: none; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  color: rgba(255,255,255,0.85); flex-shrink: 0; margin-left: auto;
}
.hero-alerts-btn.has-alerts { color: #fff; }
.alerts-badge {
  position: absolute; top: -3px; right: -3px;
  min-width: 18px; height: 18px; border-radius: 9px;
  background: var(--terracota); color: #fff;
  font-size: 10px; font-weight: 700;
  display: flex; align-items: center; justify-content: center; padding: 0 5px;
}
.proximo-card {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 14px;
  background: rgba(255,255,255,0.15);
  border: 1px solid rgba(255,255,255,0.2); border-radius: 14px;
}
.proximo-icon { color: #fff; display: flex; align-items: center; flex-shrink: 0; opacity: 0.9; }
.proximo-info { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.proximo-label { font-family: var(--font-corpo); font-size: 11px; font-weight: 600; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.3px; }
.proximo-detail { font-family: var(--font-corpo); font-size: 14px; color: #fff; }
.proximo-detail strong { font-weight: 700; }

/* ── Stats ── */
.stats-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  padding: 16px 16px 0;
}
.stat-card {
  background: #fff;
  border-radius: 14px;
  padding: 14px 12px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.06);
  display: flex; flex-direction: column; gap: 6px;
  animation: fadeInUp 0.5s var(--ease-out-smooth) both;
}
.stat-card:nth-child(2) { animation-delay: 0.05s; }
.stat-card:nth-child(3) { animation-delay: 0.1s; }
.stat-card:nth-child(4) { animation-delay: 0.15s; }
.stat-alert { border: 1.5px solid rgba(196,120,74,0.3); }
.stat-icon {
  width: 32px; height: 32px; border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
}
.stat-icon.green { background: rgba(127,168,50,0.12); color: var(--verde-salvia); }
.stat-icon.red { background: rgba(229,115,115,0.12); color: var(--terracota); }
.stat-icon.amber { background: rgba(212,160,60,0.12); color: var(--ambar); }
.stat-icon.blue { background: rgba(74,144,196,0.12); color: #4a90c4; }
.stat-number { font-family: var(--font-titulo); font-size: 22px; font-weight: 700; color: var(--verde-salvia); line-height: 1; }
.stat-number.stat-small { font-size: 16px; }
.stat-alert .stat-number { color: var(--terracota); }
.stat-label { font-family: var(--font-corpo); font-size: 11px; font-weight: 600; color: var(--texto-light); text-transform: uppercase; letter-spacing: 0.2px; }

/* ── Chart cards ── */
.charts-row, .bottom-row {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 16px 16px 0;
}
.chart-card {
  background: #fff;
  border-radius: 16px;
  padding: 18px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.05);
  animation: fadeInUp 0.5s var(--ease-out-smooth) both;
}
.chart-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 16px;
}
.chart-title {
  display: block;
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 700;
  color: var(--texto);
}
.chart-sub {
  display: block;
  font-family: var(--font-corpo);
  font-size: 11px;
  color: var(--texto-light);
  margin-top: 2px;
}
.chart-badge {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 20px;
  flex-shrink: 0;
}
.chart-badge.good { background: rgba(127,168,50,0.1); color: var(--verde-salvia); }
.chart-badge.warn { background: rgba(212,160,60,0.1); color: var(--ambar); }
.chart-badge.bad  { background: rgba(229,115,115,0.1); color: var(--terracota); }

.chart-wrap { height: 160px; position: relative; }
.sintomas-wrap { height: 180px; }

.chart-legend {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 10px;
  flex-wrap: wrap;
}
.chart-legend span {
  font-family: var(--font-corpo);
  font-size: 11px;
  color: var(--texto-light);
}
.legend-dot {
  width: 8px; height: 8px; border-radius: 50%;
  display: inline-block;
}
.legend-dot.green { background: rgba(127,168,50,0.65); }
.legend-dot.amber { background: rgba(212,160,60,0.65); }
.legend-dot.red   { background: rgba(229,115,115,0.7); }

.chart-empty {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 8px; padding: 32px 0;
  color: #ccc; font-family: var(--font-corpo); font-size: 13px;
}

/* Donut */
.donut-wrap { position: relative; display: flex; align-items: center; justify-content: center; }
.donut-center {
  position: absolute;
  display: flex; flex-direction: column; align-items: center;
}
.donut-pct { font-family: var(--font-titulo); font-size: 22px; font-weight: 700; color: var(--verde-salvia); line-height: 1; }
.donut-lbl { font-family: var(--font-corpo); font-size: 11px; color: var(--texto-light); margin-top: 2px; }
.aderencia-detail {
  display: flex; justify-content: center; gap: 20px; margin-top: 12px;
}
.ad-item {
  display: flex; align-items: center; gap: 5px;
  font-family: var(--font-corpo); font-size: 12px; color: var(--texto-light);
}
.ad-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.ad-dot.green { background: #7FA832; }
.ad-dot.gray  { background: rgba(0,0,0,0.1); }

/* ── Remédios ── */
.remedios-list { display: flex; flex-direction: column; gap: 8px; }
.remedio-card {
  display: flex; align-items: center;
  background: var(--fundo, #FAF8F5);
  border-radius: 12px; overflow: hidden;
  animation: fadeInUp 0.4s var(--ease-out-smooth) both;
  transition: opacity 0.3s;
}
.remedio-card.passado { opacity: 0.45; }
.remedio-accent { width: 3px; align-self: stretch; flex-shrink: 0; background: var(--verde-salvia); }
.remedio-accent.proximo { background: var(--ambar); }
.remedio-accent.perdido { background: rgba(91,147,199,0.6); }
.remedio-hora-box {
  width: 48px; height: 38px; border-radius: 8px;
  background: rgba(127,168,50,0.1);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; margin: 10px 0 10px 10px;
}
.remedio-hora-box.proximo { background: rgba(212,160,60,0.12); }
.remedio-hora-box.perdido { background: rgba(91,147,199,0.1); }
.remedio-hora { font-family: var(--font-corpo); font-size: 12px; font-weight: 700; color: var(--verde-salvia); }
.remedio-hora-box.proximo .remedio-hora { color: var(--ambar); }
.remedio-hora-box.perdido .remedio-hora { color: #5B93C7; }
.remedio-info { flex: 1; display: flex; flex-direction: column; gap: 2px; min-width: 0; padding: 10px; }
.remedio-nome-row { display: flex; align-items: center; gap: 6px; }
.remedio-nome { font-family: var(--font-corpo); font-size: 13px; font-weight: 600; color: var(--texto); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ciclo-badge { font-family: var(--font-corpo); font-size: 10px; font-weight: 700; color: var(--verde-salvia); background: rgba(127,168,50,0.12); border-radius: 20px; padding: 1px 7px; white-space: nowrap; flex-shrink: 0; }
.remedio-dose { font-family: var(--font-corpo); font-size: 11px; color: var(--texto-light); }
.remedio-status-icon {
  width: 28px; height: 28px; border-radius: 7px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; margin-right: 10px;
  color: rgba(0,0,0,0.2); background: rgba(0,0,0,0.03);
}
.remedio-status-icon.proximo { color: var(--ambar); background: rgba(212,160,60,0.12); animation: pulseAmbar 2s ease infinite; }
.remedio-status-icon.perdido { color: #5B93C7; background: rgba(91,147,199,0.1); }
.remedio-status-icon.passado { color: var(--verde-salvia); background: rgba(127,168,50,0.1); }

/* Animations */
@keyframes pulseAmbar {
  0%, 100% { box-shadow: 0 0 0 0 rgba(212,160,60,0.3); }
  50% { box-shadow: 0 0 0 5px rgba(212,160,60,0); }
}
@keyframes bell-ring {
  0%,75%,100% { transform: rotate(0); }
  78% { transform: rotate(18deg); }
  82% { transform: rotate(-15deg); }
  86% { transform: rotate(12deg); }
  90% { transform: rotate(-8deg); }
  93% { transform: rotate(5deg); }
  96% { transform: rotate(-2deg); }
}
.bell-ring { transform-origin: top center; animation: bell-ring 3.5s ease infinite; }
.dica-enter-active, .dica-leave-active { transition: all 0.4s var(--ease-smooth); }
.dica-enter-from { opacity: 0; transform: translateY(6px); }
.dica-leave-to { opacity: 0; transform: translateY(-6px); }

/* ── Desktop ── */
@media (min-width: 769px) {
  .home-page {
    padding-bottom: 0;
    height: 100%;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
  }

  .desktop-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 28px 40px 20px;
    border-bottom: 1px solid rgba(0,0,0,0.07);
    flex-shrink: 0;
  }
  .dph-text { display: flex; flex-direction: column; gap: 4px; }
  .dph-title { font-family: var(--font-titulo); font-size: 1.6rem; font-weight: 700; color: var(--texto); margin: 0; }
  .dph-subtitle { font-family: var(--font-corpo); font-size: 13px; color: var(--texto-light); margin: 0; }

  .hero { display: none; }

  .stats-row {
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    padding: 28px 40px 0;
  }
  .stat-card {
    padding: 20px 20px;
    border-radius: 16px;
    background: transparent;
    box-shadow: none;
    border: 1px solid rgba(0,0,0,0.08);
  }
  .stat-number { font-size: 28px; }

  .charts-row {
    flex-direction: row;
    padding: 20px 40px 0;
    gap: 16px;
  }
  .chart-large { flex: 2; }
  .chart-small { flex: 1; }
  .chart-wrap { height: 180px; }
  .donut-wrap { height: 160px; }

  .bottom-row {
    flex-direction: row;
    padding: 16px 40px 40px;
    gap: 16px;
  }
  .bottom-row .chart-card { flex: 1; }
  .sintomas-wrap { height: 220px; }

  .remedios-card { overflow-y: auto; max-height: 340px; }
  .remedios-list { gap: 6px; }

  .chart-card {
    background: transparent;
    box-shadow: none;
    border: 1px solid rgba(0,0,0,0.08);
  }
  .remedio-card { background: rgba(0,0,0,0.02); }
}
</style>
