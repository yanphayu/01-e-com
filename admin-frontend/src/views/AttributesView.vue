<template>
  <div class="attributes-page">
    <div class="page-header">
      <h1 class="page-title">Attributes</h1>
      <button class="btn btn-primary" @click="openCreate">Add Attribute</button>
    </div>

    <div v-if="loading" class="loading">Loading...</div>

    <template v-else>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in attributes" :key="a.id">
              <td class="name-cell">{{ a.name }}</td>
              <td>
                <div class="action-btns">
                  <button class="btn btn-ghost btn-sm" @click="openEdit(a)">Edit</button>
                  <button class="btn btn-ghost btn-sm danger-text" @click="handleDelete(a)">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!attributes.length">
              <td colspan="2" class="empty-row">No attributes found</td>
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
          <h2 class="modal-title">{{ editingItem ? 'Edit Attribute' : 'Add Attribute' }}</h2>
          <form @submit.prevent="handleSubmit" class="modal-form">
            <div class="field">
              <label>Name</label>
              <input v-model="form.name" type="text" class="input" required placeholder="e.g. Color, Storage, RAM" />
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
import { getAttributes, createAttribute, updateAttribute, deleteAttribute } from '../services/admin'

const loading = ref(true)
const attributes = ref([])
const showModal = ref(false)
const editingItem = ref(null)
const form = ref({ name: '' })
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

onMounted(() => fetchAttributes())

async function fetchAttributes() {
  loading.value = true
  try {
    const res = await getAttributes({ page: currentPage.value })
    attributes.value = res.data.data
    currentPage.value = res.data.current_page
    lastPage.value = res.data.last_page
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function goToPage(page) {
  currentPage.value = page
  fetchAttributes()
}

function openCreate() {
  editingItem.value = null
  form.value = { name: '' }
  error.value = ''
  showModal.value = true
}

function openEdit(a) {
  editingItem.value = a
  form.value = { name: a.name }
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
      await updateAttribute(editingItem.value.id, form.value)
    } else {
      await createAttribute(form.value)
    }
    closeModal()
    await fetchAttributes()
  } catch (e) {
    error.value = e.message
  } finally {
    submitting.value = false
  }
}

async function handleDelete(a) {
  if (!confirm(`Delete attribute "${a.name}"?`)) return
  try {
    await deleteAttribute(a.id)
    await fetchAttributes()
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
