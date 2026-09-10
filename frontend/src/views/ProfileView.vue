<template>
  <main class="store-page" :class="{ editing }">
    <template v-if="!editing">
      <div class="fb-header container">
        <div class="fb-cover">
          <img v-if="user.profile?.cover_image" :src="user.profile.cover_image" class="cover-img" alt="" />
          <div class="cover-gradient"></div>
        </div>

        <div class="fb-header-inner">
          <div class="fb-avatar-col">
            <div class="avatar-wrap">
              <img v-if="user.profile?.avatar" :src="user.profile.avatar" class="store-avatar" alt="" />
              <span v-else class="store-avatar store-avatar-fallback">{{ userInitial }}</span>
            </div>
          </div>

          <div class="fb-meta">
            <div class="fb-name-row">
              <h1 class="store-name">{{ user.name || t('auth.myProfile') }}</h1>
              <span v-if="isVerified" class="verified-badge" title="Verified">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="var(--accent)">
                  <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
              </span>
            </div>
            <p class="store-email">{{ user.email }}</p>
          </div>

          <div class="fb-actions">
            <button class="btn btn-primary" @click="editing = true">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
              {{ t('auth.editProfile') }}
            </button>
          </div>
        </div>

        <div class="fb-divider"></div>
      </div>

      <div class="store-body">
        <div class="store-stats">
          <div class="stat-item">
            <span class="stat-value">{{ orderCount }}</span>
            <span class="stat-label">{{ t('profile.orders') }}</span>
          </div>
          <div class="stat-divider"></div>
          <div class="stat-item">
            <span class="stat-value">{{ reviewCount }}</span>
            <span class="stat-label">{{ t('profile.reviews') }}</span>
          </div>
          <div class="stat-divider"></div>
          <div class="stat-item">
            <span class="stat-value">{{ memberSince }}</span>
            <span class="stat-label">{{ t('profile.memberSince') }}</span>
          </div>
        </div>

        <div class="store-sections">
          <div class="section-card">
            <h2 class="section-title">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              {{ t('profile.personalInfo') }}
            </h2>
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">{{ t('auth.phone') }}</span>
                <span class="info-value">{{ user.profile?.phone || '—' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">{{ t('auth.birthDate') }}</span>
                <span class="info-value">{{ user.profile?.birth_date || '—' }}</span>
              </div>
            </div>
          </div>

          <div class="section-card" v-if="user.profile?.facebook || user.profile?.instagram || user.profile?.twitter">
            <h2 class="section-title">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
              </svg>
              {{ t('profile.socialLinks') }}
            </h2>
            <div class="social-grid">
              <a v-if="user.profile?.facebook" :href="user.profile.facebook" target="_blank" rel="noopener" class="social-link social-facebook">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                  <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                </svg>
                <span>Facebook</span>
              </a>
              <a v-if="user.profile?.instagram" :href="user.profile.instagram" target="_blank" rel="noopener" class="social-link social-instagram">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                  <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                  <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                </svg>
                <span>Instagram</span>
              </a>
              <a v-if="user.profile?.twitter" :href="user.profile.twitter" target="_blank" rel="noopener" class="social-link social-twitter">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                  <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
                <span>X / Twitter</span>
              </a>
            </div>
          </div>

          <div class="section-card" v-if="user.profile?.address?.latitude">
            <h2 class="section-title">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                <circle cx="12" cy="10" r="3"/>
              </svg>
              {{ t('profile.location') }}
            </h2>
            <p class="location-address">{{ translatedAddress || user.profile?.address?.address }}</p>
          </div>
        </div>

        <div class="section-card my-listings" v-if="products.length || loadingProducts">
          <h2 class="section-title">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
              <path d="M16 7V5a4 4 0 0 0-8 0v2"/>
            </svg>
            {{ t('product.listings') }} ({{ products.length }})
          </h2>
          <div v-if="loadingProducts" class="loading">{{ t('product.loading') }}</div>
          <div v-else-if="products.length === 0" class="empty-text">{{ t('product.noProducts') }}</div>
          <div v-else class="my-products-grid">
            <div v-for="p in products" :key="p.id" class="my-product-card">
              <RouterLink :to="`/products/${p.id}`" class="my-product-thumb">
                <img v-if="getPrimaryImage(p)" :src="getPrimaryImage(p)" :alt="p.name" />
                <div v-else class="no-img-placeholder">
                  <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                  </svg>
                </div>
              </RouterLink>
              <div class="my-product-info">
                <RouterLink :to="`/products/${p.id}`" class="my-product-name">{{ p.name }}</RouterLink>
                <p class="my-product-price">${{ Number(p.price).toFixed(2) }}</p>
                <div class="my-product-actions">
                  <RouterLink :to="`/products/${p.id}/edit`" class="action-btn edit-btn">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    {{ t('auth.editProfile') }}
                  </RouterLink>
                  <button class="action-btn delete-btn" @click="confirmDelete(p)">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="3 6 5 6 21 6"/>
                      <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                    Delete
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="store-footer">
          <div v-if="otherAccounts.length" class="switch-accounts">
            <p class="switch-label">{{ t('nav.switchAccount') }}</p>
            <button
              v-for="acc in otherAccounts"
              :key="acc.id"
              class="switch-account-item"
              @click="switchAccount(acc)"
            >
              <img v-if="acc.profile?.avatar" :src="acc.profile.avatar" class="switch-avatar" alt="" />
              <span v-else class="switch-avatar switch-avatar-fallback">{{ (acc.name || '?')[0] }}</span>
              <span class="switch-name">{{ acc.name }}</span>
            </button>
          </div>
          <button class="btn btn-ghost btn-block" @click="logout">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            {{ t('nav.logout') }}
          </button>
        </div>
      </div>
    </template>

    <div v-else class="store-edit container">
      <ProfileSetupForm :redirect-on-save="false" @saved="onSaved" @cancel="editing = false" />
    </div>

    <!-- Delete Confirmation -->
    <div v-if="deletingProduct" class="modal-overlay" @click.self="deletingProduct = null">
      <div class="modal-box">
        <h3>Delete Product</h3>
        <p class="modal-desc">Are you sure you want to delete "{{ deletingProduct.name }}"? This action cannot be undone.</p>
        <div class="modal-actions">
          <button class="btn btn-ghost" @click="deletingProduct = null">{{ t('auth.cancel') }}</button>
          <button class="btn btn-danger" :disabled="deleting" @click="deleteProductConfirm">
            <span v-if="deleting" class="spinner-sm"></span>
            Delete
          </button>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { t, getLocale, localeRef } from '../i18n'
import { getUser } from '../services/auth'
import { getProducts, deleteProduct as apiDeleteProduct } from '../services/products'
import { STORAGE_URL } from '../services/http'
import ProfileSetupForm from '../components/auth/ProfileSetupForm.vue'

const router = useRouter()
const editing = ref(false)
const user = ref({})
const orderCount = ref(0)
const reviewCount = ref(0)
const translatedAddress = ref(null)
const products = ref([])
const loadingProducts = ref(true)

const deletingProduct = ref(null)
const deleting = ref(false)

const userInitial = computed(() => (user.value.name || '?').trim().charAt(0).toUpperCase())
const isVerified = computed(() => !!user.value.email_verified_at)
const memberSince = computed(() => {
  if (!user.value.created_at) return '—'
  return new Date(user.value.created_at).toLocaleDateString(undefined, { year: 'numeric', month: 'short' })
})

function getPrimaryImage(product) {
  const img = product.images?.find(i => i.is_primary) || product.images?.[0]
  return img ? `${STORAGE_URL}/storage/${img.image}` : null
}

function confirmDelete(product) {
  deletingProduct.value = product
}

async function deleteProductConfirm() {
  deleting.value = true
  try {
    await apiDeleteProduct(deletingProduct.value.id)
    products.value = products.value.filter(p => p.id !== deletingProduct.value.id)
    deletingProduct.value = null
  } catch {
    // ignore
  } finally {
    deleting.value = false
  }
}

async function loadUser() {
  try {
    const data = await getUser()
    user.value = data.data || {}
    localStorage.setItem('user', JSON.stringify(user.value))
    translateAddress()
  } catch {
    user.value = {}
  }
}

async function translateAddress() {
  const addr = user.value.profile?.address
  if (!addr?.latitude || !addr?.longitude) return
  const lang = getLocale() === 'kh' ? 'km' : getLocale()
  if (lang === 'en') { translatedAddress.value = null; return }
  try {
    const res = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${addr.latitude}&lon=${addr.longitude}&accept-language=${lang}`
    )
    const data = await res.json()
    translatedAddress.value = data.display_name || null
  } catch {
    translatedAddress.value = null
  }
}

async function loadProducts() {
  loadingProducts.value = true
  try {
    const data = await getProducts()
    const all = data.data?.data || []
    products.value = all.filter(p => p.user_id === user.value.id)
  } catch {
    products.value = []
  } finally {
    loadingProducts.value = false
  }
}

async function onSaved() {
  await loadUser()
  editing.value = false
}

const otherAccounts = computed(() => {
  const saved = JSON.parse(localStorage.getItem('saved_accounts') || '[]')
  const current = JSON.parse(localStorage.getItem('user') || '{}')
  return saved.filter(a => a.id !== current.id)
})

async function switchAccount(account) {
  const saved = JSON.parse(localStorage.getItem('saved_accounts') || '[]')
  const current = JSON.parse(localStorage.getItem('user') || '{}')
  const currentToken = localStorage.getItem('token')
  if (current.id && currentToken) {
    if (!saved.find(a => a.id === current.id)) {
      saved.push({ ...current, _token: currentToken })
    }
  }
  const updated = saved.filter(a => a.id !== account.id)
  localStorage.setItem('saved_accounts', JSON.stringify(updated))
  localStorage.setItem('user', JSON.stringify(account))
  localStorage.setItem('token', account._token)
  router.push('/')
}

function logout() {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}

onMounted(async () => {
  await loadUser()
  await loadProducts()
})

watch(localeRef, () => translateAddress())
</script>

<style scoped>
.store-page {
  padding-bottom: 2rem;
}

.store-page .container {
  max-width: var(--container)
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
  margin-top: -52px;
}

.avatar-wrap {
  position: relative;
  width: 168px;
  height: 168px;
  flex-shrink: 0;
}

.store-avatar {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid var(--surface);
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.08), 0 4px 16px rgba(0, 0, 0, 0.2);
}

.store-avatar-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--accent);
  color: #fff;
  font-size: 4rem;
  font-weight: 700;
}

.fb-meta {
  flex: 1;
  min-width: 0;
  padding-top: 0.75rem;
}

.fb-name-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.store-name {
  font-family: var(--font-serif);
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  word-break: break-word;
  color: #050505;
}

.verified-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.store-email {
  color: var(--text-muted);
  font-size: 0.9rem;
  margin: 0.2rem 0 0;
  word-break: break-word;
}

.fb-actions {
  flex-shrink: 0;
  padding-top: 0.75rem;
}

.fb-actions .btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: #e4e6eb;
  color: #050505;
  border-radius: 6px;
  box-shadow: none;
}

.fb-actions .btn:hover {
  background: #d8dadf;
}

.fb-divider {
  height: 1px;
  background: var(--border);
}

.store-body {
  margin: 0 auto;
  width: 100%;
  max-width: 1400px;
  padding: 0 1.5rem;
}

.store-stats {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 1.1rem 0;
  margin-top: 1.5rem;
}

.stat-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.2rem;
}

.stat-value {
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--text);
}

.stat-label {
  font-size: 0.75rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.stat-divider {
  width: 1px;
  height: 28px;
  background: var(--border);
}

.store-sections {
  margin-top: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.section-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 1.25rem 1.5rem;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.95rem;
  font-weight: 600;
  margin: 0 0 1rem;
  color: var(--text);
}

.section-title svg {
  color: var(--accent);
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.info-label {
  font-size: 0.78rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.info-value {
  font-size: 0.95rem;
  font-weight: 600;
  word-break: break-word;
}

.social-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.6rem;
}

.social-link {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.55rem 1rem;
  border-radius: var(--radius-md);
  text-decoration: none;
  font-size: 0.88rem;
  font-weight: 500;
  border: 1px solid var(--border);
  transition: all 0.15s;
}

.social-link:hover {
  border-color: var(--accent);
  background: var(--accent-soft);
  color: var(--accent);
}

.social-facebook:hover { color: #1877f2; border-color: #1877f2; }
.social-instagram:hover { color: #e4405f; border-color: #e4405f; }
.social-twitter:hover { color: #000; border-color: #000; }

.location-address {
  font-size: 0.95rem;
  line-height: 1.6;
  color: var(--text);
}

.store-footer {
  margin-top: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.switch-accounts {
  border-top: 1px solid var(--border);
  padding-top: 0.75rem;
}

.switch-label {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--text-muted);
  margin-bottom: 0.5rem;
}

.switch-account-item {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  width: 100%;
  padding: 0.6rem 0.5rem;
  border: none;
  background: none;
  cursor: pointer;
  border-radius: var(--radius-sm);
  transition: background 0.12s;
}

.switch-account-item:hover {
  background: var(--surface-2);
}

.switch-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  object-fit: cover;
}

.switch-avatar-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--accent);
  color: #fff;
  font-size: 0.75rem;
  font-weight: 600;
}

.switch-name {
  font-size: 0.9rem;
  color: var(--text);
}

.btn-block {
  width: 100%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
}

.store-edit {
  max-width: var(--container);
  padding: 1.5rem 1.5rem 0;
  margin: 0 auto;
}

.my-products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
}

.my-product-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  overflow: hidden;
}

.my-product-thumb {
  display: block;
  height: 140px;
  background: var(--surface-2);
  overflow: hidden;
}

.my-product-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-img-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  opacity: 0.3;
}

.my-product-info {
  padding: 0.75rem;
}

.my-product-name {
  font-size: 0.9rem;
  font-weight: 600;
  text-decoration: none;
  color: inherit;
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.my-product-name:hover {
  color: var(--accent);
}

.my-product-price {
  margin: 0.25rem 0 0;
  color: var(--accent);
  font-weight: 700;
  font-size: 0.95rem;
}

.my-product-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.6rem;
  flex-wrap: wrap;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.35rem 0.65rem;
  border-radius: var(--radius-sm);
  font-size: 0.75rem;
  font-weight: 500;
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text);
  cursor: pointer;
  transition: all 0.15s;
}

.edit-btn:hover {
  border-color: var(--accent);
  color: var(--accent);
  background: var(--accent-soft);
}

.delete-btn:hover {
  border-color: #dc3545;
  color: #dc3545;
  background: #fff5f5;
}

.empty-text {
  text-align: center;
  padding: 1.5rem;
  color: var(--text-muted);
  font-size: 0.9rem;
}

.my-listings {
  margin-top: 1.5rem;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal-box {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  width: 100%;
  max-width: 440px;
}

.modal-box h3 {
  margin: 0 0 1rem;
  font-size: 1.1rem;
  font-weight: 600;
}

.modal-desc {
  color: var(--text-muted);
  font-size: 0.9rem;
  line-height: 1.5;
  margin: 0 0 1rem;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1rem;
}

.btn-danger {
  background: #dc3545;
  color: #fff;
  border: 1px solid #dc3545;
  padding: 0.5rem 1rem;
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}

.btn-danger:hover {
  background: #c82333;
  border-color: #c82333;
}

.btn-danger:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner-sm {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 560px) {
  .my-products-grid {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  .my-product-thumb {
    height: 180px;
  }
}

@media (max-width: 560px) {
  .fb-cover {
    height: 150px;
    margin-top: 0;
    border-radius: 0;
    border-left: none;
    border-right: none;
  }

  .fb-header-inner {
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 0 1rem 1.25rem;
  }

  .fb-avatar-col {
    margin-top: -40px;
  }

  .avatar-wrap {
    width: 120px;
    height: 120px;
  }

  .store-avatar-fallback {
    font-size: 2.8rem;
  }

  .fb-meta {
    padding-top: 0;
  }

  .fb-name-row {
    justify-content: center;
  }

  .store-name {
    font-size: 1.4rem;
  }

  .fb-actions {
    width: 100%;
    padding-top: 0.25rem;
  }

  .fb-actions .btn {
    width: 100%;
    justify-content: center;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .stat-value {
    font-size: 1rem;
  }
}
</style>
