import { ref } from 'vue'

export const chatUnread = ref(0)

export function setChatUnread(total) {
  chatUnread.value = total
}

export function addChatUnread(delta = 1) {
  chatUnread.value += delta
}

export function resetChatUnread() {
  chatUnread.value = 0
}