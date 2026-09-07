<template>
  <div class="dashboard">
    <h1 class="page-title">Dashboard</h1>

    <div v-if="loading" class="loading">Loading...</div>

    <template v-else>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon users-icon">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ stats.total_users }}</span>
            <span class="stat-label">Total Users</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon categories-icon">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/></svg>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ stats.total_categories }}</span>
            <span class="stat-label">Categories</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon comments-icon">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ stats.total_comments }}</span>
            <span class="stat-label">Comments</span>
          </div>
        </div>
      </div>

      <div class="recent-grid">
        <div class="card">
          <h3 class="card-title">Recent Users</h3>
          <div class="recent-list">
            <div v-for="u in recentUsers" :key="u.id" class="recent-item">
              <div class="recent-avatar">{{ (u.name || '?')[0] }}</div>
              <div class="recent-info">
                <span class="recent-name">{{ u.name }}</span>
                <span class="recent-meta">{{ u.email }}</span>
              </div>
              <span class="recent-date">{{ formatDate(u.created_at) }}</span>
            </div>
            <div v-if="!recentUsers.length" class="recent-empty">No users yet</div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getDashboard } from '../services/admin'

const loading = ref(true)
const stats = ref({})
const recentUsers = ref([])

onMounted(async () => {
  try {
    const res = await getDashboard()
    stats.value = res.data.stats
    recentUsers.value = res.data.recent_users
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

function formatDate(date) {
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<style scoped>
.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: var(--radius-sm);
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.users-icon { background: var(--accent-soft); color: var(--accent); }
.categories-icon { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
.comments-icon { background: var(--danger-soft); color: var(--danger); }

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.8rem;
  color: var(--text-muted);
}

.recent-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.25rem;
}

.card-title {
  font-size: 0.95rem;
  font-weight: 600;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--border);
}

.recent-list {
  display: flex;
  flex-direction: column;
}

.recent-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.6rem 0;
  border-bottom: 1px solid var(--border);
}

.recent-item:last-child {
  border-bottom: none;
}

.recent-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--accent);
  color: #fff;
  display: grid;
  place-items: center;
  font-size: 0.8rem;
  font-weight: 600;
  flex-shrink: 0;
}

.recent-icon {
  width: 32px;
  height: 32px;
  border-radius: var(--radius-sm);
  background: var(--surface-2);
  display: grid;
  place-items: center;
  color: var(--text-muted);
  flex-shrink: 0;
}

.recent-info {
  flex: 1;
  min-width: 0;
}

.recent-name {
  display: block;
  font-size: 0.85rem;
  font-weight: 500;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.recent-meta {
  display: block;
  font-size: 0.75rem;
  color: var(--text-muted);
}

.recent-date {
  font-size: 0.75rem;
  color: var(--text-muted);
  white-space: nowrap;
}

.recent-empty {
  padding: 1.5rem;
  text-align: center;
  color: var(--text-muted);
  font-size: 0.85rem;
}

@media (max-width: 900px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .recent-grid {
    grid-template-columns: 1fr;
  }
}
</style>
