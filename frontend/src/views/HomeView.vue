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
        <RouterLink
          v-for="p in products"
          :key="p.id"
          :to="`/products/${p.id}`"
          class="card product"
        >
          <div class="product-thumb" :style="{ background: 'var(--surface-2)' }">
            <img v-if="getPrimaryImage(p)" :src="getPrimaryImage(p)" :alt="p.name" class="product-thumb-img" />
          </div>
          <div class="product-body">
            <h3>{{ p.name }}</h3>
            <p class="product-date">{{ formatDate(p.created_at) }}</p>
            <p class="price">${{ Number(p.price).toFixed(2) }}</p>
          </div>
        </RouterLink>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { t } from '../i18n'
import { getCategories, getProducts } from '../services/products'

const router = useRouter()

const categories = ref([])
const products = ref([])
const loadingProducts = ref(true)

function getPrimaryImage(product) {
  const img = product.images?.find(i => i.is_primary) || product.images?.[0]
  return img ? `/storage/${img.image}` : null
}

function goToCategory(id) {
  router.push(`/products?category_id=${id}`)
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  const now = new Date()
  const diffMs = now - d
  const diffMin = Math.floor(diffMs / 60000)
  const diffHr = Math.floor(diffMs / 3600000)
  const diffDay = Math.floor(diffMs / 86400000)

  if (diffMin < 1) return 'Just now'
  if (diffMin < 60) return `${diffMin}m ago`
  if (diffHr < 24) return `${diffHr}h ago`
  if (diffDay < 7) return `${diffDay}d ago`
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

onMounted(async () => {
  try {
    const catData = await getCategories()
    categories.value = catData.data || []
  } catch {
    categories.value = []
  }

  try {
    const data = await getProducts({ page: 1 })
    products.value = data.data?.data || []
  } catch {
    products.value = []
  } finally {
    loadingProducts.value = false
  }
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

.card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  overflow: hidden;
}

.card:hover {
  border-color: var(--border-strong);
}

.product-thumb {
  height: 160px;
}

.product-thumb-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-body {
  padding: 0.9rem 1rem 1.05rem;
}

.product-body h3 {
  font-size: 0.95rem;
  font-weight: 600;
}

.price {
  margin-top: 0.2rem;
  color: var(--accent);
  font-weight: 600;
}

.product-date {
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: var(--text-muted);
}

.product {
  text-decoration: none;
  color: inherit;
}

@media (max-width: 900px) {
  .grid-4 {
    grid-template-columns: repeat(2, 1fr);
  }
}

.loading, .empty {
  text-align: center;
  padding: 2rem;
  color: var(--text-muted);
}
</style>
