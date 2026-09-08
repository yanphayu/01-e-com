<template>
  <div class="products-page container">
    <button class="back-link" @click="router.back()">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 12H5"/>
        <polyline points="12 19 5 12 12 5"/>
      </svg>
      {{ t('product.back') }}
    </button>

    <!-- Category tabs -->
    <div v-if="categories.length && !filters.category_id" class="category-tabs">
      <button
        type="button"
        class="tab-btn active"
      >{{ t('product.allCategories') }}</button>
      <button
        v-for="cat in categories"
        :key="cat.id"
        type="button"
        class="tab-btn"
        @click="onCategoryTabClick(cat.id)"
      >{{ cat.name }}</button>
    </div>

    <!-- Subcategory tabs -->
    <div v-if="filteredSubcategories.length" class="subcategory-tabs">
      <button
        type="button"
        class="sub-tab"
        :class="{ active: !filters.subcategory_id }"
        @click="onSubcategoryTabClick(null)"
      >{{ t('product.allCategories') }}</button>
      <button
        v-for="sub in filteredSubcategories"
        :key="sub.id"
        type="button"
        class="sub-tab"
        :class="{ active: filters.subcategory_id == sub.id }"
        @click="onSubcategoryTabClick(sub.id)"
      >{{ sub.name }}</button>
    </div>

    <!-- Products grid -->
    <div v-if="products.length === 0 && !loading" class="empty">{{ t('product.noProducts') }}</div>
    <div class="products-grid">
      <ProductCard v-for="product in products" :key="product.id" :product="product" />
    </div>

    <!-- Infinite scroll sentinel -->
    <div ref="sentinel" class="load-more">
      <div v-if="loadingMore" class="spinner"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { t } from '../i18n'
import { getCategories, getProducts } from '../services/products'
import ProductCard from '../components/ProductCard.vue'

const route = useRoute()
const router = useRouter()

const categories = ref([])
const products = ref([])
const loading = ref(true)
const loadingMore = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)
const sentinel = ref(null)
let observer = null

const filters = reactive({
  category_id: route.query.category_id || '',
  subcategory_id: route.query.subcategory_id || '',
  q: route.query.q || '',
})

const filteredSubcategories = computed(() => {
  if (!filters.category_id) return []
  const cat = categories.value.find(c => c.id == filters.category_id)
  return cat?.subcategories || []
})

onMounted(async () => {
  try {
    const data = await getCategories()
    categories.value = data.data || []
  } catch {
    categories.value = []
  }
  loadProducts()
})

watch(() => route.query, (q) => {
  filters.category_id = q.category_id || ''
  filters.subcategory_id = q.subcategory_id || ''
  filters.q = q.q || ''
  loadProducts()
})

function onCategoryTabClick(id) {
  filters.category_id = id || ''
  filters.subcategory_id = ''
  updateUrl()
  loadProducts()
}

function onSubcategoryTabClick(id) {
  filters.subcategory_id = id || ''
  updateUrl()
  loadProducts()
}

function updateUrl() {
  const query = {}
  if (filters.category_id) query.category_id = filters.category_id
  if (filters.subcategory_id) query.subcategory_id = filters.subcategory_id
  if (filters.q) query.q = filters.q
  router.replace({ query })
}

async function loadProducts(page = 1, append = false) {
  if (append) loadingMore.value = true
  else loading.value = true
  try {
    const params = { page }
    if (filters.category_id) params.category_id = filters.category_id
    if (filters.subcategory_id) params.subcategory_id = filters.subcategory_id
    if (filters.q) params.q = filters.q

    const data = await getProducts(params)
    const items = data.data?.data || []
    products.value = append ? [...products.value, ...items] : items
    currentPage.value = data.data?.current_page || 1
    totalPages.value = data.data?.last_page || 1
  } catch {
    if (!append) products.value = []
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

async function loadMore() {
  if (loading.value || loadingMore.value || currentPage.value >= totalPages.value) return
  await loadProducts(currentPage.value + 1, true)
}

function setupObserver() {
  if (!sentinel.value || !('IntersectionObserver' in window)) return
  observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) loadMore()
  }, { rootMargin: '300px' })
  observer.observe(sentinel.value)
}

onMounted(() => {
  setupObserver()
})

onBeforeUnmount(() => {
  if (observer) observer.disconnect()
})
</script>

<style scoped>
.products-page {
  padding: 1.5rem 1.5rem 3rem;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  background: none;
  border: none;
  color: var(--text-muted);
  font-size: 0.88rem;
  font-weight: 500;
  cursor: pointer;
  padding: 0;
  margin-bottom: 1rem;
  transition: color 0.15s;
}

.back-link:hover {
  color: var(--accent);
}

.category-tabs {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  padding-bottom: 0.75rem;
  scrollbar-width: none;
}

.category-tabs::-webkit-scrollbar {
  display: none;
}

.tab-btn {
  flex-shrink: 0;
  padding: 0.5rem 1.1rem;
  border-radius: 999px;
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text-muted);
  font-size: 0.88rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;
}

.tab-btn:hover {
  border-color: var(--accent);
  color: var(--accent);
  background: var(--accent-soft);
}

.tab-btn.active {
  background: var(--accent);
  color: #fff;
  border-color: var(--accent);
}

.subcategory-tabs {
  display: flex;
  gap: 0.4rem;
  overflow-x: auto;
  padding-bottom: 1rem;
  margin-top: 0.5rem;
  scrollbar-width: none;
}

.subcategory-tabs::-webkit-scrollbar {
  display: none;
}

.sub-tab {
  flex-shrink: 0;
  padding: 0.5rem 1.1rem;
  border-radius: 999px;
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text-muted);
  font-size: 0.88rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;
}

.sub-tab:hover {
  border-color: var(--accent);
  color: var(--accent);
  background: var(--accent-soft);
}

.sub-tab.active {
  background: var(--surface-2);
  color: var(--accent);
  border-color: var(--accent);
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.25rem;
  margin-top: 1.5rem;
}

.load-more {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 0 0.5rem;
}

.spinner {
  width: 30px;
  height: 30px;
  border: 3px solid var(--border);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading, .empty {
  text-align: center;
  padding: 3rem;
  color: var(--text-muted);
  font-size: 1rem;
  margin-top: 1.5rem;
}
</style>
