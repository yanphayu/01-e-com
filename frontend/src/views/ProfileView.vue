<template>
  <main class="container profile-page" :class="{ editing }">
    <div v-if="!editing" class="profile-card">
      <div class="profile-head">
        <img v-if="user.avatar" :src="user.avatar" class="profile-avatar" alt="" />
        <span v-else class="profile-avatar profile-avatar-fallback">{{ userInitial }}</span>
        <div class="profile-id">
          <h1>{{ user.name || t('auth.myProfile') }}</h1>
          <p class="muted">{{ user.email }}</p>
        </div>
      </div>

      <dl class="profile-info">
        <div class="info-row">
          <dt>{{ t('auth.phone') }}</dt>
          <dd>{{ user.phone || '—' }}</dd>
        </div>
        <div class="info-row">
          <dt>{{ t('auth.birthDate') }}</dt>
          <dd>{{ user.birth_date || '—' }}</dd>
        </div>
        <div class="info-row">
          <dt>{{ t('auth.address') }}</dt>
          <dd>{{ user.address || '—' }}</dd>
        </div>
        <div class="info-row">
          <dt>{{ t('auth.facebook') }}</dt>
          <dd>{{ user.facebook || '—' }}</dd>
        </div>
        <div class="info-row">
          <dt>{{ t('auth.instagram') }}</dt>
          <dd>{{ user.instagram || '—' }}</dd>
        </div>
        <div class="info-row">
          <dt>{{ t('auth.twitter') }}</dt>
          <dd>{{ user.twitter || '—' }}</dd>
        </div>
      </dl>
    </div>

    <div v-else class="profile-edit">
      <ProfileSetupForm :redirect-on-save="false" @saved="onSaved" @cancel="editing = false" />
    </div>

    <footer v-if="!editing" class="profile-footer">
      <button class="btn btn-primary btn-lg" @click="editing = true">
        {{ t('auth.editProfile') }}
      </button>
      <button class="btn btn-ghost btn-lg" @click="addNewAccount">
        {{ t('auth.addNewAccount') }}
      </button>
    </footer>
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { t } from '../i18n'
import { getUser } from '../services/auth'
import ProfileSetupForm from '../components/auth/ProfileSetupForm.vue'

const router = useRouter()
const editing = ref(false)
const user = ref({})

const userInitial = computed(() => (user.value.name || '?').trim().charAt(0).toUpperCase())

async function loadUser() {
  try {
    const data = await getUser()
    user.value = data.data || {}
    localStorage.setItem('user', JSON.stringify(user.value))
  } catch {
    user.value = {}
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
.profile-page {
  padding-top: 2rem;
  padding-bottom: 1rem;
  max-width: 640px;
}

.profile-page.editing {
  max-width: 900px;
}

.profile-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 2rem;
}

.profile-head {
  display: flex;
  align-items: center;
  gap: 1.1rem;
}

.profile-avatar {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.profile-avatar-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--accent);
  color: #fff;
  font-size: 1.7rem;
  font-weight: 600;
}

.profile-id h1 {
  font-family: var(--font-serif);
  font-size: 1.4rem;
  font-weight: 600;
  word-break: break-word;
}

.muted {
  color: var(--text-muted);
  font-size: 0.9rem;
  margin-top: 0.2rem;
  word-break: break-word;
}

.profile-info {
  margin-top: 1.75rem;
  border-top: 1px solid var(--border);
}

.info-row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.85rem 0;
  border-bottom: 1px solid var(--border);
}

.info-row:last-child {
  border-bottom: none;
}

.info-row dt {
  color: var(--text-muted);
  font-size: 0.88rem;
  flex-shrink: 0;
}

.info-row dd {
  text-align: right;
  font-weight: 600;
  word-break: break-word;
}

.profile-footer {
  position: sticky;
  bottom: 0;
  margin-top: 1.25rem;
  padding: 0.9rem 1.5rem;
  background: var(--navbar-bg);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.65rem;
}

.profile-footer .btn {
  width: 100%;
  margin: 0;
}

.profile-edit {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.profile-edit :deep(.auth-card) {
  max-width: 100%;
}

@media (max-width: 560px) {
  .profile-head {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>