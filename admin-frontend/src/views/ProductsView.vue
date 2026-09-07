<template>
  <div class="products-page">
    <div class="page-header">
      <h1 class="page-title">Products</h1>
      <div class="page-actions">
        <select v-model="filterStatus" class="input filter-select" @change="fetchProducts">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
        <input v-model="search" type="text" class="input search-input" placeholder="Search products..." @input="debouncedFetch" />
      </div>
    </div>

    <div v-if="loading" class="loading">Loading...</div>

    <template v-else>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Product</th>
              <th>Price</th>
              <th>Category</th>
              <th>Owner</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in products" :key="p.id">
              <td>
                <div class="product-cell">
                  <img v-if="p.images?.[0]" :src="getImageUrl(p.images[0].image)" class="product-thumb" alt="" />
                  <div v-else class="product-thumb product-thumb-fallback">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                  </div>
                  <span class="product-cell-name">{{ p.name }}</span>
                </div>
              </td>
              <td class="price-cell">${{ Number(p.price).toLocaleString() }}</td>
              <td>{{ p.subcategory?.category?.name || '-' }}</td>
              <td>{{ p.user?.name || '-' }}</td>
              <td>
                <span class="badge" :class="statusBadgeClass(p.status)">{{ statusLabel(p.status) }}</span>
              </td>
              <td>{{ formatDate(p.created_at) }}</td>
              <td>
                <div class="action-btns">
                  <RouterLink :to="`/products/${p.id}`" class="btn btn-ghost btn-sm">View</RouterLink>
                  <button v-if="p.status !== 'approved'" class="btn btn-ghost btn-sm" @click="handleApprove(p)">Approve</button>
                  <button v-if="p.status !== 'rejected'" class="btn btn-ghost btn-sm danger-text" @click="handleReject(p)">Reject</button>
                  <button class="btn btn-ghost btn-sm danger-text" @click="handleDelete(p)">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!products.length">
              <td colspan="7" class="empty-row">No products found</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="lastPage > 1" class="pagination">
        <button :disabled="currentPage <= 1" @click="goToPage(currentPage - 1)">Prev</button>
        <button v-for="page in visiblePages" :key="page" :class="{ active: page === currentPage }" @click="goToPage(page)">{{ page }}</button>
        <button :disabled="currentPage >= lastPage" @click="goToPage(currentPage + 1)">Next</button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { getProducts, approveProduct, rejectProduct, deleteProduct } from '../services/admin'

const loading = ref(true)
const products = ref([])
const search = ref('')
const filterStatus = ref('')
const currentPage = ref(1)
const lastPage = ref(1)
let searchTimer = null

const visiblePages = computed(() => {
  const pages = []
  for (let i = Math.max(1, currentPage.value - 2); i <= Math.min(lastPage.value, currentPage.value + 2); i++) {
    pages.push(i)
  }
  return pages
})

onMounted(() => fetchProducts())

async function fetchProducts() {
  loading.value = true
  try {
    const params = { page: currentPage.value }
    if (search.value) params.search = search.value
    if (filterStatus.value !== '') params.status = filterStatus.value
    const res = await getProducts(params)
    products.value = res.data.data
    currentPage.value = res.data.current_page
    lastPage.value = res.data.last_page
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function debouncedFetch() {
  clearTimeout(searchTimer)
  currentPage.value = 1
  searchTimer = setTimeout(fetchProducts, 300)
}

function goToPage(page) {
  currentPage.value = page
  fetchProducts()
}

async function handleApprove(p) {
  try {
    const res = await approveProduct(p.id)
    p.status = res.data.status
  } catch (e) {
    alert(e.message)
  }
}

async function handleReject(p) {
  try {
    const res = await rejectProduct(p.id)
    p.status = res.data.status
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

async function handleDelete(p) {
  if (!confirm(`Delete product "${p.name}"?`)) return
  try {
    await deleteProduct(p.id)
    products.value = products.value.filter(x => x.id !== p.id)
  } catch (e) {
    alert(e.message)
  }
}

function getImageUrl(path) {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return `http://localhost:8000/storage/${path}`
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<style scoped>
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
  gap: 1rem;
  flex-wrap: wrap;
}

.page-title { font-size: 1.5rem; font-weight: 700; }
.page-actions { display: flex; gap: 0.5rem; }
.filter-select { width: auto; min-width: 120px; }
.search-input { max-width: 220px; }
.product-cell { display: flex; align-items: center; gap: 0.6rem; }
.product-thumb { width: 36px; height: 36px; border-radius: var(--radius-sm); object-fit: cover; flex-shrink: 0; }
.product-thumb-fallback { background: var(--surface-2); display: grid; place-items: center; color: var(--text-muted); }
.product-cell-name { font-weight: 500; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.price-cell { font-weight: 600; color: var(--accent); }
.action-btns { display: flex; gap: 0.3rem; }
.danger-text { color: var(--danger) !important; }
.danger-text:hover { background: var(--danger-soft) !important; color: var(--danger) !important; }
.empty-row { text-align: center; color: var(--text-muted); padding: 2rem !important; }
.badge { cursor: pointer; border: none; font-family: inherit; }
</style>
