<template>
  <nav class="navbar">
    <div class="container nav-inner">
      <RouterLink to="/" class="brand">
        <svg class="brand-mark" viewBox="138.8 116 322.4 283" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="TRINITY">
          <path d="M 300 130 L 259.5 276.6 L 152.8 385 L 300 346.8 L 447.2 385 L 340.5 276.6 Z" stroke="currentColor" stroke-width="26" stroke-linejoin="miter" stroke-miterlimit="10" />
          <path d="M 259.5 276.6 L 300 346.8 L 340.5 276.6 Z" fill="currentColor" />
          <circle cx="300" cy="130" r="9" fill="currentColor" />
          <circle cx="152.8" cy="385" r="9" fill="currentColor" />
          <circle cx="447.2" cy="385" r="9" fill="currentColor" />
        </svg>
        <span class="brand-name">TRINITY</span>
      </RouterLink>

      <div class="links">
        <LocaleSwitcher />
        <template v-if="isAuthenticated">
          <RouterLink to="/profile" class="user-chip" :title="userName">
            <span class="user-name">{{ userName }}</span>
            <img v-if="userAvatar" :src="userAvatar" class="user-avatar" alt="" />
            <span v-else class="user-avatar user-avatar-fallback">{{ userInitial }}</span>
          </RouterLink>
          <button class="btn btn-ghost" @click="logout">{{ t('nav.logout') }}</button>
        </template>
        <template v-else>
          <RouterLink to="/login" class="btn btn-ghost">{{ t('nav.login') }}</RouterLink>
          <RouterLink to="/register" class="btn btn-primary">{{ t('nav.register') }}</RouterLink>
        </template>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { t } from '../i18n'
import { getUser } from '../services/auth'
import LocaleSwitcher from './LocaleSwitcher.vue'

const router = useRouter()
const isAuthenticated = ref(!!localStorage.getItem('token'))

const user = ref(readStoredUser())
const userName = computed(() => user.value.name || '')
const userAvatar = computed(() => user.value.avatar || '')
const userInitial = computed(() => (userName.value || '?').trim().charAt(0).toUpperCase())

function readStoredUser() {
  try {
    return JSON.parse(localStorage.getItem('user') || '{}')
  } catch {
    return {}
  }
}

onMounted(async () => {
  if (!isAuthenticated.value) return
  try {
    const data = await getUser()
    user.value = data.data || {}
    localStorage.setItem('user', JSON.stringify(user.value))
  } catch {
    logout()
  }
})

function logout() {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  isAuthenticated.value = false
  user.value = {}
  router.push('/login')
}
</script>

<style scoped>
.navbar {
  position: sticky;
  top: 0;
  z-index: 50;
  background: var(--navbar-bg);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border-bottom: 1px solid var(--border);
}

.nav-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 0.85rem;
  padding-bottom: 0.85rem;
}

.brand {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  text-decoration: none;
  color: var(--text);
  font-family: var(--font-serif);
  font-weight: 600;
  font-size: 1.3rem;
  letter-spacing: 0.14em;
}

.brand-mark {
  width: 26px;
  height: 26px;
  color: var(--accent);
}

.links {
  display: flex;
  gap: 0.6rem;
  align-items: center;
}

.links .btn {
  padding: 0.4rem 0.85rem;
  font-size: 0.85rem;
}

.link {
  text-decoration: none;
  color: var(--text-muted);
  font-weight: 500;
  font-size: 0.95rem;
  padding: 0.5rem 0.85rem;
  border-radius: var(--radius-sm);
}

.link:hover {
  color: var(--text);
  background: rgba(28, 27, 25, 0.04);
}

.link.router-link-active {
  color: var(--accent);
  background: var(--accent-soft);
}

.user-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  text-decoration: none;
  color: var(--text);
  padding: 0.2rem 0.2rem 0.2rem 0.6rem;
  border-radius: 999px;
  border: 1px solid var(--border);
  background: var(--navbar-bg);
  transition: border-color 0.15s;
}

.user-chip:hover {
  border-color: var(--accent);
}

.user-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  object-fit: cover;
}

.user-avatar-fallback {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: var(--accent);
  color: #fff;
  font-weight: 600;
  font-size: 0.9rem;
}

.user-name {
  font-size: 0.85rem;
  font-weight: 600;
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

@media (max-width: 480px) {
  .user-name {
    display: none;
  }
}

@media (max-width: 480px) {
  .brand-name {
    display: none;
  }
}
</style>
