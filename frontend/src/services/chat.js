import { request } from './http'

export function getConversations() {
  return request('/conversations')
}

export function createConversation(userId) {
  return request('/conversations', {
    method: 'POST',
    body: JSON.stringify({ user_id: userId }),
  })
}

export function getMessages(conversationId, page = 1) {
  return request(`/conversations/${conversationId}/messages?page=${page}`)
}

export function sendMessage(conversationId, data) {
  const isFormData = data instanceof FormData
  return request(`/conversations/${conversationId}/messages`, {
    method: 'POST',
    body: isFormData ? data : JSON.stringify(data),
  })
}

export function sendProductMessage(conversationId, productId, body = null) {
  const payload = { product_id: productId }
  if (body) payload.body = body
  return request(`/conversations/${conversationId}/messages`, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function searchUsers(query) {
  return request(`/chat/search-users?q=${encodeURIComponent(query)}`)
}
