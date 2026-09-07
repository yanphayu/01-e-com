<template>
  <div class="users-page">
    <div class="page-header">
      <h1 class="page-title">Users</h1>
      <div class="page-actions">
        <input v-model="search" type="text" class="input search-input" placeholder="Search users..." @input="debouncedFetch" />
      </div>
    </div>

    <div v-if="loading" class="loading">Loading...</div>

    <template v-else>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>User</th>
              <th>Email</th>
              <th>Admin</th>
              <th>Joined</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in users" :key="u.id">
              <td>
                <div class="user-cell">
                  <img v-if="u.profile?.avatar" :src="getAvatar(u.profile.avatar)" class="user-cell-avatar" alt="" />
                  <span v-else class="user-cell-fallback">{{ (u.name || '?')[0] }}</span>
                  <span class="user-cell-name">{{ u.name }}</span>
                </div>
              </td>
              <td>{{ u.email }}</td>
              <td>
                <span v-if="u.is_admin" class="badge badge-success">Admin</span>
                <span v-else class="badge badge-neutral">User</span>
              </td>
              <td>{{ formatDate(u.created_at) }}</td>
              <td>
                <div class="action-btns">
                  <button class="btn btn-ghost btn-sm" @click="$router.push(`/users/${u.id}`)">View</button>
                  <button class="btn btn-ghost btn-sm" @click="handleToggleAdmin(u)">
                    {{ u.is_admin ? 'Remove Admin' : 'Make Admin' }}
                  </button>
                  <button class="btn btn-ghost btn-sm danger-text" @click="handleDelete(u)">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!users.length">
              <td colspan="5" class="empty-row">No users found</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="lastPage > 1" class="pagination">
        <button :disabled="currentPage <= 1" @click="goToPage(currentPage - 1)">Prev</button>
        <button v-for="page in visiblePages" :key="page" :class="{ active: page === currentPage }" @click="goToPage(page)">{{ page }}</button>
        <button :disabled="currentPage >= lastPage" @click="goToPage(currentPage + 1)">Next</button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { getUsers, toggleAdmin, deleteUser } from '../services/admin'

const loading = ref(true)
const users = ref([])
const search = ref('')
const currentPage = ref(1)
const lastPage = ref(1)
let searchTimer = null

const visiblePages = computed(() => {
  const pages = []
  for (let i = Math.max(1, currentPage.value - 2); i <= Math.min(lastPage.value, currentPage.value + 2); i++) {
    pages.push(i)
  }
  return pages
})

onMounted(() => fetchUsers())

async function fetchUsers() {
  loading.value = true
  try {
    const params = { page: currentPage.value }
    if (search.value) params.search = search.value
    const res = await getUsers(params)
    users.value = res.data.data
    currentPage.value = res.data.current_page
    lastPage.value = res.data.last_page
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function debouncedFetch() {
  clearTimeout(searchTimer)
  currentPage.value = 1
  searchTimer = setTimeout(fetchUsers, 300)
}

function goToPage(page) {
  currentPage.value = page
  fetchUsers()
}

async function handleToggleAdmin(u) {
  try {
    const res = await toggleAdmin(u.id)
    u.is_admin = res.data.is_admin
  } catch (e) {
    alert(e.message)
  }
}

async function handleDelete(u) {
  if (!confirm(`Delete user "${u.name}"?`)) return
  try {
    await deleteUser(u.id)
    users.value = users.value.filter(x => x.id !== u.id)
  } catch (e) {
    alert(e.message)
  }
}

function getAvatar(url) {
  if (!url) return ''
  if (url.startsWith('http')) return url
  return `http://localhost:8000/storage/${url}`
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<style scoped>
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
  gap: 1rem;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
}

.search-input {
  max-width: 260px;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.user-cell-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  object-fit: cover;
}

.user-cell-fallback {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: var(--accent);
  color: #fff;
  display: grid;
  place-items: center;
  font-size: 0.75rem;
  font-weight: 600;
  flex-shrink: 0;
}

.user-cell-name {
  font-weight: 500;
}

.action-btns {
  display: flex;
  gap: 0.3rem;
}

.danger-text {
  color: var(--danger) !important;
}

.danger-text:hover {
  background: var(--danger-soft) !important;
  color: var(--danger) !important;
}

.empty-row {
  text-align: center;
  color: var(--text-muted);
  padding: 2rem !important;
}
</style>
