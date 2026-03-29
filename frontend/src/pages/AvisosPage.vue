<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import Pagination from '../components/Pagination.vue'
import { useRouter } from 'vue-router'
import { useAvisosStore } from '../stores/avisos'
import AppBar from '../components/AppBar.vue'
import ModalBase from '../components/ModalBase.vue'
import LoadingDots from '../components/LoadingDots.vue'

const router = useRouter()
const store = useAvisosStore()
const selectedAviso = ref(null)
const showDetail = ref(false)
const activeTab = ref('novos')

const avisosNovos = computed(() => store.avisos.filter(a => !a.lido))
const avisosLidos = computed(() => store.avisos.filter(a => a.lido))

const PER_PAGE = 5
const pageNovos = ref(1)
const pageLidos = ref(1)
const paginatedNovos = computed(() => avisosNovos.value.slice((pageNovos.value - 1) * PER_PAGE, pageNovos.value * PER_PAGE))
const paginatedLidos = computed(() => avisosLidos.value.slice((pageLidos.value - 1) * PER_PAGE, pageLidos.value * PER_PAGE))

watch(activeTab, () => {
  pageNovos.value = 1
  pageLidos.value = 1
})

onMounted(() => {
  store.fetchAll()
})

function voltar() {
  router.push('/')
}

function formatarData(dateStr) {
  const d = new Date(dateStr)
  const agora = new Date()
  const diff = agora - d
  const minutos = Math.floor(diff / 60000)
  const horas = Math.floor(diff / 3600000)
  const dias = Math.floor(diff / 86400000)

  if (minutos < 1) return 'Agora'
  if (minutos < 60) return `${minutos}min atrás`
  if (horas < 24) return `${horas}h atrás`
  if (dias < 7) return `${dias}d atrás`
  return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' })
}

function formatarDataCompleta(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleDateString('pt-BR', { day: '2-digit', month: 'long', year: 'numeric' }) +
    ' às ' + d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

async function abrirAviso(aviso) {
  if (!aviso.lido) {
    await store.marcarLido(aviso.id)
  }
  selectedAviso.value = aviso
  showDetail.value = true
}
</script>

<template>
  <div class="avisos-page">
    <AppBar title="Avisos" subtitle="Notificações de lembretes">
      <button class="back-btn" @click="voltar">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M19 12H5M5 12l6-6M5 12l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </AppBar>

    <div class="desktop-page-header">
      <div class="dph-text">
        <h1 class="dph-title">Avisos</h1>
        <p class="dph-subtitle">Notificações e lembretes do sistema</p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button class="tab-btn" :class="{ active: activeTab === 'novos' }" @click="activeTab = 'novos'">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        Novos
        <span v-if="avisosNovos.length > 0" class="tab-count">{{ avisosNovos.length }}</span>
      </button>
      <button class="tab-btn" :class="{ active: activeTab === 'lidos' }" @click="activeTab = 'lidos'">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Lidos
        <span v-if="avisosLidos.length > 0" class="tab-count tab-count-lido">{{ avisosLidos.length }}</span>
      </button>
    </div>

    <div class="page-content">
      <!-- Loading -->
      <LoadingDots v-if="store.loading" />

      <!-- Aba: Novos -->
      <template v-else-if="activeTab === 'novos'">
        <div v-if="avisosNovos.length === 0" class="empty-state">
          <svg class="empty-icon" width="40" height="40" viewBox="0 0 24 24" fill="none">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <p>Nenhum aviso novo</p>
          <p class="empty-hint">Você está em dia!</p>
        </div>
        <div v-else class="avisos-list" data-tour="avisos-list">
          <button class="mark-all-btn" @click="store.marcarTodosLidos">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
              <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Marcar tudo como lido
          </button>
          <div
            v-for="(aviso, i) in paginatedNovos"
            :key="aviso.id"
            class="aviso-card nao-lido"
            :style="{ animationDelay: i * 0.04 + 's' }"
            @click="abrirAviso(aviso)"
          >
            <div class="aviso-dot"></div>
            <div class="aviso-icon-box">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div class="aviso-info">
              <span class="aviso-titulo">{{ aviso.titulo }}</span>
              <span class="aviso-mensagem">{{ aviso.mensagem }}</span>
            </div>
            <span class="aviso-tempo">{{ formatarData(aviso.created_at) }}</span>
          </div>
        </div>
      </template>

      <!-- Aba: Lidos -->
      <template v-else-if="activeTab === 'lidos'">
        <div v-if="avisosLidos.length === 0" class="empty-state">
          <svg class="empty-icon" width="40" height="40" viewBox="0 0 24 24" fill="none">
            <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <p>Nenhum aviso lido</p>
          <p class="empty-hint">Os avisos lidos aparecerão aqui</p>
        </div>
        <div v-else class="avisos-list">
          <div
            v-for="(aviso, i) in paginatedLidos"
            :key="aviso.id"
            class="aviso-card lido"
            :style="{ animationDelay: i * 0.04 + 's' }"
            @click="abrirAviso(aviso)"
          >
            <div class="aviso-icon-box lido-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div class="aviso-info">
              <span class="aviso-titulo">{{ aviso.titulo }}</span>
              <span class="aviso-mensagem">{{ aviso.mensagem }}</span>
            </div>
            <span class="aviso-tempo">{{ formatarData(aviso.created_at) }}</span>
          </div>
        </div>
      </template>
    </div>

    <div class="pagination-footer">
      <Pagination
        v-if="activeTab === 'novos'"
        :total="avisosNovos.length"
        :per-page="PER_PAGE"
        v-model="pageNovos"
      />
      <Pagination
        v-else
        :total="avisosLidos.length"
        :per-page="PER_PAGE"
        v-model="pageLidos"
      />
    </div>

    <!-- Modal Detalhe -->
    <ModalBase v-model="showDetail" title="">
      <template v-if="selectedAviso" #header>
        <div class="dw-hero">
          <div class="dw-icon-box">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="dw-hero-body">
            <span class="dw-badge">Lido</span>
            <h3 class="dw-title">{{ selectedAviso.titulo }}</h3>
            <span class="dw-sub">{{ formatarDataCompleta(selectedAviso.created_at) }}</span>
          </div>
        </div>
      </template>
      <div v-if="selectedAviso" class="detail-web">
        <div class="dw-obs">
          <p>{{ selectedAviso.mensagem }}</p>
        </div>
      </div>
    </ModalBase>
  </div>
</template>

<style scoped>
.avisos-page {
  height: 100dvh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.back-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  order: -1;
}

/* Tabs */
.tabs {
  display: flex;
  gap: 4px;
  background: rgba(76, 175, 80, 0.1);
  border-radius: 12px;
  padding: 4px;
  margin: 0 16px 12px;
}

.tab-btn {
  flex: 1;
  padding: 10px 8px;
  border: none;
  border-radius: 10px;
  background: transparent;
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: var(--texto-light);
  cursor: pointer;
  transition: all 0.35s var(--ease-smooth);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.tab-btn.active {
  background: #fff;
  color: var(--verde-salvia);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
}

.tab-count {
  background: var(--verde-salvia);
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  min-width: 18px;
  height: 18px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 5px;
}

.tab-count-lido {
  background: var(--texto-light);
  opacity: 0.6;
}

/* Content */
.page-content {
  flex: 1;
  overflow-y: auto;
  padding: 0 16px 24px;
}

.pagination-footer {
  flex-shrink: 0;
  background: var(--verde-bg);
  border-top: 1px solid rgba(0,0,0,0.05);
  padding: 8px 16px calc(8px + 64px + env(safe-area-inset-bottom, 0px));
}

@media (min-width: 769px) {
  .pagination-footer {
    padding: 10px 40px;
    border-top: 1px solid rgba(0,0,0,0.07);
  }
}

/* Empty */
.empty-state {
  text-align: center;
  padding: 48px 16px;
  min-height: 50vh;
  color: var(--texto-light);
  font-family: var(--font-corpo);
  display: flex;
  flex-direction: column;
  justify-content: center;
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

/* Mark all btn */
.mark-all-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: var(--verde-salvia);
  background: rgba(127, 168, 50, 0.08);
  padding: 10px 16px;
  border-radius: 10px;
  border: 1px solid rgba(127, 168, 50, 0.15);
  cursor: pointer;
  width: 100%;
  transition: background 0.2s;
  margin-bottom: 4px;
}

.mark-all-btn:active {
  background: rgba(127, 168, 50, 0.16);
}

/* List */
.avisos-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-top: 8px;
}

/* Card */
.aviso-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #fff;
  border-radius: 14px;
  padding: 14px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  cursor: pointer;
  transition: all 0.2s ease;
  animation: fadeInUp 0.4s var(--ease-out-smooth) both;
  position: relative;
}

.aviso-card:active {
  transform: scale(0.98);
}

.aviso-card.nao-lido {
  border: 1px solid rgba(127, 168, 50, 0.18);
  background: rgba(127, 168, 50, 0.03);
}

.aviso-card.lido {
  opacity: 0.55;
}

/* Ponto indicador de não lido */
.aviso-dot {
  position: absolute;
  top: 50%;
  left: 6px;
  transform: translateY(-50%);
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: var(--verde-salvia);
}

/* Ícone do card */
.aviso-icon-box {
  width: 44px;
  min-width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(127, 168, 50, 0.12);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--verde-salvia);
  flex-shrink: 0;
}

.aviso-icon-box.lido-icon {
  background: rgba(0, 0, 0, 0.04);
  color: #bbb;
}

/* Info */
.aviso-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 0;
}

.aviso-titulo {
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  color: var(--texto);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.aviso-mensagem {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.aviso-tempo {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 500;
  color: var(--verde-salvia);
  white-space: nowrap;
  flex-shrink: 0;
}

.aviso-card.lido .aviso-tempo {
  color: var(--texto-light);
}

/* ─── Desktop Header ─── */
.desktop-page-header {
  display: none;
}

/* ─── Detail Web ─── */
.detail-web {
  display: flex;
  flex-direction: column;
}

.dw-hero {
  display: flex;
  align-items: flex-start;
  gap: 16px;
}

.dw-icon-box {
  width: 56px;
  height: 56px;
  min-width: 56px;
  border-radius: 16px;
  background: rgba(127, 168, 50, 0.12);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--verde-salvia);
  flex-shrink: 0;
}

.dw-hero-body {
  display: flex;
  flex-direction: column;
  gap: 5px;
  min-width: 0;
  flex: 1;
}

.dw-badge {
  display: inline-flex;
  align-items: center;
  padding: 3px 10px;
  border-radius: 20px;
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  width: fit-content;
  background: rgba(127, 168, 50, 0.12);
  color: var(--verde-salvia);
}

.dw-title {
  font-family: var(--font-titulo);
  font-size: 1.25rem;
  color: var(--texto);
  margin: 0;
  line-height: 1.25;
  word-break: break-word;
}

.dw-sub {
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--texto-light);
}

.dw-obs {
  background: var(--fundo, #FAF8F5);
  border-radius: 12px;
  padding: 14px 16px;
  border-left: 3px solid var(--verde-salvia);
}

.dw-obs p {
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  line-height: 1.65;
  margin: 0;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (min-width: 769px) {
  .avisos-page {
    height: 100%;
    overflow: hidden;
  }

  .desktop-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px 40px 20px;
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

  /* Tabs estilo website */
  .tabs {
    background: none;
    border-radius: 0;
    padding: 0;
    margin: 0;
    border-bottom: 1.5px solid #eee;
    gap: 0;
    padding: 0 40px;
  }

  .tab-btn {
    flex: none;
    padding: 11px 20px;
    border-radius: 0;
    font-size: 14px;
    font-weight: 500;
    color: var(--texto-light);
    border-bottom: 2px solid transparent;
    margin-bottom: -1.5px;
    transition: color 0.15s, border-color 0.15s;
    justify-content: flex-start;
  }

  .tab-btn.active {
    background: none;
    box-shadow: none;
    color: var(--verde-salvia);
    border-bottom-color: var(--verde-salvia);
  }

  .tab-count {
    background: rgba(127, 168, 50, 0.15);
    color: var(--verde-salvia);
  }

  .page-content {
    padding: 24px 40px 40px;
  }

  .avisos-list {
    max-width: 720px;
  }

  .aviso-card {
    background: transparent;
    box-shadow: none;
    border: 1px solid rgba(0, 0, 0, 0.08);
    transition: border-color 0.2s var(--ease-smooth);
  }

  .aviso-card:hover {
    border-color: var(--verde-salvia);
    transform: translateY(-1px);
  }

  .aviso-card.nao-lido {
    border: 1px solid rgba(127, 168, 50, 0.2);
    background: rgba(127, 168, 50, 0.02);
  }
}
</style>
