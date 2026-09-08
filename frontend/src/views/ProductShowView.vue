<template>
  <div class="product-show container">
    <button type="button" class="back-btn" @click="$router.back()">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 12H5"/>
        <polyline points="12 19 5 12 12 5"/>
      </svg>
      {{ t('product.back') }}
    </button>
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
        <!-- Seller Mini Profile -->
        <div class="seller-mini-profile" @click="goToProfile" role="link" tabindex="0">
          <div class="seller-avatar">
            <img v-if="product.user?.profile?.avatar" :src="product.user.profile.avatar" :alt="product.user?.name" />
            <span v-else class="avatar-placeholder">{{ (product.user?.name || '?')[0] }}</span>
          </div>
          <div class="seller-details">
            <span class="seller-name">{{ product.user?.name }}</span>
            <span class="seller-label">{{ t('product.postedBy') }}</span>
          </div>
        </div>

        <div class="product-meta">
          <span v-if="product.detail?.condition" class="badge">
            {{ product.detail.condition === 'new' ? t('product.conditionNew') : t('product.conditionUsed') }}
          </span>
          <span v-if="product.detail?.brand" class="badge">
            {{ product.detail.brand.name }}
          </span>
          <span v-if="product.detail?.model" class="badge">
            {{ product.detail.model.name }}
          </span>
          <span v-if="product.subcategory?.category" class="breadcrumb">
            {{ product.subcategory.category.name }} → {{ product.subcategory.name }}
          </span>
        </div>

        <h1 class="product-title">{{ product.name }}</h1>
        <div class="product-price-row">
          <p class="product-price">${{ Number(product.price).toFixed(2) }}</p>
          <button
            v-if="isAuthenticated"
            class="fav-btn"
            :class="{ active: isFavorited }"
            @click="onFavorite"
          >
            <svg viewBox="0 0 24 24" width="22" height="22" :fill="isFavorited ? 'var(--accent)' : 'none'" :stroke="isFavorited ? 'var(--accent)' : '#999'" stroke-width="2">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
          </button>
        </div>

        <div class="info-section">
          <h3>{{ t('product.location') }}</h3>
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

        <div v-if="product.phones?.length" class="contact-section">
          <h3>{{ t('product.contact') }}</h3>
          <div v-for="phone in product.phones" :key="phone.id" class="contact-row">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
            </svg>
            <a :href="`tel:${phone.phone}`" class="contact-phone">{{ phone.phone }}</a>
          </div>
        </div>
      </div>
    </template>

    <!-- Comments Section -->
    <section class="comments-section">
      <h2 class="related-title">{{ t('product.comments') }} ({{ comments.length }})</h2>

      <div v-if="comments.length === 0" class="comment-empty">{{ t('product.noComments') }}</div>

      <CommentNode
        v-for="comment in comments"
        :key="comment.id"
        :comment="comment"
        :depth="0"
        :is-authenticated="isAuthenticated"
        :current-user-id="currentUserId"
        :is-owner="isOwner"
        :reply-to="replyTo"
        :reply-body="replyBody"
        :posting-comment="postingComment"
        @set-reply="(id) => replyTo = replyTo === id ? null : id"
        @update-reply-body="(val) => replyBody = val"
        @post-reply="(id) => postComment(id)"
        @delete-comment="(id) => removeComment(id)"
      />

      <div v-if="isAuthenticated" class="comment-form">
        <textarea
          v-model="newComment"
          class="comment-input"
          :placeholder="t('product.writeComment')"
          rows="3"
        ></textarea>
        <button
          class="btn btn-primary btn-sm"
          :disabled="!newComment.trim() || postingComment"
          @click="postComment()"
        >{{ postingComment ? t('product.posting') : t('product.postComment') }}</button>
      </div>
      <p v-else class="comment-login">{{ t('product.loginToComment') }}</p>
    </section>

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
import { getProduct, getProducts, getComments, addComment, deleteComment } from '../services/products'
import { toggleFavorite } from '../services/favorites'
import ProductCard from '../components/ProductCard.vue'
import CommentNode from '../components/CommentNode.vue'

const route = useRoute()
const router = useRouter()

const product = ref(null)
const loading = ref(true)
const activeImageIndex = ref(0)
const relatedProducts = ref([])
const comments = ref([])
const newComment = ref('')
const replyTo = ref(null)
const replyBody = ref('')
const postingComment = ref(false)
const isFavorited = ref(false)

const isAuthenticated = computed(() => !!localStorage.getItem('token'))
const currentUserId = computed(() => {
  try { return JSON.parse(localStorage.getItem('user') || '{}').id } catch { return null }
})

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
    isFavorited.value = product.value?.is_favorited || false
    if (product.value?.subcategory_id) {
      fetchRelated(product.value.subcategory_id, product.value.id)
    }
    loadComments(id)
  } catch {
    product.value = null
  } finally {
    loading.value = false
  }
}

async function onFavorite() {
  try {
    const res = await toggleFavorite(product.value.id)
    isFavorited.value = res.data.favorited
  } catch {}
}

async function loadComments(productId) {
  try {
    const data = await getComments(productId)
    comments.value = data.data || []
  } catch {
    comments.value = []
  }
}

async function postComment(parentId = null) {
  const body = parentId ? replyBody.value : newComment.value
  if (!body.trim()) return

  postingComment.value = true
  try {
    await addComment(product.value.id, body.trim(), parentId)
    if (parentId) {
      replyBody.value = ''
      replyTo.value = null
    } else {
      newComment.value = ''
    }
    await loadComments(product.value.id)
  } catch (e) {
    console.error('Failed to post comment:', e)
  } finally {
    postingComment.value = false
  }
}

async function removeComment(commentId) {
  try {
    await deleteComment(product.value.id, commentId)
    await loadComments(product.value.id)
  } catch (e) {
    console.error('Failed to delete comment:', e)
  }
}

function formatTime(dateStr) {
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

.back-btn {
  grid-column: 1 / -1;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  background: none;
  border: none;
  color: var(--text-muted);
  font: inherit;
  font-size: 0.88rem;
  font-weight: 500;
  cursor: pointer;
  padding: 0;
  margin-bottom: 0.5rem;
  transition: color 0.15s;
}

.back-btn:hover {
  color: var(--accent);
}

.breadcrumb-nav {
  grid-column: 1 / -1;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
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
  flex-wrap: wrap;
  gap: 0.5rem;
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

.product-price-row {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.fav-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  padding: 0.5rem 1rem;
  font: inherit;
  font-size: 0.88rem;
  font-weight: 500;
  color: var(--text-muted);
  cursor: pointer;
  transition: color 0.15s, border-color 0.15s;
}

.fav-btn:hover {
  color: var(--accent);
  border-color: var(--accent);
}

.fav-btn.active {
  color: var(--accent);
  border-color: var(--accent);
  background: var(--accent-soft);
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

.seller-mini-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: background 0.15s, border-color 0.15s;
}

.seller-mini-profile:hover {
  background: var(--surface-2);
  border-color: var(--accent);
}

.seller-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  overflow: hidden;
  background: var(--surface-2);
  flex-shrink: 0;
}

.seller-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-muted);
  background: var(--surface-3, var(--surface-2));
}

.seller-details {
  display: flex;
  flex-direction: column;
}

.seller-details .seller-name {
  font-weight: 600;
  font-size: 0.95rem;
  color: var(--text);
}

.seller-details .seller-phone {
  font-size: 0.85rem;
  color: var(--text-muted);
}

.seller-details .seller-label {
  font-size: 0.8rem;
  color: var(--text-muted);
}

.contact-section {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 1rem 0;
  border-top: 1px solid var(--border);
}

.contact-section h3 {
  font-size: 1rem;
  font-weight: 600;
  margin: 0;
}

.contact-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-muted);
}

.contact-phone {
  font-size: 0.95rem;
  font-weight: 500;
  color: var(--accent);
  text-decoration: none;
}

.contact-phone:hover {
  text-decoration: underline;
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

/* Comments */
.comments-section {
  grid-column: 1 / -1;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid var(--border);
}

.comment-form {
  margin-bottom: 1.5rem;
}

.comment-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font: inherit;
  font-size: 0.9rem;
  color: var(--text);
  background: var(--surface);
  resize: vertical;
  min-height: 60px;
  margin-bottom: 0.5rem;
  transition: border-color 0.15s;
}

.comment-input:focus {
  outline: none;
  border-color: var(--accent);
}

.comment-login {
  color: var(--text-muted);
  font-size: 0.88rem;
  margin-bottom: 1rem;
}

.comment-empty {
  color: var(--text-muted);
  font-size: 0.88rem;
  padding: 1rem 0;
}

.comment-item {
  padding: 1rem 0;
  border-top: 1px solid var(--border);
}

.comment-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.4rem;
}

.comment-user {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  color: inherit;
}

.comment-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  object-fit: cover;
}

.comment-avatar-fallback {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: var(--accent);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 600;
}

.comment-author {
  font-weight: 600;
  font-size: 0.88rem;
  color: var(--text);
}

.comment-time {
  font-size: 0.78rem;
  color: var(--text-muted);
}

.comment-body {
  font-size: 0.9rem;
  line-height: 1.5;
  margin: 0.3rem 0;
  color: var(--text);
}

.comment-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 0.3rem;
}

.comment-reply-btn,
.comment-delete-btn {
  background: none;
  border: none;
  font: inherit;
  font-size: 0.78rem;
  font-weight: 500;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0;
  transition: color 0.15s;
}

.comment-reply-btn:hover {
  color: var(--accent);
}

.comment-delete-btn:hover {
  color: var(--danger);
}

.reply-form {
  margin-top: 0.75rem;
}

.reply-input {
  min-height: 48px;
  font-size: 0.85rem;
}

.replies {
  margin-top: 0.75rem;
  padding-left: 1.5rem;
  border-left: 2px solid var(--border);
}

.reply-item {
  padding: 0.75rem 0;
  border-top: none;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: var(--radius-sm);
  font: inherit;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: opacity 0.15s;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--accent);
  color: #fff;
}

.btn-primary:hover:not(:disabled) {
  opacity: 0.9;
}

.btn-sm {
  padding: 0.4rem 0.85rem;
  font-size: 0.82rem;
}
</style>
