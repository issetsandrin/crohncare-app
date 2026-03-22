<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useNotificacoesStore } from '../stores/notificacoes'
import AppBar from '../components/AppBar.vue'

const router = useRouter()
const auth = useAuthStore()
const notifStore = useNotificacoesStore()

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}

const permissionStatus = computed(() => notifStore.permissao)
const notificacoesAtivas = computed(() => notifStore.ativa)

onMounted(() => {
  notifStore.verificarPermissao()
})

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
  default: 'Não solicitadas',
  unsupported: 'Não suportadas neste navegador'
}
</script>

<template>
  <main class="configuracoes-page">
    <AppBar title="Configurações" subtitle="Personalize o app do seu jeito" />

    <div class="page-content">
    <!-- Notificacoes -->
    <section class="section">
      <h2 class="section-title">Notificacoes</h2>
      <div class="setting-card">
        <div class="setting-row">
          <div class="setting-info">
            <span class="setting-label">Notificacoes de remedios</span>
            <span class="setting-status">{{ statusLabel[permissionStatus] || permissionStatus }}</span>
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
        <p v-if="permissionStatus === 'denied'" class="setting-hint">
          As notificacoes foram bloqueadas. Para reativar, altere as permissoes nas configuracoes do navegador.
        </p>
      </div>
    </section>

    <!-- Conta -->
    <section class="section">
      <h2 class="section-title">Conta</h2>
      <div class="setting-card">
        <div class="setting-row">
          <div class="setting-info">
            <span class="setting-label">{{ auth.user?.nome }}</span>
            <span class="setting-status">{{ auth.user?.email }}</span>
          </div>
          <button class="btn-logout" @click="handleLogout">Sair</button>
        </div>
      </div>
    </section>

    <!-- Sobre -->
    <section class="section">
      <h2 class="section-title">Sobre</h2>
      <div class="setting-card about-card">
        <div class="about-header">
          <span class="about-name">CrohnCare</span>
          <span class="about-version">v1.0.0</span>
        </div>
        <p class="about-description">
          CrohnCare e um aplicativo de apoio para pessoas com Doenca de Crohn.
          Acompanhe seus medicamentos, registre sintomas no diario e tenha
          controle sobre crises e estoque de remedios.
        </p>
        <p class="about-description">
          Desenvolvido com carinho para facilitar o dia a dia de quem convive
          com doencas inflamatorias intestinais.
        </p>
      </div>
    </section>
    </div>
  </main>
</template>

<style scoped>
.configuracoes-page {
  padding-bottom: 80px;
  height: 100dvh;
  overflow: hidden;
}

.page-content {
  padding: 0 16px;
}


.section {
  margin-bottom: 24px;
}

.section-title {
  font-family: var(--font-titulo);
  font-size: 1.2rem;
  color: var(--texto);
  margin: 0 0 12px;
}

.setting-card {
  background: #fff;
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
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

.setting-status {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
}

.setting-hint {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--terracota);
  margin: 8px 0 0;
  line-height: 1.4;
}

/* Toggle switch */
.toggle {
  position: relative;
  width: 48px;
  height: 28px;
  border-radius: 14px;
  background: #ddd;
  border: none;
  cursor: pointer;
  transition: background 0.25s ease;
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
  transition: transform 0.25s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
}

.toggle.active .toggle-thumb {
  transform: translateX(20px);
}

.btn-logout {
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: var(--terracota);
  background: rgba(229, 115, 115, 0.1);
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  cursor: pointer;
  transition: opacity 0.2s;
}

.btn-logout:active {
  opacity: 0.7;
}

/* About */
.about-card {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.about-header {
  display: flex;
  align-items: baseline;
  gap: 8px;
}

.about-name {
  font-family: var(--font-titulo);
  font-size: 1.1rem;
  color: var(--verde-salvia);
}

.about-version {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
}

.about-description {
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--texto);
  line-height: 1.6;
  margin: 0;
}
</style>
