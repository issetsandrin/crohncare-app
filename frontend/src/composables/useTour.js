import { ref } from 'vue'

const showTourModal = ref(false)
const showTour = ref(false)

export function useTour() {
  function verificarTourInicial() {
    const visto = localStorage.getItem('crohncare_tour_visto')
    if (!visto) {
      showTourModal.value = true
    }
  }

  function abrirTourModal() {
    showTourModal.value = true
  }

  function iniciarTour() {
    showTourModal.value = false
    showTour.value = true
    localStorage.setItem('crohncare_tour_visto', '1')
  }

  function fecharTourModal() {
    showTourModal.value = false
    localStorage.setItem('crohncare_tour_visto', '1')
  }

  function fecharTour() {
    showTour.value = false
  }

  return {
    showTourModal,
    showTour,
    verificarTourInicial,
    abrirTourModal,
    iniciarTour,
    fecharTourModal,
    fecharTour
  }
}
