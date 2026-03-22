<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api from '../composables/useApi'

const router = useRouter()
const auth = useAuthStore()

const step = ref(0)
const saving = ref(false)
const registerError = ref('')

const form = ref({
  tipo_doenca: null,
  ano_diagnostico: null,
  localizacao: null,
  situacao_atual: null,
  tem_acompanhamento: null,
  frequencia_consultas: null,
  objetivos: []
})

const conta = ref({
  nome: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const totalSteps = 7

const anosOptions = computed(() => {
  const atual = new Date().getFullYear()
  const anos = []
  for (let a = atual; a >= 1970; a--) {
    anos.push(a)
  }
  return anos
})

const objetivosOptions = [
  { id: 'lembretes', label: 'Lembrar de tomar remédios' },
  { id: 'sintomas', label: 'Registrar sintomas no diário' },
  { id: 'crises', label: 'Acompanhar crises' },
  { id: 'estoque', label: 'Controlar estoque de medicamentos' },
  { id: 'consultas', label: 'Organizar consultas médicas' },
  { id: 'padroes', label: 'Identificar padrões e gatilhos' }
]

function toggleObjetivo(id) {
  const idx = form.value.objetivos.indexOf(id)
  if (idx >= 0) {
    form.value.objetivos.splice(idx, 1)
  } else {
    form.value.objetivos.push(id)
  }
}

function next() {
  if (step.value < totalSteps - 1) {
    step.value++
  }
}

function prev() {
  if (step.value > 0) {
    step.value--
  }
}

const canRegister = computed(() => {
  return conta.value.nome && conta.value.email && conta.value.password && conta.value.password === conta.value.password_confirmation && conta.value.password.length >= 6
})

async function finalizar() {
  saving.value = true
  registerError.value = ''
  auth.errors = {}
  try {
    const success = await auth.register(
      conta.value.nome,
      conta.value.email,
      conta.value.password,
      conta.value.password_confirmation
    )
    if (!success) {
      registerError.value = Object.values(auth.errors).flat()[0] || 'Erro ao criar conta.'
      return
    }

    // Salvar perfil crohn
    try {
      await api.post('/perfil-crohn', form.value)
      auth.marcarOnboardingCompleto()
    } catch (e) {
      // Perfil é opcional, não bloquear
    }

    router.push('/')
  } catch (e) {
    registerError.value = 'Erro ao criar conta. Tente novamente.'
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <main class="onboarding">
    <!-- Progress -->
    <div class="progress-bar">
      <div class="progress-fill" :style="{ width: ((step + 1) / totalSteps * 100) + '%' }"></div>
    </div>

    <div class="step-container">
      <Transition name="slide" mode="out-in">
        <!-- Step 0: Boas-vindas -->
        <div v-if="step === 0" key="welcome" class="step">
          <div class="step-illustration">
            <svg width="72" height="72" viewBox="0 0 72 72" fill="none">
              <circle cx="36" cy="36" r="34" stroke="var(--verde-salvia)" stroke-width="2" stroke-dasharray="6 4" opacity="0.3"/>
              <circle cx="36" cy="36" r="24" fill="rgba(127, 168, 50, 0.1)"/>
              <path d="M36 24c-6.6 0-12 5.4-12 12s5.4 12 12 12 12-5.4 12-12-5.4-12-12-12zm-1 17l-5-5 1.4-1.4L35 38.2l7.6-7.6L44 32l-9 9z" fill="var(--verde-salvia)"/>
            </svg>
          </div>
          <h1 class="step-title">Bem-vindo ao CrohnCare</h1>
          <p class="step-text">
            Sabemos que conviver com uma DII não é fácil, e estamos aqui para caminhar junto com você. Vamos te fazer algumas perguntas para entender melhor a sua jornada.
          </p>
          <p class="step-hint">São perguntas rápidas e opcionais — responda só o que se sentir confortável.</p>
        </div>

        <!-- Step 1: Diagnóstico -->
        <div v-else-if="step === 1" key="diagnostico" class="step">
          <span class="step-label">Sobre seu diagnóstico</span>
          <h2 class="step-title">Qual é a sua condição?</h2>
          <p class="step-text">Cada jornada é única. Saber mais sobre o seu diagnóstico nos ajuda a cuidar melhor de você.</p>

          <div class="options-grid">
            <button
              v-for="opt in [
                { value: 'crohn', label: 'Doença de Crohn' },
                { value: 'retocolite', label: 'Retocolite Ulcerativa' },
                { value: 'indeterminada', label: 'Colite Indeterminada' },
                { value: 'nao_sei', label: 'Ainda não sei' }
              ]"
              :key="opt.value"
              class="option-btn"
              :class="{ selected: form.tipo_doenca === opt.value }"
              @click="form.tipo_doenca = opt.value"
            >
              {{ opt.label }}
            </button>
          </div>

          <div class="form-group" style="margin-top: 20px;">
            <label class="field-label">Em que ano foi o diagnóstico?</label>
            <select v-model="form.ano_diagnostico" class="field-select">
              <option :value="null" disabled>Selecione o ano</option>
              <option v-for="ano in anosOptions" :key="ano" :value="ano">{{ ano }}</option>
            </select>
          </div>
        </div>

        <!-- Step 2: Localização -->
        <div v-else-if="step === 2" key="localizacao" class="step">
          <span class="step-label">Localização</span>
          <h2 class="step-title">Onde a doença se manifesta?</h2>
          <p class="step-text">Se o seu médico já te explicou a localização, selecione abaixo. Não se preocupe se ainda não souber — isso é completamente normal.</p>

          <div class="options-grid">
            <button
              v-for="opt in [
                { value: 'ileal', label: 'Íleo (intestino delgado)' },
                { value: 'colonico', label: 'Cólon (intestino grosso)' },
                { value: 'ileocolonico', label: 'Íleo + Cólon' },
                { value: 'trato_superior', label: 'Trato superior (estômago/esôfago)' },
                { value: 'nao_sei', label: 'Não sei' }
              ]"
              :key="opt.value"
              class="option-btn"
              :class="{ selected: form.localizacao === opt.value }"
              @click="form.localizacao = opt.value"
            >
              {{ opt.label }}
            </button>
          </div>
        </div>

        <!-- Step 3: Gravidade -->
        <div v-else-if="step === 3" key="situacao" class="step">
          <span class="step-label">Momento atual</span>
          <h2 class="step-title">Como você está se sentindo?</h2>
          <p class="step-text">Não existe resposta certa ou errada. Queremos entender como você está para te apoiar da melhor forma possível.</p>

          <div class="situacao-options">
            <button
              v-for="opt in [
                { value: 'remissao', label: 'Em remissão', desc: 'Que ótimo! A doença está controlada no momento', cor: '#4CAF50', level: 1 },
                { value: 'crise_leve', label: 'Crise leve', desc: 'Alguns sintomas, mas consigo lidar no dia a dia', cor: '#D4A03C', level: 2 },
                { value: 'crise_moderada', label: 'Crise moderada', desc: 'Os sintomas estão atrapalhando minha rotina', cor: '#E88A3A', level: 3 },
                { value: 'crise_grave', label: 'Crise intensa', desc: 'Estou passando por um momento difícil e preciso de apoio', cor: '#E05555', level: 4 },
                { value: 'recente', label: 'Descobri há pouco', desc: 'Ainda estou entendendo tudo isso — e está tudo bem', cor: '#5B93C7', level: 0 }
              ]"
              :key="opt.value"
              class="situacao-btn"
              :class="{ selected: form.situacao_atual === opt.value }"
              @click="form.situacao_atual = opt.value"
            >
              <div class="gravidade-icon" :style="{ '--cor': opt.cor }">
                <!-- Remissão: check -->
                <svg v-if="opt.level === 1" width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <!-- Leve: 1 barra -->
                <svg v-else-if="opt.level === 2" width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <rect x="10" y="14" width="4" height="6" rx="1" fill="currentColor"/>
                  <rect x="10" y="8" width="4" height="4" rx="1" fill="currentColor" opacity="0.2"/>
                  <rect x="10" y="2" width="4" height="4" rx="1" fill="currentColor" opacity="0.2"/>
                </svg>
                <!-- Moderada: 2 barras -->
                <svg v-else-if="opt.level === 3" width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <rect x="10" y="14" width="4" height="6" rx="1" fill="currentColor"/>
                  <rect x="10" y="8" width="4" height="4" rx="1" fill="currentColor"/>
                  <rect x="10" y="2" width="4" height="4" rx="1" fill="currentColor" opacity="0.2"/>
                </svg>
                <!-- Intensa: 3 barras -->
                <svg v-else-if="opt.level === 4" width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <rect x="10" y="14" width="4" height="6" rx="1" fill="currentColor"/>
                  <rect x="10" y="8" width="4" height="4" rx="1" fill="currentColor"/>
                  <rect x="10" y="2" width="4" height="4" rx="1" fill="currentColor"/>
                </svg>
                <!-- Recente: interrogação -->
                <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <circle cx="12" cy="17" r="1" fill="currentColor"/>
                </svg>
              </div>
              <div class="situacao-text">
                <span class="situacao-label">{{ opt.label }}</span>
                <span class="situacao-desc">{{ opt.desc }}</span>
              </div>
            </button>
          </div>
        </div>

        <!-- Step 4: Acompanhamento -->
        <div v-else-if="step === 4" key="acompanhamento" class="step">
          <span class="step-label">Acompanhamento</span>
          <h2 class="step-title">Você tem acompanhamento médico?</h2>
          <p class="step-text">Ter um gastroenterologista faz toda a diferença nessa jornada. Se você ainda não tem, tudo bem — o CrohnCare te ajuda a se organizar de qualquer forma.</p>

          <div class="options-grid">
            <button
              class="option-btn"
              :class="{ selected: form.tem_acompanhamento === true }"
              @click="form.tem_acompanhamento = true"
            >
              Sim, tenho gastroenterologista
            </button>
            <button
              class="option-btn"
              :class="{ selected: form.tem_acompanhamento === false }"
              @click="form.tem_acompanhamento = false"
            >
              Ainda não tenho
            </button>
          </div>

          <Transition name="fade">
            <div v-if="form.tem_acompanhamento === true" class="form-group" style="margin-top: 20px;">
              <label class="field-label">Com que frequência consulta?</label>
              <div class="options-grid">
                <button
                  v-for="opt in [
                    { value: 'mensal', label: 'Todo mês' },
                    { value: 'bimestral', label: 'A cada 2 meses' },
                    { value: 'trimestral', label: 'A cada 3 meses' },
                    { value: 'semestral', label: 'A cada 6 meses' },
                    { value: 'anual', label: 'Uma vez por ano' }
                  ]"
                  :key="opt.value"
                  class="option-btn small"
                  :class="{ selected: form.frequencia_consultas === opt.value }"
                  @click="form.frequencia_consultas = opt.value"
                >
                  {{ opt.label }}
                </button>
              </div>
            </div>
          </Transition>
        </div>

        <!-- Step 5: Objetivos -->
        <div v-else-if="step === 5" key="objetivos" class="step">
          <span class="step-label">Seus objetivos</span>
          <h2 class="step-title">Como podemos te ajudar?</h2>
          <p class="step-text">Escolha o que mais importa para você no momento. Você sempre pode mudar isso depois, no seu ritmo.</p>

          <div class="objetivos-list">
            <button
              v-for="obj in objetivosOptions"
              :key="obj.id"
              class="objetivo-btn"
              :class="{ selected: form.objetivos.includes(obj.id) }"
              @click="toggleObjetivo(obj.id)"
            >
              <div class="objetivo-check">
                <svg v-if="form.objetivos.includes(obj.id)" width="16" height="16" viewBox="0 0 24 24" fill="none">
                  <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
              <span>{{ obj.label }}</span>
            </button>
          </div>
        </div>

        <!-- Step 6: Criar conta -->
        <div v-else-if="step === 6" key="conta" class="step">
          <span class="step-label">Quase lá!</span>
          <h2 class="step-title">Crie sua conta</h2>
          <p class="step-text">Seus dados estão seguros conosco. A conta permite salvar tudo e acessar de qualquer lugar.</p>

          <form class="register-form" @submit.prevent="finalizar">
            <div class="form-group">
              <label class="field-label">Como podemos te chamar?</label>
              <input
                v-model="conta.nome"
                type="text"
                class="field-input"
                placeholder="Seu nome"
                required
                autocomplete="name"
              />
            </div>

            <div class="form-group">
              <label class="field-label">Seu e-mail</label>
              <input
                v-model="conta.email"
                type="email"
                class="field-input"
                placeholder="seu@email.com"
                required
                autocomplete="email"
              />
            </div>

            <div class="form-group">
              <label class="field-label">Crie uma senha</label>
              <input
                v-model="conta.password"
                type="password"
                class="field-input"
                placeholder="Mínimo 6 caracteres"
                required
                autocomplete="new-password"
              />
            </div>

            <div class="form-group">
              <label class="field-label">Confirme a senha</label>
              <input
                v-model="conta.password_confirmation"
                type="password"
                class="field-input"
                placeholder="Repita a senha"
                required
                autocomplete="new-password"
              />
            </div>

            <span v-if="registerError" class="form-error">{{ registerError }}</span>
          </form>

          <div class="login-link">
            <span>Já tem uma conta?</span>
            <router-link to="/login">Entrar</router-link>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Actions -->
    <div class="actions">
      <button v-if="step > 0" class="btn-back" @click="prev">Voltar</button>
      <div v-else>
        <router-link to="/login" class="btn-login-link">Já tenho conta</router-link>
      </div>

      <div class="actions-right">
        <button v-if="step > 0 && step < totalSteps - 1" class="btn-skip" @click="next">Pular</button>
        <button v-if="step < totalSteps - 1" class="btn-next" @click="next">
          {{ step === 0 ? 'Vamos lá' : 'Continuar' }}
        </button>
        <button v-else class="btn-next" :disabled="saving || !canRegister" @click="finalizar">
          {{ saving ? 'Criando conta...' : 'Criar conta' }}
        </button>
      </div>
    </div>
  </main>
</template>

<style scoped>
.onboarding {
  min-height: 100dvh;
  display: flex;
  flex-direction: column;
  padding: 0 20px env(safe-area-inset-bottom, 16px);
  max-width: 430px;
  margin: 0 auto;
}

/* Progress */
.progress-bar {
  height: 3px;
  background: rgba(0, 0, 0, 0.06);
  border-radius: 2px;
  margin: 16px 0 0;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: var(--verde-salvia);
  border-radius: 2px;
  transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Step container */
.step-container {
  flex: 1;
  display: flex;
  align-items: center;
  padding: 24px 0;
  overflow-y: auto;
}

.step {
  width: 100%;
}

.step-illustration {
  margin-bottom: 24px;
}

.step-label {
  font-family: var(--font-corpo);
  font-size: 12px;
  font-weight: 600;
  color: var(--verde-salvia);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: block;
  margin-bottom: 8px;
}

.step-title {
  font-family: var(--font-titulo);
  font-size: 1.6rem;
  font-weight: 700;
  color: var(--texto);
  margin: 0 0 10px;
  line-height: 1.2;
}

.step-text {
  font-family: var(--font-corpo);
  font-size: 15px;
  color: var(--texto-light);
  line-height: 1.5;
  margin: 0 0 8px;
}

.step-hint {
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--texto-light);
  opacity: 0.7;
  margin: 0;
}

/* Options */
.options-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 16px;
}

.option-btn {
  padding: 14px 18px;
  border-radius: 12px;
  border: 1.5px solid #e0e0e0;
  background: #fff;
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 500;
  color: var(--texto);
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: left;
}

.option-btn:active {
  transform: scale(0.97);
}

.option-btn.selected {
  border-color: var(--verde-salvia);
  background: rgba(127, 168, 50, 0.08);
  color: var(--verde-salvia);
  font-weight: 600;
}

.option-btn.small {
  padding: 10px 14px;
  font-size: 13px;
}

/* Situação */
.situacao-options {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 16px;
}

.situacao-btn {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  border-radius: 14px;
  border: 1.5px solid #e0e0e0;
  background: #fff;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: left;
}

.situacao-btn:active {
  transform: scale(0.98);
}

.situacao-btn.selected {
  border-color: var(--verde-salvia);
  background: rgba(127, 168, 50, 0.06);
}

.gravidade-icon {
  width: 36px;
  height: 36px;
  min-width: 36px;
  border-radius: 10px;
  background: color-mix(in srgb, var(--cor) 12%, transparent);
  color: var(--cor);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.situacao-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.situacao-label {
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  color: var(--texto);
}

.situacao-desc {
  font-family: var(--font-corpo);
  font-size: 12px;
  color: var(--texto-light);
  line-height: 1.3;
}

/* Select */
.field-label {
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: var(--texto);
  display: block;
  margin-bottom: 8px;
}

.field-select {
  width: 100%;
  padding: 14px;
  border: 1.5px solid #e0e0e0;
  border-radius: 12px;
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  background: #fff;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%23999' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
  cursor: pointer;
}

.field-select:focus {
  outline: none;
  border-color: var(--verde-salvia);
}

/* Objetivos */
.objetivos-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 16px;
}

.objetivo-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  border-radius: 12px;
  border: 1.5px solid #e0e0e0;
  background: #fff;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: left;
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
}

.objetivo-btn:active {
  transform: scale(0.98);
}

.objetivo-btn.selected {
  border-color: var(--verde-salvia);
  background: rgba(127, 168, 50, 0.06);
  color: var(--verde-salvia);
  font-weight: 600;
}

.objetivo-check {
  width: 24px;
  height: 24px;
  border-radius: 8px;
  border: 1.5px solid #ddd;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.2s ease;
  color: #fff;
}

.objetivo-btn.selected .objetivo-check {
  background: var(--verde-salvia);
  border-color: var(--verde-salvia);
}

/* Register form */
.register-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-top: 16px;
}

.field-input {
  width: 100%;
  padding: 14px;
  border: 1.5px solid #e0e0e0;
  border-radius: 12px;
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  background: #fff;
  transition: border-color 0.2s ease;
  box-sizing: border-box;
}

.field-input:focus {
  outline: none;
  border-color: var(--verde-salvia);
  box-shadow: 0 0 0 3px rgba(127, 168, 50, 0.1);
}

.form-error {
  font-family: var(--font-corpo);
  font-size: 13px;
  color: var(--terracota);
  margin-top: 4px;
}

.login-link {
  display: flex;
  gap: 6px;
  align-items: center;
  justify-content: center;
  margin-top: 16px;
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto-light);
}

.login-link a {
  color: var(--verde-salvia);
  font-weight: 600;
  text-decoration: none;
}

/* Actions */
.actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0 20px;
  gap: 12px;
}

.actions-right {
  display: flex;
  gap: 8px;
  align-items: center;
}

.btn-back {
  padding: 12px 20px;
  border-radius: 12px;
  border: 1.5px solid #e0e0e0;
  background: #fff;
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  color: var(--texto-light);
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-back:active {
  transform: scale(0.97);
}

.btn-login-link {
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 500;
  color: var(--texto-light);
  text-decoration: none;
}

.btn-skip {
  padding: 12px 16px;
  border-radius: 12px;
  border: none;
  background: transparent;
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 500;
  color: var(--texto-light);
  cursor: pointer;
}

.btn-next {
  padding: 12px 28px;
  border-radius: 12px;
  border: none;
  background: var(--verde-salvia);
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  color: #fff;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 12px rgba(127, 168, 50, 0.3);
}

.btn-next:active:not(:disabled) {
  transform: scale(0.97);
}

.btn-next:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Transitions */
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.slide-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.form-group {
  display: flex;
  flex-direction: column;
}
</style>
