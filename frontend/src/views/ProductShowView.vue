<template>
  <div class="product-show container page-shell">
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
            <img :src="`${STORAGE_URL}/storage/${img.image}`" alt="" />
          </button>
        </div>
      </div>

      <!-- Info -->
      <div class="product-info">
        <!-- Seller Mini Profile -->
        <div class="seller-mini-profile" @click="goToProfile" role="link" tabindex="0">
          <div class="seller-avatar">
            <img v-if="product.user?.profile?.avatar" :src="resolveStorageUrl(product.user.profile.avatar)" :alt="product.user?.name" />
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
            v-if="isAuthenticated && !isOwner"
            class="fav-btn"
            @click="chatWithSeller"
          >
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            {{ t('product.chatWithSeller') }}
          </button>
          <button
            v-if="isAuthenticated"
            class="fav-btn"
            :class="{ active: isFavorited }"
            @click="onFavorite"
          >
            <svg viewBox="0 0 24 24" width="22" height="22" :fill="isFavorited ? 'var(--primary)' : 'none'" :stroke="isFavorited ? 'var(--primary)' : 'var(--text-muted)'" stroke-width="2">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
          </button>
          <button class="fav-btn report-detail-btn" @click="onReport">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            {{ t('report.button') }}
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
      <h2 class="related-title section-title">{{ t('product.comments') }} ({{ comments.length }})</h2>

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
      <h2 class="related-title section-title">{{ t('product.relatedProducts') }}</h2>
      <div class="related-grid">
        <ProductCard v-for="rp in relatedProducts" :key="rp.id" :product="rp" />
      </div>
    </section>

    <ReportModal v-if="product" v-model="showReportModal" :product-id="product.id" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { t } from '../i18n'
import { getProduct, getProducts, getComments, addComment, deleteComment } from '../services/products'
import { toggleFavorite } from '../services/favorites'
import { createConversation } from '../services/chat'
import { STORAGE_URL, resolveStorageUrl } from '../services/http'
import ProductCard from '../components/ProductCard.vue'
import CommentNode from '../components/CommentNode.vue'
import ReportModal from '../components/ReportModal.vue'

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
const showReportModal = ref(false)

const isAuthenticated = computed(() => !!localStorage.getItem('token'))
const currentUserId = computed(() => {
  try { return JSON.parse(localStorage.getItem('user') || '{}').id } catch { return null }
})

const images = computed(() => product.value?.images || [])
const mainImage = computed(() => {
  if (!images.value.length) return ''
  return `${STORAGE_URL}/storage/${images.value[activeImageIndex.value]?.image}`
})

const isOwner = computed(() => {
  const stored = localStorage.getItem('user')
  if (!stored) return false
  try {
    const me = JSON.parse(stored)
    return Number(me.id) === Number(product.value?.user_id)
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

async function chatWithSeller() {
  if (!isAuthenticated.value) {
    router.push('/login')
    return
  }
  try {
    const data = await createConversation(product.value.user_id)
    router.push({
      path: '/chat',
      query: { conversation: data.data.id, product: product.value.id, seller: product.value.user_id },
    })
  } catch (e) {
    console.error('chatWithSeller error:', e)
    router.push('/chat')
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

function onReport() {
  if (!isAuthenticated.value) {
    router.push('/login')
    return
  }
  showReportModal.value = true
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
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 2rem;
  max-width: var(--container);
  padding-block: 2rem 3rem;
}

.back-btn {
  grid-column: 1 / -1;
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  width: fit-content;
  margin-bottom: 0.25rem;
  padding: 0.45rem 0.7rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface);
  color: var(--text-muted);
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
}

.back-btn:hover {
  border-color: var(--primary);
  background: var(--accent-soft);
  color: var(--primary);
}

.breadcrumb-nav {
  grid-column: 1 / -1;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.55rem;
  margin-bottom: 0.25rem;
  color: var(--text-muted);
  font-family: var(--font-mono);
  font-size: 0.75rem;
}

.breadcrumb-nav a {
  color: var(--text-muted);
  text-decoration: none;
}

.breadcrumb-nav a:hover {
  color: var(--primary);
}

.breadcrumb-nav .sep {
  color: var(--border-strong);
}

.product-images {
  display: flex;
  min-width: 0;
  flex-direction: column;
  gap: 0.75rem;
}

.main-image {
  position: relative;
  aspect-ratio: 1;
  overflow: hidden;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  background: var(--surface-2);
  box-shadow: var(--shadow-sm);
}

.main-image img {
  display: block;
  width: 100%;
  max-width: 100%;
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
  right: 0.75rem;
  bottom: 0.75rem;
  padding: 0.3rem 0.65rem;
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.76);
  color: var(--on-primary);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  font-family: var(--font-mono);
  font-size: 0.72rem;
}

.thumb-strip {
  display: flex;
  width: 100%;
  max-width: 100%;
  gap: 0.55rem;
  overflow-x: auto;
  overscroll-behavior-inline: contain;
  scroll-snap-type: x proximity;
  padding: 0.15rem;
}

.thumb-btn {
  width: 58px;
  height: 58px;
  flex-shrink: 0;
  overflow: hidden;
  padding: 0;
  border: 2px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface);
  cursor: pointer;
  scroll-snap-align: start;
  opacity: 0.7;
}

.thumb-btn:hover,
.thumb-btn.active {
  border-color: var(--primary);
  opacity: 1;
}

.thumb-btn img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-info {
  display: flex;
  min-width: 0;
  flex-direction: column;
  gap: 1rem;
  align-self: start;
  padding: 1.4rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  background: var(--surface);
  box-shadow: var(--shadow-sm);
}

.product-meta {
  display: flex;
  min-width: 0;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.badge {
  display: inline-flex;
  align-items: center;
  padding: 0.32rem 0.65rem;
  border: 1px solid color-mix(in srgb, var(--primary) 24%, transparent);
  border-radius: 999px;
  background: var(--accent-soft);
  color: var(--primary);
  font-family: var(--font-mono);
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.breadcrumb {
  min-width: 0;
  color: var(--text-muted);
  font-size: 0.82rem;
  overflow-wrap: anywhere;
}

.product-title {
  margin: 0;
  color: var(--text);
  overflow-wrap: anywhere;
  font-family: var(--font-mono);
  font-size: clamp(1.45rem, 2.4vw, 2rem);
  font-weight: 700;
  letter-spacing: -0.045em;
  line-height: 1.2;
}

.product-price {
  margin: 0;
  color: var(--primary);
  font-family: var(--font-mono);
  font-size: 1.8rem;
  font-weight: 700;
  letter-spacing: -0.04em;
}

.product-price-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.65rem;
}

.fav-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  min-height: 40px;
  padding: 0.55rem 0.85rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface-2);
  color: var(--text-muted);
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
}

.fav-btn:hover {
  border-color: var(--primary);
  background: var(--accent-soft);
  color: var(--primary);
}

.fav-btn.active {
  border-color: var(--primary);
  background: var(--accent-soft);
  color: var(--primary);
}

.info-section {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 1rem 0 0.25rem;
  border-top: 1px solid var(--border);
}

.info-section h3,
.contact-section h3 {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin: 0 0 0.25rem;
  color: var(--text);
  font-size: 0.92rem;
  font-weight: 700;
}

.info-section h3::before,
.contact-section h3::before {
  content: '';
  width: 4px;
  height: 1.15em;
  flex: 0 0 4px;
  border-radius: 999px;
  background: var(--primary);
}

.info-row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.2rem 0;
  font-size: 0.88rem;
}

.info-label {
  color: var(--text-muted);
  font-weight: 600;
}

.info-row > span:last-child {
  min-width: 0;
  text-align: right;
  overflow-wrap: anywhere;
}

.product-desc {
  margin: 0;
  color: var(--text-muted);
  font-size: 0.9rem;
  line-height: 1.7;
}

.seller-mini-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface-2);
  cursor: pointer;
}

.seller-mini-profile:hover {
  border-color: var(--primary);
  background: var(--accent-soft);
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
  min-width: 0;
  flex-direction: column;
}

.seller-details .seller-name {
  min-width: 0;
  font-weight: 600;
  font-size: 0.95rem;
  color: var(--text);
  overflow-wrap: anywhere;
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
  padding: 1rem 0 0.25rem;
  border-top: 1px solid var(--border);
}

.contact-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-muted);
}

.contact-phone {
  color: var(--primary);
  font-family: var(--font-mono);
  font-size: 0.9rem;
  font-weight: 600;
  text-decoration: none;
}

.contact-phone:hover {
  text-decoration: underline;
}

.loading,
.empty {
  grid-column: 1 / -1;
  padding: 4rem 1.5rem;
  border: 1px dashed var(--border-strong);
  border-radius: var(--radius-md);
  background: var(--surface);
  color: var(--text-muted);
  text-align: center;
}

.related-section,
.comments-section {
  grid-column: 1 / -1;
  padding: 1.4rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  background: var(--surface);
  box-shadow: var(--shadow-sm);
}

.related-section {
  margin-top: 0.5rem;
}

.comments-section {
  margin-top: 0.5rem;
}

.related-title {
  margin: 0 0 1.1rem;
  font-size: 1rem;
}

.related-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1rem;
}

@media (max-width: 900px) {
  .related-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .product-show {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 600px) {
  .product-info,
  .related-section,
  .comments-section {
    padding: 1rem;
  }

  .product-info {
    gap: 0.85rem;
  }

  .breadcrumb-nav {
    gap: 0.35rem;
    font-size: 0.7rem;
    overflow-wrap: anywhere;
  }

  .thumb-btn {
    width: 54px;
    height: 54px;
  }

  .info-row {
    display: grid;
    grid-template-columns: minmax(72px, 0.4fr) minmax(0, 1fr);
    align-items: start;
    gap: 0.75rem;
  }

  .info-row > span:last-child {
    text-align: left;
  }

  .seller-mini-profile {
    min-width: 0;
  }

  .product-price {
    font-size: 1.55rem;
  }

  .fav-btn {
    min-height: 38px;
    padding-inline: 0.7rem;
  }

  .related-grid {
    grid-template-columns: 1fr;
  }
}

.comments-section {
  margin-top: 0;
  padding-top: 1.4rem;
  border-top: 1px solid var(--border);
}

.comment-form {
  margin-top: 1rem;
  margin-bottom: 1.5rem;
}

.comment-input {
  width: 100%;
  min-height: 92px;
  margin-bottom: 0.65rem;
  padding: 0.8rem;
  resize: vertical;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface-2);
  color: var(--text);
  font-size: 0.9rem;
}

.comment-input:focus {
  outline: none;
  border-color: var(--primary);
  background: var(--surface);
  box-shadow: 0 0 0 3px var(--focus-ring);
}

.comment-login {
  margin: 1rem 0 0;
  color: var(--text-muted);
  font-size: 0.88rem;
}

.comment-empty {
  padding: 2rem 0;
  color: var(--text-muted);
  font-size: 0.88rem;
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
  color: var(--on-primary);
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
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--primary);
  color: var(--on-primary);
}

.btn-primary:hover:not(:disabled) {
  opacity: 0.9;
}

.btn-sm {
  padding: 0.4rem 0.85rem;
  font-size: 0.82rem;
}
</style>
