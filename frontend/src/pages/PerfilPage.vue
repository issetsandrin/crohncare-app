<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useNotificacoesStore } from '../stores/notificacoes'
import api from '../composables/useApi'
import AppBar from '../components/AppBar.vue'
import ModalBase from '../components/ModalBase.vue'

const router = useRouter()
const auth = useAuthStore()
const notifStore = useNotificacoesStore()

const perfil = ref(null)
const loadingPerfil = ref(true)
const showEditNome = ref(false)
const editNome = ref('')
const salvandoNome = ref(false)

onMounted(async () => {
  notifStore.verificarPermissao()
  try {
    const res = await api.get('/perfil-crohn')
    perfil.value = res.data
  } catch (e) {
    // sem perfil
  } finally {
    loadingPerfil.value = false
  }
})

const initials = computed(() => {
  const nome = auth.user?.nome || ''
  return nome.split(' ').map(p => p[0]).filter(Boolean).slice(0, 2).join('').toUpperCase()
})

const tipoLabels = {
  crohn: 'Doenca de Crohn',
  retocolite: 'Retocolite Ulcerativa',
  indeterminada: 'Colite Indeterminada',
  nao_sei: 'Nao tenho certeza'
}

const localizacaoLabels = {
  ileal: 'Ileo (intestino delgado)',
  colonico: 'Colon (intestino grosso)',
  ileocolonico: 'Ileocolonico (ambos)',
  trato_superior: 'Trato superior',
  nao_sei: 'Nao sei'
}

const situacaoLabels = {
  remissao: 'Em remissao',
  crise_leve: 'Crise leve',
  crise_moderada: 'Crise moderada',
  crise_grave: 'Crise intensa',
  recente: 'Diagnostico recente'
}

const situacaoColors = {
  remissao: '#4CAF50',
  crise_leve: '#D4A03C',
  crise_moderada: '#E88A3A',
  crise_grave: '#E05555',
  recente: '#5B93C7'
}

const permissionStatus = computed(() => notifStore.permissao)
const notificacoesAtivas = computed(() => notifStore.ativa)

async function toggleNotificacoes() {
  if (!('Notification' in window)) return
  if (!notificacoesAtivas.value) {
    await notifStore.ativar()
  } else {
    notifStore.desativar()
  }
}

const statusLabel = {
  granted: 'Permitidas',
  denied: 'Bloqueadas pelo navegador',
  default: 'Nao solicitadas',
  unsupported: 'Nao suportadas neste navegador'
}

function abrirEditNome() {
  editNome.value = auth.user?.nome || ''
  showEditNome.value = true
}

async function salvarNome() {
  if (!editNome.value.trim()) return
  salvandoNome.value = true
  const ok = await auth.updateProfile({ nome: editNome.value.trim() })
  salvandoNome.value = false
  if (ok) showEditNome.value = false
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}

const anosDiagnostico = computed(() => {
  if (!perfil.value?.ano_diagnostico) return null
  const anos = new Date().getFullYear() - perfil.value.ano_diagnostico
  if (anos === 0) return 'Este ano'
  if (anos === 1) return 'Ha 1 ano'
  return `Ha ${anos} anos`
})
</script>

<template>
  <div class="perfil-page">
    <AppBar title="Perfil" subtitle="Suas informacoes" />

    <div class="page-content">
      <!-- Avatar + Info -->
      <section class="hero-section">
        <div class="avatar">
          <span class="avatar-text">{{ initials }}</span>
        </div>
        <h2 class="hero-nome">{{ auth.user?.nome }}</h2>
        <span class="hero-email">{{ auth.user?.email }}</span>
        <button class="btn-edit-nome" @click="abrirEditNome">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Editar nome
        </button>
      </section>

      <!-- Perfil Crohn -->
      <section v-if="perfil && !loadingPerfil" class="section">
        <h3 class="section-title">Meu perfil de saude</h3>

        <div class="info-grid">
          <div v-if="perfil.tipo_doenca" class="info-card">
            <div class="info-icon-wrap tipo">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <span class="info-label">Diagnostico</span>
            <span class="info-value">{{ tipoLabels[perfil.tipo_doenca] || perfil.tipo_doenca }}</span>
          </div>

          <div v-if="perfil.ano_diagnostico" class="info-card">
            <div class="info-icon-wrap tempo">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </div>
            <span class="info-label">Diagnosticado</span>
            <span class="info-value">{{ perfil.ano_diagnostico }} <span class="info-sub">({{ anosDiagnostico }})</span></span>
          </div>

          <div v-if="perfil.localizacao" class="info-card">
            <div class="info-icon-wrap local">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" stroke="currentColor" stroke-width="2"/>
                <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
              </svg>
            </div>
            <span class="info-label">Localizacao</span>
            <span class="info-value">{{ localizacaoLabels[perfil.localizacao] || perfil.localizacao }}</span>
          </div>

          <div v-if="perfil.situacao_atual" class="info-card">
            <div class="info-icon-wrap situacao" :style="{ background: `color-mix(in srgb, ${situacaoColors[perfil.situacao_atual] || '#888'} 12%, transparent)`, color: situacaoColors[perfil.situacao_atual] || '#888' }">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" stroke="currentColor" stroke-width="2"/>
                <path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </div>
            <span class="info-label">Momento atual</span>
            <span class="info-value" :style="{ color: situacaoColors[perfil.situacao_atual] }">{{ situacaoLabels[perfil.situacao_atual] || perfil.situacao_atual }}</span>
          </div>
        </div>

        <div v-if="perfil.tem_acompanhamento !== null" class="acompanhamento-card">
          <div class="acomp-row">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="8.5" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
              <path d="M20 8v6M23 11h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <div class="acomp-info">
              <span class="acomp-label">Acompanhamento medico</span>
              <span class="acomp-value">{{ perfil.tem_acompanhamento ? 'Sim' : 'Nao' }}{{ perfil.frequencia_consultas ? ' · ' + perfil.frequencia_consultas : '' }}</span>
            </div>
          </div>
        </div>

        <div v-if="perfil.objetivos && perfil.objetivos.length" class="objetivos-section">
          <span class="obj-title">Meus objetivos</span>
          <div class="obj-list">
            <span v-for="obj in perfil.objetivos" :key="obj" class="obj-chip">{{ obj }}</span>
          </div>
        </div>
      </section>

      <section v-else-if="loadingPerfil" class="section">
        <div class="loading-dots">
          <span></span><span></span><span></span>
        </div>
      </section>

      <!-- Configuracoes -->
      <section class="section">
        <h3 class="section-title">Configuracoes</h3>
        <div class="setting-card">
          <div class="setting-row">
            <div class="setting-info">
              <span class="setting-label">Notificacoes de remedios</span>
              <span class="setting-hint">{{ statusLabel[permissionStatus] || permissionStatus }}</span>
            </div>
            <button
              class="toggle"
              :class="{ active: notificacoesAtivas }"
              @click="toggleNotificacoes"
              role="switch"
              :aria-checked="notificacoesAtivas"
            >
              <span class="toggle-thumb" />
            </button>
          </div>
          <p v-if="permissionStatus === 'denied'" class="setting-denied">
            As notificacoes foram bloqueadas. Altere nas configuracoes do navegador.
          </p>
        </div>
      </section>

      <!-- Sobre + Sair -->
      <section class="section">
        <div class="about-card">
          <span class="about-name">CrohnCare</span>
          <span class="about-version">v1.0.0</span>
        </div>

        <button class="btn-logout" @click="handleLogout">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Sair da conta
        </button>
      </section>
    </div>

    <!-- Modal editar nome -->
    <ModalBase v-model="showEditNome" title="Editar nome">
      <form class="edit-form" @submit.prevent="salvarNome">
        <input v-model="editNome" type="text" class="edit-input" placeholder="Seu nome" required />
        <button type="submit" class="btn-save" :disabled="salvandoNome">
          {{ salvandoNome ? 'Salvando...' : 'Salvar' }}
        </button>
      </form>
    </ModalBase>
  </div>
</template>

<style scoped>
.perfil-page {
  height: 100dvh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.page-content {
  flex: 1;
  overflow-y: auto;
  padding: 0 16px calc(80px + env(safe-area-inset-bottom, 0px) + 16px);
}

/* Hero */
.hero-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 8px 0 24px;
  gap: 4px;
}

.avatar {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--verde-salvia), #8BC34A);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 8px;
}

.avatar-text {
  font-family: var(--font-titulo);
  font-size: 1.5rem;
  color: #fff;
  font-weight: 700;
}

.hero-nome {
  font-family: var(--font-titulo);
  font-size: 1.3rem;
  color: var(--texto);
  margin: 0;
}

.hero-email {
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--texto-light);
}

.btn-edit-nome {
  display: flex;
  align-items: center;
  gap: 4px;
  margin-top: 8px;
  font-family: var(--font-corpo);
  font-size: 12px;
  font-weight: 600;
  color: var(--verde-salvia);
  background: rgba(127, 168, 50, 0.08);
  border: none;
  border-radius: 8px;
  padding: 6px 12px;
  cursor: pointer;
}

/* Section */
.section {
  margin-bottom: 24px;
}

.section-title {
  font-family: var(--font-titulo);
  font-size: 1.1rem;
  color: var(--texto);
  margin: 0 0 12px;
}

/* Info grid */
.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.info-card {
  background: #fff;
  border-radius: 14px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.info-icon-wrap {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.info-icon-wrap.tipo {
  background: rgba(127, 168, 50, 0.1);
  color: var(--verde-salvia);
}

.info-icon-wrap.tempo {
  background: rgba(91, 147, 199, 0.1);
  color: #5B93C7;
}

.info-icon-wrap.local {
  background: rgba(212, 160, 60, 0.1);
  color: #D4A03C;
}

.info-label {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 600;
  color: var(--texto-light);
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.info-value {
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: var(--texto);
  line-height: 1.3;
}

.info-sub {
  font-weight: 400;
  font-size: 11px;
  color: var(--texto-light);
}

/* Acompanhamento */
.acompanhamento-card {
  background: #fff;
  border-radius: 14px;
  padding: 14px;
  margin-top: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.acomp-row {
  display: flex;
  align-items: center;
  gap: 12px;
  color: var(--verde-salvia);
}

.acomp-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.acomp-label {
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: var(--texto);
}

.acomp-value {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
}

/* Objetivos */
.objetivos-section {
  margin-top: 12px;
}

.obj-title {
  font-family: var(--font-corpo);
  font-size: 12px;
  font-weight: 600;
  color: var(--texto-light);
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.obj-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 8px;
}

.obj-chip {
  font-family: var(--font-corpo);
  font-size: 12px;
  font-weight: 500;
  color: var(--verde-salvia);
  background: rgba(127, 168, 50, 0.08);
  border: 1px solid rgba(127, 168, 50, 0.2);
  border-radius: 20px;
  padding: 6px 12px;
}

/* Settings */
.setting-card {
  background: #fff;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.setting-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.setting-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.setting-label {
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  color: var(--texto);
}

.setting-hint {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
}

.setting-denied {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--terracota);
  margin: 8px 0 0;
  line-height: 1.4;
}

/* Toggle */
.toggle {
  position: relative;
  width: 48px;
  height: 28px;
  border-radius: 14px;
  background: #ddd;
  border: none;
  cursor: pointer;
  transition: background 0.35s var(--ease-smooth);
  padding: 0;
  flex-shrink: 0;
}

.toggle.active {
  background: var(--verde-salvia);
}

.toggle-thumb {
  position: absolute;
  top: 3px;
  left: 3px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #fff;
  transition: transform 0.35s var(--ease-smooth);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
}

.toggle.active .toggle-thumb {
  transform: translateX(20px);
}

/* About */
.about-card {
  display: flex;
  align-items: baseline;
  gap: 8px;
  margin-bottom: 12px;
}

.about-name {
  font-family: var(--font-titulo);
  font-size: 1rem;
  color: var(--verde-salvia);
}

.about-version {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
}

/* Logout */
.btn-logout {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 14px;
  border-radius: 12px;
  border: 1.5px solid rgba(229, 115, 115, 0.3);
  background: rgba(229, 115, 115, 0.06);
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  color: var(--terracota);
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-logout:active {
  transform: scale(0.98);
  opacity: 0.8;
}

/* Loading */
.loading-dots {
  display: flex;
  justify-content: center;
  gap: 6px;
  padding: 32px 0;
}

.loading-dots span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--verde-salvia);
  animation: bounce 1s ease infinite;
}

.loading-dots span:nth-child(2) { animation-delay: 0.15s; }
.loading-dots span:nth-child(3) { animation-delay: 0.3s; }

@keyframes bounce {
  0%, 80%, 100% { opacity: 0.3; transform: scale(0.8); }
  40% { opacity: 1; transform: scale(1); }
}

/* Edit modal */
.edit-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.edit-input {
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 12px 14px;
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  width: 100%;
  box-sizing: border-box;
}

.edit-input:focus {
  outline: none;
  border-color: var(--verde-salvia);
  box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.btn-save {
  background: var(--verde-salvia);
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 14px;
  font-family: var(--font-corpo);
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.3s;
}

.btn-save:disabled {
  opacity: 0.6;
}

.btn-save:active {
  transform: scale(0.98);
}
</style>
