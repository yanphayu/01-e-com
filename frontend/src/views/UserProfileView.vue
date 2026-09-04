<template>
  <div class="user-profile">
    <div v-if="loading" class="loading container">{{ t('product.loading') }}</div>
    <div v-else-if="!user" class="empty container">User not found</div>
    <template v-else>
      <div class="fb-header container">
        <div class="fb-cover">
          <img v-if="user.profile?.cover_image" :src="user.profile.cover_image" class="cover-img" alt="" />
          <div class="cover-gradient"></div>
        </div>
        <div class="fb-header-inner">
          <div class="fb-avatar-col">
            <div class="avatar-wrap">
              <img v-if="user.profile?.avatar" :src="user.profile.avatar" class="store-avatar" :alt="user.name" />
              <span v-else class="store-avatar avatar-fallback">{{ initials }}</span>
            </div>
          </div>
          <div class="fb-meta">
            <h1 class="store-name">{{ user.name }}</h1>
            <p v-if="user.profile?.address?.address" class="store-location">{{ user.profile.address.address }}</p>
            <p class="store-joined">{{ t('product.joined') }} {{ new Date(user.created_at).toLocaleDateString() }}</p>
          </div>
        </div>
      </div>

      <div class="container">
        <div class="section-head">
          <h2>{{ t('product.listings') }} ({{ user.products?.length || 0 }})</h2>
        </div>

        <div v-if="!user.products?.length" class="empty">{{ t('product.noProducts') }}</div>
        <div v-else class="products-grid">
          <ProductCard v-for="product in user.products" :key="product.id" :product="product" />
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { t } from '../i18n'
import { getUser } from '../services/products'
import ProductCard from '../components/ProductCard.vue'

const route = useRoute()

const user = ref(null)
const loading = ref(true)

const initials = computed(() => {
  if (!user.value?.name) return '?'
  return user.value.name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
})

onMounted(async () => {
  try {
    const data = await getUser(route.params.id)
    user.value = data.data || null
  } catch {
    user.value = null
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.user-profile {
  min-height: 100%;
}

.fb-header {
  margin-top: 1rem;
}

.fb-cover {
  height: 340px;
  background: var(--surface-2);
  border: 1px solid var(--border);
  position: relative;
  overflow: hidden;
  border-radius: 4px 4px 0 0;
}

.cover-img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cover-gradient {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, transparent 50%, rgba(0, 0, 0, 0.18) 100%);
}

.fb-header-inner {
  display: flex;
  align-items: flex-start;
  gap: 1.25rem;
  position: relative;
  background: var(--surface);
  border: 1px solid var(--border);
  border-top: none;
  border-radius: 0 0 4px 4px;
  padding: 0 1.5rem 1.25rem;
}

.fb-avatar-col {
  flex-shrink: 0;
}

.avatar-wrap {
  margin-top: -50px;
  width: 168px;
  height: 168px;
}

.store-avatar {
  width: 168px;
  height: 168px;
  border-radius: 50%;
  border: 3px solid var(--surface);
  object-fit: cover;
  display: block;
  background: var(--surface-2);
}

.avatar-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  font-weight: 700;
  color: var(--accent);
}

.fb-meta {
  padding-top: 0.75rem;
}

.store-name {
  font-family: var(--font-serif);
  font-size: 1.4rem;
  font-weight: 600;
  margin: 0;
}

.store-location {
  color: var(--text-muted);
  font-size: 0.9rem;
  margin: 0.2rem 0 0;
}

.store-joined {
  color: var(--text-muted);
  font-size: 0.8rem;
  margin: 0.2rem 0 0;
}

.section-head {
  margin: 2rem 0 1.25rem;
}

.section-head h2 {
  font-size: 1.2rem;
  font-weight: 600;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.25rem;
  padding-bottom: 3rem;
}

.loading, .empty {
  text-align: center;
  padding: 3rem;
  color: var(--text-muted);
}

@media (max-width: 640px) {
  .fb-cover {
    height: 160px;
  }
  .fb-header-inner {
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 0 1rem 1rem;
  }
  .avatar-wrap {
    width: 120px;
    height: 120px;
  }
  .store-avatar {
    width: 120px;
    height: 120px;
  }
}
</style>
