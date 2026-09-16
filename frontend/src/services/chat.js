import { request } from './http'

export function getConversations(archived = false) {
  return request(`/conversations?archived=${archived ? 1 : 0}`)
}

export function createConversation(userId) {
  return request('/conversations', {
    method: 'POST',
    body: JSON.stringify({ user_id: userId }),
  })
}

export function updateConversation(conversationId, patch) {
  return request(`/conversations/${conversationId}`, {
    method: 'PATCH',
    body: JSON.stringify(patch),
  })
}

export function deleteConversation(conversationId) {
  return request(`/conversations/${conversationId}`, {
    method: 'DELETE',
  })
}

export function markConversationUnread(conversationId) {
  return request(`/conversations/${conversationId}/unread`, {
    method: 'POST',
  })
}

export function blockConversationUser(conversationId) {
  return request(`/conversations/${conversationId}/block`, {
    method: 'POST',
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

export function updateMessage(messageId, patch) {
  return request(`/messages/${messageId}`, {
    method: 'PATCH',
    body: JSON.stringify(patch),
  })
}

export function deleteMessage(messageId) {
  return request(`/messages/${messageId}`, {
    method: 'DELETE',
  })
}

export function pinMessage(messageId, pinned) {
  return request(`/messages/${messageId}/pin`, {
    method: 'POST',
    body: JSON.stringify({ pinned }),
  })
}

export function reactToMessage(messageId, reaction) {
  return request(`/messages/${messageId}/react`, {
    method: 'POST',
    body: JSON.stringify({ reaction }),
  })
}

export function getBlockedUsers() {
  return request('/chat/blocked-users')
}

export function unblockUser(userId) {
  return request(`/chat/blocked-users/${userId}`, {
    method: 'DELETE',
  })
}
