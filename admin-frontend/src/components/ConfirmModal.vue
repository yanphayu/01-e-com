<template>
  <Teleport to="body">
    <div v-if="state.visible" class="modal-overlay" @click.self="cancel">
      <div class="modal confirm-modal">
        <div class="confirm-icon" :class="state.variant">
          <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
        </div>
        <h3 class="modal-title">{{ state.title }}</h3>
        <p class="confirm-message">{{ state.message }}</p>
        <div class="modal-actions">
          <button class="btn btn-ghost" @click="cancel">Cancel</button>
          <button class="btn" :class="state.variant === 'danger' ? 'btn-danger' : 'btn-primary'" @click="confirm">{{ state.confirmText }}</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { confirmState, useConfirm } from '../composables/confirm'

const { resolveConfirm } = useConfirm()
const state = confirmState

function confirm() {
  resolveConfirm(true)
}

function cancel() {
  resolveConfirm(false)
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: grid;
  place-items: center;
  z-index: 300;
  backdrop-filter: blur(2px);
}

.modal {
  width: 100%;
  max-width: 380px;
  background: var(--surface);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  padding: 1.75rem;
}

.confirm-modal {
  text-align: center;
}

.confirm-icon {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  margin: 0 auto 1rem;
}

.confirm-icon.danger {
  background: var(--danger-soft);
  color: var(--danger);
}

.confirm-icon.warning {
  background: rgba(234, 179, 8, 0.12);
  color: #b45309;
}

.confirm-icon.info {
  background: var(--accent-soft);
  color: var(--accent);
}

.modal-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.confirm-message {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin-bottom: 1.25rem;
  word-break: break-word;
}

.modal-actions {
  display: flex;
  justify-content: center;
  gap: 0.6rem;
}
</style>