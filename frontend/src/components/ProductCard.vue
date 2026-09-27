<template>
  <RouterLink :to="`/products/${product.id}`" class="product-card">
    <div class="product-card-thumb">
      <img v-if="primaryImage" :src="primaryImage" :alt="product.name" />
      <div v-else class="product-card-no-img">
        <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
          <circle cx="8.5" cy="8.5" r="1.5"/>
          <polyline points="21 15 16 10 5 21"/>
        </svg>
      </div>
      <span v-if="product.detail?.condition" class="product-card-badge">
        {{ product.detail.condition === 'new' ? t('product.conditionNew') : t('product.conditionUsed') }}
      </span>
      <div class="product-card-actions">
        <button
          v-if="isAuthenticated"
          class="product-card-fav"
          :class="{ active: isFavorited }"
          :title="t('product.favorite')"
          @click.prevent.stop="onFavorite"
        >
          <svg viewBox="0 0 24 24" width="18" height="18" :fill="isFavorited ? 'var(--primary)' : 'none'" :stroke="isFavorited ? 'var(--primary)' : 'var(--text-muted)'" stroke-width="2">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
          </svg>
        </button>
      </div>
    </div>
    <div class="product-card-body">
      <h3 class="product-card-name">{{ product.name }}</h3>
      <p v-if="product.detail?.province" class="product-card-location">{{ product.detail.province }}</p>
      <p class="product-card-date">{{ formatDate(product.created_at) }}</p>
      <p class="product-card-price">${{ Number(product.price).toFixed(2) }}</p>
    </div>
  </RouterLink>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { t } from '../i18n'
import { toggleFavorite } from '../services/favorites'
import { STORAGE_URL } from '../services/http'

const props = defineProps({
  product: { type: Object, required: true },
})

const isFavorited = ref(props.product.is_favorited || false)
const isAuthenticated = computed(() => !!localStorage.getItem('token'))

watch(() => props.product.is_favorited, (val) => {
  isFavorited.value = val || false
})

const primaryImage = computed(() => {
  const img = props.product.images?.find(i => i.is_primary) || props.product.images?.[0]
  return img ? `${STORAGE_URL}/storage/${img.image}` : null
})

async function onFavorite() {
  try {
    const res = await toggleFavorite(props.product.id)
    isFavorited.value = res.data.favorited
    props.product.is_favorited = res.data.favorited
  } catch {}
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
</script>

<style scoped>
.product-card {
  display: block;
  height: 100%;
  overflow: hidden;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  background: var(--surface);
  color: inherit;
  text-decoration: none;
  box-shadow: var(--shadow-sm);
}

.product-card-thumb {
  position: relative;
  height: 180px;
  overflow: hidden;
  border-bottom: 1px solid var(--border);
  background: var(--surface-2);
}

.product-card-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-card-no-img {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  opacity: 0.4;
}

.product-card-badge {
  position: absolute;
  top: 0.65rem;
  left: 0.65rem;
  padding: 0.3rem 0.6rem;
  border: 1px solid color-mix(in srgb, var(--primary) 24%, transparent);
  border-radius: 999px;
  background: var(--navbar-bg);
  color: var(--primary);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  font-family: var(--font-mono);
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.product-card-actions {
  position: absolute;
  top: 0.65rem;
  right: 0.65rem;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.product-card-fav {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  border: 1px solid var(--border);
  border-radius: 50%;
  background: var(--navbar-bg);
  color: var(--text-muted);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  cursor: pointer;
}

.product-card-fav:hover {
  border-color: var(--primary);
  background: var(--surface);
  color: var(--primary);
}

.product-card-fav.active {
  color: var(--primary);
  background: var(--accent-soft);
}

.product-card-body {
  padding: 0.9rem 1rem 1rem;
}

.product-card-name {
  margin: 0;
  overflow: hidden;
  color: var(--text);
  font-family: var(--font-mono);
  font-size: 0.92rem;
  font-weight: 700;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.product-card-location {
  margin: 0.25rem 0 0;
  color: var(--text-muted);
  font-size: 0.8rem;
}

.product-card-price {
  margin: 0.55rem 0 0;
  color: var(--primary);
  font-family: var(--font-mono);
  font-size: 1.05rem;
  font-weight: 700;
}

.product-card-date {
  margin: 0.3rem 0 0;
  color: var(--text-muted);
  font-family: var(--font-mono);
  font-size: 0.7rem;
}

@media (max-width: 480px) {
  .product-card-thumb {
    height: 160px;
  }

  .product-card-body {
    padding: 0.75rem;
  }

  .product-card-name {
    font-size: 0.86rem;
  }

  .product-card-price {
    font-size: 1rem;
  }
}
</style>