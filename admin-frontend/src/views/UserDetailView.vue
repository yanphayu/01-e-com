<template>
  <div class="user-detail-page">
    <button class="btn btn-ghost btn-sm back-btn" @click="$router.push('/users')">&larr; Back to Users</button>

    <div v-if="loading" class="loading">Loading...</div>

    <template v-else-if="user">
      <div class="detail-header">
        <img v-if="user.profile?.avatar" :src="getAvatarUrl(user.profile.avatar)" class="detail-avatar" alt="" />
        <div v-else class="detail-avatar detail-avatar-fallback">{{ (user.name || '?')[0] }}</div>
        <div class="detail-info">
          <h1 class="detail-name">{{ user.name }}</h1>
          <p class="detail-email">{{ user.email }}</p>
          <div class="detail-badges">
            <span v-if="user.is_admin" class="badge badge-success">Admin</span>
            <span v-else class="badge badge-neutral">User</span>
            <span class="badge badge-neutral">{{ user.products?.length || 0 }} products</span>
            <span class="badge badge-neutral">{{ user.comments?.length || 0 }} comments</span>
          </div>
        </div>
      </div>

      <div class="card">
        <h3 class="card-title">User Info</h3>
        <div class="detail-rows">
          <div class="detail-row">
            <span class="detail-label">Name</span>
            <span>{{ user.name }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Email</span>
            <span>{{ user.email }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Joined</span>
            <span>{{ formatDate(user.created_at) }}</span>
          </div>
          <div class="detail-row" v-if="user.profile">
            <span class="detail-label">Phone</span>
            <span>{{ user.profile.phone || '-' }}</span>
          </div>
        </div>
      </div>

      <div class="card" style="margin-top: 1.25rem;">
        <h3 class="card-title">Products ({{ user.products?.length || 0 }})</h3>
        <div v-if="user.products?.length" class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in user.products" :key="p.id">
                <td>
                  <div class="product-cell">
                    <img v-if="getProductImage(p)" :src="getProductImage(p)" class="product-thumb" alt="" />
                    <div v-else class="product-thumb product-thumb-fallback">
                      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                    </div>
                    <span class="product-name">{{ p.name }}</span>
                  </div>
                </td>
                <td class="price-cell">${{ Number(p.price).toLocaleString() }}</td>
                <td>
                  <button class="badge" :class="p.is_active ? 'badge-success' : 'badge-warning'" @click="handleToggleActive(p)">
                    {{ p.is_active ? 'Active' : 'Inactive' }}
                  </button>
                </td>
                <td>
                  <div class="action-btns">
                    <a :href="`http://localhost:5173/products/${p.id}`" target="_blank" class="btn btn-ghost btn-sm">View</a>
                    <button class="btn btn-ghost btn-sm danger-text" @click="handleDelete(p)">Delete</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="empty-text">No products</div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { getUser, toggleProductActive, deleteProduct } from '../services/admin'

const route = useRoute()
const loading = ref(true)
const user = ref(null)

onMounted(async () => {
  try {
    const res = await getUser(route.params.id)
    user.value = res.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

function getAvatarUrl(url) {
  if (!url) return ''
  if (url.startsWith('http')) return url
  return `http://localhost:8000/storage/${url}`
}

function getProductImage(p) {
  const img = p.images?.[0]?.image
  if (!img) return null
  if (img.startsWith('http')) return img
  return `http://localhost:8000/storage/${img}`
}

async function handleToggleActive(p) {
  try {
    const res = await toggleProductActive(p.id)
    p.is_active = res.data.is_active
  } catch (e) {
    alert(e.message)
  }
}

async function handleDelete(p) {
  if (!confirm(`Delete product "${p.name}"?`)) return
  try {
    await deleteProduct(p.id)
    user.value.products = user.value.products.filter(x => x.id !== p.id)
  } catch (e) {
    alert(e.message)
  }
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
}
</script>

<style scoped>
.back-btn { margin-bottom: 1rem; }
.detail-header { display: flex; align-items: center; gap: 1.25rem; margin-bottom: 2rem; }
.detail-avatar { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.detail-avatar-fallback { background: var(--accent); color: #fff; display: grid; place-items: center; font-size: 1.5rem; font-weight: 700; }
.detail-name { font-size: 1.4rem; font-weight: 700; }
.detail-email { color: var(--text-muted); font-size: 0.9rem; }
.detail-badges { display: flex; gap: 0.4rem; margin-top: 0.4rem; }
.card-title { font-size: 0.95rem; font-weight: 600; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border); }
.detail-rows { display: flex; flex-direction: column; gap: 0.6rem; }
.detail-row { display: flex; justify-content: space-between; font-size: 0.875rem; }
.detail-label { color: var(--text-muted); font-weight: 500; }
.product-cell { display: flex; align-items: center; gap: 0.5rem; }
.product-thumb { width: 32px; height: 32px; border-radius: var(--radius-sm); object-fit: cover; flex-shrink: 0; }
.product-thumb-fallback { background: var(--surface-2); display: grid; place-items: center; color: var(--text-muted); }
.product-name { font-weight: 500; }
.price-cell { font-weight: 600; color: var(--accent); }
.action-btns { display: flex; gap: 0.3rem; }
.danger-text { color: var(--danger) !important; }
.danger-text:hover { background: var(--danger-soft) !important; color: var(--danger) !important; }
.empty-text { padding: 1.5rem; text-align: center; color: var(--text-muted); font-size: 0.85rem; }
.badge { cursor: pointer; border: none; font-family: inherit; }
</style>
