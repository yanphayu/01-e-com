<template>
  <div class="home">
    <AdCarousel placement="home" variant="hero" />

    <section class="section container">
      <!-- Category tabs -->
      <div v-if="categories.length" class="category-tabs">
        <button
          v-for="cat in categories"
          :key="cat.id"
          type="button"
          class="tab-btn"
          @click="goToCategory(cat.id)"
        >{{ cat.name }}</button>
      </div>

      <div class="list-toolbar">
        <BaseSelect
          v-model="sort"
          :label="t('product.sortBy')"
          :options="sortOptions"
          @change="onSortChange"
        />

        <div class="price-filter">
          <span class="price-label">{{ t('product.price') }}</span>
          <div class="price-fields">
            <input
              v-model="minPrice"
              type="number"
              min="0"
              inputmode="numeric"
              class="input price-input"
              :placeholder="t('product.minPrice')"
              :aria-label="t('product.minPrice')"
              @keyup.enter="applyPriceFilter"
            />
            <span class="price-sep">–</span>
            <input
              v-model="maxPrice"
              type="number"
              min="0"
              inputmode="numeric"
              class="input price-input"
              :placeholder="t('product.maxPrice')"
              :aria-label="t('product.maxPrice')"
              @keyup.enter="applyPriceFilter"
            />
            <button type="button" class="btn btn-primary price-apply" @click="applyPriceFilter">
              {{ t('product.applyFilter') }}
            </button>
            <button
              v-if="minPrice || maxPrice"
              type="button"
              class="btn btn-ghost price-clear"
              @click="clearPriceFilter"
            >
              {{ t('product.clearFilter') }}
            </button>
          </div>
        </div>
      </div>

      <!-- Products -->
      <div v-if="loadingProducts" class="loading">{{ t('product.loading') }}</div>
      <div v-else-if="products.length === 0" class="empty">{{ t('product.noProducts') }}</div>
      <div v-else class="grid grid-4">
        <ProductCard v-for="p in products" :key="p.id" :product="p" />
      </div>

      <div ref="sentinel" class="load-more">
        <div v-if="loadingMore" class="spinner"></div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { t } from '../i18n'
import { getCategories, getProducts } from '../services/products'
import { BaseSelect } from '../design-system'
import AdCarousel from '../components/AdCarousel.vue'
import ProductCard from '../components/ProductCard.vue'

const router = useRouter()

const categories = ref([])
const products = ref([])
const sort = ref('latest')
const minPrice = ref('')
const maxPrice = ref('')
const loadingProducts = ref(true)
const loadingMore = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)
const sentinel = ref(null)
let observer = null

const sortOptions = computed(() => [
  { value: 'latest', label: t('product.sortNewest') },
  { value: 'oldest', label: t('product.sortOldest') },
])

async function onHomeRefresh() {
  currentPage.value = 1
  totalPages.value = 1
  await loadProducts()
  if (observer) { observer.disconnect(); setupObserver() }
}

function goToCategory(id) {
  router.push(`/products?category_id=${id}`)
}

function onSortChange() {
  currentPage.value = 1
  totalPages.value = 1
  loadProducts()
}

function applyPriceFilter() {
  currentPage.value = 1
  totalPages.value = 1
  loadProducts()
}

function clearPriceFilter() {
  minPrice.value = ''
  maxPrice.value = ''
  applyPriceFilter()
}

async function loadProducts(page = 1, append = false) {
  if (append) loadingMore.value = true
  else loadingProducts.value = true
  try {
    const params = { page, sort: sort.value }
    if (minPrice.value) params.min_price = minPrice.value
    if (maxPrice.value) params.max_price = maxPrice.value
    const data = await getProducts(params)
    const items = data.data?.data || []
    products.value = append ? [...products.value, ...items] : items
    currentPage.value = data.data?.current_page || 1
    totalPages.value = data.data?.last_page || 1
  } catch {
    if (!append) products.value = []
  } finally {
    loadingProducts.value = false
    loadingMore.value = false
  }
}

async function loadMore() {
  if (loadingProducts.value || loadingMore.value || currentPage.value >= totalPages.value) return
  await loadProducts(currentPage.value + 1, true)
}

function setupObserver() {
  if (!sentinel.value || !('IntersectionObserver' in window)) return
  observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) loadMore()
  }, { rootMargin: '300px' })
  observer.observe(sentinel.value)
}

onMounted(async () => {
  window.addEventListener('home-refresh', onHomeRefresh)
  try {
    const catData = await getCategories()
    categories.value = catData.data || []
  } catch {
    categories.value = []
  }

  await loadProducts()
  setupObserver()
})

onBeforeUnmount(() => {
  window.removeEventListener('home-refresh', onHomeRefresh)
  if (observer) observer.disconnect()
})
</script>

<style scoped>
.home {
  min-height: 100%;
}

.section {
  padding: 2rem 1.5rem 3rem;
}

.category-tabs {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  padding-bottom: 1rem;
  margin-bottom: 1.5rem;
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
  white-space: nowrap;
}

.tab-btn:hover {
  border-color: var(--accent);
  color: var(--accent);
  background: var(--accent-soft);
}

.list-toolbar {
  display: flex;
  align-items: flex-end;
  justify-content: flex-start;
  flex-wrap: wrap;
  gap: 0.75rem 1.25rem;
  margin-bottom: 1rem;
}

.price-filter {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.price-label {
  color: var(--text-muted);
  font-size: 0.8rem;
  font-weight: 600;
}

.price-fields {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.price-input {
  width: 7rem;
  height: 42px;
  min-height: 42px;
  padding: 0 0.65rem;
  font-size: 0.9rem;
}

.price-sep {
  color: var(--text-muted);
  font-size: 0.9rem;
}

.price-apply,
.price-clear {
  height: 42px;
  min-height: 42px;
  font-size: 0.9rem;
  white-space: nowrap;
}

.grid {
  display: grid;
  gap: 1.25rem;
}

.grid-4 {
  grid-template-columns: repeat(4, 1fr);
}

@media (max-width: 1024px) {
  .grid-4 {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .grid-4 {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .grid-4 {
    grid-template-columns: repeat(2, 1fr);
  }
  .section {
    padding: 1.5rem 1rem 2rem;
  }
}

.loading, .empty {
  text-align: center;
  padding: 2rem;
  color: var(--text-muted);
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
</style>
