import { ref } from 'vue'

const collapsed = ref(localStorage.getItem('sidebar_collapsed') !== 'false')

export function useSidebar() {
  function toggle() {
    collapsed.value = !collapsed.value
    localStorage.setItem('sidebar_collapsed', collapsed.value)
  }

  return { collapsed, toggle }
}
