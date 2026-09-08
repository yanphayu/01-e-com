<template>
  <div class="product-edit container">
    <h1 class="page-title">{{ t('product.editProduct') }}</h1>

    <div v-if="loading" class="loading">{{ t('product.loading') }}</div>
    <div v-else-if="!product" class="empty">Product not found</div>
    <template v-else>
      <p v-if="error" class="alert alert-error">{{ error }}</p>
      <p v-if="success" class="alert alert-success">{{ success }}</p>

      <div class="form-grid">
        <div v-if="showBrand" class="field">
          <label>{{ t('product.brand') }}</label>
          <input v-model="form.details.brand_name" class="input" :placeholder="t('product.brandPlaceholder')" list="edit-brand-list" @input="onBrandInput" />
          <datalist id="edit-brand-list">
            <option v-for="b in brandOptions" :key="b.id" :value="b.name" />
          </datalist>
        </div>
        <div v-if="showModel" class="field">
          <label>{{ t('product.model') }}</label>
          <input v-model="form.details.model_name" class="input" :placeholder="t('product.modelPlaceholder')" list="edit-model-list" @input="onModelInput" />
          <datalist id="edit-model-list">
            <option v-for="m in modelOptions" :key="m.id" :value="m.name" />
          </datalist>
        </div>
        <div class="field full">
          <label>{{ t('product.productName') }} *</label>
          <input v-model="form.name" class="input" :placeholder="t('product.productNamePlaceholder')" />
          <p v-if="errors.name" class="field-error">{{ errors.name }}</p>
        </div>
        <div class="field full">
          <label>{{ t('product.description') }}</label>
          <textarea v-model="form.description" class="input textarea" :placeholder="t('product.descriptionPlaceholder')" rows="3"></textarea>
        </div>
        <div class="field full">
          <label>{{ t('product.condition') }}</label>
          <div class="condition-toggle">
            <button type="button" class="condition-btn" :class="{ active: form.details.condition === 'new' }" @click="form.details.condition = 'new'">{{ t('product.conditionNew') }}</button>
            <button type="button" class="condition-btn" :class="{ active: form.details.condition === 'used' }" @click="form.details.condition = 'used'">{{ t('product.conditionUsed') }}</button>
          </div>
        </div>
        <div class="field">
          <label>{{ t('product.price') }} *</label>
          <input v-model="form.price" type="number" step="0.01" min="0" class="input" :placeholder="t('product.pricePlaceholder')" />
          <p v-if="errors.price" class="field-error">{{ errors.price }}</p>
        </div>
        <div class="field">
          <label>{{ t('product.province') }}</label>
          <input v-model="form.details.province" class="input" :placeholder="t('product.provincePlaceholder')" />
        </div>
        <div class="field">
          <label>{{ t('product.khan') }}</label>
          <input v-model="form.details.khan" class="input" :placeholder="t('product.khanPlaceholder')" />
        </div>
        <div class="field">
          <label>{{ t('product.sangkat') }}</label>
          <input v-model="form.details.sangkat" class="input" :placeholder="t('product.sangkatPlaceholder')" />
        </div>
        <div class="field full">
          <label>{{ t('product.address') }}</label>
          <input v-model="form.details.address" class="input" :placeholder="t('product.addressPlaceholder')" />
        </div>
        <div class="field full">
          <label>{{ t('product.phoneNumbers') }}</label>
          <div v-for="(phone, i) in form.phones" :key="i" class="phone-row">
            <input v-model="form.phones[i]" class="input" :placeholder="t('product.phonePlaceholder')" type="tel" />
            <button type="button" v-if="form.phones.length > 1" class="remove-btn" @click="form.phones.splice(i, 1)">✕</button>
          </div>
          <button type="button" class="add-btn" @click="addPhone">+ {{ t('product.addPhone') }}</button>
        </div>
      </div>

      <!-- Images -->
      <div class="images-section">
        <h2 class="section-title">{{ t('product.images') }}</h2>
        <div v-if="existingImages.length" class="existing-images">
          <div v-for="img in existingImages" :key="img.id" class="existing-img">
            <img :src="`/storage/${img.image}`" :alt="product.name" />
            <button type="button" class="remove-img" @click="removeExistingImage(img)">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
        </div>
        <div class="upload-area">
          <label class="upload-label">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
              <circle cx="8.5" cy="8.5" r="1.5"/>
              <polyline points="21 15 16 10 5 21"/>
            </svg>
            {{ t('product.addImages') }}
            <input type="file" multiple accept="image/*" @change="onFilesChange" class="file-input" />
          </label>
        </div>
        <div v-if="newImagePreviews.length" class="new-images">
          <div v-for="(preview, i) in newImagePreviews" :key="i" class="existing-img">
            <img :src="preview" alt="" />
            <button type="button" class="remove-img" @click="removeNewImage(i)">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <RouterLink :to="`/products/${product.id}`" class="btn btn-ghost">{{ t('auth.cancel') }}</RouterLink>
        <button class="btn btn-primary" :disabled="submitting" @click="submit">
          <span v-if="submitting" class="spinner-sm"></span>
          {{ t('auth.save') }}
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { t } from '../i18n'
import { getProduct, updateProduct, getCategories, getBrands, getModels, deleteProductImage } from '../services/products'

const route = useRoute()
const router = useRouter()

function addPhone() {
  form.phones.push('')
}

const product = ref(null)
const loading = ref(true)
const submitting = ref(false)
const error = ref(null)
const success = ref(null)

const form = reactive({
  name: '',
  description: '',
  price: '',
  phones: [''],
  details: {
    brand_id: null,
    model_id: null,
    brand_name: '',
    model_name: '',
    condition: 'new',
    province: '',
    khan: '',
    sangkat: '',
    address: '',
  },
})

const errors = reactive({ name: null, price: null })

const existingImages = ref([])
const imagesToRemove = ref([])
const newImageFiles = ref([])
const newImagePreviews = ref([])

const brandOptions = ref([])
const modelOptions = ref([])

const showBrand = computed(() => {
  const sub = product.value?.subcategory
  return sub?.has_brand
})

const showModel = computed(() => {
  const sub = product.value?.subcategory
  return sub?.has_model
})

function onBrandInput() {
  const val = form.details.brand_name
  const found = brandOptions.value.find(b => b.name.toLowerCase() === val.toLowerCase())
  if (found) {
    form.details.brand_id = found.id
    loadModels(found.id)
  } else {
    form.details.brand_id = null
    form.details.model_id = null
    modelOptions.value = []
  }
}

function onModelInput() {
  const val = form.details.model_name
  const found = modelOptions.value.find(m => m.name.toLowerCase() === val.toLowerCase())
  form.details.model_id = found ? found.id : null
}

async function loadModels(brandId) {
  try {
    const data = await getModels(brandId)
    modelOptions.value = (data.data || []).map(m => ({ id: m.id, name: m.name }))
  } catch {
    modelOptions.value = []
  }
}

function onFilesChange(e) {
  const files = Array.from(e.target.files || [])
  files.forEach(file => {
    newImageFiles.value.push(file)
    newImagePreviews.value.push(URL.createObjectURL(file))
  })
  e.target.value = ''
}

function removeExistingImage(img) {
  imagesToRemove.value.push(img.id)
  existingImages.value = existingImages.value.filter(i => i.id !== img.id)
}

function removeNewImage(index) {
  newImageFiles.value.splice(index, 1)
  URL.revokeObjectURL(newImagePreviews.value[index])
  newImagePreviews.value.splice(index, 1)
}

async function submit() {
  error.value = null
  success.value = null
  errors.name = form.name.trim() ? null : t('product.nameRequired')
  errors.price = form.price ? null : t('product.priceRequired')
  if (errors.name || errors.price) return

  submitting.value = true
  try {
    await updateProduct(product.value.id, {
      name: form.name,
      description: form.description || null,
      price: form.price,
      phones: form.phones.filter(p => p.trim()).map(p => p.trim()),
      details: {
        brand_id: form.details.brand_id || null,
        model_id: form.details.model_id || null,
        condition: form.details.condition,
        province: form.details.province || null,
        khan: form.details.khan || null,
        sangkat: form.details.sangkat || null,
        address: form.details.address || null,
      },
    })

    for (const imgId of imagesToRemove.value) {
      try { await deleteProductImage(product.value.id, imgId) } catch {}
    }

    success.value = 'Product updated!'
    setTimeout(() => {
      router.push(`/products/${product.value.id}`)
    }, 1200)
  } catch (err) {
    error.value = err.message
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  try {
    const data = await getProduct(route.params.id)
    product.value = data.data || null
    if (!product.value) return

    form.name = product.value.name
    form.description = product.value.description || ''
    form.price = product.value.price
    form.details.brand_id = product.value.detail?.brand_id || null
    form.details.model_id = product.value.detail?.model_id || null
    form.details.brand_name = product.value.detail?.brand?.name || ''
    form.details.model_name = product.value.detail?.model?.name || ''
    form.details.condition = product.value.detail?.condition || 'new'
    form.details.province = product.value.detail?.province || ''
    form.details.khan = product.value.detail?.khan || ''
    form.details.sangkat = product.value.detail?.sangkat || ''
    form.details.address = product.value.detail?.address || ''
    existingImages.value = [...(product.value.images || [])]
    if (product.value.phones?.length) {
      form.phones = product.value.phones.map(p => p.phone)
    }

    if (showBrand.value) {
      try {
        const brands = await getBrands(product.value.subcategory_id)
        brandOptions.value = (brands.data || []).map(b => ({ id: b.id, name: b.name }))
        if (form.details.brand_id) await loadModels(form.details.brand_id)
      } catch {}
    }
  } catch {
    product.value = null
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.product-edit {
  padding: 2rem 1.5rem 3rem;
  max-width: 720px;
}

.page-title {
  font-family: var(--font-serif);
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0 0 1.5rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.field.full {
  grid-column: 1 / -1;
}

.field label {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--text-muted);
}

.input {
  padding: 0.6rem 0.75rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface);
  color: var(--text);
  font-size: 0.9rem;
  transition: border-color 0.15s;
}

.input:focus {
  outline: none;
  border-color: var(--accent);
}

.textarea {
  resize: vertical;
  min-height: 80px;
}

.field-error {
  color: #dc3545;
  font-size: 0.78rem;
  margin: 0;
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: var(--radius-sm);
  font-size: 0.88rem;
  margin-bottom: 1rem;
}

.alert-error {
  background: #fff5f5;
  color: #dc3545;
  border: 1px solid #fecaca;
}

.alert-success {
  background: #f0fdf4;
  color: #16a34a;
  border: 1px solid #bbf7d0;
}

.condition-toggle {
  display: flex;
  gap: 0.5rem;
}

.condition-btn {
  flex: 1;
  padding: 0.5rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface);
  color: var(--text-muted);
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 500;
  transition: all 0.15s;
}

.condition-btn.active {
  border-color: var(--accent);
  background: var(--accent-soft);
  color: var(--accent);
}

.images-section {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border);
}

.section-title {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 1rem;
}

.existing-images, .new-images {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}

.existing-img {
  position: relative;
  width: 100px;
  height: 100px;
  border-radius: var(--radius-sm);
  overflow: hidden;
  border: 1px solid var(--border);
}

.existing-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.remove-img {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.6);
  color: #fff;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-area {
  margin-bottom: 1rem;
}

.upload-label {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border: 1px dashed var(--border);
  border-radius: var(--radius-sm);
  cursor: pointer;
  color: var(--text-muted);
  font-size: 0.88rem;
  transition: all 0.15s;
}

.upload-label:hover {
  border-color: var(--accent);
  color: var(--accent);
}

.file-input {
  display: none;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.btn {
  padding: 0.6rem 1.25rem;
  border-radius: var(--radius-md);
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  border: 1px solid var(--border);
  transition: all 0.15s;
}

.btn-primary {
  background: var(--accent);
  color: #fff;
  border-color: var(--accent);
}

.btn-primary:hover {
  opacity: 0.9;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-ghost {
  background: transparent;
  color: var(--text);
}

.btn-ghost:hover {
  background: var(--surface-2);
}

.spinner-sm {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  vertical-align: middle;
  margin-right: 0.4rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading, .empty {
  text-align: center;
  padding: 3rem;
  color: var(--text-muted);
}

.phone-row {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.phone-row .input {
  flex: 1;
}

.phone-row .remove-btn {
  width: 36px;
  height: 36px;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface);
  color: var(--danger);
  cursor: pointer;
  font-size: 0.9rem;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  transition: background 0.15s;
}

.phone-row .remove-btn:hover {
  background: var(--danger-soft);
}

.add-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.6rem 1rem;
  border: 1px dashed var(--accent);
  border-radius: var(--radius-sm);
  background: var(--accent-soft);
  color: var(--accent);
  font: inherit;
  font-weight: 600;
  font-size: 0.88rem;
  cursor: pointer;
  transition: background 0.15s;
}

.add-btn:hover {
  background: color-mix(in srgb, var(--accent-soft) 80%, var(--accent) 20%);
}

@media (max-width: 560px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
