<template>
  <main class="store-page" :class="{ editing }">
    <template v-if="!editing">
      <div class="store-cover">
        <img v-if="user.profile?.cover_image" :src="user.profile.cover_image" class="cover-img" alt="" />
        <div class="cover-gradient"></div>
        <label class="cover-upload" :class="{ 'is-uploading': coverUploading }">
          <input type="file" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" :disabled="coverUploading" @change="onCoverChange" />
          <svg v-if="!coverUploading" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
            <circle cx="12" cy="13" r="4"/>
          </svg>
          <span v-if="coverUploading" class="cover-spinner"></span>
          {{ coverUploading ? '' : t('profile.changeCover') }}
        </label>
        <p v-if="coverError" class="cover-error">{{ coverError }}</p>
      </div>

      <div class="store-body container">
        <div class="store-header">
          <div class="avatar-wrap">
            <img v-if="user.profile?.avatar" :src="user.profile.avatar" class="store-avatar" alt="" />
            <span v-else class="store-avatar store-avatar-fallback">{{ userInitial }}</span>
            <span v-if="isVerified" class="verified-badge" title="Verified">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="var(--accent)">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
              </svg>
            </span>
          </div>

          <div class="store-meta">
            <h1 class="store-name">{{ user.name || t('auth.myProfile') }}</h1>
            <p class="store-email">{{ user.email }}</p>
            <div v-if="user.profile?.address?.address" class="store-location">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                <circle cx="12" cy="10" r="3"/>
              </svg>
              <span>{{ user.profile.address.address }}</span>
            </div>
          </div>

          <div class="store-actions">
            <button class="btn btn-primary" @click="editing = true">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
              {{ t('auth.editProfile') }}
            </button>
          </div>
        </div>

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
            <div class="location-coords">
              <div class="coord-item">
                <span class="coord-label">{{ t('auth.latitude') }}</span>
                <span class="coord-value">{{ user.profile?.address?.latitude }}</span>
              </div>
              <div class="coord-item">
                <span class="coord-label">{{ t('auth.longitude') }}</span>
                <span class="coord-value">{{ user.profile?.address?.longitude }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="store-footer">
          <button class="btn btn-ghost btn-block" @click="addNewAccount">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="8.5" cy="7" r="4"/>
              <line x1="20" y1="8" x2="20" y2="14"/>
              <line x1="23" y1="11" x2="17" y2="11"/>
            </svg>
            {{ t('auth.addNewAccount') }}
          </button>
        </div>
      </div>
    </template>

    <div v-else class="store-edit container">
      <ProfileSetupForm :redirect-on-save="false" @saved="onSaved" @cancel="editing = false" />
    </div>
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { t } from '../i18n'
import { getUser, uploadCoverImage } from '../services/auth'
import ProfileSetupForm from '../components/auth/ProfileSetupForm.vue'

const router = useRouter()
const editing = ref(false)
const user = ref({})
const orderCount = ref(0)
const reviewCount = ref(0)
const coverUploading = ref(false)
const coverError = ref(null)

const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']

const userInitial = computed(() => (user.value.name || '?').trim().charAt(0).toUpperCase())
const isVerified = computed(() => !!user.value.email_verified_at)
const memberSince = computed(() => {
  if (!user.value.created_at) return '—'
  return new Date(user.value.created_at).toLocaleDateString(undefined, { year: 'numeric', month: 'short' })
})

async function loadUser() {
  try {
    const data = await getUser()
    user.value = data.data || {}
    localStorage.setItem('user', JSON.stringify(user.value))
  } catch {
    user.value = {}
  }
}

async function onCoverChange(e) {
  const file = e.target.files?.[0]
  if (!file) return

  coverError.value = null

  if (!ALLOWED_TYPES.includes(file.type)) {
    coverError.value = t('auth.avatarTypeInvalid')
    e.target.value = ''
    return
  }

  coverUploading.value = true

  try {
    const data = await uploadCoverImage(file)
    user.value = data.data || {}
    localStorage.setItem('user', JSON.stringify(user.value))
  } catch (err) {
    coverError.value = err.message
  } finally {
    coverUploading.value = false
    e.target.value = ''
  }
}

async function onSaved() {
  await loadUser()
  editing.value = false
}

function addNewAccount() {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/register')
}

onMounted(loadUser)
</script>

<style scoped>
.store-page {
  padding-bottom: 2rem;
}

.store-cover {
  height: 200px;
  background: var(--surface);
  border: 1px solid var(--border);
  position: relative;
  overflow: hidden;
  margin: 0 auto;
  max-width: var(--container);
  border-radius: var(--radius-lg);
  margin-top: 1rem;
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
  background: linear-gradient(180deg, transparent 50%, rgba(0, 0, 0, 0.04) 100%);
}

.cover-upload {
  position: absolute;
  top: 12px;
  right: 12px;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.4rem 0.75rem;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(8px);
  color: #fff;
  font-size: 0.78rem;
  font-weight: 500;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: background 0.15s;
}

.cover-upload:hover {
  background: rgba(0, 0, 0, 0.7);
}

.cover-upload input {
  display: none;
}

.cover-upload.is-uploading {
  cursor: wait;
  opacity: 0.7;
}

.cover-spinner {
  width: 14px;
  height: 14px;
  border: 2px solid #fff;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

.cover-error {
  position: absolute;
  bottom: 8px;
  left: 50%;
  transform: translateX(-50%);
  background: var(--danger);
  color: #fff;
  font-size: 0.75rem;
  padding: 0.3rem 0.75rem;
  border-radius: var(--radius-sm);
  margin: 0;
  white-space: nowrap;
}

.store-body {
  position: relative;
  margin-top: 0;
}

.store-header {
  display: flex;
  align-items: flex-end;
  gap: 1.25rem;
  flex-wrap: wrap;
  margin-top: 1rem;
}

.avatar-wrap {
  position: relative;
  flex-shrink: 0;
}

.store-avatar {
  width: 96px;
  height: 96px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid var(--surface);
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.store-avatar-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--accent);
  color: #fff;
  font-size: 2.2rem;
  font-weight: 700;
}

.verified-badge {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 26px;
  height: 26px;
  background: var(--surface);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.store-meta {
  flex: 1;
  min-width: 0;
  padding-bottom: 4px;
}

.store-name {
  font-family: var(--font-serif);
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
  word-break: break-word;
}

.store-email {
  color: var(--text-muted);
  font-size: 0.88rem;
  margin: 0.15rem 0 0;
  word-break: break-word;
}

.store-location {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  color: var(--text-muted);
  font-size: 0.82rem;
  margin-top: 0.35rem;
}

.store-actions {
  padding-bottom: 4px;
}

.store-actions .btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
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

.location-coords {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.coord-item {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.coord-label {
  font-size: 0.78rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.coord-value {
  font-size: 0.95rem;
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}

.store-footer {
  margin-top: 1.5rem;
}

.btn-block {
  width: 100%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
}

.store-edit {
  max-width: 700px;
  padding-top: 1.5rem;
}

.store-edit :deep(.auth-card) {
  max-width: 100%;
}

@media (max-width: 560px) {
  .store-cover {
    height: 140px;
    margin-top: 0.5rem;
    border-radius: 0;
    border-left: none;
    border-right: none;
  }

  .cover-upload span {
    display: none;
  }

  .store-avatar {
    width: 80px;
    height: 80px;
  }

  .store-avatar-fallback {
    font-size: 1.8rem;
  }

  .store-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .store-actions {
    width: 100%;
  }

  .store-actions .btn {
    width: 100%;
    justify-content: center;
  }

  .info-grid,
  .location-coords {
    grid-template-columns: 1fr;
  }

  .stat-value {
    font-size: 1rem;
  }
}
</style>
