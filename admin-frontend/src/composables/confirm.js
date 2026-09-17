import { reactive } from 'vue'

export const confirmState = reactive({
  visible: false,
  title: 'Are you sure?',
  message: '',
  confirmText: 'Confirm',
  variant: 'danger',
  resolving: null,
})

export function useConfirm() {
  function confirmDialog(message, options = {}) {
    confirmState.visible = true
    confirmState.title = options.title || 'Are you sure?'
    confirmState.message = message
    confirmState.confirmText = options.confirmText || 'Confirm'
    confirmState.variant = options.variant || 'danger'

    return new Promise((resolve) => {
      confirmState.resolving = resolve
    })
  }

  function resolveConfirm(result) {
    confirmState.visible = false
    if (confirmState.resolving) confirmState.resolving(result)
    confirmState.resolving = null
  }

  return { confirmDialog, resolveConfirm }
}