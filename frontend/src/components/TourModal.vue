<script setup>
import { useTour } from '../composables/useTour'

const { showTourModal, iniciarTour, fecharTourModal } = useTour()
</script>

<template>
  <Teleport to="body">
    <Transition name="tour-fade">
      <div v-if="showTourModal" class="tour-backdrop" @click.self="fecharTourModal">
        <div class="tour-prompt-card">
          <div class="tour-prompt-icon">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" stroke="var(--verde-salvia)" stroke-width="1.8"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" stroke="var(--verde-salvia)" stroke-width="1.8" stroke-linecap="round"/>
              <circle cx="12" cy="17" r="1" fill="var(--verde-salvia)"/>
            </svg>
          </div>
          <h2 class="tour-prompt-title">Bem-vindo ao CrohnCare!</h2>
          <p class="tour-prompt-desc">
            Quer conhecer as principais funcionalidades do app? Faremos um tour rápido pelas seções mais importantes.
          </p>
          <div class="tour-prompt-actions">
            <button class="tour-btn-sim" @click="iniciarTour">Sim, quero o tour!</button>
            <button class="tour-btn-nao" @click="fecharTourModal">Agora não</button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.tour-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 900;
  padding: 16px;
}

.tour-prompt-card {
  background: #fff;
  border-radius: 20px;
  padding: 32px 28px;
  max-width: 380px;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  text-align: center;
  box-shadow: 0 20px 60px rgba(0,0,0,0.18);
}

.tour-prompt-icon {
  width: 72px;
  height: 72px;
  background: #7fa8321f;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 4px;
}

.tour-prompt-title {
  font-family: var(--font-titulo);
  font-size: 1.35rem;
  color: var(--texto);
  margin: 0;
}

.tour-prompt-desc {
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto-light);
  line-height: 1.6;
  margin: 0;
}

.tour-prompt-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
  width: 100%;
  margin-top: 8px;
}

.tour-btn-sim {
  background: var(--verde-salvia);
  color: #fff;
  border: none;
  border-radius: 12px;
  padding: 13px 20px;
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}

.tour-btn-sim:hover {
  opacity: 0.88;
}

.tour-btn-nao {
  background: none;
  border: none;
  color: var(--texto-light);
  font-family: var(--font-corpo);
  font-size: 13px;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: background 0.2s;
}

.tour-btn-nao:hover {
  background: rgba(0,0,0,0.05);
}

/* Transition */
.tour-fade-enter-active,
.tour-fade-leave-active {
  transition: opacity 0.25s ease;
}
.tour-fade-enter-active .tour-prompt-card,
.tour-fade-leave-active .tour-prompt-card {
  transition: transform 0.25s ease, opacity 0.25s ease;
}
.tour-fade-enter-from,
.tour-fade-leave-to {
  opacity: 0;
}
.tour-fade-enter-from .tour-prompt-card {
  transform: scale(0.94) translateY(10px);
}
</style>
