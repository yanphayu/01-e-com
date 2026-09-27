<template>
  <Teleport to="body">
    <div v-if="modelValue" class="modal-overlay" @click.self="close">
      <div class="modal" :class="`modal-${size}`" role="dialog" aria-modal="true">
        <header v-if="title || $slots.header" class="modal-head">
          <slot name="header">
            <h3>{{ title }}</h3>
          </slot>
          <button class="modal-close" type="button" aria-label="Close" @click="close">×</button>
        </header>
        <div class="modal-body">
          <slot />
        </div>
        <footer v-if="$slots.footer" class="modal-foot">
          <slot name="footer" />
        </footer>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: '' },
  size: { type: String, default: 'md' },
})

const emit = defineEmits(['update:modelValue', 'close'])

function close() {
  emit('update:modelValue', false)
  emit('close')
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 100;
  display: grid;
  place-items: center;
  padding: 1.25rem;
  background: var(--overlay);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
}

.modal {
  display: flex;
  flex-direction: column;
  width: 100%;
  max-height: calc(100dvh - 2.5rem);
  overflow: hidden;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  background: var(--surface);
  box-shadow: var(--shadow-lg);
}

.modal-sm {
  max-width: 380px;
}
.modal-md {
  max-width: 520px;
}
.modal-lg {
  max-width: 720px;
}

.modal-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-shrink: 0;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border);
  background: var(--surface-2);
}

.modal-head h3 {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.modal-head h3::before {
  content: '';
  width: 4px;
  height: 1.15em;
  border-radius: 999px;
  background: var(--primary);
}

.modal-close {
  display: grid;
  place-items: center;
  width: 34px;
  height: 34px;
  flex-shrink: 0;
  border: 1px solid var(--border);
  border-radius: 50%;
  background: var(--surface);
  color: var(--text-muted);
  font-size: 1.45rem;
  line-height: 1;
  cursor: pointer;
}

.modal-close:hover {
  color: var(--text);
  background: var(--surface-3);
  border-color: var(--border-strong);
}

.modal-body {
  min-height: 0;
  overflow-y: auto;
  padding: 1.25rem;
}

.modal-foot {
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
  flex-shrink: 0;
  padding: 1rem 1.25rem;
  border-top: 1px solid var(--border);
  background: var(--surface-2);
}

@media (max-width: 480px) {
  .modal-overlay {
    align-items: end;
    padding: 0.75rem;
  }

  .modal {
    max-height: calc(100dvh - 1.5rem);
  }

  .modal-body,
  .modal-head,
  .modal-foot {
    padding-inline: 1rem;
  }
}
</style>
