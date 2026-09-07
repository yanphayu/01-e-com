<template>
  <div class="subcategories-page">
    <div class="page-header">
      <h1 class="page-title">Subcategories</h1>
      <button class="btn btn-primary" @click="openCreate">Add Subcategory</button>
    </div>

    <div v-if="loading" class="loading">Loading...</div>

    <template v-else>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Category</th>
              <th>Brand/Model</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in subcategories" :key="s.id">
              <td class="name-cell">{{ s.name }}</td>
              <td>{{ s.category?.name || '-' }}</td>
              <td>
                <span v-if="s.has_brand" class="badge badge-success">Brand</span>
                <span v-if="s.has_model" class="badge badge-neutral">Model</span>
                <span v-if="!s.has_brand && !s.has_model" class="badge badge-neutral">-</span>
              </td>
              <td>
                <button class="badge" :class="s.is_active ? 'badge-success' : 'badge-warning'" @click="handleToggleActive(s)">
                  {{ s.is_active ? 'Active' : 'Inactive' }}
                </button>
              </td>
              <td>
                <div class="action-btns">
                  <button class="btn btn-ghost btn-sm" @click="openEdit(s)">Edit</button>
                  <button class="btn btn-ghost btn-sm danger-text" @click="handleDelete(s)">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!subcategories.length">
              <td colspan="5" class="empty-row">No subcategories found</td>
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

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal">
          <h2 class="modal-title">{{ editingItem ? 'Edit Subcategory' : 'Add Subcategory' }}</h2>
          <form @submit.prevent="handleSubmit" class="modal-form">
            <div class="field">
              <label>Category</label>
              <select v-model="form.category_id" class="input" required>
                <option value="">Select category</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div class="field">
              <label>Name</label>
              <input v-model="form.name" type="text" class="input" required />
            </div>
            <div class="field">
              <label>Description</label>
              <input v-model="form.description" type="text" class="input" />
            </div>
            <div class="checkbox-row">
              <label><input type="checkbox" v-model="form.has_brand" /> Has Brand</label>
              <label><input type="checkbox" v-model="form.has_model" /> Has Model</label>
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
import { getSubcategories, createSubcategory, updateSubcategory, deleteSubcategory, toggleSubcategoryActive, getCategories } from '../services/admin'

const loading = ref(true)
const subcategories = ref([])
const categories = ref([])
const showModal = ref(false)
const editingItem = ref(null)
const form = ref({ category_id: '', name: '', description: '', has_brand: false, has_model: false })
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
  await Promise.all([fetchSubcategories(), fetchCategories()])
  loading.value = false
})

async function fetchSubcategories() {
  try {
    const res = await getSubcategories({ page: currentPage.value })
    subcategories.value = res.data.data
    currentPage.value = res.data.current_page
    lastPage.value = res.data.last_page
  } catch (e) {
    console.error(e)
  }
}

async function fetchCategories() {
  try {
    const res = await getCategories({ per_page: 100 })
    categories.value = res.data.data
  } catch (e) {
    console.error(e)
  }
}

function goToPage(page) {
  currentPage.value = page
  fetchSubcategories()
}

function openCreate() {
  editingItem.value = null
  form.value = { category_id: '', name: '', description: '', has_brand: false, has_model: false }
  error.value = ''
  showModal.value = true
}

function openEdit(s) {
  editingItem.value = s
  form.value = { category_id: s.category_id, name: s.name, description: s.description || '', has_brand: s.has_brand, has_model: s.has_model }
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
      await updateSubcategory(editingItem.value.id, form.value)
    } else {
      await createSubcategory(form.value)
    }
    closeModal()
    await fetchSubcategories()
  } catch (e) {
    error.value = e.message
  } finally {
    submitting.value = false
  }
}

async function handleDelete(s) {
  if (!confirm(`Delete subcategory "${s.name}"?`)) return
  try {
    await deleteSubcategory(s.id)
    await fetchSubcategories()
  } catch (e) {
    alert(e.message)
  }
}

async function handleToggleActive(s) {
  try {
    const res = await toggleSubcategoryActive(s.id)
    s.is_active = res.data.is_active
  } catch (e) {
    alert(e.message)
  }
}
</script>

<style scoped>
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}
.page-title { font-size: 1.5rem; font-weight: 700; }
.name-cell { font-weight: 500; }
.action-btns { display: flex; gap: 0.3rem; }
.danger-text { color: var(--danger) !important; }
.danger-text:hover { background: var(--danger-soft) !important; color: var(--danger) !important; }
.empty-row { text-align: center; color: var(--text-muted); padding: 2rem !important; }
.badge { cursor: pointer; border: none; font-family: inherit; }
.checkbox-row { display: flex; gap: 1.5rem; font-size: 0.875rem; }
.checkbox-row label { display: flex; align-items: center; gap: 0.4rem; cursor: pointer; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: grid; place-items: center; z-index: 200; backdrop-filter: blur(2px); }
.modal { width: 100%; max-width: 420px; background: var(--surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); padding: 1.75rem; }
.modal-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem; }
.modal-form { display: flex; flex-direction: column; gap: 1rem; }
.modal-actions { display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.5rem; }
</style>
