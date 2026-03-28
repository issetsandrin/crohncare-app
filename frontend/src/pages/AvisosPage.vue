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

const PER_PAGE = 10
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

    <div class="page-content">
      <!-- Tabs -->
      <div class="tabs">
        <button class="tab-btn" :class="{ active: activeTab === 'novos' }" @click="activeTab = 'novos'">
          Novos
          <span v-if="avisosNovos.length > 0" class="tab-count">{{ avisosNovos.length }}</span>
        </button>
        <button class="tab-btn" :class="{ active: activeTab === 'lidos' }" @click="activeTab = 'lidos'">
          Lidos
          <span v-if="avisosLidos.length > 0" class="tab-count tab-count-lido">{{ avisosLidos.length }}</span>
        </button>
      </div>

      <!-- Loading -->
      <LoadingDots v-if="store.loading" />

      <!-- Aba: Novos -->
      <div v-else-if="activeTab === 'novos'">
        <div v-if="avisosNovos.length === 0" class="empty-state">
          <svg class="empty-icon" width="40" height="40" viewBox="0 0 24 24" fill="none">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <p>Nenhum aviso novo</p>
          <p class="empty-hint">Você está em dia!</p>
        </div>
        <div v-else class="avisos-list">
          <button class="mark-all-btn" @click="store.marcarTodosLidos">
            Marcar tudo como lido
          </button>
          <div
            v-for="aviso in paginatedNovos"
            :key="aviso.id"
            class="aviso-card nao-lido"
            @click="abrirAviso(aviso)"
          >
            <div class="aviso-indicator"></div>
            <div class="aviso-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div class="aviso-body">
              <span class="aviso-titulo">{{ aviso.titulo }}</span>
              <span class="aviso-mensagem">{{ aviso.mensagem }}</span>
            </div>
            <span class="aviso-tempo">{{ formatarData(aviso.created_at) }}</span>
          </div>
          <Pagination :total="avisosNovos.length" :per-page="PER_PAGE" v-model="pageNovos" />
        </div>
      </div>

      <!-- Aba: Lidos -->
      <div v-else-if="activeTab === 'lidos'">
        <div v-if="avisosLidos.length === 0" class="empty-state">
          <svg class="empty-icon" width="40" height="40" viewBox="0 0 24 24" fill="none">
            <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <p>Nenhum aviso lido</p>
          <p class="empty-hint">Os avisos lidos aparecerão aqui</p>
        </div>
        <div v-else class="avisos-list">
          <div
            v-for="aviso in paginatedLidos"
            :key="aviso.id"
            class="aviso-card lido"
            @click="abrirAviso(aviso)"
          >
            <div class="aviso-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div class="aviso-body">
              <span class="aviso-titulo">{{ aviso.titulo }}</span>
              <span class="aviso-mensagem">{{ aviso.mensagem }}</span>
            </div>
            <span class="aviso-tempo">{{ formatarData(aviso.created_at) }}</span>
          </div>
          <Pagination :total="avisosLidos.length" :per-page="PER_PAGE" v-model="pageLidos" />
        </div>
      </div>
    </div>

    <!-- Modal Detalhe -->
    <ModalBase v-model="showDetail" title="">
      <div v-if="selectedAviso" class="detail-content">
        <div class="detail-header">
          <div class="detail-icon-wrap">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <span class="detail-tempo">{{ formatarDataCompleta(selectedAviso.created_at) }}</span>
        </div>

        <h3 class="detail-titulo">{{ selectedAviso.titulo }}</h3>

        <div class="detail-mensagem-card">
          <p class="detail-mensagem">{{ selectedAviso.mensagem }}</p>
        </div>

        <div class="detail-status">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
            <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Lido</span>
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
  margin-bottom: 16px;
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

.mark-all-btn {
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: var(--verde-salvia);
  background: rgba(127, 168, 50, 0.1);
  padding: 10px 16px;
  border-radius: 10px;
  border: none;
  cursor: pointer;
  width: 100%;
  transition: background 0.3s var(--ease-smooth);
}

.mark-all-btn:active {
  background: rgba(127, 168, 50, 0.2);
}

.page-content {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
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

/* List */
.avisos-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.aviso-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #fff;
  border-radius: 14px;
  padding: 14px;
  padding-left: 18px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  position: relative;
  cursor: pointer;
  transition: all 0.3s var(--ease-smooth);
  animation: fadeInUp 0.4s var(--ease-out-smooth) both;
}

.aviso-card:active {
  transform: scale(0.98);
}

.aviso-card.nao-lido {
  background: rgba(127, 168, 50, 0.06);
  border: 1px solid rgba(127, 168, 50, 0.15);
}

.aviso-card.lido {
  opacity: 0.55;
}

.aviso-card.lido .aviso-icon {
  background: #f0f0f0;
  color: #bbb;
}

.aviso-card.lido .aviso-titulo {
  color: #999;
  font-weight: 500;
}

.aviso-card.lido .aviso-mensagem {
  color: #bbb;
}

.aviso-card.lido .aviso-tempo {
  color: #ccc;
}

.aviso-indicator {
  position: absolute;
  top: 50%;
  left: 7px;
  transform: translateY(-50%);
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: var(--verde-salvia);
}

.aviso-icon {
  width: 40px;
  height: 40px;
  min-width: 40px;
  border-radius: 12px;
  background: rgba(127, 168, 50, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--verde-salvia);
}

.nao-lido .aviso-icon {
  background: rgba(127, 168, 50, 0.18);
}

.aviso-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
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
  color: var(--texto-light);
  white-space: nowrap;
  flex-shrink: 0;
}

/* Detail modal */
.detail-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.detail-header {
  display: flex;
  align-items: center;
  gap: 12px;
}

.detail-icon-wrap {
  width: 48px;
  height: 48px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(127, 168, 50, 0.15), rgba(127, 168, 50, 0.08));
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--verde-salvia);
  flex-shrink: 0;
}

.detail-tempo {
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--texto-light);
  line-height: 1.4;
}

.detail-titulo {
  font-family: var(--font-titulo);
  font-size: 1.25rem;
  color: var(--texto);
  margin: 0;
  line-height: 1.3;
}

.detail-mensagem-card {
  background: var(--fundo, #FAF8F5);
  border-radius: 14px;
  padding: 16px;
  border-left: 3px solid var(--verde-salvia);
}

.detail-mensagem {
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  line-height: 1.6;
  margin: 0;
}

.detail-status {
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: var(--font-corpo);
  font-size: 12px;
  font-weight: 600;
  color: var(--verde-salvia);
  opacity: 0.7;
}
</style>
