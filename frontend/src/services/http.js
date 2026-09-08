const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

import { getLocale } from '../i18n'

export async function request(endpoint, options = {}) {
  const sep = endpoint.includes('?') ? '&' : '?'
  const url = `${API_URL}${endpoint}${sep}lang=${getLocale()}`

  const token = localStorage.getItem('token')
  const isForm = options.body instanceof FormData
  const headers = {
    Accept: 'application/json',
    ...(!isForm ? { 'Content-Type': 'application/json' } : {}),
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...options.headers,
  }

  const response = await fetch(url, {
    headers,
    ...options,
  })

  const data = await response.json().catch(() => ({}))

  if (response.status === 401 && token) {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    const path = window.location.pathname
    if (path !== '/login' && path !== '/register') {
      window.location.href = '/login'
    }
    const isUnauth = typeof data.message === 'string' && data.message.startsWith('Unauthenticated')
    throw new Error(isUnauth ? 'Session expired. Please log in again.' : data.message)
  }

  if (!response.ok) {
    throw new Error(data.message || 'Something went wrong')
  }

  if (data.success === false) {
    throw new Error(data.message || 'Something went wrong')
  }

  return data
}
