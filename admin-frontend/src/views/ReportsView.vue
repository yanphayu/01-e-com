<template>
  <div class="reports-page">
    <div class="page-header">
      <h1 class="page-title">Reports</h1>
      <div class="page-actions">
        <select v-model="filterStatus" class="input filter-select" @change="fetchReports">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="resolved">Resolved</option>
          <option value="dismissed">Dismissed</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="loading">Loading...</div>

    <template v-else>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Product</th>
              <th>Reported by</th>
              <th>Reason</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in reports" :key="r.id">
              <td>
                <div class="product-cell">
                  <img v-if="r.product?.images?.[0]" :src="getImageUrl(r.product.images[0].image)" class="product-thumb" alt="" />
                  <div v-else class="product-thumb product-thumb-fallback">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                  </div>
                  <span class="product-cell-name">{{ r.product?.name || '-' }}</span>
                </div>
              </td>
              <td>{{ r.user?.name || '-' }}</td>
              <td>
                <span class="reason-text">{{ reasonLabel(r.reason) }}</span>
                <span v-if="r.details" class="reason-details">{{ r.details }}</span>
              </td>
              <td>
                <span class="badge" :class="statusBadgeClass(r.status)">{{ statusLabel(r.status) }}</span>
              </td>
              <td>{{ formatDate(r.created_at) }}</td>
              <td>
                <div class="action-btns">
                  <RouterLink v-if="r.product" :to="`/products/${r.product.id}`" class="btn btn-ghost btn-sm">View</RouterLink>
                  <button v-if="r.status === 'pending'" class="btn btn-ghost btn-sm" @click="handleResolve(r)">Resolve</button>
                  <button v-if="r.status === 'pending'" class="btn btn-ghost btn-sm danger-text" @click="handleDismiss(r)">Dismiss</button>
                </div>
              </td>
            </tr>
            <tr v-if="!reports.length">
              <td colspan="6" class="empty-row">No reports found</td>
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
import { RouterLink } from 'vue-router'
import { getReports, resolveReport } from '../services/admin'

const loading = ref(true)
const reports = ref([])
const filterStatus = ref('pending')
const currentPage = ref(1)
const lastPage = ref(1)

const visiblePages = computed(() => {
  const total = lastPage.value
  const current = currentPage.value
  const pages = []
  const start = Math.max(1, current - 2)
  const end = Math.min(total, current + 2)
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

async function fetchReports() {
  loading.value = true
  try {
    const params = { page: currentPage.value }
    if (filterStatus.value) params.status = filterStatus.value
    const res = await getReports(params)
    reports.value = res.data.data || []
    currentPage.value = res.data.current_page || 1
    lastPage.value = res.data.last_page || 1
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function handleResolve(r) {
  await resolveReport(r.id, 'resolved')
  await fetchReports()
}

async function handleDismiss(r) {
  await resolveReport(r.id, 'dismissed')
  await fetchReports()
}

function goToPage(page) {
  currentPage.value = page
  fetchReports()
}

function getImageUrl(image) {
  if (!image) return ''
  return image.startsWith('http')
    ? image
    : `http://localhost:8000/storage/${image}`
}

function reasonLabel(reason) {
  const labels = {
    spam: 'Spam',
    fraud: 'Fraud / Scam',
    fake: 'Counterfeit product',
    inappropriate: 'Inappropriate content',
    duplicate: 'Duplicate listing',
    other: 'Other',
  }
  return labels[reason] || reason
}

function statusLabel(status) {
  return status ? status.charAt(0).toUpperCase() + status.slice(1) : '-'
}

function statusBadgeClass(status) {
  if (status === 'pending') return 'badge-warning'
  if (status === 'resolved') return 'badge-success'
  return 'badge-muted'
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

onMounted(fetchReports)
</script>

<style scoped>
.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.page-actions {
  display: flex;
  gap: 0.75rem;
}

.filter-select {
  min-width: 160px;
}

.loading {
  padding: 2rem;
  text-align: center;
  color: var(--text-muted);
}

.table-wrap {
  overflow-x: auto;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}

th {
  text-align: left;
  padding: 0.8rem 1rem;
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
  border-bottom: 1px solid var(--border);
  background: var(--surface-2);
}

td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--border);
  vertical-align: middle;
}

tbody tr:last-child td {
  border-bottom: none;
}

tbody tr:hover {
  background: var(--surface-2);
}

.product-cell {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  min-width: 200px;
}

.product-thumb {
  width: 36px;
  height: 36px;
  border-radius: var(--radius-sm);
  object-fit: cover;
  flex-shrink: 0;
}

.product-thumb-fallback {
  display: grid;
  place-items: center;
  background: var(--surface-2);
  color: var(--text-muted);
}

.product-cell-name {
  font-weight: 500;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.reason-text {
  font-weight: 500;
  display: block;
}

.reason-details {
  display: block;
  margin-top: 0.2rem;
  font-size: 0.78rem;
  color: var(--text-muted);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 260px;
}

.badge {
  display: inline-block;
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: capitalize;
}

.badge-warning {
  background: rgba(234, 179, 8, 0.12);
  color: #b45309;
}

.badge-success {
  background: var(--success-soft);
  color: var(--success);
}

.badge-muted {
  background: var(--surface-2);
  color: var(--text-muted);
}

.action-btns {
  display: flex;
  gap: 0.5rem;
}

.empty-row {
  text-align: center;
  color: var(--text-muted);
  padding: 2rem !important;
}

.pagination {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-top: 1.25rem;
  justify-content: center;
}

.pagination button {
  min-width: 34px;
  padding: 0.35rem 0.6rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface);
  color: var(--text);
  font-size: 0.85rem;
  cursor: pointer;
}

.pagination button.active {
  background: var(--accent);
  color: #fff;
  border-color: var(--accent);
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>