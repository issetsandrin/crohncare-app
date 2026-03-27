import { ref, onMounted, onUnmounted } from 'vue'

export function useBreakpoint() {
  const isDesktop = ref(false)

  function update() {
    isDesktop.value = window.innerWidth > 768
  }

  onMounted(() => {
    update()
    window.addEventListener('resize', update)
  })

  onUnmounted(() => {
    window.removeEventListener('resize', update)
  })

  return { isDesktop }
}
