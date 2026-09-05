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
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { t } from '../i18n'
import { getCategories, getProducts } from '../services/products'
import ProductCard from '../components/ProductCard.vue'

const router = useRouter()

const categories = ref([])
const products = ref([])
const loadingProducts = ref(true)

function goToCategory(id) {
  router.push(`/products?category_id=${id}`)
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

.loading, .empty {
  text-align: center;
  padding: 2rem;
  color: var(--text-muted);
}
</style>
