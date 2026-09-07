<template>
  <div class="models-page">
    <div class="page-header">
      <h1 class="page-title">Models</h1>
      <button class="btn btn-primary" @click="openCreate">Add Model</button>
    </div>

    <div v-if="loading" class="loading">Loading...</div>

    <template v-else>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Brand</th>
              <th>Attributes</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="m in models" :key="m.id">
              <td class="name-cell">{{ m.name }}</td>
              <td>{{ m.brand?.name || '-' }}</td>
              <td>
                <div v-if="m.attributes?.length" class="attr-tags">
                  <span v-for="attr in m.attributes" :key="attr.id" class="attr-tag">{{ attr.name }}</span>
                </div>
                <span v-else class="badge badge-neutral">-</span>
              </td>
              <td>
                <div class="action-btns">
                  <button class="btn btn-ghost btn-sm" @click="openEdit(m)">Edit</button>
                  <button class="btn btn-ghost btn-sm danger-text" @click="handleDelete(m)">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!models.length">
              <td colspan="4" class="empty-row">No models found</td>
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
        <div class="modal modal-lg">
          <h2 class="modal-title">{{ editingItem ? 'Edit Model' : 'Add Model' }}</h2>
          <form @submit.prevent="handleSubmit" class="modal-form">
            <div class="field">
              <label>Brand</label>
              <select v-model="form.brand_id" class="input" required>
                <option value="">Select brand</option>
                <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
              </select>
            </div>
            <div class="field">
              <label>Name</label>
              <input v-model="form.name" type="text" class="input" required />
            </div>
            <div class="field">
              <label>Attributes</label>
              <div class="attr-picker">
                <label v-for="a in allAttributes" :key="a.id" class="attr-checkbox">
                  <input type="checkbox" :value="a.id" v-model="form.attribute_ids" />
                  <span>{{ a.name }}</span>
                </label>
                <div v-if="!allAttributes.length" class="attr-empty">No attributes available</div>
              </div>
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
import { getModels, createModel, updateModel, deleteModel, getBrands, getAttributes } from '../services/admin'

const loading = ref(true)
const models = ref([])
const brands = ref([])
const allAttributes = ref([])
const showModal = ref(false)
const editingItem = ref(null)
const form = ref({ brand_id: '', name: '', attribute_ids: [] })
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
  await Promise.all([fetchModels(), fetchBrands(), fetchAttributes()])
  loading.value = false
})

async function fetchModels() {
  try {
    const res = await getModels({ page: currentPage.value })
    models.value = res.data.data
    currentPage.value = res.data.current_page
    lastPage.value = res.data.last_page
  } catch (e) {
    console.error(e)
  }
}

async function fetchBrands() {
  try {
    const res = await getBrands({ per_page: 100 })
    brands.value = res.data.data
  } catch (e) {
    console.error(e)
  }
}

async function fetchAttributes() {
  try {
    const res = await getAttributes({ per_page: 100 })
    allAttributes.value = res.data.data
  } catch (e) {
    console.error(e)
  }
}

function goToPage(page) {
  currentPage.value = page
  fetchModels()
}

function openCreate() {
  editingItem.value = null
  form.value = { brand_id: '', name: '', attribute_ids: [] }
  error.value = ''
  showModal.value = true
}

function openEdit(m) {
  editingItem.value = m
  form.value = {
    brand_id: m.brand_id,
    name: m.name,
    attribute_ids: m.attributes?.map(a => a.id) || [],
  }
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
      await updateModel(editingItem.value.id, form.value)
    } else {
      await createModel(form.value)
    }
    closeModal()
    await fetchModels()
  } catch (e) {
    error.value = e.message
  } finally {
    submitting.value = false
  }
}

async function handleDelete(m) {
  if (!confirm(`Delete model "${m.name}"?`)) return
  try {
    await deleteModel(m.id)
    await fetchModels()
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
.attr-tags { display: flex; flex-wrap: wrap; gap: 0.3rem; }
.attr-tag { font-size: 0.75rem; font-weight: 500; padding: 0.15rem 0.5rem; border-radius: 999px; background: var(--accent-soft); color: var(--accent); }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: grid; place-items: center; z-index: 200; backdrop-filter: blur(2px); }
.modal { width: 100%; max-width: 420px; background: var(--surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); padding: 1.75rem; }
.modal-lg { max-width: 520px; }
.modal-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem; }
.modal-form { display: flex; flex-direction: column; gap: 1rem; }
.modal-actions { display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.5rem; }
.attr-picker { display: flex; flex-wrap: wrap; gap: 0.5rem; padding: 0.6rem; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--surface-2); max-height: 140px; overflow-y: auto; }
.attr-checkbox { display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; cursor: pointer; }
.attr-checkbox input { accent-color: var(--accent); }
.attr-empty { width: 100%; text-align: center; color: var(--text-muted); font-size: 0.85rem; padding: 0.5rem; }
</style>
