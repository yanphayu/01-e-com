import { request } from './http'

export function reportProduct(productId, data) {
  return request(`/products/${productId}/reports`, {
    method: 'POST',
    body: JSON.stringify(data),
  })
}