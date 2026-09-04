<template>
  <div class="product-show container">
    <nav v-if="product" class="breadcrumb-nav">
      <RouterLink to="/">{{ t('nav.home') }}</RouterLink>
      <span class="sep">|</span>
      <RouterLink v-if="product.subcategory?.category" :to="`/products?category_id=${product.subcategory.category.id}`">{{ product.subcategory.category.name }}</RouterLink>
      <span v-if="product.subcategory?.category" class="sep">|</span>
      <RouterLink v-if="product.subcategory" :to="`/products?category_id=${product.subcategory.category?.id}&subcategory_id=${product.subcategory.id}`">{{ product.subcategory.name }}</RouterLink>
      <span class="sep">|</span>
      <span>{{ product.name }}</span>
    </nav>
    <div v-if="loading" class="loading">{{ t('product.loading') }}</div>
    <div v-else-if="!product" class="empty">{{ t('product.noProducts') }}</div>
    <template v-else>
      <!-- Images -->
      <div class="product-images">
        <div v-if="images.length" class="main-image">
          <img :src="mainImage" :alt="product.name" />
          <span class="image-counter">{{ activeImageIndex + 1 }} / {{ images.length }}</span>
        </div>
        <div v-else class="main-image no-image">
          <svg viewBox="0 0 24 24" width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
            <circle cx="8.5" cy="8.5" r="1.5"/>
            <polyline points="21 15 16 10 5 21"/>
          </svg>
        </div>
        <div v-if="images.length > 1" class="thumb-strip">
          <button
            v-for="(img, i) in images"
            :key="img.id"
            type="button"
            class="thumb-btn"
            :class="{ active: i === activeImageIndex }"
            @click="activeImageIndex = i"
          >
            <img :src="`/storage/${img.image}`" alt="" />
          </button>
        </div>
      </div>

      <!-- Info -->
      <div class="product-info">
        <div class="product-meta">
          <span v-if="product.detail?.condition" class="badge">
            {{ product.detail.condition === 'new' ? t('product.conditionNew') : t('product.conditionUsed') }}
          </span>
          <span v-if="product.subcategory?.category" class="breadcrumb">
            {{ product.subcategory.category.name }} → {{ product.subcategory.name }}
          </span>
        </div>

        <h1 class="product-title">{{ product.name }}</h1>
        <p class="product-price">${{ Number(product.price).toFixed(2) }}</p>

        <div class="info-section">
          <div v-if="product.detail?.province" class="info-row">
            <span class="info-label">{{ t('product.province') }}</span>
            <span>{{ product.detail.province }}</span>
          </div>
          <div v-if="product.detail?.khan" class="info-row">
            <span class="info-label">{{ t('product.khan') }}</span>
            <span>{{ product.detail.khan }}</span>
          </div>
          <div v-if="product.detail?.sangkat" class="info-row">
            <span class="info-label">{{ t('product.sangkat') }}</span>
            <span>{{ product.detail.sangkat }}</span>
          </div>
          <div v-if="product.detail?.address" class="info-row">
            <span class="info-label">{{ t('product.address') }}</span>
            <span>{{ product.detail.address }}</span>
          </div>
        </div>

        <div v-if="product.product_attributes?.length" class="info-section">
          <h3>{{ t('product.specifications') }}</h3>
          <div v-for="attr in product.product_attributes" :key="attr.id" class="info-row">
            <span class="info-label">{{ attr.attribute?.name }}</span>
            <span>{{ attr.value }}</span>
          </div>
        </div>

        <div v-if="product.description" class="info-section">
          <h3>{{ t('product.description') }}</h3>
          <p class="product-desc">{{ product.description }}</p>
        </div>

        <div class="seller-info" @click="goToProfile" role="link" tabindex="0">
          <span class="info-label">{{ t('product.postedBy') }}</span>
          <span class="seller-name">{{ product.user?.name }}</span>
        </div>
      </div>
    </template>

    <!-- Related Products -->
    <section v-if="relatedProducts.length" class="related-section">
      <h2 class="related-title">{{ t('product.relatedProducts') }}</h2>
      <div class="related-grid">
        <ProductCard v-for="rp in relatedProducts" :key="rp.id" :product="rp" />
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { t } from '../i18n'
import { getProduct, getProducts } from '../services/products'
import ProductCard from '../components/ProductCard.vue'

const route = useRoute()
const router = useRouter()

const product = ref(null)
const loading = ref(true)
const activeImageIndex = ref(0)
const relatedProducts = ref([])

const images = computed(() => product.value?.images || [])
const mainImage = computed(() => {
  if (!images.value.length) return ''
  return `/storage/${images.value[activeImageIndex.value]?.image}`
})

const isOwner = computed(() => {
  const stored = localStorage.getItem('user')
  if (!stored) return false
  try {
    const me = JSON.parse(stored)
    return me.id === product.value?.user_id
  } catch {
    return false
  }
})

function goToProfile() {
  if (isOwner.value) {
    router.push('/profile')
  } else {
    router.push(`/users/${product.value.user_id}`)
  }
}

async function fetchRelated(subcategoryId, currentId) {
  try {
    const data = await getProducts({ subcategory_id: subcategoryId })
    relatedProducts.value = (data.data?.data || []).filter(p => p.id !== currentId)
  } catch {
    relatedProducts.value = []
  }
}

async function loadProduct(id) {
  loading.value = true
  relatedProducts.value = []
  activeImageIndex.value = 0
  try {
    const data = await getProduct(id)
    product.value = data.data || null
    if (product.value?.subcategory_id) {
      fetchRelated(product.value.subcategory_id, product.value.id)
    }
  } catch {
    product.value = null
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadProduct(route.params.id)
})

watch(() => route.params.id, (newId) => {
  if (newId) loadProduct(newId)
})
</script>

<style scoped>
.product-show {
  padding: 2rem 1.5rem 3rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  max-width: 1200px;
}

.breadcrumb-nav {
  grid-column: 1 / -1;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: var(--text-muted);
  margin-bottom: 0.5rem;
}

.breadcrumb-nav a {
  color: var(--text-muted);
  text-decoration: none;
  transition: color 0.15s;
}

.breadcrumb-nav a:hover {
  color: var(--accent);
}

.breadcrumb-nav .sep {
  color: var(--border-strong);
}

.product-images {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.main-image {
  position: relative;
  aspect-ratio: 1;
  border-radius: var(--radius-md);
  overflow: hidden;
  background: var(--surface-2);
  border: 1px solid var(--border);
}

.main-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image {
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  opacity: 0.3;
}

.image-counter {
  position: absolute;
  bottom: 0.75rem;
  right: 0.75rem;
  background: rgba(0, 0, 0, 0.6);
  color: #fff;
  font-size: 0.8rem;
  padding: 0.25rem 0.6rem;
  border-radius: var(--radius-sm);
}

.thumb-strip {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
}

.thumb-btn {
  width: 56px;
  height: 56px;
  border-radius: var(--radius-sm);
  overflow: hidden;
  border: 2px solid var(--border);
  cursor: pointer;
  padding: 0;
  background: none;
  flex-shrink: 0;
  transition: border-color 0.15s;
}

.thumb-btn.active {
  border-color: var(--accent);
}

.thumb-btn img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.product-meta {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: var(--radius-sm);
  background: var(--accent);
  color: #fff;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.breadcrumb {
  font-size: 0.85rem;
  color: var(--text-muted);
}

.product-title {
  font-family: var(--font-serif);
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
}

.product-price {
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--accent);
  margin: 0;
}

.info-section {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 1rem 0;
  border-top: 1px solid var(--border);
}

.info-section h3 {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 0.25rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  padding: 0.2rem 0;
}

.info-label {
  color: var(--text-muted);
  font-weight: 500;
}

.product-desc {
  font-size: 0.9rem;
  color: var(--text-muted);
  line-height: 1.6;
  margin: 0;
}

.seller-info {
  padding-top: 1rem;
  border-top: 1px solid var(--border);
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background 0.15s;
  border-radius: var(--radius-sm);
  padding: 1rem 0.5rem 0.5rem;
  margin: 0 -0.5rem;
}

.seller-info:hover {
  background: var(--surface-2);
}

.seller-name {
  color: var(--accent);
  font-weight: 600;
}

.loading, .empty {
  grid-column: 1 / -1;
  text-align: center;
  padding: 3rem;
  color: var(--text-muted);
}

.related-section {
  grid-column: 1 / -1;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid var(--border);
}

.related-title {
  font-size: 1.15rem;
  font-weight: 600;
  margin: 0 0 1rem;
}

.related-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.25rem;
}

@media (max-width: 900px) {
  .related-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .product-show {
    grid-template-columns: 1fr;
  }
}
</style>
