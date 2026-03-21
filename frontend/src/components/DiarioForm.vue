<script setup>
import { reactive, ref, computed, nextTick, onMounted } from 'vue'
import { useDiarioStore } from '../stores/diario'
import IntensidadeEscala from './IntensidadeEscala.vue'

const props = defineProps({
  diario: { type: Object, default: null },
  tipo: { type: String, default: 'anotacao' }
})

const emit = defineEmits(['submit', 'cancel'])
const diarioStore = useDiarioStore()

onMounted(() => {
  diarioStore.fetchTags()
})

function hoje() {
  return new Date().toISOString().slice(0, 10)
}

function parseTags(sintomas) {
  if (!sintomas) return []
  return sintomas.split(',').map(t => t.trim()).filter(Boolean)
}

function initForm(val) {
  if (val) {
    return {
      data: val.data || hoje(),
      tags: parseTags(val.sintomas),
      intensidade: val.intensidade || 1,
      observacoes: val.observacoes || ''
    }
  }
  return { data: hoje(), tags: [], intensidade: 1, observacoes: '' }
}

const form = reactive(initForm(props.diario))

const tagInput = ref('')
const inputRef = ref(null)
const showSugestoes = ref(false)

const tagsDisponiveis = computed(() => {
  return props.tipo === 'crise' ? diarioStore.tagsCrise : diarioStore.tagsAnotacao
})

const sugestoesAutocomplete = computed(() => {
  const query = tagInput.value.trim().toLowerCase()
  if (!query) return []
  const jaAdicionadas = new Set(form.tags.map(t => t.toLowerCase()))
  return tagsDisponiveis.value
    .filter(t => t.toLowerCase().includes(query) && !jaAdicionadas.has(t.toLowerCase()))
    .slice(0, 5)
})

const sugestoesRapidas = computed(() => {
  const jaAdicionadas = new Set(form.tags.map(t => t.toLowerCase()))
  return tagsDisponiveis.value
    .filter(t => !jaAdicionadas.has(t.toLowerCase()))
    .slice(0, 10)
})

function adicionarTag(tag) {
  const trimmed = tag.trim()
  if (!trimmed) return
  const jaExiste = form.tags.some(t => t.toLowerCase() === trimmed.toLowerCase())
  if (!jaExiste) {
    form.tags.push(trimmed)
  }
  tagInput.value = ''
  showSugestoes.value = false
  nextTick(() => inputRef.value?.focus())
}

function removerTag(index) {
  form.tags.splice(index, 1)
}

function onInputKeydown(e) {
  if (e.key === 'Enter') {
    e.preventDefault()
    if (sugestoesAutocomplete.value.length > 0) {
      adicionarTag(sugestoesAutocomplete.value[0])
    } else if (tagInput.value.trim()) {
      adicionarTag(tagInput.value)
    }
  } else if (e.key === 'Backspace' && !tagInput.value && form.tags.length > 0) {
    form.tags.pop()
  }
}

function onInputFocus() {
  showSugestoes.value = true
}

function onInputBlur() {
  setTimeout(() => { showSugestoes.value = false }, 150)
}

function onSubmit() {
  if (tagInput.value.trim()) {
    adicionarTag(tagInput.value)
  }
  emit('submit', {
    data: form.data,
    sintomas: form.tags.join(', '),
    intensidade: form.intensidade,
    observacoes: form.observacoes
  })
}
</script>

<template>
  <form class="diario-form" @submit.prevent="onSubmit">
    <div class="form-group">
      <label class="form-label">Data</label>
      <input type="date" v-model="form.data" class="form-input" required />
    </div>

    <div class="form-group">
      <label class="form-label">Sintomas</label>
      <div class="tag-input-container" @click="inputRef?.focus()">
        <span
          v-for="(tag, i) in form.tags"
          :key="i"
          class="tag-chip"
        >
          {{ tag }}
          <button type="button" class="tag-remove" @click.stop="removerTag(i)" aria-label="Remover">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
              <path d="M3 3l6 6M9 3l-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
          </button>
        </span>
        <input
          ref="inputRef"
          v-model="tagInput"
          class="tag-text-input"
          placeholder="Digite um sintoma..."
          @keydown="onInputKeydown"
          @focus="onInputFocus"
          @blur="onInputBlur"
        />
      </div>

      <!-- Autocomplete dropdown -->
      <div v-if="showSugestoes && sugestoesAutocomplete.length > 0" class="autocomplete-dropdown">
        <button
          v-for="sug in sugestoesAutocomplete"
          :key="sug"
          type="button"
          class="autocomplete-item"
          @mousedown.prevent="adicionarTag(sug)"
        >
          {{ sug }}
        </button>
      </div>

      <!-- Tags rapidas -->
      <div v-if="sugestoesRapidas.length > 0" class="tags-rapidas">
        <button
          v-for="tag in sugestoesRapidas"
          :key="tag"
          type="button"
          class="tag-sugestao"
          @click="adicionarTag(tag)"
        >
          + {{ tag }}
        </button>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Intensidade</label>
      <IntensidadeEscala v-model="form.intensidade" />
    </div>

    <div class="form-group">
      <label class="form-label">Observacoes</label>
      <textarea v-model="form.observacoes" class="form-input form-textarea" rows="2" placeholder="Notas adicionais..." />
    </div>

    <div class="form-actions">
      <button type="button" class="btn btn-secondary" @click="emit('cancel')">Cancelar</button>
      <button type="submit" class="btn btn-primary" :disabled="form.tags.length === 0 && !tagInput.trim()">Salvar</button>
    </div>
  </form>
</template>

<style scoped>
.diario-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  position: relative;
}

.form-label {
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 600;
  color: var(--texto);
}

.form-input {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 12px;
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  background: #fff;
  transition: border-color 0.2s;
  width: 100%;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: var(--verde-salvia);
}

.form-textarea {
  resize: vertical;
  min-height: 60px;
}

/* Tag input */
.tag-input-container {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  padding: 8px 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #fff;
  cursor: text;
  transition: border-color 0.2s;
  min-height: 42px;
  align-items: center;
}

.tag-input-container:focus-within {
  border-color: var(--verde-salvia);
}

.tag-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  background: rgba(76, 175, 80, 0.12);
  color: var(--verde-salvia);
  border-radius: 6px;
  font-family: var(--font-corpo);
  font-size: 13px;
  font-weight: 500;
  white-space: nowrap;
  animation: chipIn 0.15s ease;
}

@keyframes chipIn {
  from { transform: scale(0.85); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

.tag-remove {
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  cursor: pointer;
  color: var(--verde-salvia);
  padding: 0;
  opacity: 0.6;
  transition: opacity 0.15s;
}

.tag-remove:hover {
  opacity: 1;
}

.tag-text-input {
  flex: 1;
  min-width: 100px;
  border: none;
  outline: none;
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  background: transparent;
  padding: 4px 0;
}

.tag-text-input::placeholder {
  color: #bbb;
}

/* Autocomplete */
.autocomplete-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  z-index: 20;
  overflow: hidden;
  margin-top: 2px;
}

.autocomplete-item {
  display: block;
  width: 100%;
  text-align: left;
  padding: 10px 12px;
  border: none;
  background: transparent;
  font-family: var(--font-corpo);
  font-size: 14px;
  color: var(--texto);
  cursor: pointer;
  transition: background 0.15s;
}

.autocomplete-item:hover {
  background: rgba(76, 175, 80, 0.08);
  color: var(--verde-salvia);
}

.autocomplete-item + .autocomplete-item {
  border-top: 1px solid #f0f0f0;
}

/* Tags rapidas (sugestoes) */
.tags-rapidas {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 2px;
}

.tag-sugestao {
  padding: 5px 10px;
  border: 1px dashed rgba(76, 175, 80, 0.35);
  border-radius: 6px;
  background: transparent;
  color: var(--texto-light);
  font-family: var(--font-corpo);
  font-size: 12px;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;
}

.tag-sugestao:hover {
  background: rgba(76, 175, 80, 0.08);
  border-color: var(--verde-salvia);
  color: var(--verde-salvia);
}

/* Actions */
.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 8px;
}

.btn {
  flex: 1;
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-family: var(--font-corpo);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}

.btn:active {
  opacity: 0.8;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--verde-salvia);
  color: #fff;
}

.btn-secondary {
  background: #f0f0f0;
  color: var(--texto);
}
</style>
