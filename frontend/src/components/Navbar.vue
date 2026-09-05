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

      <div class="search-wrapper" ref="searchDropdownRef">
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
            @input="onSearchInput"
            @focus="searchFocused = true"
          />
          <button v-if="searchQuery" type="button" class="search-clear" @click="clearSearch">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </form>
        <div v-if="searchFocused && searchQuery.length >= 2" class="search-dropdown">
        <div v-if="searchLoading" class="search-dropdown-loading">{{ t('product.loading') }}</div>
        <template v-else>
          <template v-if="hasResults">
            <div v-if="searchData.products.length" class="search-group">
              <div class="search-group-label">{{ t('nav.products') }}</div>
              <RouterLink
                v-for="item in searchData.products"
                :key="'p'+item.id"
                :to="`/products/${item.id}`"
                class="search-dropdown-item"
                @click="closeSearch"
              >{{ item.name }}</RouterLink>
            </div>
            <div v-if="searchData.users.length" class="search-group">
              <div class="search-group-label">{{ t('nav.users') }}</div>
              <RouterLink
                v-for="item in searchData.users"
                :key="'u'+item.id"
                :to="`/users/${item.id}`"
                class="search-dropdown-item"
                @click="closeSearch"
              >{{ item.name }}</RouterLink>
            </div>
            <div v-if="searchData.categories.length" class="search-group">
              <div class="search-group-label">{{ t('nav.categories') }}</div>
              <RouterLink
                v-for="item in searchData.categories"
                :key="'c'+item.id"
                :to="`/products?category_id=${item.id}`"
                class="search-dropdown-item"
                @click="closeSearch"
              >{{ item.name }}</RouterLink>
            </div>
            <div v-if="searchData.subcategories.length" class="search-group">
              <div class="search-group-label">{{ t('nav.subcategories') }}</div>
              <RouterLink
                v-for="item in searchData.subcategories"
                :key="'s'+item.id"
                :to="`/products?category_id=${item.category_id}&subcategory_id=${item.id}`"
                class="search-dropdown-item"
                @click="closeSearch"
              >{{ item.name }}</RouterLink>
            </div>
            <div v-if="searchData.brands.length" class="search-group">
              <div class="search-group-label">{{ t('nav.brands') }}</div>
              <RouterLink
                v-for="item in searchData.brands"
                :key="'b'+item.id"
                :to="`/products?q=${item.name}`"
                class="search-dropdown-item"
                @click="closeSearch"
              >{{ item.name }}</RouterLink>
            </div>
            <div v-if="searchData.models.length" class="search-group">
              <div class="search-group-label">{{ t('nav.models') }}</div>
              <RouterLink
                v-for="item in searchData.models"
                :key="'m'+item.id"
                :to="`/products?q=${item.name}`"
                class="search-dropdown-item"
                @click="closeSearch"
              >{{ item.name }}</RouterLink>
            </div>
            <div v-if="searchData.attributes.length" class="search-group">
              <div class="search-group-label">{{ t('nav.attributes') }}</div>
              <RouterLink
                v-for="item in searchData.attributes"
                :key="'a'+item.id"
                :to="`/products?q=${item.name}`"
                class="search-dropdown-item"
                @click="closeSearch"
              >{{ item.name }}</RouterLink>
            </div>
          </template>
          <div v-if="!hasResults && searchQuery.length >= 2" class="search-dropdown-empty">
            {{ t('product.noProducts') }}
          </div>
          <button v-if="searchQuery.trim()" class="search-dropdown-all" @click="onSearch">
            {{ t('nav.searchAll') }} "{{ searchQuery }}"
          </button>
        </template>
        </div>
      </div>

      <div class="links">
        <LocaleSwitcher />
        <template v-if="isAuthenticated">
          <RouterLink to="/products/create" class="btn btn-primary btn-sm">
            {{ t('nav.postProduct') }}
          </RouterLink>

          <!-- Notifications -->
          <div class="dropdown" ref="notifDropdownRef">
            <button class="notif-trigger" @click="showNotifDropdown = !showNotifDropdown">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
              <span v-if="unreadCount > 0" class="notif-badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
            </button>
            <div v-if="showNotifDropdown" class="dropdown-menu notif-menu">
              <div class="notif-header">
                <span>{{ t('nav.notifications') }}</span>
                <button v-if="unreadCount > 0" class="notif-mark-all" @click="markAllNotifsRead">{{ t('nav.markAllRead') }}</button>
              </div>
              <div v-if="notifications.length === 0" class="notif-empty">{{ t('nav.noNotifications') }}</div>
              <div v-else class="notif-list">
                <div
                  v-for="n in notifications"
                  :key="n.id"
                  class="notif-item"
                  :class="{ unread: !n.read_at }"
                  @click="onNotifClick(n)"
                >
                  <img v-if="n.data.user?.avatar" :src="n.data.user.avatar" class="notif-avatar" alt="" />
                  <span v-else class="notif-avatar notif-avatar-fallback">{{ (n.data.user?.name || '?')[0] }}</span>
                  <div class="notif-content">
                    <p class="notif-text">
                      <strong>{{ n.data.user?.name }}</strong>
                      {{ t('nav.commentedOn') }}
                      <strong>{{ n.data.product_name }}</strong>
                    </p>
                    <span class="notif-time">{{ formatNotifTime(n.created_at) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="dropdown" ref="userDropdownRef">
            <button class="btn btn-ghost user-trigger" @click="showUserDropdown = !showUserDropdown">
              <span class="user-name">{{ userName }}</span>
              <img v-if="userAvatar" :src="userAvatar" class="user-avatar" alt="" />
              <span v-else class="user-avatar user-avatar-fallback">{{ userInitial }}</span>
            </button>
            <div v-if="showUserDropdown" class="dropdown-menu">
              <RouterLink to="/profile" class="dropdown-item" @click="showUserDropdown = false">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                {{ t('nav.myProfile') }}
              </RouterLink>
              <button class="dropdown-item danger" @click="logout">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                {{ t('nav.logout') }}
              </button>
            </div>
          </div>
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
    </div>
  </nav>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { t } from '../i18n'
import { getUser } from '../services/auth'
import { globalSearch } from '../services/products'
import { getNotifications, getUnreadCount, markAsRead, markAllAsRead } from '../services/notifications'
import { initEcho, leaveEcho } from '../services/echo'
import LocaleSwitcher from './LocaleSwitcher.vue'

const router = useRouter()
const isAuthenticated = ref(!!localStorage.getItem('token'))
const searchQuery = ref('')
const showUserDropdown = ref(false)
const userDropdownRef = ref(null)
const showSwitchDropdown = ref(false)
const switchDropdownRef = ref(null)
const searchInputRef = ref(null)
const searchDropdownRef = ref(null)
const searchFocused = ref(false)
const searchData = ref({ products: [], users: [], categories: [], subcategories: [], brands: [], models: [], attributes: [] })
const searchLoading = ref(false)
let searchTimer = null

const showNotifDropdown = ref(false)
const notifDropdownRef = ref(null)
const notifications = ref([])
const unreadCount = ref(0)
let notifPollTimer = null

const hasResults = computed(() => {
  const d = searchData.value
  return d.products.length || d.users.length || d.categories.length || d.subcategories.length || d.brands.length || d.models.length || d.attributes.length
})

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

onMounted(async () => {
  if (!isAuthenticated.value) return
  try {
    const data = await getUser()
    user.value = data.data || {}
    localStorage.setItem('user', JSON.stringify(user.value))
    loadNotifications()
    startNotifPolling()
    connectEcho()
  } catch {
    logout()
  }
})

onUnmounted(() => {
  clearInterval(notifPollTimer)
  leaveEcho()
})

function connectEcho() {
  const echo = initEcho(user.value.id)
  if (!echo) return

  echo.private(`App.Models.User.${user.value.id}`)
    .notification((notification) => {
      unreadCount.value++
      notifications.value.unshift({
        id: notification.id,
        data: notification,
        read_at: null,
        created_at: notification.created_at,
      })
    })
}

async function loadNotifications() {
  try {
    const [notifs, count] = await Promise.all([getNotifications(), getUnreadCount()])
    notifications.value = notifs.data || []
    unreadCount.value = count.data?.count || 0
  } catch {}
}

function startNotifPolling() {
  notifPollTimer = setInterval(loadNotifications, 30000)
}

async function onNotifClick(n) {
  if (!n.read_at) {
    await markAsRead(n.id)
    n.read_at = new Date().toISOString()
    unreadCount.value = Math.max(0, unreadCount.value - 1)
  }
  showNotifDropdown.value = false
  if (n.data.product_id) {
    router.push(`/products/${n.data.product_id}`)
  }
}

async function markAllNotifsRead() {
  await markAllAsRead()
  notifications.value.forEach(n => { n.read_at = new Date().toISOString() })
  unreadCount.value = 0
}

function formatNotifTime(dateStr) {
  const now = new Date()
  const date = new Date(dateStr)
  const diff = Math.floor((now - date) / 1000)
  if (diff < 60) return 'just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h`
  return `${Math.floor(diff / 86400)}d`
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

function switchAccount(account) {
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
  user.value = account
  showSwitchDropdown.value = false
  window.location.reload()
}

function onSearch() {
  const q = searchQuery.value.trim()
  if (!q) return
  closeSearch()
  router.push({ path: '/products', query: { q } })
  searchQuery.value = ''
}

function onSearchInput() {
  clearTimeout(searchTimer)
  const q = searchQuery.value.trim()
  if (q.length < 2) {
    searchData.value = { products: [], users: [], categories: [], subcategories: [], brands: [], models: [], attributes: [] }
    return
  }
  searchLoading.value = true
  searchTimer = setTimeout(async () => {
    try {
      const res = await globalSearch(q)
      searchData.value = res.data || { products: [], users: [], categories: [], subcategories: [], brands: [], models: [], attributes: [] }
    } catch {
      searchData.value = { products: [], users: [], categories: [], subcategories: [], brands: [], models: [], attributes: [] }
    } finally {
      searchLoading.value = false
    }
  }, 300)
}

function clearSearch() {
  searchQuery.value = ''
  searchData.value = { products: [], users: [], categories: [], subcategories: [], brands: [], models: [], attributes: [] }
}

function closeSearch() {
  searchFocused.value = false
  searchData.value = { products: [], users: [], categories: [], subcategories: [], brands: [], models: [], attributes: [] }
}

function handleClickOutside(e) {
  if (userDropdownRef.value && !userDropdownRef.value.contains(e.target)) {
    showUserDropdown.value = false
  }
  if (switchDropdownRef.value && !switchDropdownRef.value.contains(e.target)) {
    showSwitchDropdown.value = false
  }
  if (notifDropdownRef.value && !notifDropdownRef.value.contains(e.target)) {
    showNotifDropdown.value = false
  }
  if (searchDropdownRef.value && !searchDropdownRef.value.contains(e.target) && searchInputRef.value && !searchInputRef.value.contains(e.target)) {
    closeSearch()
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
  border-radius: 999px;
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

.search-dropdown {
  position: absolute;
  top: calc(100% + 4px);
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: 380px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-md);
  z-index: 100;
  overflow: hidden;
}

.search-dropdown-loading {
  padding: 1rem;
  text-align: center;
  color: var(--text-muted);
  font-size: 0.85rem;
}

.search-dropdown-item {
  display: block;
  padding: 0.5rem 0.8rem;
  font-size: 0.85rem;
  text-decoration: none;
  color: var(--text);
  transition: background 0.12s;
}

.search-dropdown-item:hover {
  background: var(--surface-2);
}

.search-dropdown-empty {
  padding: 1rem;
  text-align: center;
  color: var(--text-muted);
  font-size: 0.85rem;
}

.search-group {
  padding: 0.25rem 0;
}

.search-group:not(:last-child) {
  border-bottom: 1px solid var(--border);
}

.search-group-label {
  padding: 0.35rem 0.8rem;
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--text-muted);
}

.search-dropdown-all {
  display: block;
  width: 100%;
  padding: 0.6rem 0.8rem;
  text-align: center;
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--accent);
  background: var(--accent-soft);
  border: none;
  border-top: 1px solid var(--border);
  cursor: pointer;
  transition: background 0.12s;
}

.search-dropdown-all:hover {
  background: var(--accent);
  color: #fff;
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

@media (max-width: 480px) {
  .user-name {
    display: none;
  }
}

@media (max-width: 480px) {
  .brand-name {
    display: none;
  }
  .search-wrapper {
    margin: 0 0.75rem;
    max-width: none;
  }
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

.notif-menu {
  width: 340px;
  max-height: 400px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.notif-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.6rem 0.9rem;
  font-weight: 600;
  font-size: 0.88rem;
  border-bottom: 1px solid var(--border);
}

.notif-mark-all {
  background: none;
  border: none;
  color: var(--accent);
  font: inherit;
  font-size: 0.78rem;
  font-weight: 500;
  cursor: pointer;
}

.notif-mark-all:hover {
  text-decoration: underline;
}

.notif-empty {
  padding: 1.5rem;
  text-align: center;
  color: var(--text-muted);
  font-size: 0.85rem;
}

.notif-list {
  overflow-y: auto;
}

.notif-item {
  display: flex;
  gap: 0.6rem;
  padding: 0.65rem 0.9rem;
  cursor: pointer;
  transition: background 0.12s;
}

.notif-item:hover {
  background: var(--surface-2);
}

.notif-item.unread {
  background: var(--accent-soft);
}

.notif-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.notif-avatar-fallback {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: var(--accent);
  color: #fff;
  font-size: 0.72rem;
  font-weight: 600;
  width: 32px;
  height: 32px;
  border-radius: 50%;
}

.notif-content {
  flex: 1;
  min-width: 0;
}

.notif-text {
  font-size: 0.82rem;
  line-height: 1.4;
  margin: 0;
  color: var(--text);
}

.notif-text strong {
  font-weight: 600;
}

.notif-time {
  font-size: 0.72rem;
  color: var(--text-muted);
}
</style>
