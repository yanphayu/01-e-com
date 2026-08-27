const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

import { getLocale } from '../i18n'

async function request(endpoint, options = {}) {
  const sep = endpoint.includes('?') ? '&' : '?'
  const url = `${API_URL}${endpoint}${sep}lang=${getLocale()}`

  const token = localStorage.getItem('token')
  const headers = {
    'Content-Type': 'application/json',
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
  return request('/v1/auth/register', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function loginUser(payload) {
  return request('/v1/auth/login', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function verifyEmail(payload) {
  return request('/v1/auth/verify-email', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function resendVerification(payload) {
  return request('/v1/auth/resend-verification', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function updateProfile(payload) {
  return request('/v1/auth/profile', {
    method: 'PUT',
    body: JSON.stringify(payload),
  })
}
