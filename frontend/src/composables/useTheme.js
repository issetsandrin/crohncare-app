const TEMAS = {
  verde: {
    '--verde-salvia': '#7FA832',
    '--verde-claro': '#9BC24A',
    '--verde-bg': '#F2F6E8',
    themeColor: '#7FA832',
  },
  azul: {
    '--verde-salvia': '#4A86C8',
    '--verde-claro': '#6BA3D6',
    '--verde-bg': '#EBF1F9',
    themeColor: '#4A86C8',
  },
  amarelo: {
    '--verde-salvia': '#C8962A',
    '--verde-claro': '#D4A93E',
    '--verde-bg': '#FBF4E3',
    themeColor: '#C8962A',
  },
  rosa: {
    '--verde-salvia': '#C06A84',
    '--verde-claro': '#D0899D',
    '--verde-bg': '#F8EEF1',
    themeColor: '#C06A84',
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
    if (prop.startsWith('--')) root.style.setProperty(prop, val)
  }
  // Atualiza a meta theme-color para o browser/PWA respeitar a cor
  const meta = document.querySelector('meta[name="theme-color"]')
  if (meta) meta.setAttribute('content', tema.themeColor)
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
