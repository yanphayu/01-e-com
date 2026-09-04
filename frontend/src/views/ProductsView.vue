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
    <div v-if="loading" class="loading">{{ t('product.loading') }}</div>
    <div v-else-if="products.length === 0" class="empty">{{ t('product.noProducts') }}</div>
    <div v-else class="products-grid">
      <ProductCard v-for="product in products" :key="product.id" :product="product" />
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="pagination">
      <button
        class="btn btn-ghost"
        :disabled="currentPage <= 1"
        @click="goToPage(currentPage - 1)"
      >{{ t('product.back') }}</button>
      <span class="page-info">{{ currentPage }} {{ t('product.of') }} {{ totalPages }}</span>
      <button
        class="btn btn-ghost"
        :disabled="currentPage >= totalPages"
        @click="goToPage(currentPage + 1)"
      >{{ t('product.next') }}</button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { t } from '../i18n'
import { getCategories, getProducts } from '../services/products'
import ProductCard from '../components/ProductCard.vue'

const route = useRoute()
const router = useRouter()

const categories = ref([])
const products = ref([])
const loading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)

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

async function loadProducts(page = 1) {
  loading.value = true
  try {
    const params = { page }
    if (filters.category_id) params.category_id = filters.category_id
    if (filters.subcategory_id) params.subcategory_id = filters.subcategory_id
    if (filters.q) params.q = filters.q

    const data = await getProducts(params)
    products.value = data.data?.data || []
    currentPage.value = data.data?.current_page || 1
    totalPages.value = data.data?.last_page || 1
  } catch {
    products.value = []
  } finally {
    loading.value = false
  }
}

function goToPage(page) {
  loadProducts(page)
}
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
  border-color: var(--border-strong);
  color: var(--text);
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

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-top: 2rem;
}

.page-info {
  font-size: 0.9rem;
  color: var(--text-muted);
}

.loading, .empty {
  text-align: center;
  padding: 3rem;
  color: var(--text-muted);
  font-size: 1rem;
  margin-top: 1.5rem;
}
</style>
