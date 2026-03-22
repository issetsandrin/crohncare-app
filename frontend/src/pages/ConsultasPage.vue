<script setup>
import { ref, onMounted, computed } from 'vue'
import { useConsultasStore } from '../stores/consultas'
import AppBar from '../components/AppBar.vue'
import ModalBase from '../components/ModalBase.vue'

const store = useConsultasStore()

const showForm = ref(false)
const showDetail = ref(false)
const showDeleteConfirm = ref(false)
const editing = ref(null)
const selected = ref(null)

const form = ref(defaultForm())

function defaultForm() {
  return {
    medico: '',
    especialidade: '',
    data_hora: '',
    local: '',
    observacoes: '',
    status: 'agendada'
  }
}

onMounted(() => {
  store.fetchAll()
})

const activeTab = ref('proximas')

const listaFiltrada = computed(() => {
  return activeTab.value === 'proximas' ? store.proximas : store.passadas
})

function abrirNova() {
  editing.value = null
  form.value = defaultForm()
  showForm.value = true
}

function abrirDetalhes(consulta) {
  selected.value = consulta
  showDetail.value = true
}

function editarDoDetalhe() {
  showDetail.value = false
  editing.value = selected.value
  form.value = {
    medico: selected.value.medico,
    especialidade: selected.value.especialidade || '',
    data_hora: selected.value.data_hora,
    local: selected.value.local || '',
    observacoes: selected.value.observacoes || '',
    status: selected.value.status
  }
  showForm.value = true
}

function excluirDoDetalhe() {
  showDetail.value = false
  showDeleteConfirm.value = true
}

async function salvar() {
  try {
    if (editing.value) {
      await store.update(editing.value.id, form.value)
    } else {
      await store.create(form.value)
    }
    showForm.value = false
  } catch (e) {
    // store logs
  }
}

async function confirmarExclusao() {
  if (!selected.value) return
  try {
    await store.remove(selected.value.id)
  } catch (e) {
    // store logs
  }
  showDeleteConfirm.value = false
  selected.value = null
}

function formatarData(dt) {
  if (!dt) return ''
  const d = new Date(dt)
  return d.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' })
}

function formatarHora(dt) {
  if (!dt) return ''
  const d = new Date(dt)
  return d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

function formatarDataCompleta(dt) {
  if (!dt) return ''
  const d = new Date(dt)
  return d.toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' }) +
    ' às ' + d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

function diasAte(dt) {
  if (!dt) return null
  const agora = new Date()
  const data = new Date(dt)
  const diff = Math.ceil((data - agora) / (1000 * 60 * 60 * 24))
  if (diff === 0) return 'Hoje'
  if (diff === 1) return 'Amanhã'
  if (diff < 0) return null
  return `Em ${diff} dias`
}

const especialidades = [
  'Gastroenterologista',
  'Coloproctologista',
  'Clínico Geral',
  'Nutricionista',
  'Psicólogo',
  'Dermatologista',
  'Reumatologista',
  'Outro'
]
</script>

<template>
  <div class="consultas-page">
    <AppBar title="Consultas" subtitle="Suas consultas médicas" />

    <!-- Tabs -->
    <div class="tabs">
      <button
        class="tab"
        :class="{ active: activeTab === 'proximas' }"
        @click="activeTab = 'proximas'"
      >
        Próximas
        <span v-if="store.proximas.length" class="tab-badge">{{ store.proximas.length }}</span>
      </button>
      <button
        class="tab"
        :class="{ active: activeTab === 'historico' }"
        @click="activeTab = 'historico'"
      >
        Histórico
      </button>
    </div>

    <div class="page-content">
      <!-- Loading -->
      <div v-if="store.loading" class="loading-state">
        <div class="loading-dots">
          <span></span><span></span><span></span>
        </div>
      </div>

      <!-- Empty -->
      <div v-else-if="listaFiltrada.length === 0" class="empty-state">
        <div class="empty-icon">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
            <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
            <path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <p class="empty-title">{{ activeTab === 'proximas' ? 'Nenhuma consulta agendada' : 'Sem histórico ainda' }}</p>
        <p class="empty-hint">{{ activeTab === 'proximas' ? 'Agende sua próxima consulta aqui' : 'Suas consultas realizadas aparecerão aqui' }}</p>
      </div>

      <!-- List -->
      <div v-else class="consultas-list">
        <div
          v-for="(consulta, i) in listaFiltrada"
          :key="consulta.id"
          class="consulta-card"
          :class="{ cancelada: consulta.status === 'cancelada', realizada: consulta.status === 'realizada' }"
          :style="{ animationDelay: i * 0.04 + 's' }"
          @click="abrirDetalhes(consulta)"
        >
          <div class="consulta-icon-box" :class="consulta.status">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <rect x="3" y="6" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
              <path d="M3 10h18" stroke="currentColor" stroke-width="1.8"/>
              <path d="M8 2v4M16 2v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="consulta-info">
            <span class="consulta-medico">{{ consulta.medico }}</span>
            <span class="consulta-especialidade">{{ consulta.especialidade || 'Consulta médica' }}</span>
            <span class="consulta-data-text">{{ formatarData(consulta.data_hora) }} · {{ formatarHora(consulta.data_hora) }}</span>
            <span v-if="consulta.local" class="consulta-local">{{ consulta.local }}</span>
          </div>
          <div class="consulta-badge-area">
            <span v-if="diasAte(consulta.data_hora) && consulta.status === 'agendada'" class="dias-badge">{{ diasAte(consulta.data_hora) }}</span>
            <span v-else-if="consulta.status === 'realizada'" class="status-badge realizada">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <span v-else-if="consulta.status === 'cancelada'" class="status-badge cancelada">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- FAB -->
    <button class="fab" @click="abrirNova" aria-label="Agendar consulta">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
      </svg>
    </button>

    <!-- Modal Form -->
    <ModalBase v-model="showForm" :title="editing ? 'Editar consulta' : 'Nova consulta'">
      <form class="form" @submit.prevent="salvar">
        <div class="form-group">
          <label class="form-label">Nome do médico(a)</label>
          <input v-model="form.medico" type="text" class="form-input" placeholder="Dr(a). ..." required />
        </div>

        <div class="form-group">
          <label class="form-label">Especialidade</label>
          <div class="chips-row">
            <button
              v-for="esp in especialidades"
              :key="esp"
              type="button"
              class="chip"
              :class="{ selected: form.especialidade === esp }"
              @click="form.especialidade = form.especialidade === esp ? '' : esp"
            >
              {{ esp }}
            </button>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Data e hora</label>
          <input v-model="form.data_hora" type="datetime-local" class="form-input" required />
        </div>

        <div class="form-group">
          <label class="form-label">Local (opcional)</label>
          <input v-model="form.local" type="text" class="form-input" placeholder="Hospital, clínica..." />
        </div>

        <div class="form-group">
          <label class="form-label">Observações (opcional)</label>
          <textarea v-model="form.observacoes" class="form-input form-textarea" rows="3" placeholder="Levar exames, perguntas para o médico..."></textarea>
        </div>

        <div v-if="editing" class="form-group">
          <label class="form-label">Status</label>
          <div class="chips-row">
            <button
              v-for="s in [
                { value: 'agendada', label: 'Agendada' },
                { value: 'realizada', label: 'Realizada' },
                { value: 'cancelada', label: 'Cancelada' }
              ]"
              :key="s.value"
              type="button"
              class="chip"
              :class="{ selected: form.status === s.value, ['chip-' + s.value]: true }"
              @click="form.status = s.value"
            >
              {{ s.label }}
            </button>
          </div>
        </div>

        <button type="submit" class="btn-submit">
          {{ editing ? 'Salvar alterações' : 'Agendar consulta' }}
        </button>
      </form>
    </ModalBase>

    <!-- Modal Detalhes -->
    <ModalBase v-model="showDetail" title="">
      <div v-if="selected" class="detail">
        <div class="detail-header">
          <div class="detail-icon-wrap">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
              <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/>
              <path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="detail-header-text">
            <span class="detail-status" :class="selected.status">{{ selected.status === 'agendada' ? 'Agendada' : selected.status === 'realizada' ? 'Realizada' : 'Cancelada' }}</span>
            <span class="detail-data">{{ formatarDataCompleta(selected.data_hora) }}</span>
          </div>
        </div>

        <h3 class="detail-medico">{{ selected.medico }}</h3>
        <span v-if="selected.especialidade" class="detail-especialidade">{{ selected.especialidade }}</span>

        <div v-if="selected.local" class="detail-row">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="1.8"/></svg>
          <span>{{ selected.local }}</span>
        </div>

        <div v-if="selected.observacoes" class="detail-obs">
          <p>{{ selected.observacoes }}</p>
        </div>

        <div class="detail-actions">
          <button class="btn-detail edit" @click="editarDoDetalhe">Editar</button>
          <button class="btn-detail delete" @click="excluirDoDetalhe">Excluir</button>
        </div>
      </div>
    </ModalBase>

    <!-- Modal Confirmar exclusão -->
    <ModalBase v-model="showDeleteConfirm" title="Excluir consulta?">
      <p class="delete-text">Essa ação não pode ser desfeita.</p>
      <div class="delete-actions">
        <button class="btn-detail edit" @click="showDeleteConfirm = false">Cancelar</button>
        <button class="btn-detail delete" @click="confirmarExclusao">Excluir</button>
      </div>
    </ModalBase>
  </div>
</template>

<style scoped>
.consultas-page {
  height: 100dvh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* Tabs */
.tabs {
  display: flex;
  gap: 4px;
  padding: 0 16px 12px;
  background: #fff;
}

.tab {
  flex: 1;
  padding: 10px 16px;
  border-radius: 10px;
  border: none;
  background: rgba(0, 0, 0, 0.04);
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: var(--texto-light);
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.tab.active {
  background: var(--verde-salvia);
  color: #fff;
}

.tab-badge {
  min-width: 18px;
  height: 18px;
  border-radius: 9px;
  background: rgba(255, 255, 255, 0.3);
  font-size: 11px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 5px;
}

.tab:not(.active) .tab-badge {
  background: var(--verde-salvia);
  color: #fff;
}

/* Content */
.page-content {
  flex: 1;
  overflow-y: auto;
  padding: 0 16px 120px;
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
  padding: 60px 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.empty-icon {
  width: 72px;
  height: 72px;
  border-radius: 20px;
  background: rgba(127, 168, 50, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #bbb;
  margin-bottom: 4px;
}

.empty-title {
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  color: var(--texto);
  margin: 0;
}

.empty-hint {
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--texto-light);
  margin: 0;
}

/* List */
.consultas-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.consulta-card {
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
}

.consulta-card:active {
  transform: scale(0.98);
}

.consulta-card.cancelada {
  opacity: 0.45;
}

.consulta-card.realizada {
  opacity: 0.65;
}

.consulta-icon-box {
  width: 44px;
  min-width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(127, 168, 50, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--verde-salvia);
}

.consulta-icon-box.realizada {
  background: rgba(127, 168, 50, 0.06);
  color: var(--texto-light);
}

.consulta-icon-box.cancelada {
  background: rgba(0, 0, 0, 0.04);
  color: var(--texto-light);
}

.consulta-data-text {
  font-family: var(--font-corpo);
  font-size: 12px;
  font-weight: 500;
  color: var(--verde-salvia);
}

.consulta-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.consulta-medico {
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  color: var(--texto);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.consulta-especialidade {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
}

.consulta-local {
  font-family: var(--font-corpo);
  font-size: 11px;
  color: var(--texto-light);
  opacity: 0.7;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.consulta-badge-area {
  flex-shrink: 0;
}

.dias-badge {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 600;
  color: var(--verde-salvia);
  background: rgba(127, 168, 50, 0.1);
  padding: 4px 8px;
  border-radius: 8px;
  white-space: nowrap;
}

.status-badge {
  width: 24px;
  height: 24px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.status-badge.realizada {
  color: var(--verde-salvia);
  background: rgba(127, 168, 50, 0.1);
}

.status-badge.cancelada {
  color: var(--texto-light);
  background: rgba(0, 0, 0, 0.06);
}

/* FAB */
.fab {
  position: fixed;
  bottom: calc(80px + env(safe-area-inset-bottom, 0px));
  right: calc(50% - 195px);
  width: 52px;
  height: 52px;
  border-radius: 16px;
  background: var(--verde-salvia);
  color: #fff;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(76, 175, 80, 0.35);
  transition: transform 0.3s var(--ease-smooth), box-shadow 0.3s var(--ease-smooth);
  z-index: 50;
}

.fab:active {
  transform: scale(0.93);
}

@media (max-width: 430px) {
  .fab {
    right: 16px;
  }
}

/* Form */
.form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-label {
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: var(--texto);
}

.form-input {
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 12px 14px;
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  background: #fff;
  transition: border-color 0.3s;
  width: 100%;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: var(--verde-salvia);
  box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 60px;
}

.chips-row {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.chip {
  padding: 8px 14px;
  border-radius: 20px;
  border: 1.5px solid #e0e0e0;
  background: #fff;
  font-family: var(--font-corpo);
  font-size: 12px;
  font-weight: 500;
  color: var(--texto);
  cursor: pointer;
  transition: all 0.2s ease;
}

.chip.selected {
  border-color: var(--verde-salvia);
  background: rgba(127, 168, 50, 0.08);
  color: var(--verde-salvia);
  font-weight: 600;
}

.chip-realizada.selected {
  border-color: var(--verde-salvia);
  background: rgba(127, 168, 50, 0.08);
  color: var(--verde-salvia);
}

.chip-cancelada.selected {
  border-color: var(--terracota);
  background: rgba(196, 120, 74, 0.08);
  color: var(--terracota);
}

.btn-submit {
  background: var(--verde-salvia);
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 14px;
  font-family: var(--font-corpo);
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 4px;
  transition: opacity 0.3s, transform 0.2s;
}

.btn-submit:active {
  transform: scale(0.98);
}

/* Detail */
.detail {
  display: flex;
  flex-direction: column;
  gap: 16px;
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

.detail-header-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.detail-status {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-status.agendada { color: var(--verde-salvia); }
.detail-status.realizada { color: var(--texto-light); }
.detail-status.cancelada { color: var(--terracota); }

.detail-data {
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--texto-light);
}

.detail-medico {
  font-family: var(--font-titulo);
  font-size: 1.25rem;
  color: var(--texto);
  margin: 0;
}

.detail-especialidade {
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto-light);
  margin-top: -8px;
}

.detail-row {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
}

.detail-row svg {
  color: var(--texto-light);
  flex-shrink: 0;
}

.detail-obs {
  background: var(--fundo, #FAF8F5);
  border-radius: 14px;
  padding: 16px;
  border-left: 3px solid var(--verde-salvia);
}

.detail-obs p {
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  line-height: 1.6;
  margin: 0;
}

.detail-actions {
  display: flex;
  gap: 8px;
}

.btn-detail {
  flex: 1;
  padding: 12px;
  border-radius: 10px;
  border: none;
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-detail:active {
  transform: scale(0.97);
}

.btn-detail.edit {
  background: rgba(127, 168, 50, 0.1);
  color: var(--verde-salvia);
}

.btn-detail.delete {
  background: rgba(196, 120, 74, 0.1);
  color: var(--terracota);
}

.delete-text {
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto-light);
  margin: 0 0 16px;
}

.delete-actions {
  display: flex;
  gap: 8px;
}
</style>
