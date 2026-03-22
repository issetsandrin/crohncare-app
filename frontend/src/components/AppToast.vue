<script setup>
import { ref, watch } from 'vue'

const visible = ref(false)
const message = ref('')
const type = ref('info')
let hideTimer = null

function show(msg, toastType = 'info', duration = 3000) {
  message.value = msg
  type.value = toastType
  visible.value = true

  if (hideTimer) clearTimeout(hideTimer)
  hideTimer = setTimeout(() => {
    visible.value = false
  }, duration)
}

defineExpose({ show })
</script>

<template>
  <Transition name="toast">
    <div v-if="visible" class="app-toast" :class="type">
      {{ message }}
    </div>
  </Transition>
</template>

<style scoped>
.app-toast {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  max-width: 400px;
  width: calc(100% - 32px);
  padding: 12px 20px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  text-align: center;
  z-index: 1000;
  animation: fadeInUp 0.4s var(--ease-out-smooth);
}

.app-toast.info {
  background: var(--verde-salvia);
  color: #fff;
}

.app-toast.error {
  background: var(--intensidade-5);
  color: #fff;
}

.app-toast.warning {
  background: var(--ambar);
  color: var(--texto);
}

.toast-enter-active {
  animation: fadeInUp 0.4s var(--ease-out-smooth);
}

.toast-leave-active {
  transition: opacity 0.3s var(--ease-smooth), transform 0.3s var(--ease-smooth);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(-10px);
}
</style>
