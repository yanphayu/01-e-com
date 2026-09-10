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
      <button
        v-if="isAuthenticated"
        class="product-card-fav"
        :class="{ active: isFavorited }"
        @click.prevent.stop="onFavorite"
      >
        <svg viewBox="0 0 24 24" width="18" height="18" :fill="isFavorited ? 'var(--accent)' : 'none'" :stroke="isFavorited ? 'var(--accent)' : '#999'" stroke-width="2">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
      </button>
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
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  overflow: hidden;
  text-decoration: none;
  color: inherit;
  transition: border-color 0.15s, box-shadow 0.15s;
}

.product-card:hover {
  border-color: var(--border-strong);
  box-shadow: var(--shadow-sm);
}

.product-card-thumb {
  position: relative;
  height: 180px;
  background: var(--surface-2);
  overflow: hidden;
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
  top: 0.5rem;
  left: 0.5rem;
  background: var(--accent);
  color: #fff;
  font-size: 0.72rem;
  font-weight: 600;
  padding: 0.2rem 0.5rem;
  border-radius: var(--radius-sm);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.product-card-body {
  padding: 0.85rem 1rem 1rem;
}

.product-card-name {
  font-size: 0.95rem;
  font-weight: 600;
  margin: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.product-card-location {
  font-size: 0.8rem;
  color: var(--text-muted);
  margin: 0.2rem 0 0;
}

.product-card-price {
  margin: 0.35rem 0 0;
  color: var(--accent);
  font-weight: 700;
  font-size: 1.05rem;
}

.product-card-date {
  margin: 0.3rem 0 0;
  font-size: 0.75rem;
  color: var(--text-muted);
}

.product-card-fav {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: rgba(255,255,255,0.85);
  border: none;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #999;
  transition: color 0.15s, background 0.15s;
}

.product-card-fav:hover {
  background: #fff;
  color: var(--accent);
}

.product-card-fav.active {
  color: var(--accent);
}

@media (max-width: 480px) {
  .product-card-thumb {
    height: 160px;
  }
  .product-card-body {
    padding: 0.75rem;
  }
  .product-card-name {
    font-size: 0.9rem;
  }
  .product-card-price {
    font-size: 1rem;
  }
}
</style>
