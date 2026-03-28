<script setup>
const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: '' }
})

const emit = defineEmits(['update:modelValue'])

function close() {
  emit('update:modelValue', false)
}

function onBackdropClick(e) {
  if (e.target === e.currentTarget) close()
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="modelValue" class="modal-backdrop" @click="onBackdropClick">
        <div class="modal-card">
          <div class="modal-header">
            <div class="modal-header-left">
              <slot name="header">
                <h2 class="modal-title">{{ title }}</h2>
              </slot>
            </div>
            <button class="modal-close" @click="close" aria-label="Fechar">
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M15 5L5 15M5 5l10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </button>
          </div>
          <div class="modal-body">
            <slot />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: flex-end;
  justify-content: center;
  z-index: 500;
}

.modal-card {
  background: #fff;
  border-radius: 20px 20px 0 0;
  width: 100%;
  max-width: 430px;
  max-height: 85vh;
  overflow-y: auto;
  padding: 24px 16px;
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  padding-bottom: 16px;
  margin-bottom: 16px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.07);
}

.modal-header-left {
  flex: 1;
  min-width: 0;
  display: flex;
  align-items: flex-start;
}

.modal-title {
  font-family: var(--font-titulo);
  font-size: 1.4rem;
  color: var(--texto);
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  cursor: pointer;
  color: var(--texto-light);
  padding: 4px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s var(--ease-smooth);
}

.modal-close:hover {
  background: rgba(0, 0, 0, 0.06);
}

.modal-body {
  padding-bottom: env(safe-area-inset-bottom, 16px);
  min-width: 0;
  overflow-x: hidden;
}

/* Transitions */
.modal-enter-active {
  transition: opacity 0.35s var(--ease-smooth);
}
.modal-enter-active .modal-card {
  animation: slideUp 0.4s var(--ease-out-smooth);
}
.modal-leave-active {
  transition: opacity 0.25s var(--ease-smooth);
}
.modal-leave-to {
  opacity: 0;
}
.modal-enter-from {
  opacity: 0;
}

/* ── Desktop: modal centrado ── */
@keyframes modalFadeIn {
  from { opacity: 0; transform: scale(0.96) translateY(-10px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}

@media (min-width: 769px) {
  .modal-backdrop {
    align-items: center;
  }

  .modal-card {
    border-radius: 16px;
    max-width: 680px;
    max-height: 88vh;
    padding: 28px 32px;
    width: 100%;
  }

  .modal-body {
    padding-bottom: 4px;
  }

  .modal-title {
    font-size: 1.25rem;
  }

  .modal-enter-active .modal-card {
    animation: modalFadeIn 0.2s var(--ease-out-smooth);
  }
}
</style>
