const TEMAS = {
  verde: {
    '--verde-salvia': '#7FA832',
    '--verde-claro': '#9BC24A',
    '--verde-bg': '#F2F6E8',
  },
  azul: {
    '--verde-salvia': '#4A86C8',
    '--verde-claro': '#6BA3D6',
    '--verde-bg': '#EBF1F9',
  },
  amarelo: {
    '--verde-salvia': '#C8962A',
    '--verde-claro': '#D4A93E',
    '--verde-bg': '#FBF4E3',
  },
  rosa: {
    '--verde-salvia': '#C06A84',
    '--verde-claro': '#D0899D',
    '--verde-bg': '#F8EEF1',
  },
}

export const LISTA_TEMAS = [
  { id: 'verde',   label: 'Verde',   cor: '#7FA832' },
  { id: 'azul',    label: 'Azul',    cor: '#4A86C8' },
  { id: 'amarelo', label: 'Amarelo', cor: '#C8962A' },
  { id: 'rosa',    label: 'Rosa',    cor: '#C06A84' },
]

export function aplicarTema(id) {
  const tema = TEMAS[id] || TEMAS.verde
  const root = document.documentElement
  for (const [prop, val] of Object.entries(tema)) {
    root.style.setProperty(prop, val)
  }
}

export function useTheme() {
  function getTema() {
    return localStorage.getItem('tema') || 'verde'
  }

  function setTema(id) {
    localStorage.setItem('tema', id)
    aplicarTema(id)
  }

  return { getTema, setTema, LISTA_TEMAS }
}
