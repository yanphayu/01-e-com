<template>
  <nav class="navbar">
    <div class="container nav-inner">
      <div class="brand-left">
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
      </div>

      <div v-if="!isProfilePage" class="search-wrapper">
        <form class="search-box" @submit.prevent="onSearch">
          <svg class="search-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input
            ref="searchInputRef"
            v-model="searchQuery"
            type="text"
            class="search-input"
            :placeholder="t('nav.search')"
          />
          <button v-if="searchQuery" type="button" class="search-clear" @click="clearSearch">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </form>
      </div>

      <div class="links">
        <LocaleSwitcher />
        <template v-if="isAuthenticated">
          <RouterLink to="/products/create" class="btn btn-primary btn-sm">
            {{ t('nav.postProduct') }}
          </RouterLink>

          <!-- Notifications -->
          <RouterLink to="/notifications" class="notif-trigger">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span v-if="unreadCount > 0" class="notif-badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
          </RouterLink>

          <!-- Favorites -->
          <RouterLink to="/favorites" class="notif-trigger">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="var(--accent)" stroke="var(--accent)" stroke-width="2">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
          </RouterLink>

          <RouterLink to="/profile" class="btn btn-ghost user-trigger">
            <span class="user-name">{{ userName }}</span>
            <img v-if="userAvatar" :src="userAvatar" class="user-avatar" alt="" />
            <span v-else class="user-avatar user-avatar-fallback">{{ userInitial }}</span>
          </RouterLink>
          <!-- Switch Account -->
          <div v-if="otherAccounts.length" class="dropdown" ref="switchDropdownRef">
            <button class="btn btn-ghost switch-trigger" @click="showSwitchDropdown = !showSwitchDropdown">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
            </button>
            <div v-if="showSwitchDropdown" class="dropdown-menu switch-menu">
              <div class="switch-label">{{ t('nav.switchAccount') }}</div>
              <button
                v-for="acc in otherAccounts"
                :key="acc.id"
                class="dropdown-item switch-item"
                @click="switchAccount(acc)"
              >
                <img v-if="acc.profile?.avatar" :src="acc.profile.avatar" class="switch-avatar" alt="" />
                <span v-else class="switch-avatar switch-avatar-fallback">{{ (acc.name || '?')[0] }}</span>
                <span class="switch-name">{{ acc.name }}</span>
              </button>
            </div>
          </div>
        </template>
        <template v-else>
          <RouterLink to="/login" class="btn btn-ghost">{{ t('nav.login') }}</RouterLink>
          <RouterLink to="/register" class="btn btn-primary">{{ t('nav.register') }}</RouterLink>
        </template>
      </div>

      <!-- Mobile search icon -->
      <RouterLink v-if="!isProfilePage && !isSearchPage" to="/search" class="mobile-search-btn">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
      </RouterLink>

    </div>
  </nav>

  <!-- Bottom tab bar (mobile/tablet) -->
  <nav v-if="isAuthenticated" class="bottom-bar">
    <RouterLink to="/" class="bottom-tab" :class="{ active: route.path === '/' }">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
      </svg>
      <span>{{ t('nav.home') }}</span>
    </RouterLink>
    <RouterLink to="/products/create" class="bottom-tab" :class="{ active: route.path === '/products/create' }">
      <span class="bottom-tab-plus">+</span>
      <span>{{ t('nav.postProduct') }}</span>
    </RouterLink>
    <RouterLink to="/notifications" class="bottom-tab" :class="{ active: route.path === '/notifications' }">
      <span class="bottom-tab-icon-wrap">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        <span v-if="unreadCount > 0" class="bottom-notif-badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
      </span>
      <span>{{ t('nav.notifications') }}</span>
    </RouterLink>
    <RouterLink to="/profile" class="bottom-tab" :class="{ active: route.path === '/profile' }">
      <img v-if="userAvatar" :src="userAvatar" class="bottom-tab-avatar" alt="" />
      <span v-else class="bottom-tab-avatar bottom-tab-avatar-fallback">{{ userInitial }}</span>
      <span>{{ t('nav.myProfile') }}</span>
    </RouterLink>
  </nav>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { t } from '../i18n'
import { getUser } from '../services/auth'
import { getUnreadCount } from '../services/notifications'
import { initEcho, leaveEcho } from '../services/echo'
import LocaleSwitcher from './LocaleSwitcher.vue'

const router = useRouter()
const route = useRoute()
const isProfilePage = computed(() => route.name === 'profile')
const isSearchPage = computed(() => route.name === 'search')
const isAuthenticated = ref(!!localStorage.getItem('token'))
const searchQuery = ref('')
const showSwitchDropdown = ref(false)
const switchDropdownRef = ref(null)
const searchInputRef = ref(null)

const unreadCount = ref(0)

const user = ref(readStoredUser())
const userName = computed(() => user.value.name || '')
const userAvatar = computed(() => user.value.profile?.avatar || '')
const userInitial = computed(() => (userName.value || '?').trim().charAt(0).toUpperCase())

function readStoredUser() {
  try {
    return JSON.parse(localStorage.getItem('user') || '{}')
  } catch {
    return {}
  }
}

onMounted(() => {
  refreshAuth()
  window.addEventListener('auth-changed', refreshAuth)
})

onUnmounted(() => {
  leaveEcho()
  window.removeEventListener('notifications-read', loadUnreadCount)
  window.removeEventListener('auth-changed', refreshAuth)
})

async function refreshAuth() {
  isAuthenticated.value = !!localStorage.getItem('token')

  if (!isAuthenticated.value) {
    user.value = {}
    return
  }

  try {
    const data = await getUser()
    user.value = data.data || {}
    localStorage.setItem('user', JSON.stringify(user.value))
    loadUnreadCount()
    try { connectEcho() } catch {}
    window.addEventListener('notifications-read', loadUnreadCount)
  } catch {
    logout()
  }
}

function connectEcho() {
  const echo = initEcho(user.value.id)
  if (!echo) return

  echo.private(`App.Models.User.${user.value.id}`)
    .notification(() => {
      unreadCount.value++
    })
}

async function loadUnreadCount() {
  try {
    const count = await getUnreadCount()
    unreadCount.value = count.data?.count || 0
  } catch {}
}

function logout() {
  const current = JSON.parse(localStorage.getItem('user') || '{}')
  const token = localStorage.getItem('token')
  if (current.id && token) {
    const saved = JSON.parse(localStorage.getItem('saved_accounts') || '[]')
    if (!saved.find(a => a.id === current.id)) {
      saved.push({ ...current, _token: token })
      localStorage.setItem('saved_accounts', JSON.stringify(saved))
    }
  }
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  isAuthenticated.value = false
  user.value = {}
  router.push('/login')
}

const otherAccounts = computed(() => {
  const saved = JSON.parse(localStorage.getItem('saved_accounts') || '[]')
  return saved.filter(a => a.id !== user.value.id)
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
  showSwitchDropdown.value = false

  try {
    const data = await getUser()
    user.value = data.data || {}
    localStorage.setItem('user', JSON.stringify(user.value))
    loadUnreadCount()
  } catch {
    user.value = account
  }

  router.push('/')
}

function onSearch() {
  const q = searchQuery.value.trim()
  if (!q) return
  router.push({ path: '/products', query: { q } })
  searchQuery.value = ''
}

function clearSearch() {
  searchQuery.value = ''
  searchInputRef.value?.focus()
}

function handleClickOutside(e) {
  if (switchDropdownRef.value && !switchDropdownRef.value.contains(e.target)) {
    showSwitchDropdown.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
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

.brand-left {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.links {
  display: flex;
  gap: 0.6rem;
  align-items: center;
}

.search-wrapper {
  position: relative;
  flex: 1;
  max-width: 320px;
  margin: 0 1.5rem;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  width: 100%;
  padding: 0.4rem 0.75rem;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  background: var(--surface);
  transition: border-color 0.15s;
}

.search-box:focus-within {
  border-color: var(--accent);
}

.search-icon {
  color: var(--text-muted);
  flex-shrink: 0;
}

.search-input {
  border: none;
  outline: none;
  background: transparent;
  font-size: 0.85rem;
  color: var(--text);
  width: 100%;
  padding: 0;
}

.search-input::placeholder {
  color: var(--text-muted);
}

.search-clear {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0;
  flex-shrink: 0;
}

.search-clear:hover {
  color: var(--text);
}

.links .btn {
  padding: 0.4rem 0.85rem;
  font-size: 0.85rem;
}

.dropdown {
  position: relative;
}

.dropdown-trigger {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.4rem !important;
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 6px);
  right: 0;
  min-width: 180px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-md);
  z-index: 100;
  overflow: hidden;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.6rem 0.9rem;
  border: none;
  background: transparent;
  color: var(--text);
  font: inherit;
  font-size: 0.85rem;
  text-decoration: none;
  cursor: pointer;
  text-align: left;
  transition: background 0.12s;
}

.dropdown-item:hover {
  background: var(--surface-2);
}

.dropdown-item.danger {
  color: var(--danger);
}

.dropdown-item.danger:hover {
  background: var(--danger-soft);
}

.switch-trigger {
  padding: 0.45rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface);
  color: var(--text-muted);
  cursor: pointer;
  transition: border-color 0.15s;
}

.switch-trigger:hover {
  border-color: var(--border-strong);
  color: var(--text);
}

.switch-menu {
  min-width: 180px;
}

.switch-label {
  padding: 0.5rem 0.9rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  border-bottom: 1px solid var(--border);
}

.switch-item {
  gap: 0.6rem;
}

.switch-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.switch-avatar-fallback {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: var(--accent);
  color: #fff;
  font-size: 0.75rem;
  font-weight: 600;
}

.switch-name {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
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
  width: 20px;
  height: 20px;
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

.user-trigger {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.45rem 0.85rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface);
  cursor: pointer;
  transition: border-color 0.15s;
}

.user-trigger:hover {
  border-color: var(--border-strong);
}

.notif-trigger {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.45rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface);
  color: var(--text-muted);
  cursor: pointer;
  text-decoration: none;
  transition: border-color 0.15s, color 0.15s;
}

.notif-trigger:hover {
  border-color: var(--border-strong);
  color: var(--text);
}

.notif-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  min-width: 16px;
  height: 16px;
  padding: 0 4px;
  border-radius: 999px;
  background: var(--danger);
  color: #fff;
  font-size: 0.65rem;
  font-weight: 700;
  line-height: 16px;
  text-align: center;
}

.mobile-search-btn {
  display: none;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border: none;
  border-radius: var(--radius-sm);
  background: transparent;
  color: var(--text-muted);
  text-decoration: none;
  transition: color 0.15s;
}

.mobile-search-btn:hover {
  color: var(--text);
}

@media (max-width: 768px) {
  .links {
    display: none;
  }
  .search-wrapper {
    display: none;
  }
  .mobile-search-btn {
    display: inline-flex;
  }
}

@media (max-width: 480px) {
  .user-name {
    display: none;
  }
  .brand-name {
    display: none;
  }
  .search-wrapper {
    margin: 0 0.5rem;
    max-width: none;
  }
}

/* Bottom tab bar */
.bottom-bar {
  display: none;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 50;
  background: var(--navbar-bg);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border-top: 1px solid var(--border);
  padding: 0.35rem 0 max(0.35rem, env(safe-area-inset-bottom));
}

.bottom-tab {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.15rem;
  flex: 1;
  padding: 0.35rem 0;
  text-decoration: none;
  color: var(--text-muted);
  font-size: 0.65rem;
  font-weight: 500;
  transition: color 0.15s;
  -webkit-tap-highlight-color: transparent;
}

.bottom-tab.active {
  color: var(--accent);
}

.bottom-tab-plus {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: var(--accent);
  color: #fff;
  font-size: 1.2rem;
  font-weight: 300;
  line-height: 1;
}

.bottom-tab-icon-wrap {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 26px;
}

.bottom-notif-badge {
  position: absolute;
  top: -4px;
  right: -8px;
  min-width: 15px;
  height: 15px;
  padding: 0 4px;
  border-radius: 999px;
  background: var(--danger);
  color: #fff;
  font-size: 0.6rem;
  font-weight: 700;
  line-height: 15px;
  text-align: center;
}

.bottom-tab-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  object-fit: cover;
}

.bottom-tab-avatar-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: var(--accent);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 600;
}

@media (max-width: 768px) {
  .bottom-bar {
    display: flex;
  }
}
</style>
