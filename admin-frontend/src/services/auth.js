import { request } from './http'

export function loginUser(payload) {
  return request('/login', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function getUser() {
  return request('/me')
}

export function logout() {
  return request('/logout', { method: 'POST' })
}
