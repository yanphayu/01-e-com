<template>
  <div class="categories-page">
    <div class="page-header">
      <h1 class="page-title">Categories</h1>
      <button class="btn btn-primary" @click="openCreate">Add Category</button>
    </div>

    <div v-if="loading" class="loading">Loading...</div>

    <template v-else>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Slug</th>
              <th>Subcategories</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in categories" :key="c.id">
              <td class="name-cell">{{ c.name }}</td>
              <td class="slug-cell">{{ c.slug }}</td>
              <td>{{ c.subcategories?.length || 0 }}</td>
              <td>
                <button class="badge" :class="c.is_active ? 'badge-success' : 'badge-warning'" @click="handleToggleActive(c)">
                  {{ c.is_active ? 'Active' : 'Inactive' }}
                </button>
              </td>
              <td>
                <div class="action-btns">
                  <button class="btn btn-ghost btn-sm" @click="openEdit(c)">Edit</button>
                  <button class="btn btn-ghost btn-sm danger-text" @click="handleDelete(c)">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!categories.length">
              <td colspan="5" class="empty-row">No categories found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal">
          <h2 class="modal-title">{{ editingCategory ? 'Edit Category' : 'Add Category' }}</h2>
          <form @submit.prevent="handleSubmit" class="modal-form">
            <div class="field">
              <label>Name</label>
              <input v-model="form.name" type="text" class="input" required />
            </div>
            <div class="field">
              <label>Description</label>
              <input v-model="form.description" type="text" class="input" />
            </div>
            <div v-if="error" class="alert alert-error">{{ error }}</div>
            <div class="modal-actions">
              <button type="button" class="btn btn-ghost" @click="closeModal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="submitting">
                {{ submitting ? 'Saving...' : (editingCategory ? 'Save' : 'Create') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getCategories, createCategory, updateCategory, deleteCategory, toggleCategoryActive } from '../services/admin'

const loading = ref(true)
const categories = ref([])
const showModal = ref(false)
const editingCategory = ref(null)
const form = ref({ name: '', description: '' })
const submitting = ref(false)
const error = ref('')

onMounted(() => fetchCategories())

async function fetchCategories() {
  loading.value = true
  try {
    const res = await getCategories()
    categories.value = res.data.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingCategory.value = null
  form.value = { name: '', description: '' }
  error.value = ''
  showModal.value = true
}

function openEdit(c) {
  editingCategory.value = c
  form.value = { name: c.name, description: c.description || '' }
  error.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingCategory.value = null
  form.value = { name: '', description: '' }
  error.value = ''
}

async function handleSubmit() {
  submitting.value = true
  error.value = ''
  try {
    if (editingCategory.value) {
      const res = await updateCategory(editingCategory.value.id, form.value)
      const idx = categories.value.findIndex(c => c.id === editingCategory.value.id)
      if (idx !== -1) categories.value[idx] = { ...categories.value[idx], ...res.data }
    } else {
      const res = await createCategory(form.value)
      categories.value.unshift(res.data)
    }
    closeModal()
  } catch (e) {
    error.value = e.message
  } finally {
    submitting.value = false
  }
}

async function handleDelete(c) {
  if (!confirm(`Delete category "${c.name}"?`)) return
  try {
    await deleteCategory(c.id)
    categories.value = categories.value.filter(x => x.id !== c.id)
  } catch (e) {
    alert(e.message)
  }
}

async function handleToggleActive(c) {
  try {
    const res = await toggleCategoryActive(c.id)
    c.is_active = res.data.is_active
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

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
}

.name-cell {
  font-weight: 500;
}

.slug-cell {
  color: var(--text-muted);
  font-size: 0.85rem;
}

.action-btns {
  display: flex;
  gap: 0.3rem;
}

.danger-text {
  color: var(--danger) !important;
}

.danger-text:hover {
  background: var(--danger-soft) !important;
  color: var(--danger) !important;
}

.empty-row {
  text-align: center;
  color: var(--text-muted);
  padding: 2rem !important;
}

.badge {
  cursor: pointer;
  border: none;
  font-family: inherit;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: grid;
  place-items: center;
  z-index: 200;
  backdrop-filter: blur(2px);
}

.modal {
  width: 100%;
  max-width: 420px;
  background: var(--surface);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  padding: 1.75rem;
}

.modal-title {
  font-size: 1.15rem;
  font-weight: 700;
  margin-bottom: 1.25rem;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 0.5rem;
}
</style>
