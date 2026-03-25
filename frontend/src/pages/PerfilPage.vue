<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useNotificacoesStore } from '../stores/notificacoes'
import { useTheme, LISTA_TEMAS } from '../composables/useTheme'
import api from '../composables/useApi'
import AppBar from '../components/AppBar.vue'
import ModalBase from '../components/ModalBase.vue'
import LoadingDots from '../components/LoadingDots.vue'

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
  remissao: 'Em remissão',
  crise_leve: 'Crise leve',
  crise_moderada: 'Crise moderada',
  crise_grave: 'Crise intensa',
  recente: 'Diagnóstico recente'
}

const situacaoColors = {
  remissao: '#4CAF50',
  crise_leve: '#D4A03C',
  crise_moderada: '#E88A3A',
  crise_grave: '#E05555',
  recente: '#5B93C7'
}

const tipoLabels = {
  crohn: 'Doença de Crohn',
  retocolite: 'Retocolite Ulcerativa',
  indeterminada: 'Colite Indeterminada',
  nao_sei: 'Não definido'
}

const { getTema, setTema } = useTheme()
const temaAtual = ref(getTema())

function selecionarTema(id) {
  temaAtual.value = id
  setTema(id)
}

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


</script>

<template>
  <div class="perfil-page">
    <AppBar title="Perfil" subtitle="Seu resumo pessoal" />

    <div class="page-content">
      <LoadingDots v-if="loading" />

      <template v-else>
        <!-- Hero -->
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

        <!-- Situação atual -->
        <div v-if="perfil?.situacao_atual" class="situacao-chip" :style="{ '--cor': situacaoColors[perfil.situacao_atual] || '#888' }">
          <span class="situacao-dot"></span>
          <span class="situacao-text">{{ situacaoLabels[perfil.situacao_atual] || perfil.situacao_atual }}</span>
        </div>

        <!-- Stats compactos -->
        <div v-if="stats" class="stats-card">
          <div class="stat-item">
            <span class="stat-num">{{ stats.meds_ativos }}</span>
            <span class="stat-lbl">Remédios</span>
          </div>
          <div class="stat-divider" />
          <div class="stat-item">
            <span class="stat-num">{{ stats.total_diarios }}</span>
            <span class="stat-lbl">Registros</span>
          </div>
          <div class="stat-divider" />
          <div class="stat-item" :class="{ 'stat-green': stats.dias_sem_crise !== null && stats.dias_sem_crise >= 7 }">
            <span class="stat-num">{{ stats.dias_sem_crise !== null ? stats.dias_sem_crise : '—' }}</span>
            <span class="stat-lbl">Sem crise</span>
          </div>
          <div class="stat-divider" />
          <div class="stat-item">
            <span class="stat-num">{{ stats.consultas_realizadas }}</span>
            <span class="stat-lbl">Consultas</span>
          </div>
        </div>

        <!-- Streak -->
        <div v-if="stats?.streak_diario > 0" class="streak-row">
          <div class="streak-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M12 2c.5 4-3 6-3 10a5 5 0 0010 0c0-4-2-6-3-8-1 2-3 3-4 2 0-2 .5-4 0-2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <span class="streak-text"><strong>{{ stats.streak_diario }} {{ stats.streak_diario === 1 ? 'dia' : 'dias' }}</strong> seguidos no diário</span>
        </div>

        <!-- Configurações unificadas -->
        <div class="settings-card">
          <!-- Notificações -->
          <div class="settings-row">
            <div class="settings-row-left">
              <div class="settings-icon-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                  <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M13.73 21a2 2 0 01-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
              </div>
              <div class="settings-text">
                <span class="settings-label">Notificações</span>
                <span class="settings-hint">Lembretes de remédios</span>
              </div>
            </div>
            <button class="toggle" :class="{ active: notificacoesAtivas }" @click="toggleNotificacoes" role="switch" :aria-checked="notificacoesAtivas">
              <span class="toggle-thumb" />
            </button>
          </div>

          <div class="settings-sep" />

          <!-- Tema -->
          <div class="settings-row settings-row--tema">
            <div class="settings-row-left">
              <div class="settings-icon-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                  <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                  <path d="M12 3a9 9 0 000 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                  <circle cx="12" cy="12" r="3" fill="currentColor" opacity=".3"/>
                </svg>
              </div>
              <div class="settings-text">
                <span class="settings-label">Tema</span>
                <span class="settings-hint">Cor principal do app</span>
              </div>
            </div>
            <div class="tema-swatches">
              <button
                v-for="t in LISTA_TEMAS"
                :key="t.id"
                class="swatch"
                :class="{ active: temaAtual === t.id }"
                :style="{ '--swatch-cor': t.cor }"
                :aria-label="t.label"
                @click="selecionarTema(t.id)"
              />
            </div>
          </div>

          <div class="settings-sep" />

          <!-- Sair -->
          <button class="settings-row settings-logout" @click="handleLogout">
            <div class="settings-row-left">
              <div class="settings-icon-box logout-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                  <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
              <span class="settings-label logout-label">Sair da conta</span>
            </div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
              <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>

        <!-- Footer -->
        <p class="footer-version">CrohnCare · v1.0.0</p>
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

/* Situação chip */
.situacao-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 12px;
  border-radius: 20px;
  background: color-mix(in srgb, var(--cor) 10%, transparent);
  margin-bottom: 16px;
}

.situacao-dot {
  width: 7px;
  height: 7px;
  min-width: 7px;
  border-radius: 50%;
  background: var(--cor);
}

.situacao-text {
  font-family: var(--font-corpo);
  font-size: 12px;
  font-weight: 600;
  color: var(--cor);
}

/* Stats compactos */
.stats-card {
  display: flex;
  align-items: center;
  background: #fff;
  border-radius: 16px;
  padding: 16px 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  margin-bottom: 10px;
}

.stat-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 3px;
}

.stat-divider {
  width: 1px;
  height: 32px;
  background: rgba(0, 0, 0, 0.07);
  flex-shrink: 0;
}

.stat-num {
  font-family: var(--font-titulo);
  font-size: 1.5rem;
  color: var(--texto);
  line-height: 1;
}

.stat-lbl {
  font-family: var(--font-corpo);
  font-size: 10px;
  font-weight: 500;
  color: var(--texto-light);
}

.stat-green .stat-num {
  color: var(--verde-salvia);
}

/* Streak */
.streak-row {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  background: rgba(232, 138, 58, 0.07);
  border-radius: 12px;
  margin-bottom: 10px;
}

.streak-icon {
  color: #E88A3A;
  display: flex;
  align-items: center;
}

.streak-text {
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--texto-light);
}

.streak-text strong {
  color: #E88A3A;
}

/* Settings card */
.settings-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  margin-bottom: 10px;
}

.settings-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  width: 100%;
  background: none;
  border: none;
  cursor: pointer;
  text-align: left;
}

.settings-row-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.settings-icon-box {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(127, 168, 50, 0.1);
  color: var(--verde-salvia);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.logout-icon {
  background: rgba(229, 115, 115, 0.1);
  color: var(--terracota);
}

.settings-text {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.settings-label {
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  color: var(--texto);
}

.logout-label {
  color: var(--terracota);
}

.settings-hint {
  font-family: var(--font-corpo);
  font-size: 11px;
  color: var(--texto-light);
}

.settings-sep {
  height: 1px;
  background: rgba(0, 0, 0, 0.05);
  margin: 0 16px;
}

.settings-logout svg:last-child {
  color: var(--texto-light);
}

/* Toggle */
.toggle {
  position: relative;
  width: 44px;
  height: 26px;
  border-radius: 13px;
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
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #fff;
  transition: transform 0.35s var(--ease-smooth);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
}

.toggle.active .toggle-thumb {
  transform: translateX(18px);
}

/* Tema swatches */
.settings-row--tema {
  cursor: default;
  flex-wrap: wrap;
  gap: 10px;
}

.tema-swatches {
  display: flex;
  gap: 8px;
  align-items: center;
}

.swatch {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: var(--swatch-cor);
  border: 2.5px solid transparent;
  cursor: pointer;
  transition: transform 0.2s var(--ease-spring), border-color 0.2s;
  flex-shrink: 0;
  box-shadow: 0 1px 4px rgba(0,0,0,0.15);
}

.swatch.active {
  border-color: var(--swatch-cor);
  outline: 2px solid color-mix(in srgb, var(--swatch-cor) 30%, transparent);
  outline-offset: 2px;
  transform: scale(1.15);
}

.swatch:active {
  transform: scale(0.9);
}

/* Footer */
.footer-version {
  font-family: var(--font-corpo);
  font-size: 11px;
  color: var(--texto-light);
  text-align: center;
  margin: 4px 0 0;
  opacity: 0.6;
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
