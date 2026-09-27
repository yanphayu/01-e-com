<template>
  <div ref="root" class="select-field">
    <label v-if="label" :for="id">{{ label }}</label>

    <button
      :id="id"
      type="button"
      class="select-trigger"
      :class="{ open }"
      :aria-expanded="open"
      aria-haspopup="listbox"
      @click="open = !open"
      @keydown.down.prevent="openOptions('down')"
      @keydown.up.prevent="openOptions('up')"
      @keydown.escape="open = false"
    >
      <span class="select-value">{{ selectedLabel }}</span>
      <svg class="chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </button>

    <ul v-if="open" class="select-menu" role="listbox">
      <li v-for="option in options" :key="option.value">
        <button
          type="button"
          class="select-option"
          :class="{ active: option.value === modelValue }"
          role="option"
          :aria-selected="option.value === modelValue"
          @click="choose(option.value)"
        >
          <span>{{ option.label }}</span>
          <svg v-if="option.value === modelValue" class="check" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12" />
          </svg>
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  options: { type: Array, default: () => [] },
  label: { type: String, default: '' },
  id: { type: String, default: () => `select-${Math.random().toString(36).slice(2, 9)}` },
})

const emit = defineEmits(['update:modelValue', 'change'])

const open = ref(false)
const root = ref(null)

const selectedLabel = computed(() => {
  const match = props.options.find(option => option.value === props.modelValue)
  return match ? match.label : ''
})

function choose(value) {
  emit('update:modelValue', value)
  emit('change', value)
  open.value = false
}

function openOptions(direction) {
  open.value = true
  requestAnimationFrame(() => {
    const items = root.value?.querySelectorAll('.select-option') ?? []
    if (!items.length) return
    const index = [...items].findIndex(item => item.classList.contains('active'))
    const next = direction === 'down'
      ? (index + 1) % items.length
      : (index - 1 + items.length) % items.length
    items[index === -1 ? 0 : next]?.focus()
  })
}

function onDocumentClick(e) {
  if (root.value && !root.value.contains(e.target)) open.value = false
}

onMounted(() => document.addEventListener('click', onDocumentClick))
onUnmounted(() => document.removeEventListener('click', onDocumentClick))
</script>

<style scoped>
.select-field {
  position: relative;
  width: auto;
  min-width: 11rem;
}

.select-field > label {
  display: block;
  margin-bottom: 0.35rem;
  color: var(--text-muted);
  font-size: 0.8rem;
  font-weight: 600;
}

.select-trigger {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  width: 100%;
  height: 42px;
  min-height: 42px;
  padding: 0 0.75rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface);
  color: var(--text);
  font: inherit;
  font-size: 0.9rem;
  text-align: left;
  cursor: pointer;
}

.select-trigger:hover {
  border-color: var(--border-strong);
}

.select-trigger:focus,
.select-trigger.open {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px var(--focus-ring);
  outline: none;
}

.select-value {
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}

.chevron {
  flex-shrink: 0;
  color: var(--text-muted);
  transition: transform 0.15s;
}

.select-trigger.open .chevron {
  transform: rotate(180deg);
}

.select-menu {
  position: absolute;
  top: calc(100% + 0.35rem);
  left: 0;
  right: 0;
  z-index: 60;
  margin: 0;
  padding: 0.3rem;
  list-style: none;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  box-shadow: var(--shadow-md);
}

.select-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  width: 100%;
  padding: 0.55rem 0.6rem;
  border: none;
  border-radius: var(--radius-sm);
  background: none;
  color: var(--text);
  font: inherit;
  font-size: 0.9rem;
  text-align: left;
  cursor: pointer;
}

.select-option:hover {
  background: var(--surface-2);
}

.select-option.active {
  color: var(--accent);
  font-weight: 600;
  background: var(--accent-soft);
}

.check {
  flex-shrink: 0;
  color: var(--accent);
}
</style>
