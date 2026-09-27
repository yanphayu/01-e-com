<template>
  <div class="toast-host" role="region" :aria-label="t('toast.label')">
    <TransitionGroup name="toast">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="toast"
        :class="`toast--${toast.type}`"
        role="status"
      >
        <div class="toast-body">
          <p v-if="toast.title" class="toast-title">{{ toast.title }}</p>
          <p v-if="toast.message" class="toast-message">{{ toast.message }}</p>
        </div>

        <div class="toast-actions">
          <button
            v-if="toast.action"
            type="button"
            class="toast-action"
            @click="runAction(toast)"
          >
            {{ toast.action }}
          </button>
          <button
            type="button"
            class="toast-close"
            :aria-label="t('toast.dismiss')"
            @click="dismissToast(toast.id)"
          >
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
              <path d="M18 6 6 18M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { onUnmounted, watch } from 'vue'
import { t } from '../i18n'
import { dismissToast, toasts } from '../stores/toast'

const timers = new Map()

watch(
  toasts,
  (items) => {
    items.forEach((toast) => {
      if (timers.has(toast.id)) return
      timers.set(
        toast.id,
        window.setTimeout(() => {
          timers.delete(toast.id)
          dismissToast(toast.id)
        }, toast.duration),
      )
    })
  },
  { deep: true, immediate: true },
)

function runAction(toast) {
  dismissToast(toast.id)
  if (toast.onAction) toast.onAction()
}

onUnmounted(() => {
  timers.forEach((timer) => window.clearTimeout(timer))
  timers.clear()
})
</script>

<style scoped>
.toast-host {
  position: fixed;
  top: 1.25rem;
  right: 1.25rem;
  z-index: 200;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  width: min(340px, calc(100vw - 2.5rem));
  pointer-events: none;
}

.toast {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.8rem 0.85rem;
  border: 1px solid var(--border);
  border-left: 3px solid var(--accent);
  border-radius: var(--radius-sm);
  background: var(--surface);
  box-shadow: var(--shadow-md);
  pointer-events: auto;
}

.toast--success {
  border-left-color: #16a34a;
}

.toast--error {
  border-left-color: #dc2626;
}

.toast--warning {
  border-left-color: #d97706;
}

.toast-body {
  min-width: 0;
}

.toast-title {
  color: var(--text);
  font-size: 0.85rem;
  font-weight: 700;
}

.toast-message {
  margin-top: 0.15rem;
  color: var(--text-muted);
  font-size: 0.82rem;
  line-height: 1.35;
  word-break: break-word;
}

.toast-actions {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  gap: 0.35rem;
}

.toast-action {
  border: none;
  border-radius: 999px;
  background: var(--accent-soft);
  padding: 0.25rem 0.6rem;
  color: var(--accent);
  font: inherit;
  font-size: 0.75rem;
  font-weight: 700;
  cursor: pointer;
}

.toast-action:hover {
  background: var(--accent);
  color: var(--on-primary);
}

.toast-close {
  display: grid;
  place-items: center;
  width: 22px;
  height: 22px;
  border: none;
  border-radius: 999px;
  background: none;
  color: var(--text-muted);
  cursor: pointer;
}

.toast-close:hover {
  background: var(--surface-2);
  color: var(--text);
}

.toast-enter-active,
.toast-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

@media (max-width: 480px) {
  .toast-host {
    top: 0.75rem;
    right: 0.75rem;
    left: 0.75rem;
    width: auto;
  }
}
</style>
