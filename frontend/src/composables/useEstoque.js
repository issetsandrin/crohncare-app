export function useEstoque() {
  function diasRestantes(quantidade, doseDiaria) {
    if (!doseDiaria || doseDiaria <= 0) return Infinity
    return Math.floor(quantidade / doseDiaria)
  }

  function nivelAlerta(dias) {
    if (dias <= 3) return 'urgente'
    if (dias <= 7) return 'atencao'
    return 'ok'
  }

  return {
    diasRestantes,
    nivelAlerta
  }
}
