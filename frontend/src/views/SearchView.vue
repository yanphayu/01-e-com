<template>
  <div class="search-page">
    <div class="search-header">
      <button class="back-btn" @click="router.back()">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
        </svg>
      </button>
      <form class="search-form" @submit.prevent="onSearch">
        <div class="search-input-wrap">
          <svg class="search-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input
            ref="searchInput"
            v-model="query"
            type="text"
            class="search-input"
            :placeholder="t('nav.search')"
            @input="onInput"
            @focus="onFocus"
          />
          <button v-if="query" type="button" class="search-clear" @click="clearSearch">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
      </form>
    </div>

    <!-- Category filter buttons -->
    <div v-if="categories.length" class="filter-section">
      <div class="filter-scroll">
        <button
          class="filter-btn"
          :class="{ active: !selectedCategory }"
          @click="selectCategory(null)"
        >{{ t('nav.all') }}</button>
        <button
          v-for="cat in categories"
          :key="cat.id"
          class="filter-btn"
          :class="{ active: selectedCategory === cat.id }"
          @click="selectCategory(cat.id)"
        >{{ cat.name }}</button>
      </div>
      <div v-if="selectedCategory && selectedSubcategories.length" class="filter-scroll sub-scroll">
        <button
          class="filter-btn sub-btn"
          :class="{ active: !selectedSubcategory }"
          @click="selectSubcategory(null)"
        >{{ t('nav.all') }}</button>
        <button
          v-for="sub in selectedSubcategories"
          :key="sub.id"
          class="filter-btn sub-btn"
          :class="{ active: selectedSubcategory === sub.id }"
          @click="selectSubcategory(sub.id)"
        >{{ sub.name }}</button>
      </div>
    </div>

    <!-- Search history (when no query and no category selected) -->
    <div v-if="!query && !selectedCategory && searchHistory.length" class="history-section">
      <div class="history-header">
        <span class="section-label">{{ t('nav.recentSearches') }}</span>
        <button class="clear-history-btn" @click="clearHistory">{{ t('nav.clear') }}</button>
      </div>
      <div class="history-list">
        <button
          v-for="(item, i) in searchHistory"
          :key="i"
          class="history-item"
          @click="applyHistory(item)"
        >
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
          </svg>
          <span>{{ item.query || item.name }}</span>
        </button>
      </div>
    </div>

    <!-- Search results -->
    <div v-if="query.length >= 2" class="search-results">
      <div v-if="searchLoading" class="loading">{{ t('product.loading') }}</div>
      <template v-else>
        <div v-if="hasResults">
          <div v-if="filteredResults.products.length" class="result-group">
            <div class="result-group-label">{{ t('nav.products') }}</div>
            <RouterLink
              v-for="item in filteredResults.products"
              :key="'p'+item.id"
              :to="`/products/${item.id}`"
              class="result-item"
              @click="saveToHistory({ query, type: 'product', id: item.id, name: item.name })"
            >{{ item.name }}</RouterLink>
          </div>
          <div v-if="filteredResults.categories.length" class="result-group">
            <div class="result-group-label">{{ t('nav.categories') }}</div>
            <RouterLink
              v-for="item in filteredResults.categories"
              :key="'c'+item.id"
              :to="`/products?category_id=${item.id}`"
              class="result-item"
              @click="saveToHistory({ query, type: 'category', id: item.id, name: item.name })"
            >{{ item.name }}</RouterLink>
          </div>
          <div v-if="filteredResults.subcategories.length" class="result-group">
            <div class="result-group-label">{{ t('nav.subcategories') }}</div>
            <RouterLink
              v-for="item in filteredResults.subcategories"
              :key="'s'+item.id"
              :to="`/products?category_id=${item.category_id}&subcategory_id=${item.id}`"
              class="result-item"
              @click="saveToHistory({ query, type: 'subcategory', id: item.id, name: item.name })"
            >{{ item.name }}</RouterLink>
          </div>
          <div v-if="filteredResults.brands.length" class="result-group">
            <div class="result-group-label">{{ t('nav.brands') }}</div>
            <RouterLink
              v-for="item in filteredResults.brands"
              :key="'b'+item.id"
              :to="`/products?q=${item.name}`"
              class="result-item"
              @click="saveToHistory({ query, type: 'brand', id: item.id, name: item.name })"
            >{{ item.name }}</RouterLink>
          </div>
          <div v-if="filteredResults.models.length" class="result-group">
            <div class="result-group-label">{{ t('nav.models') }}</div>
            <RouterLink
              v-for="item in filteredResults.models"
              :key="'m'+item.id"
              :to="`/products?q=${item.name}`"
              class="result-item"
              @click="saveToHistory({ query, type: 'model', id: item.id, name: item.name })"
            >{{ item.name }}</RouterLink>
          </div>
          <div v-if="filteredResults.users.length" class="result-group">
            <div class="result-group-label">{{ t('nav.users') }}</div>
            <RouterLink
              v-for="item in filteredResults.users"
              :key="'u'+item.id"
              :to="`/users/${item.id}`"
              class="result-item"
            >{{ item.name }}</RouterLink>
          </div>
        </div>
        <div v-else class="empty">{{ t('product.noProducts') }}</div>
      </template>
    </div>

    <!-- Categories list (when no search, no category selected) -->
    <div v-if="!query && !selectedCategory" class="categories-section">
      <h2 class="section-title">{{ t('nav.categories') }}</h2>
      <div v-if="loadingCategories" class="loading">{{ t('product.loading') }}</div>
      <div v-else class="categories-list">
        <div v-for="cat in categories" :key="cat.id" class="category-block">
          <button class="category-name" @click="selectCategory(cat.id)">
            <span>{{ cat.name }}</span>
            <span class="category-count" v-if="cat.subcategories?.length">{{ cat.subcategories.length }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { t } from '../i18n'
import { getCategories, globalSearch } from '../services/products'

const router = useRouter()
const searchInput = ref(null)
const query = ref('')
const results = ref({ products: [], users: [], categories: [], subcategories: [], brands: [], models: [], attributes: [] })
const searchLoading = ref(false)
const categories = ref([])
const loadingCategories = ref(true)
const selectedCategory = ref(null)
const selectedSubcategory = ref(null)
const searchHistory = ref([])
const STORAGE_KEY = 'search_history'
let searchTimer = null

const hasResults = computed(() => {
  const d = results.value
  return d.products.length || d.users.length || d.categories.length || d.subcategories.length || d.brands.length || d.models.length
})

const selectedSubcategories = computed(() => {
  if (!selectedCategory.value) return []
  const cat = categories.value.find(c => c.id === selectedCategory.value)
  return cat?.subcategories || []
})

const filteredResults = computed(() => {
  const d = results.value
  if (!selectedCategory.value) return d
  return {
    ...d,
    products: d.products.filter(p => p.subcategory?.category_id === selectedCategory.value),
    subcategories: d.subcategories.filter(s => s.category_id === selectedCategory.value),
  }
})

onMounted(async () => {
  loadHistory()
  await nextTick()
  searchInput.value?.focus()
  try {
    const data = await getCategories()
    categories.value = data.data || []
  } catch {
    categories.value = []
  } finally {
    loadingCategories.value = false
  }
})

function loadHistory() {
  try {
    searchHistory.value = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]')
  } catch {
    searchHistory.value = []
  }
}

function saveHistory() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(searchHistory.value))
}

function saveToHistory(item) {
  const exists = searchHistory.value.findIndex(h => h.query === item.query && h.type === item.type && h.id === item.id)
  if (exists !== -1) searchHistory.value.splice(exists, 1)
  searchHistory.value.unshift(item)
  if (searchHistory.value.length > 20) searchHistory.value.pop()
  saveHistory()
}

function clearHistory() {
  searchHistory.value = []
  saveHistory()
}

function applyHistory(item) {
  if (item.query) {
    query.value = item.query
    onInput()
  }
}

function onInput() {
  clearTimeout(searchTimer)
  const q = query.value.trim()
  if (q.length < 2) {
    results.value = { products: [], users: [], categories: [], subcategories: [], brands: [], models: [], attributes: [] }
    return
  }
  searchLoading.value = true
  searchTimer = setTimeout(async () => {
    try {
      const res = await globalSearch(q)
      results.value = res.data || { products: [], users: [], categories: [], subcategories: [], brands: [], models: [], attributes: [] }
    } catch {
      results.value = { products: [], users: [], categories: [], subcategories: [], brands: [], models: [], attributes: [] }
    } finally {
      searchLoading.value = false
    }
  }, 300)
}

function onSearch() {
  const q = query.value.trim()
  if (!q) return
  saveToHistory({ query: q, type: 'search', id: null, name: q })
  router.replace(`/products?q=${encodeURIComponent(q)}`)
}

function onFocus() {
  query.value = ''
  selectedCategory.value = null
  selectedSubcategory.value = null
}

function selectCategory(id) {
  selectedCategory.value = id
  selectedSubcategory.value = null
}

function selectSubcategory(id) {
  selectedSubcategory.value = id
  if (selectedCategory.value && id) {
    router.push(`/products?category_id=${selectedCategory.value}&subcategory_id=${id}`)
  } else if (selectedCategory.value) {
    router.push(`/products?category_id=${selectedCategory.value}`)
  }
}

function clearSearch() {
  query.value = ''
  results.value = { products: [], users: [], categories: [], subcategories: [], brands: [], models: [], attributes: [] }
  searchInput.value?.focus()
}
</script>

<style scoped>
.search-page {
  max-width: 720px;
  margin: 0 auto;
  padding: 1.5rem 1rem 3rem;
}

.search-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.back-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border: none;
  border-radius: var(--radius-sm);
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  flex-shrink: 0;
  transition: color 0.15s;
}

.back-btn:hover {
  color: var(--text);
}

.search-form {
  flex: 1;
}

.search-input-wrap {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 0.85rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--border);
  background: var(--surface);
  transition: border-color 0.15s;
}

.search-input-wrap:focus-within {
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
  font-size: 1rem;
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

/* Filter buttons */
.filter-section {
  margin-bottom: 1.25rem;
}

.filter-scroll {
  display: flex;
  gap: 0.4rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
  scrollbar-width: none;
}

.filter-scroll::-webkit-scrollbar {
  display: none;
}

.sub-scroll {
  margin-top: 0.4rem;
}

.filter-btn {
  flex-shrink: 0;
  padding: 0.4rem 0.9rem;
  border-radius: 999px;
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text-muted);
  font-size: 0.82rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;
}

.filter-btn:hover {
  border-color: var(--accent);
  color: var(--accent);
}

.filter-btn.active {
  background: var(--accent);
  color: #fff;
  border-color: var(--accent);
}

.filter-btn.sub-btn {
  font-size: 0.78rem;
  padding: 0.35rem 0.75rem;
}

/* Search history */
.history-section {
  margin-bottom: 1.25rem;
}

.history-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.section-label {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--text-muted);
}

.clear-history-btn {
  font-size: 0.78rem;
  color: var(--accent);
  background: none;
  border: none;
  cursor: pointer;
  font-weight: 500;
}

.clear-history-btn:hover {
  text-decoration: underline;
}

.history-list {
  display: flex;
  flex-direction: column;
}

.history-item {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.6rem 0.4rem;
  font-size: 0.9rem;
  color: var(--text);
  background: none;
  border: none;
  text-align: left;
  cursor: pointer;
  border-radius: var(--radius-sm);
  transition: background 0.12s;
}

.history-item:hover {
  background: var(--surface-2);
}

.history-item svg {
  color: var(--text-muted);
  flex-shrink: 0;
}

/* Search results */
.search-results {
  margin-top: 0.5rem;
}

.result-group {
  margin-bottom: 1.25rem;
}

.result-group-label {
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--text-muted);
  padding: 0.35rem 0;
  border-bottom: 1px solid var(--border);
  margin-bottom: 0.25rem;
}

.result-item {
  display: block;
  padding: 0.65rem 0.5rem;
  font-size: 0.95rem;
  color: var(--text);
  text-decoration: none;
  border-radius: var(--radius-sm);
  transition: background 0.12s;
}

.result-item:hover {
  background: var(--surface-2);
}

/* Categories list */
.categories-section {
  margin-top: 0.5rem;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.category-block {
  margin-bottom: 0.25rem;
}

.category-name {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 0.75rem 0.5rem;
  font-size: 1rem;
  font-weight: 500;
  color: var(--text);
  background: none;
  border: none;
  text-align: left;
  cursor: pointer;
  border-radius: var(--radius-sm);
  transition: background 0.12s;
}

.category-name:hover {
  background: var(--surface-2);
}

.category-count {
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--text-muted);
  background: var(--surface-2);
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
}

.loading, .empty {
  text-align: center;
  padding: 2rem;
  color: var(--text-muted);
}
</style>
