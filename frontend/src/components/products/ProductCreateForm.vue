<template>
  <div class="product-create">
    <button type="button" class="back-btn" @click="$router.back()">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 12H5"/>
        <polyline points="12 19 5 12 12 5"/>
      </svg>
      {{ t('product.back') }}
    </button>
    <h1 class="page-title">{{ t('product.createTitle') }}</h1>

    <!-- Steps indicator -->
    <div class="steps">
      <div
        v-for="(step, i) in steps"
        :key="step.key"
        class="step"
        :class="{ active: currentStep === i, done: currentStep > i }"
      >
        <span class="step-num">{{ currentStep > i ? '✓' : i + 1 }}</span>
        <span class="step-label">{{ step.label }}</span>
      </div>
    </div>

    <!-- Alerts -->
    <p v-if="error" class="alert alert-error">{{ error }}</p>
    <p v-if="success" class="alert alert-success">{{ success }}</p>

    <!-- Step 1: Category -->
    <div v-if="currentStep === 0" class="step-content">
      <h2 class="step-title">{{ t('product.selectCategory') }}</h2>
      <div v-if="loadingCategories" class="loading">{{ t('product.loading') }}</div>
      <div v-else-if="categories.length === 0" class="empty">{{ t('product.noCategories') }}</div>
      <div v-else class="category-grid">
        <button
          v-for="cat in categories"
          :key="cat.id"
          type="button"
          class="category-card"
          :class="{ selected: selectedCategory?.id === cat.id }"
          @click="selectCategory(cat)"
        >
          <span class="category-name">{{ cat.name }}</span>
          <span class="category-count">{{ cat.subcategories?.length || 0 }} {{ t('home.items') }}</span>
        </button>
      </div>
    </div>

    <!-- Step 2: Subcategory -->
    <div v-if="currentStep === 1" class="step-content">
      <h2 class="step-title">{{ t('product.selectSubcategory') }}</h2>
      <div v-if="!selectedCategory" class="empty">{{ t('product.selectAtLeast') }}</div>
      <div v-else-if="selectedCategory.subcategories?.length === 0" class="empty">{{ t('product.noSubcategories') }}</div>
      <div v-else class="category-grid">
        <button
          v-for="sub in selectedCategory.subcategories"
          :key="sub.id"
          type="button"
          class="category-card"
          :class="{ selected: form.subcategory_id === sub.id }"
          @click="form.subcategory_id = sub.id"
        >
          <span class="category-name">{{ sub.name }}</span>
        </button>
      </div>
    </div>

    <!-- Step 3: Product Details -->
    <div v-if="currentStep === 2" class="step-content">
      <h2 class="step-title">{{ t('product.stepDetails') }}</h2>
      <div class="form-grid">
        <div v-if="showBrand" class="field">
          <label>{{ t('product.brand') }}</label>
          <input v-model="form.details.brand_name" class="input" :placeholder="t('product.brandPlaceholder')" list="brand-list" @input="onBrandInput" />
          <datalist id="brand-list">
            <option v-for="b in brandOptions" :key="b.id" :value="b.name" />
          </datalist>
        </div>
        <div v-if="showModel" class="field">
          <label>{{ t('product.model') }}</label>
          <input v-model="form.details.model_name" class="input" :placeholder="t('product.modelPlaceholder')" list="model-list" @input="onModelInput" />
          <datalist id="model-list">
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
            <button
              type="button"
              class="condition-btn"
              :class="{ active: form.details.condition === 'new' }"
              @click="form.details.condition = 'new'"
            >{{ t('product.conditionNew') }}</button>
            <button
              type="button"
              class="condition-btn"
              :class="{ active: form.details.condition === 'used' }"
              @click="form.details.condition = 'used'"
            >{{ t('product.conditionUsed') }}</button>
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
          <button type="button" class="loc-btn" :disabled="locating" @click="getLocation">
            <svg v-if="!locating" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            <span v-if="locating" class="spinner" />
            {{ locating ? t('product.gettingLocation') : t('product.getLocation') }}
          </button>
          <p v-if="locError" class="field-error">{{ locError }}</p>
        </div>
        <div class="field full">
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
    </div>

    <!-- Step 4: Specifications (Attributes) -->
    <div v-if="currentStep === 3" class="step-content">
      <h2 class="step-title">{{ t('product.stepSpecs') }}</h2>
      <div v-if="modelAttributes.length === 0" class="empty">{{ t('product.noAttributes') }}</div>
      <div v-else>
        <div v-for="(attr, i) in form.attributes" :key="attr.attribute_id" class="attr-row">
          <div class="field">
            <label>{{ attr.name }}</label>
          </div>
          <div class="field">
            <input v-model="attr.value" class="input" :placeholder="t('product.valuePlaceholder')" />
          </div>
        </div>
      </div>
    </div>

    <!-- Step 5: Images -->
    <div v-if="currentStep === 4" class="step-content">
      <h2 class="step-title">{{ t('product.uploadImages') }}</h2>
      <div
        class="upload-zone"
        :class="{ dragging }"
        @dragover.prevent="dragging = true"
        @dragleave="dragging = false"
        @drop.prevent="onDrop"
        @click="$refs.fileInput.click()"
      >
        <input
          ref="fileInput"
          type="file"
          accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
          multiple
          style="display: none"
          @change="onFileSelect"
        />
        <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="17 8 12 3 7 8"/>
          <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
        <p>{{ t('product.dragOrClick') }}</p>
        <p class="upload-hint">{{ t('product.maxImages') }}</p>
      </div>
      <div v-if="imagePreviews.length" class="image-grid">
        <div v-for="(img, i) in imagePreviews" :key="i" class="image-thumb">
          <img :src="img.url" alt="" />
          <button type="button" class="img-remove" @click="removeImage(i)">✕</button>
          <button
            v-if="i !== primaryIndex"
            type="button"
            class="img-primary"
            @click="primaryIndex = i"
          >{{ t('product.setPrimary') }}</button>
          <span v-else class="img-primary-badge">{{ t('product.primary') }}</span>
        </div>
      </div>
    </div>

    <!-- Step 6: Review -->
    <div v-if="currentStep === 5" class="step-content">
      <h2 class="step-title">{{ t('product.stepReview') }}</h2>
      <div class="review-card">
        <div class="review-row">
          <span class="review-label">{{ t('product.category') }}</span>
          <span>{{ selectedCategory?.name || '-' }}</span>
        </div>
        <div class="review-row">
          <span class="review-label">{{ t('product.subcategory') }}</span>
          <span>{{ selectedSubcategory?.name || '-' }}</span>
        </div>
        <div class="review-row">
          <span class="review-label">{{ t('product.productName') }}</span>
          <span>{{ form.name || '-' }}</span>
        </div>
        <div class="review-row">
          <span class="review-label">{{ t('product.description') }}</span>
          <span>{{ form.description || '-' }}</span>
        </div>
        <div class="review-row">
          <span class="review-label">{{ t('product.price') }}</span>
          <span>${{ Number(form.price || 0).toFixed(2) }}</span>
        </div>
        <div class="review-row">
          <span class="review-label">{{ t('product.condition') }}</span>
          <span>{{ form.details.condition === 'new' ? t('product.conditionNew') : t('product.conditionUsed') }}</span>
        </div>
        <div v-if="form.details.brand_name" class="review-row">
          <span class="review-label">{{ t('product.brand') }}</span>
          <span>{{ form.details.brand_name }}</span>
        </div>
        <div v-if="form.details.model_name" class="review-row">
          <span class="review-label">{{ t('product.model') }}</span>
          <span>{{ form.details.model_name }}</span>
        </div>
        <div v-if="form.details.province" class="review-row">
          <span class="review-label">{{ t('product.province') }}</span>
          <span>{{ form.details.province }}</span>
        </div>
        <div v-if="form.details.khan" class="review-row">
          <span class="review-label">{{ t('product.khan') }}</span>
          <span>{{ form.details.khan }}</span>
        </div>
        <div v-if="form.details.sangkat" class="review-row">
          <span class="review-label">{{ t('product.sangkat') }}</span>
          <span>{{ form.details.sangkat }}</span>
        </div>
        <div v-if="form.details.address" class="review-row">
          <span class="review-label">{{ t('product.address') }}</span>
          <span>{{ form.details.address }}</span>
        </div>
        <div v-if="activeAttributes.length" class="review-section">
          <span class="review-label">{{ t('product.specifications') }}</span>
          <div v-for="attr in activeAttributes" :key="attr.attribute_id" class="review-attr">
            {{ attr.name }}: {{ attr.value }}
          </div>
        </div>
        <div v-if="imagePreviews.length" class="review-section">
          <span class="review-label">{{ t('product.images') }}</span>
          <span>{{ imagePreviews.length }} {{ t('home.items') }}</span>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <div class="step-nav">
      <button
        v-if="currentStep > 0"
        type="button"
        class="btn btn-ghost"
        @click="prevStep"
      >{{ t('product.back') }}</button>
      <div v-else></div>
      <button
        v-if="currentStep < steps.length - 1"
        type="button"
        class="btn btn-primary"
        :disabled="!canProceed"
        @click="nextStep"
      >{{ t('product.next') }}</button>
      <button
        v-else
        type="button"
        class="btn btn-primary"
        :loading="submitting"
        :disabled="submitting"
        @click="submitProduct"
      >{{ submitting ? t('product.posting') : t('product.postProduct') }}</button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { t, getLocale } from '../../i18n'
import { getCategories, getAttributes, getBrands, getModels, createProduct } from '../../services/products'

const router = useRouter()

function addPhone() {
  form.phones.push('')
}

const steps = computed(() => [
  { key: 'category', label: t('product.stepCategory') },
  { key: 'subcategory', label: t('product.stepSubcategory') },
  { key: 'details', label: t('product.stepDetails') },
  { key: 'specs', label: t('product.stepSpecs') },
  { key: 'images', label: t('product.stepImages') },
  { key: 'review', label: t('product.stepReview') },
])

const currentStep = ref(0)
const categories = ref([])
const modelAttributes = ref([])
const brands = ref([])
const models = ref([])
const loadingCategories = ref(true)
const selectedCategory = ref(null)
const dragging = ref(false)
const imageFiles = ref([])
const imagePreviews = ref([])
const primaryIndex = ref(0)
const submitting = ref(false)
const error = ref(null)
const success = ref(null)
const locating = ref(false)
const locError = ref(null)
const errors = reactive({ name: null, price: null })

const form = reactive({
  subcategory_id: null,
  name: '',
  description: '',
  price: '',
  phones: [''],
  details: {
    condition: 'new',
    brand_id: null,
    model_id: null,
    brand_name: '',
    model_name: '',
    address: '',
    province: '',
    khan: '',
    sangkat: '',
    latitude: null,
    longitude: null,
  },
  attributes: [],
})

const selectedSubcategory = computed(() => {
  if (!selectedCategory.value || !form.subcategory_id) return null
  return selectedCategory.value.subcategories?.find(s => s.id === form.subcategory_id)
})

const showBrand = computed(() => selectedSubcategory.value?.has_brand ?? false)
const showModel = computed(() => selectedSubcategory.value?.has_model ?? false)

const brandOptions = computed(() => brands.value.map(b => ({ id: b.id, name: b.name })))

let previousAutoName = ''

function onBrandInput() {
  const brand = brands.value.find(b => b.name === form.details.brand_name)
  if (brand) {
    form.details.brand_id = brand.id
    loadModels(brand.id)
  } else {
    form.details.brand_id = null
    form.details.model_id = null
    models.value = []
  }
  form.details.model_name = ''
  form.details.model_id = null
  modelAttributes.value = []
  form.attributes = []
  autoFillName()
}

function onBrandSelect(brandName) {
  const brand = brands.value.find(b => b.name === brandName)
  if (brand) {
    form.details.brand_id = brand.id
    loadModels(brand.id)
  } else {
    form.details.brand_id = null
    form.details.model_id = null
    models.value = []
  }
}

async function loadModels(brandId) {
  try {
    const data = await getModels(brandId)
    models.value = data.data || []
  } catch {
    models.value = []
  }
}

const modelOptions = computed(() => models.value.map(m => ({ id: m.id, name: m.name })))

function findAndSetModel() {
  const model = models.value.find(m => m.name === form.details.model_name)
  form.details.model_id = model ? model.id : null
  autoFillName()

  if (model && model.attributes) {
    modelAttributes.value = model.attributes
    form.attributes = model.attributes.map(a => ({
      attribute_id: a.id,
      name: a.name,
      value: '',
    }))
  } else {
    modelAttributes.value = []
    form.attributes = []
  }
}

function onModelInput() {
  findAndSetModel()
}

watch(models, () => {
  if (form.details.model_name) {
    findAndSetModel()
  }
})

function autoFillName() {
  const brand = form.details.brand_name || ''
  const model = form.details.model_name || ''
  const autoName = `${brand} ${model}`.trim()

  if (!form.name || form.name === previousAutoName) {
    form.name = autoName
    previousAutoName = autoName
  }
}

const activeAttributes = computed(() =>
  form.attributes.filter(a => a.attribute_id && a.value)
)

const canProceed = computed(() => {
  if (currentStep.value === 0) return selectedCategory.value !== null
  if (currentStep.value === 1) return form.subcategory_id !== null
  if (currentStep.value === 2) return form.name.trim() && form.price
  return true
})

onMounted(async () => {
  try {
    const data = await getCategories()
    categories.value = data.data || []
  } catch {
    error.value = 'Failed to load categories'
  } finally {
    loadingCategories.value = false
  }

  if (form.attributes.length === 0) {
    // will be populated when model is selected
  }
})

watch(() => form.subcategory_id, async (subId) => {
  form.details.brand_id = null
  form.details.model_id = null
  form.details.brand_name = ''
  form.details.model_name = ''
  models.value = []
  modelAttributes.value = []
  form.attributes = []

  if (!subId) {
    brands.value = []
    return
  }

  try {
    const data = await getBrands(subId)
    brands.value = data.data || []
  } catch {
    brands.value = []
  }
})

function selectCategory(cat) {
  selectedCategory.value = cat
  form.subcategory_id = null
}

async function getLocation() {
  if (!navigator.geolocation) {
    locError.value = t('auth.locationUnsupported')
    return
  }

  locating.value = true
  locError.value = null

  navigator.geolocation.getCurrentPosition(
    async (position) => {
      const { latitude: lat, longitude: lng } = position.coords
      form.details.latitude = lat
      form.details.longitude = lng
      try {
        const res = await fetch(
          `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=${getLocale()}`
        )
        const data = await res.json()
        const addr = data.address || {}

        form.details.province = addr.state || addr.region || ''

        const rawKhan = addr.town || addr.city || addr.county || addr.suburb || ''
        form.details.khan = rawKhan.replace(/^Khan\s+/i, '').trim()

        const rawSangkat = addr.village || addr.suburb || addr.neighbourhood || ''
        form.details.sangkat = rawSangkat.replace(/^Sangkat\s+/i, '').trim()

        form.details.address = [addr.road, addr.house_number].filter(Boolean).join(' ') || data.display_name || ''
      } catch {
        form.details.address = `${lat}, ${lng}`
      } finally {
        locating.value = false
      }
    },
    () => {
      locError.value = t('auth.locationDenied')
      locating.value = false
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  )
}

function nextStep() {
  if (currentStep.value === 2) {
    errors.name = form.name.trim() ? null : t('product.nameRequired')
    errors.price = form.price ? null : t('product.priceRequired')
    if (errors.name || errors.price) return
  }
  if (currentStep.value < steps.value.length - 1) {
    currentStep.value++
  }
}

function prevStep() {
  if (currentStep.value > 0) {
    currentStep.value--
  }
}

function onFileSelect(e) {
  const files = Array.from(e.target.files || [])
  addImages(files)
  e.target.value = ''
}

function onDrop(e) {
  dragging.value = false
  const files = Array.from(e.dataTransfer.files || [])
  addImages(files)
}

function addImages(files) {
  const imageTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']
  for (const file of files) {
    if (!imageTypes.includes(file.type)) continue
    if (imageFiles.value.length >= 10) break
    imageFiles.value.push(file)
    imagePreviews.value.push({ url: URL.createObjectURL(file) })
  }
}

function removeImage(index) {
  URL.revokeObjectURL(imagePreviews.value[index].url)
  imageFiles.value.splice(index, 1)
  imagePreviews.value.splice(index, 1)
  if (primaryIndex.value >= imagePreviews.value.length) {
    primaryIndex.value = Math.max(0, imagePreviews.value.length - 1)
  }
}

async function submitProduct() {
  submitting.value = true
  error.value = null
  success.value = null

  try {
    const fd = new FormData()
    fd.append('subcategory_id', form.subcategory_id)
    fd.append('name', form.name.trim())
    fd.append('description', form.description.trim())
    fd.append('price', form.price)

    if (form.details.condition) fd.append('details[condition]', form.details.condition)
    if (form.details.brand_id) fd.append('details[brand_id]', form.details.brand_id)
    if (form.details.model_id) fd.append('details[model_id]', form.details.model_id)
    if (form.details.address) fd.append('details[address]', form.details.address)
    if (form.details.province) fd.append('details[province]', form.details.province)
    if (form.details.khan) fd.append('details[khan]', form.details.khan)
    if (form.details.sangkat) fd.append('details[sangkat]', form.details.sangkat)
    if (form.details.latitude) fd.append('details[latitude]', form.details.latitude)
    if (form.details.longitude) fd.append('details[longitude]', form.details.longitude)

    form.phones.filter(p => p.trim()).forEach((phone, i) => {
      fd.append(`phones[${i}]`, phone.trim())
    })

    activeAttributes.value.forEach((attr, i) => {
      fd.append(`attributes[${i}][attribute_id]`, attr.attribute_id)
      fd.append(`attributes[${i}][value]`, attr.value)
    })

    imageFiles.value.forEach((file) => {
      fd.append('images[]', file)
    })

    const result = await createProduct(fd)
    success.value = t('product.postedSuccess')

    setTimeout(() => {
      router.push('/')
    }, 1500)
  } catch (err) {
    error.value = err.message
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.product-create {
  max-width: 720px;
  margin: 0 auto;
  padding: 2rem 1.5rem 3rem;
}

.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  background: none;
  border: none;
  color: var(--text-muted);
  font: inherit;
  font-size: 0.88rem;
  font-weight: 500;
  cursor: pointer;
  padding: 0;
  margin-bottom: 0.5rem;
  transition: color 0.15s;
}

.back-btn:hover {
  color: var(--accent);
}

.page-title {
  font-family: var(--font-serif);
  font-size: 1.6rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

/* Steps */
.steps {
  display: flex;
  gap: 0.25rem;
  margin-bottom: 1.5rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
}

.step {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 0.85rem;
  border-radius: var(--radius-sm);
  background: var(--surface-2);
  color: var(--text-muted);
  font-size: 0.82rem;
  font-weight: 600;
  white-space: nowrap;
  transition: background 0.15s, color 0.15s;
}

.step.active {
  background: var(--accent-soft);
  color: var(--accent);
}

.step.done {
  background: var(--success-soft);
  color: var(--success);
}

.step-num {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-size: 0.75rem;
  background: var(--border);
  color: var(--text-muted);
}

.step.active .step-num {
  background: var(--accent);
  color: #fff;
}

.step.done .step-num {
  background: var(--success);
  color: #fff;
}

/* Step content */
.step-content {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  padding: 1.5rem;
  margin-bottom: 1rem;
}

.step-title {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

/* Category grid */
.category-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 0.75rem;
}

.category-card {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.3rem;
  padding: 1rem;
  background: var(--surface-2);
  border: 2px solid var(--border);
  border-radius: var(--radius-md);
  cursor: pointer;
  font: inherit;
  text-align: left;
  transition: border-color 0.15s, background 0.15s;
}

.category-card:hover {
  border-color: var(--border-strong);
  background: var(--surface);
}

.category-card:focus {
  outline: none;
}

.category-card.selected {
  border-color: var(--accent);
  background: rgba(47, 158, 107, 0.15);
  box-shadow: inset 0 0 0 1px var(--accent);
}

.category-name {
  font-weight: 600;
  font-size: 0.95rem;
}

.category-count {
  font-size: 0.8rem;
  color: var(--text-muted);
}

/* Form grid */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-grid .full {
  grid-column: 1 / -1;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.field label {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--text-muted);
}

.textarea {
  resize: vertical;
  min-height: 80px;
}

select.input {
  cursor: pointer;
}

/* Attributes */
.attr-row {
  display: grid;
  grid-template-columns: 1fr 1fr auto;
  gap: 0.75rem;
  align-items: end;
  margin-bottom: 0.75rem;
}

.remove-btn {
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
  transition: background 0.15s;
}

.remove-btn:hover {
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

/* Upload zone */
.upload-zone {
  border: 2px dashed var(--border);
  border-radius: var(--radius-md);
  padding: 2.5rem 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.15s, background 0.15s;
  color: var(--text-muted);
}

.upload-zone:hover,
.upload-zone.dragging {
  border-color: var(--accent);
  background: var(--accent-soft);
}

.upload-zone p {
  margin: 0.5rem 0 0;
  font-size: 0.9rem;
}

.upload-hint {
  font-size: 0.8rem !important;
  opacity: 0.7;
}

/* Image grid */
.image-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 0.75rem;
  margin-top: 1rem;
}

.image-thumb {
  position: relative;
  aspect-ratio: 1;
  border-radius: var(--radius-sm);
  overflow: hidden;
  border: 1px solid var(--border);
}

.image-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.img-remove {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: none;
  background: rgba(0, 0, 0, 0.6);
  color: #fff;
  font-size: 0.75rem;
  cursor: pointer;
  display: grid;
  place-items: center;
  transition: background 0.15s;
}

.img-remove:hover {
  background: var(--danger);
}

.img-primary {
  position: absolute;
  bottom: 4px;
  left: 4px;
  right: 4px;
  padding: 0.25rem;
  border-radius: var(--radius-sm);
  border: none;
  background: rgba(0, 0, 0, 0.6);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}

.img-primary:hover {
  background: var(--accent);
}

.img-primary-badge {
  position: absolute;
  bottom: 4px;
  left: 4px;
  padding: 0.25rem 0.5rem;
  border-radius: var(--radius-sm);
  background: var(--accent);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 600;
}

/* Review */
.review-card {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.review-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--border);
  gap: 1rem;
}

.review-label {
  font-weight: 600;
  font-size: 0.88rem;
  color: var(--text-muted);
  flex-shrink: 0;
}

.review-section {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--border);
}

.review-attr {
  font-size: 0.88rem;
  padding-left: 0.5rem;
}

/* Nav */
.step-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Helpers */
.loading, .empty {
  text-align: center;
  padding: 2rem;
  color: var(--text-muted);
}

.loc-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  padding: 0.6rem 0.85rem;
  border: 1px dashed var(--accent);
  border-radius: var(--radius-sm);
  background: var(--accent-soft);
  color: var(--accent);
  font: inherit;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: background 0.15s;
}

.loc-btn:hover {
  background: color-mix(in srgb, var(--accent-soft) 80%, var(--accent) 20%);
}

.loc-btn:disabled {
  cursor: not-allowed;
  opacity: 0.6;
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

.spinner {
  width: 14px;
  height: 14px;
  border: 2px solid currentColor;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 560px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  .attr-row {
    grid-template-columns: 1fr;
  }
  .category-grid {
    grid-template-columns: 1fr;
  }
}
</style>
