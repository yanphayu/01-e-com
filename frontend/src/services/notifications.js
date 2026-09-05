import { request } from './http'

export function getNotifications() {
  return request('/notifications')
}

export function getUnreadCount() {
  return request('/notifications/unread-count')
}

export function markAsRead(id) {
  return request(`/notifications/${id}/read`, { method: 'POST' })
}

export function markAllAsRead() {
  return request('/notifications/read-all', { method: 'POST' })
}
