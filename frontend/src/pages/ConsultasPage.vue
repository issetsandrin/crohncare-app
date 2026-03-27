<script setup>
import { ref, onMounted, computed } from 'vue'
import { useConsultasStore } from '../stores/consultas'
import { useExamesStore } from '../stores/exames'
import AppBar from '../components/AppBar.vue'
import ModalBase from '../components/ModalBase.vue'
import LoadingDots from '../components/LoadingDots.vue'

const store = useConsultasStore()
const examesStore = useExamesStore()

// ─── Consultas ──────────────────────────────────────────────
const showForm = ref(false)
const showDetail = ref(false)
const showDeleteConfirm = ref(false)
const editing = ref(null)
const selected = ref(null)

const form = ref(defaultForm())

function hojeFormatado() {
  const d = new Date()
  const dia = String(d.getDate()).padStart(2, '0')
  const mes = String(d.getMonth() + 1).padStart(2, '0')
  return `${dia}/${mes}/${d.getFullYear()}`
}

function horaAtual() {
  const d = new Date()
  return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`
}

function defaultForm() {
  return {
    medico: '',
    especialidade: '',
    data_hora: '',
    data_input: hojeFormatado(),
    hora_input: horaAtual(),
    local: '',
    observacoes: '',
    status: 'agendada'
  }
}

function splitDataHora(isoStr) {
  if (!isoStr) return { data: '', hora: '' }
  const d = new Date(isoStr)
  const dia = String(d.getDate()).padStart(2, '0')
  const mes = String(d.getMonth() + 1).padStart(2, '0')
  const ano = d.getFullYear()
  const h = String(d.getHours()).padStart(2, '0')
  const m = String(d.getMinutes()).padStart(2, '0')
  return { data: `${dia}/${mes}/${ano}`, hora: `${h}:${m}` }
}

function parseDataHora(dataStr, horaStr) {
  if (!dataStr) return ''
  const [dia, mes, ano] = dataStr.split('/')
  const hora = horaStr || '00:00'
  return `${ano}-${mes.padStart(2,'0')}-${dia.padStart(2,'0')}T${hora}:00`
}

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
  const dt = splitDataHora(selected.value.data_hora)
  form.value = {
    medico: selected.value.medico,
    especialidade: selected.value.especialidade || '',
    data_hora: selected.value.data_hora,
    data_input: dt.data,
    hora_input: dt.hora,
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
    const payload = { ...form.value, data_hora: parseDataHora(form.value.data_input, form.value.hora_input) }
    if (editing.value) {
      await store.update(editing.value.id, payload)
    } else {
      await store.create(payload)
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

// ─── Exames ──────────────────────────────────────────────────
const showExameForm = ref(false)
const showExameDetail = ref(false)
const showExameCancelConfirm = ref(false)
const editingExame = ref(null)
const selectedExame = ref(null)

const exameForm = ref(defaultExameForm())

function defaultExameForm() {
  return {
    nome: '',
    tipo: '',
    data: '',
    data_input: hojeFormatado(),
    hora_input: horaAtual(),
    local: '',
    observacoes: '',
    status: 'agendado'
  }
}

function abrirNovoExame() {
  editingExame.value = null
  exameForm.value = defaultExameForm()
  showExameForm.value = true
}

function abrirDetalhesExame(exame) {
  selectedExame.value = exame
  showExameDetail.value = true
}

function editarExameDoDetalhe() {
  showExameDetail.value = false
  editingExame.value = selectedExame.value
  const dtEx = splitDataHora(selectedExame.value.data)
  exameForm.value = {
    nome: selectedExame.value.nome,
    tipo: selectedExame.value.tipo || '',
    data: selectedExame.value.data,
    data_input: dtEx.data,
    hora_input: dtEx.hora,
    local: selectedExame.value.local || '',
    observacoes: selectedExame.value.observacoes || '',
    status: selectedExame.value.status
  }
  showExameForm.value = true
}

function cancelarExameDoDetalhe() {
  showExameDetail.value = false
  showExameCancelConfirm.value = true
}

async function salvarExame() {
  try {
    const payload = { ...exameForm.value, data: parseDataHora(exameForm.value.data_input, exameForm.value.hora_input) }
    if (editingExame.value) {
      await examesStore.update(editingExame.value.id, payload)
    } else {
      await examesStore.create(payload)
    }
    showExameForm.value = false
  } catch (e) {
    // store logs
  }
}

async function confirmarCancelamentoExame() {
  if (!selectedExame.value) return
  try {
    await examesStore.update(selectedExame.value.id, { ...selectedExame.value, status: 'cancelado' })
  } catch (e) {
    // store logs
  }
  showExameCancelConfirm.value = false
  selectedExame.value = null
}

// ─── Tabs ────────────────────────────────────────────────────
const activeTab = ref('proximas')

onMounted(() => {
  store.fetchAll()
  examesStore.fetchAll()
})

// ─── FAB contextual ─────────────────────────────────────────
function abrirNovoPorTab() {
  if (activeTab.value === 'exames') {
    abrirNovoExame()
  } else {
    abrirNova()
  }
}


const totalProximas = computed(() => store.proximas.length)

// ─── Formatação ──────────────────────────────────────────────
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

const tiposExame = [
  'Laboratorial',
  'Imagem',
  'Endoscopia',
  'Patologia',
  'Outro'
]
</script>

<template>
  <div class="consultas-page">
    <AppBar title="Consultas e Exames" subtitle="Seus atendimentos médicos" />

    <!-- Tabs -->
    <div class="tabs">
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'proximas' }"
        @click="activeTab = 'proximas'"
      >
        Consultas
        <span v-if="totalProximas > 0" class="tab-count">{{ totalProximas }}</span>
      </button>
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'exames' }"
        @click="activeTab = 'exames'"
      >
        Exames
        <span v-if="examesStore.proximos.length" class="tab-count">{{ examesStore.proximos.length }}</span>
      </button>
    </div>

    <div class="page-content">
      <!-- Loading -->
      <LoadingDots v-if="store.loading || examesStore.loading" />

      <!-- Aba Próximas: apenas consultas -->
      <template v-else-if="activeTab === 'proximas'">
        <div v-if="store.proximas.length === 0" class="empty-state">
          <svg class="empty-icon" width="40" height="40" viewBox="0 0 24 24" fill="none">
            <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
            <path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <p>Nenhuma consulta agendada</p>
          <p class="empty-hint">Toque no botão + para agendar</p>
        </div>

        <div v-else class="consultas-list">
          <p class="section-label">Agendados</p>
          <div
            v-for="(consulta, i) in store.proximas"
            :key="consulta.id"
            class="consulta-card"
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
              <span v-if="diasAte(consulta.data_hora)" class="dias-badge">{{ diasAte(consulta.data_hora) }}</span>
            </div>
          </div>
        </div>
      </template>

      <!-- Aba Exames -->
      <template v-else>
        <!-- Lista de próximos -->
        <div v-if="examesStore.proximos.length === 0 && examesStore.historico.length === 0" class="empty-state">
          <svg class="empty-icon" width="40" height="40" viewBox="0 0 24 24" fill="none">
            <path d="M9 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2h-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <rect x="9" y="1" width="6" height="4" rx="1" stroke="currentColor" stroke-width="1.5"/>
            <path d="M9 12h6M9 16h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
          <p>Nenhum exame cadastrado</p>
          <p class="empty-hint">Toque no botão + para registrar</p>
        </div>

        <div v-else class="consultas-list">
          <!-- Próximos -->
          <template v-if="examesStore.proximos.length > 0">
            <p class="section-label">Agendados</p>
            <div
              v-for="(exame, i) in examesStore.proximos"
              :key="'p' + exame.id"
              class="consulta-card"
              :style="{ animationDelay: i * 0.04 + 's' }"
              @click="abrirDetalhesExame(exame)"
            >
              <div class="consulta-icon-box exame-agendado">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                  <path d="M9 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2h-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                  <rect x="9" y="1" width="6" height="4" rx="1" stroke="currentColor" stroke-width="1.8"/>
                  <path d="M9 12h6M9 16h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
              </div>
              <div class="consulta-info">
                <span class="consulta-medico">{{ exame.nome }}</span>
                <span class="consulta-especialidade">{{ exame.tipo || 'Exame médico' }}</span>
                <span class="consulta-data-text">{{ formatarData(exame.data) }} · {{ formatarHora(exame.data) }}</span>
                <span v-if="exame.local" class="consulta-local">{{ exame.local }}</span>
              </div>
              <div class="consulta-badge-area">
                <span v-if="diasAte(exame.data)" class="dias-badge">{{ diasAte(exame.data) }}</span>
              </div>
            </div>
          </template>

          <!-- Histórico de exames -->
          <template v-if="examesStore.historico.length > 0">
            <p class="section-label" :style="{ marginTop: examesStore.proximos.length ? '16px' : '0' }">Histórico</p>
            <div
              v-for="(exame, i) in examesStore.historico"
              :key="'h' + exame.id"
              class="consulta-card"
              :class="{ cancelada: exame.status === 'cancelado', realizada: exame.status === 'realizado' }"
              :style="{ animationDelay: i * 0.04 + 's' }"
              @click="abrirDetalhesExame(exame)"
            >
              <div class="consulta-icon-box" :class="exame.status === 'realizado' ? 'realizada' : exame.status === 'cancelado' ? 'cancelada' : 'exame-agendado'">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                  <path d="M9 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2h-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                  <rect x="9" y="1" width="6" height="4" rx="1" stroke="currentColor" stroke-width="1.8"/>
                  <path d="M9 12h6M9 16h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
              </div>
              <div class="consulta-info">
                <span class="consulta-medico">{{ exame.nome }}</span>
                <span class="consulta-especialidade">{{ exame.tipo || 'Exame médico' }}</span>
                <span class="consulta-data-text">{{ formatarData(exame.data) }} · {{ formatarHora(exame.data) }}</span>
                <span v-if="exame.local" class="consulta-local">{{ exame.local }}</span>
              </div>
              <div class="consulta-badge-area">
                <span v-if="exame.status === 'realizado'" class="status-badge realizada">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span v-else-if="exame.status === 'cancelado'" class="status-badge cancelada">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                </span>
              </div>
            </div>
          </template>
        </div>
      </template>
    </div>

    <!-- FAB -->
    <button class="fab" @click="abrirNovoPorTab" :aria-label="activeTab === 'exames' ? 'Registrar exame' : 'Agendar consulta'">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
      </svg>
    </button>

    <!-- ─── Modais Consultas ──────────────────────────────────── -->

    <!-- Modal Form Consulta -->
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
          <div class="data-hora-row">
            <input v-model="form.data_input" type="text" class="form-input" placeholder="DD/MM/AAAA" required />
            <input v-model="form.hora_input" type="text" class="form-input hora-input" placeholder="HH:MM" required />
          </div>
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

    <!-- Modal Detalhes Consulta -->
    <ModalBase v-model="showDetail" title="">
      <template v-if="selected" #header>
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
      </template>

      <div v-if="selected" class="detail">
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

    <!-- Modal Confirmar exclusão consulta -->
    <ModalBase v-model="showDeleteConfirm" title="Excluir consulta?">
      <p class="delete-text">Essa ação não pode ser desfeita.</p>
      <div class="delete-actions">
        <button class="btn-detail edit" @click="showDeleteConfirm = false">Cancelar</button>
        <button class="btn-detail delete" @click="confirmarExclusao">Excluir</button>
      </div>
    </ModalBase>

    <!-- ─── Modais Exames ──────────────────────────────────────── -->

    <!-- Modal Form Exame -->
    <ModalBase v-model="showExameForm" :title="editingExame ? 'Editar exame' : 'Novo exame'">
      <form class="form" @submit.prevent="salvarExame">
        <div class="form-group">
          <label class="form-label">Nome do exame</label>
          <input v-model="exameForm.nome" type="text" class="form-input" placeholder="Ex: Colonoscopia, Calprotectina..." required />
        </div>

        <div class="form-group">
          <label class="form-label">Tipo</label>
          <div class="chips-row">
            <button
              v-for="tipo in tiposExame"
              :key="tipo"
              type="button"
              class="chip"
              :class="{ selected: exameForm.tipo === tipo }"
              @click="exameForm.tipo = exameForm.tipo === tipo ? '' : tipo"
            >
              {{ tipo }}
            </button>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Data e hora</label>
          <div class="data-hora-row">
            <input v-model="exameForm.data_input" type="text" class="form-input" placeholder="DD/MM/AAAA" required />
            <input v-model="exameForm.hora_input" type="text" class="form-input hora-input" placeholder="HH:MM" required />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Local (opcional)</label>
          <input v-model="exameForm.local" type="text" class="form-input" placeholder="Hospital, laboratório..." />
        </div>

        <div class="form-group">
          <label class="form-label">Observações (opcional)</label>
          <textarea v-model="exameForm.observacoes" class="form-input form-textarea" rows="3" placeholder="Preparo, jejum, instruções..."></textarea>
        </div>

        <div v-if="editingExame" class="form-group">
          <label class="form-label">Status</label>
          <div class="chips-row">
            <button
              v-for="s in [
                { value: 'agendado', label: 'Agendado' },
                { value: 'realizado', label: 'Realizado' },
                { value: 'cancelado', label: 'Cancelado' }
              ]"
              :key="s.value"
              type="button"
              class="chip"
              :class="{ selected: exameForm.status === s.value, ['chip-status-' + s.value]: true }"
              @click="exameForm.status = s.value"
            >
              {{ s.label }}
            </button>
          </div>
        </div>

        <button type="submit" class="btn-submit">
          {{ editingExame ? 'Salvar alterações' : 'Registrar exame' }}
        </button>
      </form>
    </ModalBase>

    <!-- Modal Detalhes Exame -->
    <ModalBase v-model="showExameDetail" title="">
      <template v-if="selectedExame" #header>
        <div class="detail-icon-wrap exame-icon-wrap">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M9 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2h-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <rect x="9" y="1" width="6" height="4" rx="1" stroke="currentColor" stroke-width="1.8"/>
            <path d="M9 12h6M9 16h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          </svg>
        </div>
        <div class="detail-header-text">
          <span class="detail-status" :class="selectedExame.status === 'cancelado' ? 'cancelada' : selectedExame.status === 'realizado' ? 'realizada' : 'agendada'">
            {{ selectedExame.status === 'agendado' ? 'Agendado' : selectedExame.status === 'realizado' ? 'Realizado' : 'Cancelado' }}
          </span>
          <span class="detail-data">{{ formatarDataCompleta(selectedExame.data) }}</span>
        </div>
      </template>

      <div v-if="selectedExame" class="detail">
        <h3 class="detail-medico">{{ selectedExame.nome }}</h3>
        <span v-if="selectedExame.tipo" class="detail-especialidade">{{ selectedExame.tipo }}</span>

        <div v-if="selectedExame.local" class="detail-row">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="1.8"/></svg>
          <span>{{ selectedExame.local }}</span>
        </div>

        <div v-if="selectedExame.observacoes" class="detail-obs">
          <p>{{ selectedExame.observacoes }}</p>
        </div>

        <div class="detail-actions">
          <button class="btn-detail edit" @click="editarExameDoDetalhe">Editar</button>
          <button
            v-if="selectedExame.status === 'agendado'"
            class="btn-detail delete"
            @click="cancelarExameDoDetalhe"
          >
            Cancelar exame
          </button>
        </div>
      </div>
    </ModalBase>

    <!-- Modal Confirmar cancelamento exame -->
    <ModalBase v-model="showExameCancelConfirm" title="Cancelar exame?">
      <p class="delete-text">O exame ficará registrado no histórico como cancelado.</p>
      <div class="delete-actions">
        <button class="btn-detail edit" @click="showExameCancelConfirm = false">Voltar</button>
        <button class="btn-detail delete" @click="confirmarCancelamentoExame">Confirmar</button>
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

/* Content */
.page-content {
  flex: 1;
  overflow-y: auto;
  padding: 0 16px 120px;
}

/* Section label */
.section-label {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--texto-light);
  margin: 8px 0 6px;
}

/* Empty */
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

/* List */
.consultas-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-top: 8px;
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

.consulta-icon-box.exame-agendado {
  background: rgba(74, 144, 196, 0.1);
  color: #4a90c4;
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

@media (min-width: 769px) {
  .fab {
    right: 24px;
    bottom: 24px;
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

.chip-cancelada.selected,
.chip-status-cancelado.selected {
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

.detail-icon-wrap.exame-icon-wrap {
  background: linear-gradient(135deg, rgba(74, 144, 196, 0.15), rgba(74, 144, 196, 0.08));
  color: #4a90c4;
}

.detail-header-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
  margin-left: 12px;
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

.data-hora-row {
  display: flex;
  gap: 8px;
}

.hora-input {
  max-width: 100px;
}

/* ── Desktop ── */
@media (min-width: 769px) {
  .page-content {
    padding: 0 32px 80px;
  }

  .consultas-list {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    align-items: start;
  }

  .section-label {
    grid-column: 1 / -1;
  }

  .consulta-card {
    transition: box-shadow 0.2s var(--ease-smooth), transform 0.2s var(--ease-smooth);
  }

  .consulta-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
  }
}
</style>
