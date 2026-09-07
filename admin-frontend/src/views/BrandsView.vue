<template>
  <div class="brands-page">
    <div class="page-header">
      <h1 class="page-title">Brands</h1>
      <button class="btn btn-primary" @click="openCreate">Add Brand</button>
    </div>

    <div v-if="loading" class="loading">Loading...</div>

    <template v-else>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Subcategory</th>
              <th>Models</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in brands" :key="b.id">
              <td class="name-cell">{{ b.name }}</td>
              <td>{{ b.subcategory?.name || '-' }}</td>
              <td><span class="badge badge-neutral">{{ b.models_count ?? '-' }}</span></td>
              <td>
                <div class="action-btns">
                  <button class="btn btn-ghost btn-sm" @click="openEdit(b)">Edit</button>
                  <button class="btn btn-ghost btn-sm danger-text" @click="handleDelete(b)">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!brands.length">
              <td colspan="4" class="empty-row">No brands found</td>
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

    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal">
          <h2 class="modal-title">{{ editingItem ? 'Edit Brand' : 'Add Brand' }}</h2>
          <form @submit.prevent="handleSubmit" class="modal-form">
            <div class="field">
              <label>Subcategory</label>
              <select v-model="form.subcategory_id" class="input" required>
                <option value="">Select subcategory</option>
                <option v-for="s in subcategories" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
            <div class="field">
              <label>Name</label>
              <input v-model="form.name" type="text" class="input" required />
            </div>
            <div v-if="error" class="alert alert-error">{{ error }}</div>
            <div class="modal-actions">
              <button type="button" class="btn btn-ghost" @click="closeModal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="submitting">
                {{ submitting ? 'Saving...' : (editingItem ? 'Save' : 'Create') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { getBrands, createBrand, updateBrand, deleteBrand, getSubcategories } from '../services/admin'

const loading = ref(true)
const brands = ref([])
const subcategories = ref([])
const showModal = ref(false)
const editingItem = ref(null)
const form = ref({ subcategory_id: '', name: '' })
const submitting = ref(false)
const error = ref('')
const currentPage = ref(1)
const lastPage = ref(1)

const visiblePages = computed(() => {
  const pages = []
  for (let i = Math.max(1, currentPage.value - 2); i <= Math.min(lastPage.value, currentPage.value + 2); i++) {
    pages.push(i)
  }
  return pages
})

onMounted(async () => {
  await Promise.all([fetchBrands(), fetchSubcategories()])
  loading.value = false
})

async function fetchBrands() {
  try {
    const res = await getBrands({ page: currentPage.value })
    brands.value = res.data.data
    currentPage.value = res.data.current_page
    lastPage.value = res.data.last_page
  } catch (e) {
    console.error(e)
  }
}

async function fetchSubcategories() {
  try {
    const res = await getSubcategories({ per_page: 100 })
    subcategories.value = res.data.data
  } catch (e) {
    console.error(e)
  }
}

function goToPage(page) {
  currentPage.value = page
  fetchBrands()
}

function openCreate() {
  editingItem.value = null
  form.value = { subcategory_id: '', name: '' }
  error.value = ''
  showModal.value = true
}

function openEdit(b) {
  editingItem.value = b
  form.value = { subcategory_id: b.subcategory_id, name: b.name }
  error.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingItem.value = null
}

async function handleSubmit() {
  submitting.value = true
  error.value = ''
  try {
    if (editingItem.value) {
      await updateBrand(editingItem.value.id, form.value)
    } else {
      await createBrand(form.value)
    }
    closeModal()
    await fetchBrands()
  } catch (e) {
    error.value = e.message
  } finally {
    submitting.value = false
  }
}

async function handleDelete(b) {
  if (!confirm(`Delete brand "${b.name}"?`)) return
  try {
    await deleteBrand(b.id)
    await fetchBrands()
  } catch (e) {
    alert(e.message)
  }
}
</script>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-title { font-size: 1.5rem; font-weight: 700; }
.name-cell { font-weight: 500; }
.action-btns { display: flex; gap: 0.3rem; }
.danger-text { color: var(--danger) !important; }
.danger-text:hover { background: var(--danger-soft) !important; color: var(--danger) !important; }
.empty-row { text-align: center; color: var(--text-muted); padding: 2rem !important; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: grid; place-items: center; z-index: 200; backdrop-filter: blur(2px); }
.modal { width: 100%; max-width: 420px; background: var(--surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); padding: 1.75rem; }
.modal-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem; }
.modal-form { display: flex; flex-direction: column; gap: 1rem; }
.modal-actions { display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.5rem; }
</style>
