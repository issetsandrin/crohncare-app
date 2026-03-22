<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useDiarioStore } from '../stores/diario'
import { useCrisesStore } from '../stores/crises'
import MesNavegacao from '../components/MesNavegacao.vue'
import ModalBase from '../components/ModalBase.vue'
import DiarioForm from '../components/DiarioForm.vue'
import CriseForm from '../components/CriseForm.vue'
import IntensidadeDots from '../components/IntensidadeDots.vue'
import AppBar from '../components/AppBar.vue'

const diarioStore = useDiarioStore()
const crisesStore = useCrisesStore()

const activeTab = ref('anotacoes')
const mesAtual = ref(diarioStore.mesAtual)
const showFormModal = ref(false)
const showCriseFormModal = ref(false)
const showDeleteModal = ref(false)
const showDetailModal = ref(false)
const editingEntrada = ref(null)
const deletingEntrada = ref(null)
const deletingType = ref('diario')
const selectedItem = ref(null)
const selectedType = ref('diario')

onMounted(async () => {
  await Promise.all([
    diarioStore.fetchMes(mesAtual.value),
    crisesStore.fetchAll()
  ])
})

watch(mesAtual, (novoMes) => {
  diarioStore.fetchMes(novoMes)
})

const entradasOrdenadas = computed(() => {
  return [...diarioStore.entradas].sort((a, b) => {
    return (b.data || '').localeCompare(a.data || '')
  })
})

const crisesMes = computed(() => {
  return crisesStore.lista.filter((c) => {
    if (!c.data_hora) return false
    return c.data_hora.slice(0, 7) === mesAtual.value
  })
})

function abrirNovaEntrada() {
  editingEntrada.value = null
  selectedItem.value = null
  selectedType.value = 'diario'
  showFormModal.value = true
}

function abrirNovaCrise() {
  selectedItem.value = null
  selectedType.value = 'crise'
  showCriseFormModal.value = true
}

function abrirDetalhes(item, type = 'diario') {
  selectedItem.value = item
  selectedType.value = type
  showDetailModal.value = true
}

function editarDoDetalhe() {
  showDetailModal.value = false
  if (selectedType.value === 'crise') {
    showCriseFormModal.value = true
  } else {
    editingEntrada.value = selectedItem.value
    showFormModal.value = true
  }
}

function excluirDoDetalhe() {
  showDetailModal.value = false
  deletingEntrada.value = selectedItem.value
  deletingType.value = selectedType.value
  showDeleteModal.value = true
}

async function salvarEntrada(dados) {
  try {
    if (editingEntrada.value) {
      await diarioStore.update(editingEntrada.value.id, dados)
    } else {
      await diarioStore.create(dados)
    }
    showFormModal.value = false
    editingEntrada.value = null
    selectedItem.value = null
  } catch (e) {}
}

async function salvarCrise(dados) {
  try {
    if (selectedItem.value && selectedType.value === 'crise') {
      await crisesStore.update(selectedItem.value.id, dados)
    } else {
      await crisesStore.create(dados)
    }
    showCriseFormModal.value = false
    selectedItem.value = null
  } catch (e) {}
}

async function confirmarDelete() {
  if (!deletingEntrada.value) return
  try {
    if (deletingType.value === 'crise') {
      await crisesStore.remove(deletingEntrada.value.id)
    } else {
      await diarioStore.remove(deletingEntrada.value.id)
    }
    showDeleteModal.value = false
    deletingEntrada.value = null
  } catch (e) {}
}

async function exportarCSV() {
  try {
    const blob = await diarioStore.exportar(mesAtual.value)
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `diario-${mesAtual.value}.csv`
    a.click()
    URL.revokeObjectURL(url)
  } catch (e) {}
}

function formatarData(data) {
  if (!data) return ''
  const d = new Date(data + 'T00:00:00')
  return d.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatarDataHora(dataHora) {
  if (!dataHora) return ''
  const d = new Date(dataHora)
  return d.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' }) +
    ' · ' + d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <main class="diario-page">
    <AppBar title="Diário" subtitle="Registre sintomas e acompanhe seu dia a dia" />

    <div class="page-content">
    <MesNavegacao v-model="mesAtual" />

    <!-- Tabs -->
    <div class="tabs">
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'anotacoes' }"
        @click="activeTab = 'anotacoes'"
      >
        Anotações
        <span v-if="entradasOrdenadas.length > 0" class="tab-count">{{ entradasOrdenadas.length }}</span>
      </button>
      <button
        class="tab-btn"
        :class="{ active: activeTab === 'crises' }"
        @click="activeTab = 'crises'"
      >
        Crises
        <span v-if="crisesMes.length > 0" class="tab-count tab-count-crise">{{ crisesMes.length }}</span>
      </button>
    </div>

    <div v-if="diarioStore.loading" class="loading-state">
      <p>Carregando...</p>
    </div>

    <!-- Tab: Anotações -->
    <div v-else-if="activeTab === 'anotacoes'" class="tab-content">
      <div v-if="entradasOrdenadas.length === 0" class="empty-state">
        <svg class="empty-icon" width="40" height="40" viewBox="0 0 24 24" fill="none">
          <path d="M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1zM3 8h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M7 13h4M7 16h6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
        </svg>
        <p>Nenhuma anotação neste mês</p>
      </div>
      <template v-else>
        <div class="entries-list">
          <div
            v-for="(entrada, i) in entradasOrdenadas"
            :key="entrada.id"
            class="entry-card clickable"
            :style="{ animationDelay: i * 0.04 + 's' }"
            @click="abrirDetalhes(entrada, 'diario')"
          >
            <div class="entry-header">
              <span class="entry-data">{{ formatarData(entrada.data) }}</span>
              <IntensidadeDots :valor="entrada.intensidade" />
            </div>
            <p v-if="entrada.sintomas" class="entry-sintomas">{{ entrada.sintomas }}</p>
            <p v-if="entrada.observacoes" class="entry-obs-preview">{{ entrada.observacoes }}</p>
          </div>
        </div>
      </template>
    </div>

    <!-- Tab: Crises -->
    <div v-else-if="activeTab === 'crises'" class="tab-content">
      <div v-if="crisesMes.length === 0" class="empty-state">
        <svg class="empty-icon" width="40" height="40" viewBox="0 0 24 24" fill="none">
          <path d="M12 9v4M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <p>Nenhuma crise registrada neste mês</p>
      </div>
      <template v-else>
        <div class="entries-list">
          <div
            v-for="(crise, i) in crisesMes"
            :key="crise.id"
            class="entry-card crise-card clickable"
            :style="{ animationDelay: i * 0.04 + 's' }"
            @click="abrirDetalhes(crise, 'crise')"
          >
            <div class="entry-header">
              <span class="entry-data crise-data">{{ formatarDataHora(crise.data_hora) }}</span>
              <IntensidadeDots :valor="crise.intensidade" />
            </div>
            <p v-if="crise.sintomas" class="entry-sintomas">{{ crise.sintomas }}</p>
            <p v-if="crise.observacoes" class="entry-obs-preview">{{ crise.observacoes }}</p>
          </div>
        </div>
      </template>
    </div>

    <!-- FAB -->
    <button class="fab" @click="activeTab === 'crises' ? abrirNovaCrise() : abrirNovaEntrada()" aria-label="Adicionar">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
      </svg>
    </button>

    </div>

    <!-- Modal Detalhes -->
    <ModalBase v-model="showDetailModal" :title="selectedType === 'crise' ? 'Detalhes da Crise' : 'Detalhes da Anotação'">
      <div v-if="selectedItem" class="detail-content">
        <div class="detail-row">
          <span class="detail-label">{{ selectedType === 'crise' ? 'Data e Hora' : 'Data' }}</span>
          <span class="detail-value">
            {{ selectedType === 'crise' ? formatarDataHora(selectedItem.data_hora) : formatarData(selectedItem.data) }}
          </span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Intensidade</span>
          <IntensidadeDots :valor="selectedItem.intensidade" />
        </div>
        <div v-if="selectedItem.sintomas" class="detail-row">
          <span class="detail-label">Sintomas</span>
          <div class="detail-tags">
            <span v-for="tag in selectedItem.sintomas.split(',')" :key="tag" class="detail-tag" :class="{ 'detail-tag-crise': selectedType === 'crise' }">
              {{ tag.trim() }}
            </span>
          </div>
        </div>
        <div v-if="selectedType === 'crise' && selectedItem.duracao_estimada" class="detail-row">
          <span class="detail-label">Duração</span>
          <span class="detail-value">{{ selectedItem.duracao_estimada }}</span>
        </div>
        <div v-if="selectedItem.observacoes" class="detail-row">
          <span class="detail-label">Observações</span>
          <p class="detail-obs">{{ selectedItem.observacoes }}</p>
        </div>
        <div class="detail-actions">
          <button class="btn btn-primary" @click="editarDoDetalhe">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
              <path d="M12 1.5l2.5 2.5-8 8H4v-2.5l8-8z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Editar
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

    <!-- Modal Nova/Editar Entrada -->
    <ModalBase v-model="showFormModal" :title="editingEntrada ? 'Editar Entrada' : 'Nova Entrada'">
      <DiarioForm
        :diario="editingEntrada"
        @submit="salvarEntrada"
        @cancel="showFormModal = false"
      />
    </ModalBase>

    <!-- Modal Nova/Editar Crise -->
    <ModalBase v-model="showCriseFormModal" :title="selectedItem && selectedType === 'crise' ? 'Editar Crise' : 'Registrar Crise'">
      <CriseForm
        :crise="selectedType === 'crise' ? selectedItem : null"
        @submit="salvarCrise"
        @cancel="showCriseFormModal = false; selectedItem = null"
      />
    </ModalBase>

    <!-- Modal Confirmar Delete -->
    <ModalBase v-model="showDeleteModal" title="Confirmar Exclusão">
      <div class="confirm-content">
        <p>Tem certeza que deseja excluir este registro?</p>
        <p class="confirm-hint">Esta ação não pode ser desfeita.</p>
        <div class="form-actions">
          <button class="btn btn-secondary" @click="showDeleteModal = false">Cancelar</button>
          <button class="btn btn-danger" @click="confirmarDelete" :disabled="diarioStore.loading">Excluir</button>
        </div>
      </div>
    </ModalBase>
  </main>
</template>

<style scoped>
.diario-page {
  padding-bottom: calc(80px + env(safe-area-inset-bottom, 0px));
  height: 100dvh;
  overflow: hidden;
}

.page-content {
  padding: 0 16px;
}

/* Tabs */
.tabs {
  display: flex;
  gap: 4px;
  background: rgba(76, 175, 80, 0.1);
  border-radius: 12px;
  padding: 4px;
  margin: 12px 0 16px;
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

.tab-count-crise {
  background: var(--terracota);
}

/* Content */
.tab-content {
  animation: fadeInUp 0.35s var(--ease-out-smooth);
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

/* Entries */
.entries-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.entry-card {
  background: #fff;
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
  animation: fadeInUp 0.4s var(--ease-out-smooth) both;
}

.crise-card {
  border-left: 3px solid var(--terracota);
}

.entry-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.entry-data {
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: var(--texto-light);
}

.crise-data {
  color: var(--terracota);
}

.entry-card.clickable {
  cursor: pointer;
  transition: transform 0.3s var(--ease-smooth), box-shadow 0.3s var(--ease-smooth);
}

.entry-card.clickable:active {
  transform: scale(0.98);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}

.entry-sintomas {
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  margin: 0;
  line-height: 1.5;
}

.entry-obs-preview {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
  margin: 4px 0 0;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
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

.detail-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.detail-tag {
  padding: 5px 10px;
  background: rgba(76, 175, 80, 0.1);
  color: var(--verde-salvia);
  border-radius: 6px;
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 500;
}

.detail-tag-crise {
  background: rgba(229, 115, 115, 0.1);
  color: var(--terracota);
}

.detail-obs {
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  line-height: 1.5;
  margin: 0;
}

.detail-actions {
  display: flex;
  gap: 12px;
  margin-top: 8px;
}

.detail-actions .btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.btn-primary {
  background: var(--verde-salvia);
  color: #fff;
}

.btn-danger-outline {
  background: var(--terracota);
  color: #fff;
}

.btn-danger-outline:active {
  opacity: 0.8;
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

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 16px;
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

.btn-secondary {
  background: #f0f0f0;
  color: var(--texto);
}

.btn-danger {
  background: var(--terracota);
  color: #fff;
}
</style>
