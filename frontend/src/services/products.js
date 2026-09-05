import { request } from './http'

export function getCategories() {
  return request('/categories')
}

export function getSubcategories(categoryId) {
  return request(`/categories/${categoryId}/subcategories`)
}

export function getAttributes() {
  return request('/attributes')
}

export function getBrands(subcategoryId) {
  const qs = subcategoryId ? `?subcategory_id=${subcategoryId}` : ''
  return request(`/brands${qs}`)
}

export function getModels(brandId) {
  return request(`/brands/${brandId}/models`)
}

export function getProducts(params = {}) {
  const query = new URLSearchParams()
  if (params.category_id) query.set('category_id', params.category_id)
  if (params.subcategory_id) query.set('subcategory_id', params.subcategory_id)
  if (params.province) query.set('province', params.province)
  if (params.q) query.set('q', params.q)
  if (params.page) query.set('page', params.page)

  const qs = query.toString()
  return request(`/products${qs ? '?' + qs : ''}`)
}

export function getProduct(id) {
  return request(`/products/${id}`)
}

export function createProduct(formData) {
  return request('/products', {
    method: 'POST',
    body: formData,
  })
}

export function updateProduct(id, payload) {
  return request(`/products/${id}`, {
    method: 'PUT',
    body: JSON.stringify(payload),
  })
}

export function deleteProduct(id) {
  return request(`/products/${id}`, {
    method: 'DELETE',
  })
}

export function uploadProductImage(productId, file) {
  const form = new FormData()
  form.append('image', file)
  return request(`/products/${productId}/images`, {
    method: 'POST',
    body: form,
  })
}

export function deleteProductImage(productId, imageId) {
  return request(`/products/${productId}/images/${imageId}`, {
    method: 'DELETE',
  })
}

export function getUser(userId) {
  return request(`/users/${userId}`)
}

export function globalSearch(q) {
  return request(`/search?q=${encodeURIComponent(q)}`)
}

export function getComments(productId) {
  return request(`/products/${productId}/comments`)
}

export function addComment(productId, body, parentId = null) {
  return request(`/products/${productId}/comments`, {
    method: 'POST',
    body: JSON.stringify({ body, parent_id: parentId }),
  })
}

export function deleteComment(productId, commentId) {
  return request(`/products/${productId}/comments/${commentId}`, {
    method: 'DELETE',
  })
}
