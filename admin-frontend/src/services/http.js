const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

export async function request(endpoint, options = {}) {
  const sep = endpoint.includes('?') ? '&' : '?'
  const url = `${API_URL}${endpoint}${sep}lang=en`

  const token = localStorage.getItem('admin_token')
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

  if (!response.ok) {
    throw new Error(data.message || 'Something went wrong')
  }

  if (data.success === false) {
    throw new Error(data.message || 'Something went wrong')
  }

  return data
}
