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
            <h2 class="modal-title">{{ title }}</h2>
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
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
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
  transition: background 0.2s;
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
  transition: opacity 0.25s ease;
}
.modal-enter-active .modal-card {
  animation: slideUp 0.3s ease;
}
.modal-leave-active {
  transition: opacity 0.2s ease;
}
.modal-leave-to {
  opacity: 0;
}
.modal-enter-from {
  opacity: 0;
}
</style>
