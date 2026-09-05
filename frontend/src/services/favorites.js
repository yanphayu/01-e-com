import { request } from './http'

export function getFavorites(params = {}) {
  const query = new URLSearchParams()
  if (params.page) query.set('page', params.page)
  const qs = query.toString()
  return request(`/favorites${qs ? '?' + qs : ''}`)
}

export function toggleFavorite(productId) {
  return request(`/products/${productId}/favorite`, {
    method: 'POST',
  })
}
