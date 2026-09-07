<template>
  <div class="product-detail-page">
    <button class="btn btn-ghost btn-sm back-btn" @click="$router.push('/products')">&larr; Back to Products</button>

    <div v-if="loading" class="loading">Loading...</div>

    <template v-else-if="product">
      <div class="detail-header">
        <div class="detail-heading">
          <h1 class="detail-name">{{ product.name }}</h1>
          <div class="detail-badges">
            <span class="badge" :class="statusBadgeClass(product.status)">{{ statusLabel(product.status) }}</span>
            <span class="badge" :class="product.is_active ? 'badge-success' : 'badge-warning'">{{ product.is_active ? 'Active' : 'Inactive' }}</span>
            <span class="badge badge-neutral">{{ product.subcategory?.name }} · {{ product.subcategory?.category?.name }}</span>
          </div>
        </div>
        <div class="detail-price">${{ Number(product.price).toLocaleString() }}</div>
      </div>

      <div v-if="product.images?.length" class="gallery">
        <div v-for="img in product.images" :key="img.id" class="gallery-item">
          <img :src="getImageUrl(img.image)" :alt="product.name" class="gallery-img" />
          <span v-if="img.is_primary" class="gallery-badge">Primary</span>
        </div>
      </div>

      <div class="cards">
        <div class="card">
          <h3 class="card-title">Owner</h3>
          <div class="owner-row">
            <img v-if="product.user?.profile?.avatar" :src="getImageUrl(product.user.profile.avatar)" class="owner-avatar" alt="" />
            <div v-else class="owner-avatar owner-avatar-fallback">{{ (product.user?.name || '?')[0] }}</div>
            <div class="owner-info">
              <span class="owner-name">{{ product.user?.name || '-' }}</span>
              <span class="owner-email">{{ product.user?.email || '' }}</span>
            </div>
          </div>
        </div>

        <div class="card">
          <h3 class="card-title">Details</h3>
          <div class="detail-rows">
            <div class="detail-row">
              <span class="detail-label">Description</span>
              <span class="detail-value">{{ product.description || '-' }}</span>
            </div>
            <div class="detail-row" v-if="product.detail">
              <span class="detail-label">Brand</span>
              <span>{{ product.detail.brand?.name || '-' }}</span>
            </div>
            <div class="detail-row" v-if="product.detail">
              <span class="detail-label">Model</span>
              <span>{{ product.detail.model?.name || '-' }}</span>
            </div>
            <div class="detail-row" v-if="product.detail">
              <span class="detail-label">Condition</span>
              <span>{{ product.detail.condition || '-' }}</span>
            </div>
            <div class="detail-row" v-if="product.detail?.province">
              <span class="detail-label">Province</span>
              <span>{{ product.detail.province }}</span>
            </div>
            <div class="detail-row" v-if="product.detail?.khan">
              <span class="detail-label">Khan</span>
              <span>{{ product.detail.khan }}</span>
            </div>
            <div class="detail-row" v-if="product.detail?.sangkat">
              <span class="detail-label">Sangkat</span>
              <span>{{ product.detail.sangkat }}</span>
            </div>
            <div class="detail-row" v-if="product.detail?.address">
              <span class="detail-label">Address</span>
              <span>{{ product.detail.address }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Phones</span>
              <span>{{ product.phones?.map(ph => ph.phone).join(', ') || '-' }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Posted</span>
              <span>{{ formatDate(product.created_at) }}</span>
            </div>
          </div>
        </div>

        <div class="card" v-if="product.productAttributes?.length">
          <h3 class="card-title">Attributes</h3>
          <div class="detail-rows">
            <div v-for="attr in product.productAttributes" :key="attr.id" class="detail-row">
              <span class="detail-label">{{ attr.attribute?.name || attr.attribute_id }}</span>
              <span>{{ attr.value }}</span>
            </div>
          </div>
        </div>

        <div class="card" v-if="product.comments?.length">
          <h3 class="card-title">Comments ({{ product.comments.length }})</h3>
          <div class="comment" v-for="c in product.comments" :key="c.id">
            <span class="comment-author">{{ c.user?.name || 'Unknown' }}</span>
            <span class="comment-body">{{ c.body }}</span>
          </div>
        </div>
      </div>

      <div class="action-bar">
        <button v-if="product.status !== 'approved'" class="btn btn-primary" @click="handleApprove">Approve</button>
        <button v-if="product.status !== 'rejected'" class="btn btn-danger" @click="handleReject">Reject</button>
        <button class="btn btn-ghost" @click="handleDelete">Delete</button>
        <a :href="`http://localhost:5173/products/${product.id}`" target="_blank" class="btn btn-ghost">View on site</a>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getProduct, approveProduct, rejectProduct, deleteProduct } from '../services/admin'

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const product = ref(null)

onMounted(async () => {
  try {
    const res = await getProduct(route.params.id)
    product.value = res.data
  } catch (e) {
    alert(e.message)
    router.push('/products')
  } finally {
    loading.value = false
  }
})

function getImageUrl(url) {
  if (!url) return ''
  if (url.startsWith('http')) return url
  return `http://localhost:8000/storage/${url}`
}

async function handleApprove() {
  try {
    const res = await approveProduct(product.value.id)
    product.value.status = res.data.status
    product.value.is_active = true
  } catch (e) {
    alert(e.message)
  }
}

async function handleReject() {
  try {
    const res = await rejectProduct(product.value.id)
    product.value.status = res.data.status
    product.value.is_active = false
  } catch (e) {
    alert(e.message)
  }
}

async function handleDelete() {
  if (!confirm(`Delete product "${product.value.name}"?`)) return
  try {
    await deleteProduct(product.value.id)
    router.push('/products')
  } catch (e) {
    alert(e.message)
  }
}

function statusLabel(status) {
  return { pending: 'Pending', approved: 'Approved', rejected: 'Rejected' }[status] || status || '-'
}

function statusBadgeClass(status) {
  return { pending: 'badge-warning', approved: 'badge-success', rejected: 'badge-danger' }[status] || 'badge-neutral'
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
}
</script>

<style scoped>
.back-btn { margin-bottom: 1rem; }
.detail-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.detail-name { font-size: 1.5rem; font-weight: 700; }
.detail-badges { display: flex; gap: 0.4rem; margin-top: 0.5rem; flex-wrap: wrap; }
.detail-price { font-size: 1.6rem; font-weight: 700; color: var(--accent); }
.gallery { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.gallery-item { position: relative; }
.gallery-img { width: 140px; height: 140px; border-radius: var(--radius-sm); object-fit: cover; }
.gallery-badge { position: absolute; top: 6px; left: 6px; background: var(--accent); color: #fff; font-size: 0.65rem; font-weight: 700; padding: 0.1rem 0.4rem; border-radius: 4px; }
.cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
.card-title { font-size: 0.95rem; font-weight: 600; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border); }
.detail-rows { display: flex; flex-direction: column; gap: 0.6rem; }
.detail-row { display: flex; justify-content: space-between; gap: 1rem; font-size: 0.875rem; }
.detail-label { color: var(--text-muted); font-weight: 500; flex-shrink: 0; }
.detail-value { text-align: right; word-break: break-word; }
.owner-row { display: flex; align-items: center; gap: 0.75rem; }
.owner-avatar { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.owner-avatar-fallback { background: var(--accent); color: #fff; display: grid; place-items: center; font-weight: 700; }
.owner-info { display: flex; flex-direction: column; }
.owner-name { font-weight: 600; font-size: 0.9rem; }
.owner-email { font-size: 0.8rem; color: var(--text-muted); }
.comment { display: flex; flex-direction: column; gap: 0.2rem; padding: 0.6rem 0; border-bottom: 1px solid var(--border); }
.comment:last-child { border-bottom: none; }
.comment-author { font-weight: 600; font-size: 0.8rem; }
.comment-body { font-size: 0.875rem; }
.action-bar { display: flex; gap: 0.5rem; flex-wrap: wrap; }
</style>