const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

import { getLocale } from '../i18n'

async function request(endpoint, options = {}) {
  const sep = endpoint.includes('?') ? '&' : '?'
  const url = `${API_URL}${endpoint}${sep}lang=${getLocale()}`

  const token = localStorage.getItem('token')
  const isForm = options.body instanceof FormData
  const headers = {
    ...(!isForm ? { 'Content-Type': 'application/json' } : {}),
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...options.headers,
  }

  const response = await fetch(url, {
    headers,
    ...options,
  })

  const data = await response.json().catch(() => ({}))

  if (!response.ok) {
    throw new Error(data.message || 'Something went wrong')
  }

  return data
}

export function registerUser(payload) {
  return request('/register', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function loginUser(payload) {
  return request('/login', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function verifyEmail(payload) {
  return request('/verify', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function resendVerification(payload) {
  return request('/resend', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function updateProfile(payload) {
  return request('/profile', {
    method: 'PUT',
    body: JSON.stringify(payload),
  })
}

export function getUser() {
  return request('/me')
}

export function uploadAvatar(file) {
  const form = new FormData()
  form.append('avatar', file)
  return request('/avatar', {
    method: 'POST',
    body: form,
  })
}
