const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

import { getLocale } from '../i18n'

async function request(endpoint, options = {}) {
  const sep = endpoint.includes('?') ? '&' : '?'
  const url = `${API_URL}${endpoint}${sep}lang=${getLocale()}`

  const response = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
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
  return request('/verify-email', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function resendVerification(payload) {
  return request('/resend-verification', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}
