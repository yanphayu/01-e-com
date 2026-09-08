<template>
  <div class="favorites-page container">
    <h1 class="page-title">{{ t('nav.favorites') }}</h1>

    <div v-if="products.length === 0 && !loading" class="empty">
      {{ t('product.noFavorites') }}
    </div>

    <div v-if="products.length" class="products-grid">
      <ProductCard v-for="product in products" :key="product.id" :product="product" />
    </div>

    <div ref="sentinel" class="load-more">
      <div v-if="loadingMore" class="spinner"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { t } from '../i18n'
import { getFavorites } from '../services/favorites'
import ProductCard from '../components/ProductCard.vue'

const products = ref([])
const loading = ref(true)
const loadingMore = ref(false)
const currentPage = ref(1)
const lastPage = ref(1)
const sentinel = ref(null)
let observer = null

async function loadFavorites(page = 1, append = false) {
  if (append) loadingMore.value = true
  else loading.value = true
  try {
    const res = await getFavorites({ page })
    const paginated = res.data
    const items = paginated.data || []
    products.value = append ? [...products.value, ...items] : items
    currentPage.value = paginated.current_page
    lastPage.value = paginated.last_page
  } catch {
    if (!append) products.value = []
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

async function loadMore() {
  if (loading.value || loadingMore.value || currentPage.value >= lastPage.value) return
  await loadFavorites(currentPage.value + 1, true)
}

function setupObserver() {
  if (!sentinel.value || !('IntersectionObserver' in window)) return
  observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) loadMore()
  }, { rootMargin: '300px' })
  observer.observe(sentinel.value)
}

onMounted(async () => {
  setupObserver()
  await loadFavorites(1)
})
</script>

<style scoped>
.favorites-page {
  padding: 2rem 1.5rem 3rem;
  max-width: 1200px;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
}

.loading, .empty {
  text-align: center;
  padding: 3rem 0;
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

.products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.25rem;
}

@media (max-width: 900px) {
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .products-grid {
    grid-template-columns: 1fr;
  }
}
</style>
