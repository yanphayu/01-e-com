import { request } from './http'

export function getDashboard() {
  return request('/admin/dashboard')
}

export function getUsers(params = {}) {
  const qs = new URLSearchParams(params).toString()
  return request(`/admin/users${qs ? '?' + qs : ''}`)
}

export function getUser(id) {
  return request(`/admin/users/${id}`)
}

export function updateUser(id, data) {
  return request(`/admin/users/${id}`, {
    method: 'PUT',
    body: JSON.stringify(data),
  })
}

export function deleteUser(id) {
  return request(`/admin/users/${id}`, { method: 'DELETE' })
}

export function toggleAdmin(id) {
  return request(`/admin/users/${id}/toggle-admin`, { method: 'POST' })
}

export function getProducts(params = {}) {
  const qs = new URLSearchParams(params).toString()
  return request(`/admin/products${qs ? '?' + qs : ''}`)
}

export function getProduct(id) {
  return request(`/admin/products/${id}`)
}

export function toggleProductActive(id) {
  return request(`/admin/products/${id}/toggle-active`, { method: 'POST' })
}

export function deleteProduct(id) {
  return request(`/admin/products/${id}`, { method: 'DELETE' })
}

export function getCategories(params = {}) {
  const qs = new URLSearchParams(params).toString()
  return request(`/admin/categories${qs ? '?' + qs : ''}`)
}

export function createCategory(data) {
  return request('/admin/categories', {
    method: 'POST',
    body: JSON.stringify(data),
  })
}

export function updateCategory(id, data) {
  return request(`/admin/categories/${id}`, {
    method: 'PUT',
    body: JSON.stringify(data),
  })
}

export function deleteCategory(id) {
  return request(`/admin/categories/${id}`, { method: 'DELETE' })
}

export function toggleCategoryActive(id) {
  return request(`/admin/categories/${id}/toggle-active`, { method: 'POST' })
}

export function getSubcategories(params = {}) {
  const qs = new URLSearchParams(params).toString()
  return request(`/admin/subcategories${qs ? '?' + qs : ''}`)
}

export function createSubcategory(data) {
  return request('/admin/subcategories', {
    method: 'POST',
    body: JSON.stringify(data),
  })
}

export function updateSubcategory(id, data) {
  return request(`/admin/subcategories/${id}`, {
    method: 'PUT',
    body: JSON.stringify(data),
  })
}

export function deleteSubcategory(id) {
  return request(`/admin/subcategories/${id}`, { method: 'DELETE' })
}

export function toggleSubcategoryActive(id) {
  return request(`/admin/subcategories/${id}/toggle-active`, { method: 'POST' })
}

export function getBrands(params = {}) {
  const qs = new URLSearchParams(params).toString()
  return request(`/admin/brands${qs ? '?' + qs : ''}`)
}

export function createBrand(data) {
  return request('/admin/brands', {
    method: 'POST',
    body: JSON.stringify(data),
  })
}

export function updateBrand(id, data) {
  return request(`/admin/brands/${id}`, {
    method: 'PUT',
    body: JSON.stringify(data),
  })
}

export function deleteBrand(id) {
  return request(`/admin/brands/${id}`, { method: 'DELETE' })
}

export function getModels(params = {}) {
  const qs = new URLSearchParams(params).toString()
  return request(`/admin/models${qs ? '?' + qs : ''}`)
}

export function createModel(data) {
  return request('/admin/models', {
    method: 'POST',
    body: JSON.stringify(data),
  })
}

export function updateModel(id, data) {
  return request(`/admin/models/${id}`, {
    method: 'PUT',
    body: JSON.stringify(data),
  })
}

export function deleteModel(id) {
  return request(`/admin/models/${id}`, { method: 'DELETE' })
}

export function getAttributes(params = {}) {
  const qs = new URLSearchParams(params).toString()
  return request(`/admin/attributes${qs ? '?' + qs : ''}`)
}

export function createAttribute(data) {
  return request('/admin/attributes', {
    method: 'POST',
    body: JSON.stringify(data),
  })
}

export function updateAttribute(id, data) {
  return request(`/admin/attributes/${id}`, {
    method: 'PUT',
    body: JSON.stringify(data),
  })
}

export function deleteAttribute(id) {
  return request(`/admin/attributes/${id}`, { method: 'DELETE' })
}
