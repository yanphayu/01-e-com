<template>
  <div class="home">
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
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { t } from '../i18n'
import { getCategories, getProducts } from '../services/products'
import ProductCard from '../components/ProductCard.vue'

const router = useRouter()

const categories = ref([])
const products = ref([])
const loadingProducts = ref(true)
const loadingMore = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)
const sentinel = ref(null)
let observer = null

function goToCategory(id) {
  router.push(`/products?category_id=${id}`)
}

async function loadProducts(page = 1, append = false) {
  if (append) loadingMore.value = true
  else loadingProducts.value = true
  try {
    const data = await getProducts({ page })
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
  transition: all 0.15s;
  white-space: nowrap;
}

.tab-btn:hover {
  border-color: var(--accent);
  color: var(--accent);
  background: var(--accent-soft);
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
    grid-template-columns: 1fr;
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
