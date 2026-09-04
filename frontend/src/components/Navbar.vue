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
          <div class="dropdown" ref="dropdownRef">
            <button class="btn btn-primary btn-sm dropdown-trigger" @click="showDropdown = !showDropdown">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
              </svg>
            </button>
            <div v-if="showDropdown" class="dropdown-menu">
              <RouterLink to="/products/create" class="dropdown-item" @click="showDropdown = false">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>
                </svg>
                {{ t('nav.postProduct') }}
              </RouterLink>
              <RouterLink to="/profile" class="dropdown-item" @click="showDropdown = false">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                {{ t('nav.myProfile') }}
              </RouterLink>
            </div>
          </div>
          <RouterLink to="/profile" class="btn btn-ghost" :title="userName">
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
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { t } from '../i18n'
import { getUser } from '../services/auth'
import { globalSearch } from '../services/products'
import LocaleSwitcher from './LocaleSwitcher.vue'

const router = useRouter()
const isAuthenticated = ref(!!localStorage.getItem('token'))
const searchQuery = ref('')
const showDropdown = ref(false)
const dropdownRef = ref(null)
const searchInputRef = ref(null)
const searchDropdownRef = ref(null)
const searchFocused = ref(false)
const searchData = ref({ products: [], users: [], categories: [], subcategories: [], brands: [], models: [], attributes: [] })
const searchLoading = ref(false)
let searchTimer = null

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
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    showDropdown.value = false
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
  padding: 0.6rem 0.9rem;
  font-size: 0.85rem;
  color: var(--text);
  text-decoration: none;
  transition: background 0.12s;
}

.dropdown-item:hover {
  background: var(--surface-2);
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
</style>
