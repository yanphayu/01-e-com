<template>
  <Teleport to="body">
    <div v-if="state.visible" class="modal-overlay" @click.self="cancel">
      <div class="modal" role="dialog" aria-modal="true">
        <div class="modal-head">
          <div class="modal-icon" :class="state.variant">
            <span class="material-symbols-outlined">{{ iconName }}</span>
          </div>
          <div class="modal-head-text">
            <h4 class="modal-title">{{ state.title }}</h4>
            <span class="modal-eyebrow">{{ state.eyebrow || 'Confirmation Required' }}</span>
          </div>
        </div>

        <p class="modal-message">{{ state.message }}</p>

        <div v-if="state.note" class="modal-note">
          <span class="material-symbols-outlined note-ic">{{
            state.variant === 'danger' ? 'report_problem' : 'verified_user'
          }}</span>
          <span>{{ state.note }}</span>
        </div>

        <div class="modal-actions">
          <button class="modal-btn cancel-btn" type="button" @click="cancel">Cancel</button>
          <button
            class="modal-btn confirm-btn"
            :class="state.variant"
            type="button"
            @click="confirm"
          >
            <span class="material-symbols-outlined confirm-ic">{{
              state.variant === 'danger' ? 'block' : 'check_circle'
            }}</span>
            <span>{{ state.confirmText }}</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'
import { confirmState, useConfirm } from '../composables/confirm'

const { resolveConfirm } = useConfirm()
const state = confirmState

const iconName = computed(() => {
  if (state.variant === 'danger') return 'warning'
  if (state.variant === 'warning') return 'warning'
  return 'info'
})

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
  background: rgba(28, 27, 25, 0.55);
  display: grid;
  place-items: center;
  z-index: 300;
  padding: 1rem;
  backdrop-filter: blur(4px);
}

.modal {
  width: 100%;
  max-width: 440px;
  background: var(--surface);
  border-radius: 14px;
  box-shadow: 0 40px 80px -32px rgba(28, 27, 25, 0.4);
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  animation: pop 0.16s ease-out;
}

@keyframes pop {
  from {
    opacity: 0;
    transform: translateY(8px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: none;
  }
}

.modal-head {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.modal-icon {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.modal-icon span {
  font-size: 22px;
}

.modal-icon.danger {
  background: var(--danger-soft);
  color: var(--danger-strong);
}

.modal-icon.warning {
  background: var(--warning-soft);
  color: var(--warning);
}

.modal-icon.info {
  background: var(--accent-soft);
  color: var(--accent);
}

.modal-head-text {
  padding-top: 0.15rem;
  min-width: 0;
}

.modal-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text);
  margin: 0;
  letter-spacing: -0.01em;
}

.modal-eyebrow {
  display: block;
  margin-top: 0.15rem;
  font-size: 0.62rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--text-muted);
}

.modal-message {
  font-size: 0.88rem;
  color: var(--text-muted);
  line-height: 1.6;
  margin: 0;
}

.modal-message :deep(strong) {
  color: var(--text);
}

.modal-note {
  display: flex;
  align-items: flex-start;
  gap: 0.55rem;
  padding: 0.7rem 0.85rem;
  border-radius: 8px;
  background: var(--surface-2);
  font-size: 0.78rem;
  color: var(--text-muted);
}

.note-ic {
  font-size: 17px;
  color: var(--accent);
  flex-shrink: 0;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
  padding-top: 0.25rem;
}

.modal-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  padding: 0.55rem 1.1rem;
  border-radius: 8px;
  font-size: 0.84rem;
  font-weight: 600;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.15s;
}

.cancel-btn {
  background: var(--surface-2);
  color: var(--text);
  border-color: transparent;
}

.cancel-btn:hover {
  background: var(--surface-3);
}

.confirm-btn {
  color: #fff;
  box-shadow: 0 4px 12px -6px rgba(28, 27, 25, 0.35);
}

.confirm-btn.danger {
  background: var(--danger);
}

.confirm-btn.danger:hover {
  background: var(--danger-strong);
}

.confirm-btn.warning {
  background: var(--warning);
}

.confirm-btn.warning:hover {
  background: #8c4a04;
}

.confirm-btn.info {
  background: var(--accent);
}

.confirm-btn.info:hover {
  background: var(--accent-dark);
}

.confirm-ic {
  font-size: 18px;
}
</style>