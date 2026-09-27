import { ref } from 'vue'

export const toasts = ref([])

let nextId = 1

const PRESET_DURATION = {
  info: 4000,
  success: 4000,
  error: 6000,
  warning: 5000,
}

export function pushToast({ type = 'info', message, title, duration, action, onAction }) {
  const id = `toast-${nextId++}`
  const item = {
    id,
    type,
    message,
    title: title || null,
    action: action || null,
    onAction: typeof onAction === 'function' ? onAction : null,
    duration: duration ?? PRESET_DURATION[type] ?? 4000,
  }
  toasts.value.push(item)
  return id
}

export function dismissToast(id) {
  toasts.value = toasts.value.filter((toast) => toast.id !== id)
}

export function dismissAll() {
  toasts.value = []
}
