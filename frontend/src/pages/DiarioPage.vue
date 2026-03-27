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
import LoadingDots from '../components/LoadingDots.vue'

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

    <div class="desktop-page-header">
      <div class="dph-left">
        <div class="dph-text">
          <h1 class="dph-title">Diário de Saúde</h1>
          <p class="dph-subtitle">Registre anotações, sintomas e crises ao longo do tempo</p>
        </div>
      </div>
      <div class="dph-right">
        <MesNavegacao v-model="mesAtual" />
        <div class="dph-actions">
          <button class="dph-action secondary" @click="abrirNovaCrise">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
            Registrar crise
          </button>
          <button class="dph-action" @click="abrirNovaEntrada">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
            Nova anotação
          </button>
        </div>
      </div>
    </div>

    <div class="page-content">
    <MesNavegacao v-model="mesAtual" class="mobile-mes-nav" />

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

    <LoadingDots v-if="diarioStore.loading" />

    <!-- Tab: Anotações -->
    <div v-else-if="activeTab === 'anotacoes'" class="tab-content">
      <div v-if="entradasOrdenadas.length === 0" class="empty-state">
        <svg class="empty-icon" width="40" height="40" viewBox="0 0 24 24" fill="none">
          <path d="M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1zM3 8h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M7 13h4M7 16h6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
        </svg>
        <p>Nenhuma anotação neste mês</p>
        <p class="empty-hint">Toque em + para registrar</p>
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
          <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/>
          <path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <p>Nenhuma crise neste mês</p>
        <p class="empty-hint">Toque em + para registrar uma crise</p>
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
    <ModalBase v-model="showDetailModal" title="">
      <template v-if="selectedItem" #header>
        <div class="detail-icon-wrap" :class="{ 'crise-icon-wrap': selectedType === 'crise' }">
          <svg v-if="selectedType === 'crise'" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 9v4M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1zM3 8h18M8 4v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M7 13h4M7 16h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          </svg>
        </div>
        <div class="detail-header-text">
          <span class="detail-tipo" :class="selectedType">{{ selectedType === 'crise' ? 'Crise' : 'Anotação' }}</span>
          <span class="detail-data-text">{{ selectedType === 'crise' ? formatarDataHora(selectedItem.data_hora) : formatarData(selectedItem.data) }}</span>
        </div>
      </template>

      <div v-if="selectedItem" class="detail">
        <div class="detail-intensidade-row">
          <span class="detail-row-label">Intensidade</span>
          <IntensidadeDots :valor="selectedItem.intensidade" />
        </div>

        <div v-if="selectedItem.sintomas" class="detail-tags">
          <span v-for="tag in selectedItem.sintomas.split(',')" :key="tag" class="detail-tag" :class="selectedType">{{ tag.trim() }}</span>
        </div>

        <div v-if="selectedType === 'crise' && selectedItem.duracao_estimada" class="detail-row">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
            <path d="M12 7v5l3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          </svg>
          <span>{{ selectedItem.duracao_estimada }}</span>
        </div>

        <div v-if="selectedItem.observacoes" class="detail-obs" :class="{ 'detail-obs-crise': selectedType === 'crise' }">
          <p>{{ selectedItem.observacoes }}</p>
        </div>

        <div class="detail-actions">
          <button class="btn-detail edit" @click="editarDoDetalhe">Editar</button>
          <button class="btn-detail delete" @click="excluirDoDetalhe">Excluir</button>
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

.desktop-page-header {
  display: none;
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
    display: none;
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
  color: var(--verde-salvia);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.crise-icon-wrap {
  background: linear-gradient(135deg, rgba(229, 115, 115, 0.15), rgba(229, 115, 115, 0.08));
  color: var(--terracota);
}

.detail-header-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
  margin-left: 12px;
}

.detail-tipo {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--verde-salvia);
}

.detail-tipo.crise {
  color: var(--terracota);
}

.detail-data-text {
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--texto-light);
}

.detail-intensidade-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.detail-row-label {
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--texto-light);
}

.detail-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.detail-tag {
  padding: 5px 10px;
  background: rgba(127, 168, 50, 0.1);
  color: var(--verde-salvia);
  border-radius: 6px;
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 500;
}

.detail-tag.crise {
  background: rgba(229, 115, 115, 0.1);
  color: var(--terracota);
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

.detail-obs.detail-obs-crise {
  border-left-color: var(--terracota);
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
  background: rgba(229, 115, 115, 0.1);
  color: var(--terracota);
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

/* ── Desktop ── */
@media (min-width: 769px) {
  .diario-page {
    padding-bottom: 0;
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
  }

  /* Tabs estilo website (underline) */
  .tabs {
    background: none;
    border-radius: 0;
    padding: 0 40px;
    margin: 0;
    border-bottom: 1.5px solid #eee;
    gap: 0;
  }

  .tab-btn {
    flex: none;
    padding: 12px 20px;
    border-radius: 0;
    font-size: 14px;
    font-weight: 500;
    color: var(--texto-light);
    border-bottom: 2px solid transparent;
    margin-bottom: -1.5px;
    transition: color 0.15s, border-color 0.15s;
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

  .tab-count-crise {
    background: rgba(229, 115, 115, 0.12);
    color: var(--terracota);
  }

  .mobile-mes-nav {
    display: none;
  }

  .desktop-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px 40px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.07);
    background: transparent;
    flex-shrink: 0;
    gap: 24px;
  }

  .dph-left {
    flex: 1;
    min-width: 0;
  }

  .dph-right {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-shrink: 0;
  }

  /* O MesNavegacao dentro do dph-right fica menor */
  .dph-right :deep(.mes-navegacao) {
    padding: 0;
  }

  .dph-right :deep(.mes-label) {
    font-size: 14px;
    font-weight: 600;
    color: var(--texto);
    min-width: 130px;
    text-align: center;
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

  .dph-actions {
    display: flex;
    gap: 10px;
  }

  .dph-action {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: var(--verde-salvia);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-family: var(--font-corpo);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s var(--ease-smooth), transform 0.15s var(--ease-smooth);
  }

  .dph-action:hover {
    background: var(--verde-claro);
    transform: translateY(-1px);
  }

  .dph-action.secondary {
    background: transparent;
    color: var(--verde-salvia);
    border: 1.5px solid var(--verde-salvia);
  }

  .dph-action.secondary:hover {
    background: rgba(127, 168, 50, 0.06);
    transform: translateY(-1px);
  }

  .page-content {
    flex: 1;
    overflow-y: auto;
    padding: 0 40px 40px;
  }

  .entries-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 14px;
    align-items: start;
  }

  .entry-card {
    background: transparent;
    box-shadow: none;
    border: 1px solid rgba(0, 0, 0, 0.08);
    transition: border-color 0.2s var(--ease-smooth);
  }

  .entry-card:hover {
    border-color: var(--verde-salvia);
    transform: translateY(-1px);
  }
}
</style>
