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
const stats = ref(null)
const loading = ref(true)
const showEditNome = ref(false)
const editNome = ref('')
const salvandoNome = ref(false)

onMounted(async () => {
  notifStore.verificarPermissao()
  try {
    const [perfilRes, statsRes] = await Promise.all([
      api.get('/perfil-crohn').catch(() => ({ data: null })),
      api.get('/perfil/stats').catch(() => ({ data: null }))
    ])
    perfil.value = perfilRes.data
    stats.value = statsRes.data
  } finally {
    loading.value = false
  }
})

const initials = computed(() => {
  const nome = auth.user?.nome || ''
  return nome.split(' ').map(p => p[0]).filter(Boolean).slice(0, 2).join('').toUpperCase()
})

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

const tipoLabels = {
  crohn: 'Doenca de Crohn',
  retocolite: 'Retocolite Ulcerativa',
  indeterminada: 'Colite Indeterminada',
  nao_sei: 'Nao definido'
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

function formatarProximaConsulta(consulta) {
  if (!consulta) return null
  const d = new Date(consulta.data_hora)
  const hoje = new Date()
  const diff = Math.ceil((d - hoje) / (1000 * 60 * 60 * 24))
  let quando = ''
  if (diff === 0) quando = 'Hoje'
  else if (diff === 1) quando = 'Amanha'
  else quando = `Em ${diff} dias`
  const hora = d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
  return { quando, hora, medico: consulta.medico, especialidade: consulta.especialidade }
}
</script>

<template>
  <div class="perfil-page">
    <AppBar title="Perfil" subtitle="Seu resumo pessoal" />

    <div class="page-content">
      <!-- Loading -->
      <div v-if="loading" class="loading-state">
        <div class="loading-dots"><span></span><span></span><span></span></div>
      </div>

      <template v-else>
        <!-- Header -->
        <section class="hero">
          <div class="avatar">
            <span class="avatar-text">{{ initials }}</span>
          </div>
          <div class="hero-info">
            <h2 class="hero-nome">{{ auth.user?.nome }}</h2>
            <span class="hero-email">{{ auth.user?.email }}</span>
            <div class="hero-tags">
              <span v-if="perfil?.tipo_doenca" class="hero-tag">{{ tipoLabels[perfil.tipo_doenca] || perfil.tipo_doenca }}</span>
              <span v-if="stats?.membro_desde" class="hero-tag membro">Desde {{ stats.membro_desde }}</span>
            </div>
          </div>
          <button class="btn-edit" @click="abrirEditNome" aria-label="Editar nome">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </section>

        <!-- Situacao atual -->
        <section v-if="perfil?.situacao_atual" class="situacao-banner" :style="{ '--cor': situacaoColors[perfil.situacao_atual] || '#888' }">
          <div class="situacao-dot"></div>
          <span class="situacao-text">{{ situacaoLabels[perfil.situacao_atual] || perfil.situacao_atual }}</span>
        </section>

        <!-- Stats grid -->
        <section v-if="stats" class="stats-section">
          <div class="stats-grid">
            <div class="stat-card">
              <span class="stat-number">{{ stats.meds_ativos }}</span>
              <span class="stat-label">Remedios ativos</span>
              <svg class="stat-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                <rect x="6" y="2" width="12" height="20" rx="6" stroke="currentColor" stroke-width="1.5"/>
                <line x1="6" y1="12" x2="18" y2="12" stroke="currentColor" stroke-width="1.5"/>
              </svg>
            </div>

            <div class="stat-card">
              <span class="stat-number">{{ stats.total_diarios }}</span>
              <span class="stat-label">Registros no diario</span>
              <svg class="stat-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1zM3 8h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </div>

            <div class="stat-card" :class="{ destaque: stats.dias_sem_crise !== null && stats.dias_sem_crise >= 7 }">
              <span class="stat-number">{{ stats.dias_sem_crise !== null ? stats.dias_sem_crise : '-' }}</span>
              <span class="stat-label">{{ stats.dias_sem_crise !== null ? 'Dias sem crise' : 'Nenhuma crise' }}</span>
              <svg class="stat-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" stroke="currentColor" stroke-width="1.5"/>
                <path d="M8 14s1.5 2 4 2 4-2 4-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M9 9h.01M15 9h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </div>

            <div class="stat-card">
              <span class="stat-number">{{ stats.consultas_realizadas }}</span>
              <span class="stat-label">Consultas realizadas</span>
              <svg class="stat-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                <rect x="3" y="6" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                <path d="M3 10h18M8 2v4M16 2v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </div>
          </div>

          <!-- Streak -->
          <div v-if="stats.streak_diario > 0" class="streak-card">
            <div class="streak-fire">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M12 2c.5 4-3 6-3 10a5 5 0 0010 0c0-4-2-6-3-8-1 2-3 3-4 2 0-2 .5-4 0-2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div class="streak-info">
              <span class="streak-count">{{ stats.streak_diario }} {{ stats.streak_diario === 1 ? 'dia' : 'dias' }} seguidos</span>
              <span class="streak-hint">registrando no diario</span>
            </div>
          </div>
        </section>

        <!-- Proxima consulta -->
        <section v-if="stats?.proxima_consulta" class="section">
          <h3 class="section-title">Proxima consulta</h3>
          <div class="proxima-card" @click="router.push('/consultas')">
            <div class="proxima-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <rect x="3" y="6" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
                <path d="M3 10h18M8 2v4M16 2v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
              </svg>
            </div>
            <div class="proxima-info">
              <span class="proxima-medico">{{ formatarProximaConsulta(stats.proxima_consulta).medico }}</span>
              <span v-if="stats.proxima_consulta.especialidade" class="proxima-esp">{{ stats.proxima_consulta.especialidade }}</span>
              <span class="proxima-quando">{{ formatarProximaConsulta(stats.proxima_consulta).quando }} as {{ formatarProximaConsulta(stats.proxima_consulta).hora }}</span>
            </div>
            <svg class="proxima-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
        </section>

        <!-- Configuracoes -->
        <section class="section">
          <h3 class="section-title">Configuracoes</h3>
          <div class="setting-card">
            <div class="setting-row">
              <div class="setting-info">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M13.73 21a2 2 0 01-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <div class="setting-text">
                  <span class="setting-label">Notificacoes</span>
                  <span class="setting-hint">Lembretes de remedios</span>
                </div>
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
          </div>
        </section>

        <!-- Sair -->
        <section class="section last-section">
          <div class="about-line">
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
      </template>
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

/* Loading */
.loading-state {
  display: flex;
  justify-content: center;
  padding: 60px 0;
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
  animation: bounce 1s ease infinite;
}

.loading-dots span:nth-child(2) { animation-delay: 0.15s; }
.loading-dots span:nth-child(3) { animation-delay: 0.3s; }

@keyframes bounce {
  0%, 80%, 100% { opacity: 0.3; transform: scale(0.8); }
  40% { opacity: 1; transform: scale(1); }
}

/* Hero */
.hero {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 8px 0 16px;
  position: relative;
}

.avatar {
  width: 56px;
  height: 56px;
  min-width: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--verde-salvia), #8BC34A);
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-text {
  font-family: var(--font-titulo);
  font-size: 1.25rem;
  color: #fff;
  font-weight: 700;
}

.hero-info {
  flex: 1;
  min-width: 0;
}

.hero-nome {
  font-family: var(--font-titulo);
  font-size: 1.2rem;
  color: var(--texto);
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.hero-email {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
}

.hero-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-top: 6px;
}

.hero-tag {
  font-family: var(--font-corpo);
  font-size: 10px;
  font-weight: 600;
  color: var(--verde-salvia);
  background: rgba(127, 168, 50, 0.1);
  border-radius: 6px;
  padding: 2px 8px;
}

.hero-tag.membro {
  color: var(--texto-light);
  background: rgba(0, 0, 0, 0.05);
}

.btn-edit {
  position: absolute;
  top: 8px;
  right: 0;
  width: 32px;
  height: 32px;
  border-radius: 10px;
  border: none;
  background: rgba(0, 0, 0, 0.04);
  color: var(--texto-light);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

/* Situacao banner */
.situacao-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  border-radius: 12px;
  background: color-mix(in srgb, var(--cor) 8%, #fff);
  border: 1px solid color-mix(in srgb, var(--cor) 20%, transparent);
  margin-bottom: 16px;
}

.situacao-dot {
  width: 10px;
  height: 10px;
  min-width: 10px;
  border-radius: 50%;
  background: var(--cor);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--cor) 25%, transparent);
}

.situacao-text {
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  color: var(--cor);
}

/* Stats */
.stats-section {
  margin-bottom: 20px;
}

.stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.stat-card {
  background: #fff;
  border-radius: 14px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 2px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  position: relative;
  overflow: hidden;
}

.stat-card.destaque {
  background: linear-gradient(135deg, rgba(127, 168, 50, 0.08), rgba(127, 168, 50, 0.02));
  border: 1px solid rgba(127, 168, 50, 0.15);
}

.stat-number {
  font-family: var(--font-titulo);
  font-size: 1.6rem;
  color: var(--texto);
  line-height: 1;
}

.stat-card.destaque .stat-number {
  color: var(--verde-salvia);
}

.stat-label {
  font-family: var(--font-corpo);
  font-size: 11px;
  font-weight: 500;
  color: var(--texto-light);
  margin-top: 2px;
}

.stat-icon {
  position: absolute;
  top: 12px;
  right: 12px;
  color: rgba(0, 0, 0, 0.08);
}

/* Streak */
.streak-card {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 8px;
  padding: 14px 16px;
  background: linear-gradient(135deg, rgba(232, 138, 58, 0.1), rgba(232, 138, 58, 0.04));
  border: 1px solid rgba(232, 138, 58, 0.2);
  border-radius: 14px;
}

.streak-fire {
  width: 36px;
  height: 36px;
  min-width: 36px;
  border-radius: 10px;
  background: rgba(232, 138, 58, 0.15);
  color: #E88A3A;
  display: flex;
  align-items: center;
  justify-content: center;
}

.streak-info {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.streak-count {
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 700;
  color: #E88A3A;
}

.streak-hint {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
}

/* Section */
.section {
  margin-bottom: 20px;
}

.last-section {
  margin-bottom: 8px;
}

.section-title {
  font-family: var(--font-titulo);
  font-size: 1rem;
  color: var(--texto);
  margin: 0 0 10px;
}

/* Proxima consulta */
.proxima-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #fff;
  border-radius: 14px;
  padding: 14px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  cursor: pointer;
  transition: transform 0.2s ease;
}

.proxima-card:active {
  transform: scale(0.98);
}

.proxima-icon {
  width: 40px;
  height: 40px;
  min-width: 40px;
  border-radius: 12px;
  background: rgba(127, 168, 50, 0.1);
  color: var(--verde-salvia);
  display: flex;
  align-items: center;
  justify-content: center;
}

.proxima-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1px;
  min-width: 0;
}

.proxima-medico {
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  color: var(--texto);
}

.proxima-esp {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
}

.proxima-quando {
  font-family: var(--font-corpo);
  font-size: 12px;
  font-weight: 600;
  color: var(--verde-salvia);
  margin-top: 2px;
}

.proxima-arrow {
  color: var(--texto-light);
  flex-shrink: 0;
}

/* Settings */
.setting-card {
  background: #fff;
  border-radius: 14px;
  padding: 14px 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.setting-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.setting-info {
  display: flex;
  align-items: center;
  gap: 12px;
  color: var(--texto-light);
}

.setting-text {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.setting-label {
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  color: var(--texto);
}

.setting-hint {
  font-family: var(--font-corpo);
  font-size: 11px;
  color: var(--texto-light);
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

/* About + Logout */
.about-line {
  display: flex;
  align-items: baseline;
  gap: 6px;
  margin-bottom: 12px;
}

.about-name {
  font-family: var(--font-titulo);
  font-size: 0.95rem;
  color: var(--verde-salvia);
}

.about-version {
  font-family: var(--font-corpo);
  font-size: 11px;
  color: var(--texto-light);
}

.btn-logout {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 14px;
  border-radius: 12px;
  border: 1.5px solid rgba(229, 115, 115, 0.3);
  background: rgba(229, 115, 115, 0.04);
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
