<template>
  <div class="favorites-page container">
    <h1 class="page-title">{{ t('nav.favorites') }}</h1>

    <div v-if="loading" class="loading">{{ t('product.loading') }}</div>

    <template v-else>
      <div v-if="products.length === 0" class="empty">
        {{ t('product.noFavorites') }}
      </div>

      <div v-else class="products-grid">
        <ProductCard v-for="product in products" :key="product.id" :product="product" />
      </div>

      <div v-if="lastPage > 1" class="pagination">
        <button
          class="page-btn"
          :disabled="currentPage <= 1"
          @click="goPage(currentPage - 1)"
        >←</button>
        <span class="page-info">{{ currentPage }} / {{ lastPage }}</span>
        <button
          class="page-btn"
          :disabled="currentPage >= lastPage"
          @click="goPage(currentPage + 1)"
        >→</button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { t } from '../i18n'
import { getFavorites } from '../services/favorites'
import ProductCard from '../components/ProductCard.vue'

const route = useRoute()
const router = useRouter()

const products = ref([])
const loading = ref(true)
const currentPage = ref(1)
const lastPage = ref(1)

async function loadFavorites(page = 1) {
  loading.value = true
  try {
    const res = await getFavorites({ page })
    const paginated = res.data
    products.value = paginated.data || []
    currentPage.value = paginated.current_page
    lastPage.value = paginated.last_page
  } catch {
    products.value = []
  } finally {
    loading.value = false
  }
}

function goPage(page) {
  router.push({ query: { page } })
  loadFavorites(page)
}

onMounted(() => {
  const page = parseInt(route.query.page) || 1
  loadFavorites(page)
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

.products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.25rem;
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-top: 2rem;
}

.page-btn {
  padding: 0.4rem 0.8rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface);
  cursor: pointer;
  font-size: 0.85rem;
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.88rem;
  color: var(--text-muted);
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
