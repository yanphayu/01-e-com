<template>
  <div class="admin-layout">
    <aside class="sidebar">
      <div class="sidebar-scroll">
        <header class="sidebar-brand">
          <div class="sidebar-brand-inner">
            <svg viewBox="138.8 116 322.4 283" fill="none" class="sidebar-logo">
              <path d="M 300 130 L 259.5 276.6 L 152.8 385 L 300 346.8 L 447.2 385 L 340.5 276.6 Z" stroke="currentColor" stroke-width="26" stroke-linejoin="miter" stroke-miterlimit="10" />
              <path d="M 259.5 276.6 L 300 346.8 L 340.5 276.6 Z" fill="currentColor" />
              <circle cx="300" cy="130" r="9" fill="currentColor" />
              <circle cx="152.8" cy="385" r="9" fill="currentColor" />
              <circle cx="447.2" cy="385" r="9" fill="currentColor" />
            </svg>
            <span class="sidebar-brand-name">TRINITY</span>
          </div>
          <span class="sidebar-badge">Admin</span>
        </header>

        <div class="sidebar-group-label">Operational Units</div>

        <nav class="sidebar-nav">
          <RouterLink to="/" class="sidebar-link" :class="{ active: $route.name === 'dashboard' }">
            <span class="material-symbols-outlined nav-ic">dashboard</span>
            <span>Dashboard</span>
          </RouterLink>
          <RouterLink to="/analytics" class="sidebar-link" :class="{ active: $route.name === 'analytics' }">
            <span class="material-symbols-outlined nav-ic">analytics</span>
            <span>Analytics</span>
          </RouterLink>
          <RouterLink to="/users" class="sidebar-link" :class="{ active: $route.name === 'users' || $route.name === 'user-detail' }">
            <span class="material-symbols-outlined nav-ic">group</span>
            <span>Users</span>
          </RouterLink>
          <RouterLink to="/products" class="sidebar-link" :class="{ active: $route.name === 'products' || $route.name === 'product-detail' }">
            <span class="material-symbols-outlined nav-ic">inventory_2</span>
            <span>Products</span>
          </RouterLink>
          <RouterLink to="/reports" class="sidebar-link report-link" :class="{ active: $route.name === 'reports' }">
            <span class="report-link-inner">
              <span class="material-symbols-outlined nav-ic">summarize</span>
              <span>Reports</span>
            </span>
            <span v-if="pendingReports > 0" class="sidebar-alert-dot"></span>
          </RouterLink>
        </nav>

        <div class="sidebar-group-label">Inventory Taxonomies</div>

        <nav class="sidebar-nav">
          <RouterLink to="/categories" class="sidebar-link" :class="{ active: $route.name === 'categories' }">
            <span class="material-symbols-outlined nav-ic">category</span>
            <span>Categories</span>
          </RouterLink>
          <RouterLink to="/subcategories" class="sidebar-link" :class="{ active: $route.name === 'subcategories' }">
            <span class="material-symbols-outlined nav-ic">account_tree</span>
            <span>Subcategories</span>
          </RouterLink>
          <RouterLink to="/brands" class="sidebar-link" :class="{ active: $route.name === 'brands' }">
            <span class="material-symbols-outlined nav-ic">verified</span>
            <span>Brands</span>
          </RouterLink>
          <RouterLink to="/models" class="sidebar-link" :class="{ active: $route.name === 'models' }">
            <span class="material-symbols-outlined nav-ic">devices</span>
            <span>Models</span>
          </RouterLink>
          <RouterLink to="/attributes" class="sidebar-link" :class="{ active: $route.name === 'attributes' }">
            <span class="material-symbols-outlined nav-ic">tune</span>
            <span>Attributes</span>
          </RouterLink>
        </nav>
      </div>

      <div class="sidebar-footer">
        <div class="sidebar-user">
          <div class="sidebar-avatar">{{ userInitial }}</div>
          <div class="sidebar-user-details">
            <span class="sidebar-user-name">{{ userName }}</span>
            <span class="sidebar-user-role">Super Admin</span>
          </div>
          <button class="sidebar-logout" title="Logout" @click="handleLogout">
            <span class="material-symbols-outlined">logout</span>
          </button>
        </div>
      </div>
    </aside>

    <div class="admin-shell">
      <header class="topbar">
        <div class="topbar-search">
          <span class="material-symbols-outlined search-ic">search</span>
          <input
            v-model="search"
            class="topbar-input"
            type="text"
            placeholder="Search commands, SKU, or users..."
            @keydown.enter="jumpToSearch"
          />
          <span class="topbar-kbd">⌘K</span>
        </div>

        <div class="topbar-end">
          <div class="health-pill">
            <span class="health-dot"></span>
            <span>System Healthy</span>
          </div>

          <button class="range-btn" type="button">
            <span class="material-symbols-outlined range-ic">calendar_today</span>
            <span>Last 30 Days</span>
            <span class="material-symbols-outlined range-caret">expand_more</span>
          </button>

          <button class="icon-btn" type="button" title="Notifications" @click="$router.push('/reports')">
            <span class="material-symbols-outlined">notifications</span>
            <span v-if="pendingReports > 0" class="notif-dot"></span>
          </button>

          <div class="topbar-divider"></div>

          <div class="topbar-avatar">{{ userInitial }}</div>
        </div>
      </header>

      <main class="admin-main">
        <RouterView />
      </main>
    </div>

    <ConfirmModal />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import ConfirmModal from '../components/ConfirmModal.vue'
import { getDashboard } from '../services/admin'

const router = useRouter()

const search = ref('')
const pendingReports = ref(0)

const user = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('admin_user') || '{}')
  } catch {
    return {}
  }
})

const userName = computed(() => user.value.name || 'Admin')
const userInitial = computed(() => (user.value.name || 'A')[0].toUpperCase())

const ROUTE_MAP = {
  dashboard: '/', analytics: '/analytics', users: '/users', products: '/products',
  reports: '/reports', categories: '/categories', subcategories: '/subcategories',
  brands: '/brands', models: '/models', attributes: '/attributes',
}

function jumpToSearch() {
  const q = search.value.trim().toLowerCase()
  if (!q) return
  for (const [key, path] of Object.entries(ROUTE_MAP)) {
    if (key.includes(q) || q.includes(key)) {
      router.push(path)
      search.value = ''
      return
    }
  }
  const userMatch = q.match(/^user\s+(\d+)$/)
  if (userMatch) {
    router.push(`/users/${userMatch[1]}`)
    search.value = ''
    return
  }
  const productMatch = q.match(/^product\s+(\d+)$/)
  if (productMatch) {
    router.push(`/products/${productMatch[1]}`)
    search.value = ''
  }
}

function handleLogout() {
  localStorage.removeItem('admin_token')
  localStorage.removeItem('admin_user')
  router.push('/login')
}

onMounted(async () => {
  try {
    const res = await getDashboard()
    pendingReports.value = res.data?.stats?.pending_reports ?? 0
  } catch {
    pendingReports.value = 0
  }
})
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
}

/* ---------- Sidebar ---------- */
.sidebar {
  width: var(--sidebar-width);
  background: var(--sidebar-bg);
  color: var(--sidebar-text);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  z-index: 50;
}

.sidebar-scroll {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}

.sidebar-brand {
  height: 4rem;
  padding: 0 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  flex-shrink: 0;
}

.sidebar-brand-inner {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.sidebar-logo {
  width: 26px;
  height: 26px;
  color: var(--accent);
  flex-shrink: 0;
}

.sidebar-brand-name {
  font-weight: 700;
  font-size: 0.95rem;
  letter-spacing: 0.12em;
  color: var(--sidebar-active);
  text-transform: uppercase;
}

.sidebar-badge {
  font-size: 0.6rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #fff;
  background: var(--accent);
  padding: 0.15rem 0.45rem;
  border-radius: 4px;
  box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.14);
}

.sidebar-group-label {
  padding: 1rem 1rem 0.4rem;
  font-size: 0.62rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #6e7a71;
  flex-shrink: 0;
}

.sidebar-nav {
  padding: 0 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex-shrink: 0;
}

.sidebar-link {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.65rem;
  padding: 0.5rem 0.625rem;
  border-radius: 6px;
  color: var(--sidebar-text);
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  border-left: 2px solid transparent;
  transition: all 0.15s;
  cursor: pointer;
}

.sidebar-link:hover {
  color: var(--sidebar-active);
  background: var(--sidebar-hover);
}

.sidebar-link.active {
  color: var(--sidebar-active);
  background: rgba(255, 255, 255, 0.10);
  border-left-color: #5fe0a5;
  font-weight: 600;
}

.report-link-inner {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.nav-ic {
  font-size: 20px;
  line-height: 1;
}

.sidebar-alert-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--danger);
  box-shadow: 0 0 0 3px rgba(186, 26, 26, 0.2);
}

.sidebar-footer {
  padding: 0.75rem;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  background: rgba(0, 0, 0, 0.2);
  flex-shrink: 0;
}

.sidebar-user {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.sidebar-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2f9e6b, #0058be);
  color: #fff;
  display: grid;
  place-items: center;
  font-size: 0.8rem;
  font-weight: 700;
  flex-shrink: 0;
  box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.12);
}

.sidebar-user-details {
  flex: 1;
  min-width: 0;
}

.sidebar-user-name {
  display: block;
  font-size: 0.84rem;
  font-weight: 600;
  color: var(--sidebar-active);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.sidebar-user-role {
  display: block;
  font-size: 0.66rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #6e7a71;
}

.sidebar-logout {
  display: grid;
  place-items: center;
  width: 34px;
  height: 34px;
  border: none;
  border-radius: 6px;
  background: transparent;
  color: var(--sidebar-text);
  cursor: pointer;
  transition: all 0.15s;
  flex-shrink: 0;
}

.sidebar-logout span {
  font-size: 20px;
}

.sidebar-logout:hover {
  color: var(--danger);
  background: rgba(186, 26, 26, 0.12);
}

/* ---------- Shell ---------- */
.admin-shell {
  flex: 1;
  margin-left: var(--sidebar-width);
  min-width: 0;
}

.topbar {
  position: sticky;
  top: 0;
  z-index: 40;
  height: 4rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0 1.5rem;
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border);
}

.topbar-search {
  position: relative;
  width: 100%;
  max-width: 420px;
}

.search-ic {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 20px;
  color: var(--text-muted);
  pointer-events: none;
}

.topbar-input {
  width: 100%;
  padding: 0.45rem 3.1rem 0.45rem 2.5rem;
  background: var(--surface-2);
  border: 1px solid transparent;
  border-radius: 8px;
  font-size: 0.85rem;
  color: var(--text);
  outline: none;
  transition: all 0.15s;
}

.topbar-input:focus {
  background: var(--surface-3);
  border-color: var(--border);
}

.topbar-input::placeholder {
  color: var(--text-muted);
}

.topbar-kbd {
  position: absolute;
  right: 0.6rem;
  top: 50%;
  transform: translateY(-50%);
  font-family: var(--font-mono);
  font-size: 0.68rem;
  font-weight: 500;
  letter-spacing: 0.04em;
  color: var(--text-muted);
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 5px;
  padding: 0.15rem 0.4rem;
}

.topbar-end {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-shrink: 0;
}

.health-pill {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.3rem 0.75rem;
  background: var(--surface-2);
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-muted);
  white-space: nowrap;
}

.health-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--accent);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(47, 158, 107, 0.4); }
  50% { box-shadow: 0 0 0 5px rgba(47, 158, 107, 0); }
}

.range-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.45rem 0.75rem;
  background: var(--surface-2);
  border: 1px solid transparent;
  border-radius: 8px;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--text);
  cursor: pointer;
  transition: background 0.15s;
  white-space: nowrap;
}

.range-btn:hover {
  background: var(--surface-3);
}

.range-ic, .range-caret {
  font-size: 17px;
  color: var(--text-muted);
}

.icon-btn {
  position: relative;
  display: grid;
  place-items: center;
  width: 38px;
  height: 38px;
  border: none;
  border-radius: 50%;
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.15s;
}

.icon-btn:hover {
  color: var(--text);
  background: var(--surface-2);
}

.icon-btn span {
  font-size: 22px;
}

.notif-dot {
  position: absolute;
  top: 7px;
  right: 7px;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--danger);
  box-shadow: 0 0 0 2px var(--surface);
}

.topbar-divider {
  width: 1px;
  height: 1.5rem;
  background: var(--border-strong);
}

.topbar-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2f9e6b, #0058be);
  color: #fff;
  display: grid;
  place-items: center;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
}

.admin-main {
  padding: 2rem 2.5rem 3rem;
  min-height: calc(100vh - 4rem);
}

@media (max-width: 1100px) {
  .health-pill {
    display: none;
  }
}
</style>