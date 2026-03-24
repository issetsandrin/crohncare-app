<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useMedicamentosStore } from '../stores/medicamentos'
import { useRegistrosUsoStore } from '../stores/registrosUso'
import MedicamentoCard from '../components/MedicamentoCard.vue'
import EstoqueIndicador from '../components/EstoqueIndicador.vue'
import ModalBase from '../components/ModalBase.vue'
import AppBar from '../components/AppBar.vue'
import { useEstoque } from '../composables/useEstoque'
import LoadingDots from '../components/LoadingDots.vue'

const store = useMedicamentosStore()
const registrosUsoStore = useRegistrosUsoStore()
const { nivelAlerta } = useEstoque()

// Modal de comprovação de tomada
const fotoTomada = ref(null)
const fotoPreview = ref(null)

function selecionarFoto(event) {
  const file = event.target.files[0]
  if (!file) return
  fotoTomada.value = file
  fotoPreview.value = URL.createObjectURL(file)
}

async function confirmarUso() {
  await registrosUsoStore.registrar(registrosUsoStore.pendente.medicamentoId, fotoTomada.value)
  fotoTomada.value = null
  fotoPreview.value = null
}

function dispensarUso() {
  registrosUsoStore.dispensar()
  fotoTomada.value = null
  fotoPreview.value = null
}

const showFormModal = ref(false)
const showDeleteModal = ref(false)
const showReabastecerModal = ref(false)
const showDetailModal = ref(false)
const editingMed = ref(null)
const deletingMed = ref(null)
const selectedMed = ref(null)
const reabastecerQtd = ref(0)

const diasSemana = [
  { value: 0, label: 'Dom', short: 'D' },
  { value: 1, label: 'Seg', short: 'S' },
  { value: 2, label: 'Ter', short: 'T' },
  { value: 3, label: 'Qua', short: 'Q' },
  { value: 4, label: 'Qui', short: 'Q' },
  { value: 5, label: 'Sex', short: 'S' },
  { value: 6, label: 'Sáb', short: 'S' }
]

const form = reactive({
  nome: '',
  dose: '',
  instrucoes: '',
  periodicidade_tipo: 'diario',
  periodicidade_dias: [],
  periodicidade_intervalo: 2,
  horarios: [''],
  quantidade_atual: 0,
  exige_comprovacao: false,
})

function resetForm() {
  form.nome = ''
  form.dose = ''
  form.instrucoes = ''
  form.periodicidade_tipo = 'diario'
  form.periodicidade_dias = []
  form.periodicidade_intervalo = 2
  form.horarios = ['']
  form.quantidade_atual = 0
  form.exige_comprovacao = false
  editingMed.value = null
}

function abrirAdicionar() {
  resetForm()
  showFormModal.value = true
}

function abrirDetalhes(med) {
  selectedMed.value = med
  showDetailModal.value = true
}

function editarDoDetalhe() {
  showDetailModal.value = false
  const med = selectedMed.value
  editingMed.value = med
  form.nome = med.nome || ''
  form.dose = med.dose || ''
  form.instrucoes = med.instrucoes || ''
  form.periodicidade_tipo = med.periodicidade_tipo || 'diario'
  form.periodicidade_dias = med.periodicidade_valor?.dias || []
  form.periodicidade_intervalo = med.periodicidade_valor?.intervalo || 2
  form.horarios = med.horarios && med.horarios.length ? med.horarios.map(h => h.substring(0, 5)) : ['']
  form.quantidade_atual = med.estoque?.quantidade_atual || 0
  form.exige_comprovacao = med.exige_comprovacao ?? false
  showFormModal.value = true
}

function excluirDoDetalhe() {
  showDetailModal.value = false
  deletingMed.value = selectedMed.value
  showDeleteModal.value = true
}

function reabastecerDoDetalhe() {
  showDetailModal.value = false
  reabastecerQtd.value = 0
  showReabastecerModal.value = true
}

function toggleDia(dia) {
  const idx = form.periodicidade_dias.indexOf(dia)
  if (idx === -1) {
    form.periodicidade_dias.push(dia)
  } else {
    form.periodicidade_dias.splice(idx, 1)
  }
}

function adicionarHorario() {
  form.horarios.push('')
}

function removerHorario(index) {
  if (form.horarios.length > 1) {
    form.horarios.splice(index, 1)
  }
}

const diasSemanaLabel = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']

function periodicidadeTexto(med) {
  const tipo = med.periodicidade_tipo || 'diario'
  const valor = med.periodicidade_valor

  if (tipo === 'diario') return 'Todos os dias'
  if (tipo === 'sob_demanda') return 'Sob demanda (quando necessário)'
  if (tipo === 'intervalo' && valor?.intervalo) {
    return `A cada ${valor.intervalo} dia${valor.intervalo > 1 ? 's' : ''}`
  }
  if (tipo === 'dias_semana' && valor?.dias?.length) {
    return valor.dias.sort((a, b) => a - b).map(d => diasSemanaLabel[d]).join(', ')
  }
  return 'Todos os dias'
}

function calcularDoseDiaria() {
  const horariosValidos = form.horarios.filter(h => h.trim() !== '').length
  const tipo = form.periodicidade_tipo
  if (tipo === 'diario') return horariosValidos || 1
  if (tipo === 'dias_semana') {
    const diasPorSemana = form.periodicidade_dias.length || 1
    return Math.max(1, Math.round((horariosValidos * diasPorSemana) / 7))
  }
  if (tipo === 'intervalo') {
    const intervalo = form.periodicidade_intervalo || 2
    return Math.max(1, Math.round(horariosValidos / intervalo))
  }
  return 1
}

async function salvar() {
  let periodicidade_valor = null
  if (form.periodicidade_tipo === 'dias_semana') {
    periodicidade_valor = { dias: [...form.periodicidade_dias].sort((a, b) => a - b) }
  } else if (form.periodicidade_tipo === 'intervalo') {
    periodicidade_valor = { intervalo: Number(form.periodicidade_intervalo) }
  }

  const payload = {
    nome: form.nome,
    dose: form.dose,
    instrucoes: form.instrucoes,
    periodicidade_tipo: form.periodicidade_tipo,
    periodicidade_valor: periodicidade_valor,
    horarios: form.horarios.filter((h) => h.trim() !== ''),
    exige_comprovacao: form.exige_comprovacao,
    estoque: {
      quantidade_atual: Number(form.quantidade_atual),
      dose_diaria: calcularDoseDiaria()
    }
  }

  try {
    if (editingMed.value) {
      await store.update(editingMed.value.id, payload)
    } else {
      await store.create(payload)
    }
    showFormModal.value = false
    resetForm()
  } catch (e) {
    // store logs error
  }
}

async function confirmarDelete() {
  if (!deletingMed.value) return
  try {
    await store.remove(deletingMed.value.id)
    showDeleteModal.value = false
    deletingMed.value = null
  } catch (e) {
    // store logs error
  }
}

async function confirmarReabastecer() {
  if (!selectedMed.value || reabastecerQtd.value <= 0) return
  try {
    await store.reabastecer(selectedMed.value.id, Number(reabastecerQtd.value))
    showReabastecerModal.value = false
  } catch (e) {
    // store logs error
  }
}

function diasRestantes(med) {
  return med.dias_restantes ?? 0
}

onMounted(() => {
  store.fetchAll()
})
</script>

<template>
  <main class="medicamentos-page">
    <AppBar title="Medicamentos" subtitle="Gerencie seus remédios e estoque" />

    <div class="page-content">
    <LoadingDots v-if="store.loading && store.lista.length === 0" />

    <div v-else-if="store.lista.length === 0" class="empty-state">
      <svg class="empty-icon" width="40" height="40" viewBox="0 0 24 24" fill="none">
        <path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
      </svg>
      <p>Nenhum medicamento cadastrado</p>
      <p class="empty-hint">Toque no botão + para começar</p>
    </div>

    <div v-else class="med-list">
      <MedicamentoCard
        v-for="(med, i) in store.lista"
        :key="med.id"
        :medicamento="med"
        :style="{ animationDelay: i * 0.04 + 's' }"
        @click="abrirDetalhes"
      />
    </div>
    </div>

    <!-- Modal Detalhes -->
    <ModalBase v-model="showDetailModal" title="Detalhes do Medicamento">
      <div v-if="selectedMed" class="detail-content">
        <div class="detail-row">
          <span class="detail-label">Nome</span>
          <span class="detail-value detail-nome">{{ selectedMed.nome }}</span>
        </div>

        <div class="detail-row">
          <span class="detail-label">Dose</span>
          <span class="detail-value">{{ selectedMed.dose || '—' }}</span>
        </div>

        <div class="detail-row">
          <span class="detail-label">Frequência</span>
          <span class="detail-value">{{ periodicidadeTexto(selectedMed) }}</span>
        </div>

        <div v-if="selectedMed.instrucoes" class="detail-row">
          <span class="detail-label">Instruções</span>
          <p class="detail-obs">{{ selectedMed.instrucoes }}</p>
        </div>

        <div v-if="selectedMed.periodicidade_tipo !== 'sob_demanda' && selectedMed.horarios && selectedMed.horarios.length" class="detail-row">
          <span class="detail-label">Horários</span>
          <div class="detail-tags">
            <span v-for="h in selectedMed.horarios" :key="h" class="detail-tag">
              {{ h.substring(0, 5) }}
            </span>
          </div>
        </div>

        <div class="detail-row">
          <span class="detail-label">Estoque</span>
          <div class="detail-estoque">
            <EstoqueIndicador :dias-restantes="diasRestantes(selectedMed) === Infinity ? 0 : diasRestantes(selectedMed)" />
            <div class="detail-estoque-info">
              <span class="estoque-info-item">
                <strong>{{ selectedMed.estoque?.quantidade_atual ?? 0 }}</strong> unidades
              </span>
            </div>
          </div>
        </div>

        <div class="detail-actions">
          <button class="btn btn-primary" @click="editarDoDetalhe">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
              <path d="M12 1.5l2.5 2.5-8 8H4v-2.5l8-8z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Editar
          </button>
          <button
            v-if="nivelAlerta(diasRestantes(selectedMed)) !== 'ok'"
            class="btn btn-ambar"
            @click="reabastecerDoDetalhe"
          >
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
              <path d="M8 2v12M4 10l4 4 4-4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Reabastecer
          </button>
          <button class="btn btn-danger-outline" @click="excluirDoDetalhe">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
              <path d="M2.5 4.5h11M6 4.5V2.5h4v2M5 7v5M8 7v5M11 7v5M3.5 4.5l.5 9h8l.5-9" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Excluir
          </button>
        </div>
      </div>
    </ModalBase>

    <!-- Modal Adicionar/Editar -->
    <ModalBase v-model="showFormModal" :title="editingMed ? 'Editar Medicamento' : 'Novo Medicamento'">
      <form class="med-form" @submit.prevent="salvar">
        <div class="form-group">
          <label class="form-label">Nome</label>
          <input type="text" v-model="form.nome" class="form-input" placeholder="Ex: Mesalazina" required />
        </div>

        <div class="form-group">
          <label class="form-label">Dose</label>
          <input type="text" v-model="form.dose" class="form-input" placeholder="Ex: 500mg" />
        </div>

        <div class="form-group">
          <label class="form-label">Instruções</label>
          <textarea v-model="form.instrucoes" class="form-input form-textarea" rows="2" placeholder="Como tomar..." />
        </div>

        <!-- Periodicidade -->
        <div class="form-group">
          <label class="form-label">Frequência de uso</label>
          <div class="periodicidade-options">
            <button
              type="button"
              class="periodicidade-chip"
              :class="{ active: form.periodicidade_tipo === 'diario' }"
              @click="form.periodicidade_tipo = 'diario'"
            >
              Diário
            </button>
            <button
              type="button"
              class="periodicidade-chip"
              :class="{ active: form.periodicidade_tipo === 'dias_semana' }"
              @click="form.periodicidade_tipo = 'dias_semana'"
            >
              Dias da semana
            </button>
            <button
              type="button"
              class="periodicidade-chip"
              :class="{ active: form.periodicidade_tipo === 'sob_demanda' }"
              @click="form.periodicidade_tipo = 'sob_demanda'"
            >
              Sob demanda
            </button>
          </div>

          <!-- Dias da semana selector -->
          <div v-if="form.periodicidade_tipo === 'dias_semana'" class="dias-semana-selector">
            <button
              v-for="dia in diasSemana"
              :key="dia.value"
              type="button"
              class="dia-btn"
              :class="{ active: form.periodicidade_dias.includes(dia.value) }"
              @click="toggleDia(dia.value)"
            >
              {{ dia.label }}
            </button>
          </div>

          <!-- Intervalo input -->
          <div v-if="form.periodicidade_tipo === 'intervalo'" class="intervalo-input">
            <label class="form-label">A cada quantos dias?</label>
            <input
              type="number"
              v-model.number="form.periodicidade_intervalo"
              class="form-input intervalo-number"
              min="2"
              max="90"
            />
          </div>
        </div>

        <div v-if="form.periodicidade_tipo !== 'sob_demanda'" class="form-group">
          <label class="form-label">Horários</label>
          <div v-for="(h, i) in form.horarios" :key="i" class="horario-row">
            <input type="time" v-model="form.horarios[i]" class="form-input horario-input" />
            <button
              v-if="form.horarios.length > 1"
              type="button"
              class="btn-remove-horario"
              @click="removerHorario(i)"
              aria-label="Remover horário"
            >
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M4 8h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </button>
          </div>
          <button type="button" class="btn-add-horario" @click="adicionarHorario">+ Horário</button>
        </div>

        <div class="form-group">
          <label class="form-label">Estoque (unidades)</label>
          <input type="number" v-model.number="form.quantidade_atual" class="form-input" min="0" />
        </div>

        <div class="form-group">
          <label class="comprovacao-toggle" @click="form.exige_comprovacao = !form.exige_comprovacao">
            <span class="toggle-track" :class="{ active: form.exige_comprovacao }">
              <span class="toggle-thumb" />
            </span>
            <span class="toggle-label-text">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="flex-shrink:0">
                <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="13" r="4" stroke="currentColor" stroke-width="1.8"/>
              </svg>
              Exigir foto ao registrar uso
            </span>
          </label>
          <p class="toggle-hint">Ao receber o lembrete, o app pedirá uma foto como comprovante do uso</p>
        </div>

        <div class="form-actions">
          <button type="button" class="btn btn-secondary" @click="showFormModal = false">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
      </form>
    </ModalBase>

    <!-- Modal Delete -->
    <ModalBase v-model="showDeleteModal" title="Confirmar Exclusão">
      <div class="confirm-content">
        <p>Tem certeza que deseja excluir <strong>{{ deletingMed?.nome }}</strong>?</p>
        <p class="confirm-hint">Esta ação não pode ser desfeita.</p>
        <div class="form-actions">
          <button class="btn btn-secondary" @click="showDeleteModal = false">Cancelar</button>
          <button class="btn btn-danger" @click="confirmarDelete">Excluir</button>
        </div>
      </div>
    </ModalBase>

    <!-- FAB -->
    <button class="fab" @click="abrirAdicionar" aria-label="Adicionar medicamento">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
      </svg>
    </button>

    <!-- Modal Registro de Uso -->
    <ModalBase :model-value="!!registrosUsoStore.pendente" @update:model-value="dispensarUso" title="Registrar uso">
      <div v-if="registrosUsoStore.pendente" class="tomada-content">
        <p class="tomada-med-nome">{{ registrosUsoStore.pendente.nome }}</p>

        <template v-if="registrosUsoStore.pendente.exige_comprovacao">
          <p class="tomada-hint">Tire uma foto para registrar o uso do medicamento.</p>

          <label class="foto-upload-area" :class="{ 'has-foto': fotoPreview }">
            <input
              type="file"
              accept="image/*"
              capture="environment"
              class="foto-input-hidden"
              @change="selecionarFoto"
            />
            <template v-if="fotoPreview">
              <img :src="fotoPreview" class="foto-preview" alt="Preview" />
              <span class="foto-trocar">Trocar foto</span>
            </template>
            <template v-else>
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" class="foto-icon">
                <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="13" r="4" stroke="currentColor" stroke-width="1.5"/>
              </svg>
              <span class="foto-upload-label">Tirar foto / Selecionar</span>
            </template>
          </label>

          <div class="form-actions">
            <button class="btn btn-secondary" @click="dispensarUso">Agora não</button>
            <button
              class="btn btn-primary"
              :disabled="!fotoTomada || registrosUsoStore.loading"
              @click="confirmarUso"
            >
              {{ registrosUsoStore.loading ? 'Enviando...' : 'Confirmar uso' }}
            </button>
          </div>
        </template>

        <template v-else>
          <p class="tomada-hint">Confirme que você usou o medicamento.</p>
          <div class="form-actions">
            <button class="btn btn-secondary" @click="dispensarUso">Agora não</button>
            <button
              class="btn btn-primary"
              :disabled="registrosUsoStore.loading"
              @click="confirmarUso"
            >
              {{ registrosUsoStore.loading ? 'Registrando...' : 'Confirmei, usei!' }}
            </button>
          </div>
        </template>
      </div>
    </ModalBase>

    <!-- Modal Reabastecer -->
    <ModalBase v-model="showReabastecerModal" title="Reabastecer">
      <form class="med-form" @submit.prevent="confirmarReabastecer">
        <p class="reabastecer-info">{{ selectedMed?.nome }} - {{ selectedMed?.dose }}</p>
        <div class="form-group">
          <label class="form-label">Quantidade a adicionar</label>
          <input type="number" v-model.number="reabastecerQtd" class="form-input" min="1" required />
        </div>
        <div class="form-actions">
          <button type="button" class="btn btn-secondary" @click="showReabastecerModal = false">Cancelar</button>
          <button type="submit" class="btn btn-primary" :disabled="reabastecerQtd <= 0">Confirmar</button>
        </div>
      </form>
    </ModalBase>
  </main>
</template>

<style scoped>
.medicamentos-page {
  padding-bottom: calc(80px + env(safe-area-inset-bottom, 0px));
  height: 100dvh;
  overflow: hidden;
}

.page-content {
  padding: 0 16px;
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

.loading-state,
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

.med-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Detail modal */
.detail-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.detail-row {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-label {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 600;
  color: var(--texto-light);
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.detail-value {
  font-family: var(--font-corpo);
  font-size: 15px;
  color: var(--texto);
}

.detail-nome {
  font-family: var(--font-titulo);
  font-size: 1.2rem;
}

.detail-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.detail-tag {
  padding: 5px 12px;
  background: rgba(127, 168, 50, 0.12);
  color: var(--verde-salvia);
  border-radius: 8px;
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
}

.detail-obs {
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  line-height: 1.5;
  margin: 0;
}

.detail-estoque {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.detail-estoque-info {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--texto-light);
}

.estoque-info-item strong {
  color: var(--texto);
}

.estoque-info-sep {
  color: #ccc;
}

.detail-actions {
  display: flex;
  gap: 10px;
  margin-top: 8px;
}

.detail-actions .btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

/* Periodicidade */
.periodicidade-options {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  max-width: 100%;
}

.periodicidade-chip {
  padding: 8px 14px;
  border: 1.5px solid #ddd;
  border-radius: 10px;
  background: #fff;
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 500;
  color: var(--texto-light);
  cursor: pointer;
  transition: all 0.3s var(--ease-smooth);
}

.periodicidade-chip.active {
  border-color: var(--verde-salvia);
  background: rgba(127, 168, 50, 0.1);
  color: var(--verde-salvia);
  font-weight: 600;
}

.periodicidade-chip:active {
  transform: scale(0.96);
}

.dias-semana-selector {
  display: flex;
  gap: 4px;
  margin-top: 12px;
  width: 100%;
}

.dia-btn {
  flex: 1;
  min-width: 0;
  height: 36px;
  border-radius: 12px;
  border: 1.5px solid #ddd;
  background: #fff;
  font-family: var(--font-corpo);
  font-size: 12px;
  font-weight: 600;
  color: var(--texto-light);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s var(--ease-smooth);
}

.dia-btn.active {
  border-color: var(--verde-salvia);
  background: var(--verde-salvia);
  color: #fff;
}

.dia-btn:active {
  transform: scale(0.93);
}

.intervalo-input {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-top: 12px;
}

.intervalo-number {
  width: 30px;
  text-align: center;
  padding: 8px 4px;
  font-size: 13px;
}

/* Form styles */
.med-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
}

.med-form * {
  box-sizing: border-box;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 0;
  max-width: 100%;
}

.form-label {
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: var(--texto);
}

.form-input {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 12px;
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  background: #fff;
  transition: border-color 0.3s var(--ease-smooth);
  width: 100%;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: var(--verde-salvia);
}

.form-textarea {
  resize: vertical;
  min-height: 50px;
}

.form-row {
  display: flex;
  gap: 12px;
}

.form-row .form-group {
  flex: 1;
}

.horario-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 6px;
}

.horario-input {
  flex: 1;
}

.btn-remove-horario {
  background: none;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 10px;
  cursor: pointer;
  color: var(--terracota);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s var(--ease-smooth);
}

.btn-remove-horario:hover {
  background: rgba(196, 120, 74, 0.08);
}

.btn-add-horario {
  background: none;
  border: none;
  color: var(--verde-salvia);
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  padding: 4px 0;
  text-align: left;
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 8px;
}

.btn {
  flex: 1;
  padding: 12px 24px;
  border: none;
  border-radius: 10px;
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.3s var(--ease-smooth);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn:active:not(:disabled) {
  opacity: 0.8;
}

.btn-primary {
  background: var(--verde-salvia);
  color: #fff;
}

.btn-secondary {
  background: #f0f0f0;
  color: var(--texto);
}

.btn-danger {
  background: var(--terracota);
  color: #fff;
}

.btn-danger-outline {
  background: var(--terracota);
  color: #fff;
}

.btn-ambar {
  background: var(--ambar);
  color: #fff;
}

/* Confirm modal */
.confirm-content p {
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  margin: 0 0 8px;
  line-height: 1.5;
}

.confirm-hint {
  color: var(--texto-light) !important;
  font-size: 13px !important;
}

.reabastecer-info {
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  color: var(--texto);
  margin: 0;
}

/* Toggle exige_comprovacao */
.comprovacao-toggle {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  user-select: none;
}

.toggle-track {
  position: relative;
  width: 44px;
  min-width: 44px;
  height: 26px;
  border-radius: 13px;
  background: #ddd;
  transition: background 0.25s;
}

.toggle-track.active {
  background: var(--verde-salvia);
}

.toggle-thumb {
  position: absolute;
  top: 3px;
  left: 3px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #fff;
  box-shadow: 0 1px 4px rgba(0,0,0,.2);
  transition: transform 0.25s;
}

.toggle-track.active .toggle-thumb {
  transform: translateX(18px);
}

.toggle-label-text {
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 500;
  color: var(--texto);
  display: flex;
  align-items: center;
  gap: 6px;
}

.toggle-hint {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
  margin: 2px 0 0;
}

/* Modal de tomada */
.tomada-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.tomada-med-nome {
  font-family: var(--font-titulo);
  font-size: 1.15rem;
  color: var(--texto);
  margin: 0;
}

.tomada-hint {
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto-light);
  margin: 0;
}

.foto-upload-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  border: 2px dashed #ddd;
  border-radius: 14px;
  padding: 28px 16px;
  cursor: pointer;
  transition: border-color 0.25s;
  position: relative;
  overflow: hidden;
  min-height: 130px;
}

.foto-upload-area:hover {
  border-color: var(--verde-salvia);
}

.foto-upload-area.has-foto {
  padding: 0;
  border-style: solid;
  border-color: var(--verde-salvia);
}

.foto-input-hidden {
  position: absolute;
  inset: 0;
  opacity: 0;
  cursor: pointer;
  width: 100%;
  height: 100%;
}

.foto-icon {
  color: #bbb;
}

.foto-upload-label {
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--texto-light);
}

.foto-preview {
  width: 100%;
  max-height: 200px;
  object-fit: cover;
  border-radius: 12px;
  display: block;
}

.foto-trocar {
  position: absolute;
  bottom: 8px;
  right: 10px;
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 600;
  color: #fff;
  background: rgba(0,0,0,.45);
  padding: 3px 8px;
  border-radius: 8px;
}
</style>
