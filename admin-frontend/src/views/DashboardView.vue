<template>
  <div class="dashboard">
    <div class="insights-head">
      <div>
        <h2 class="section-title">Platform Insights</h2>
        <p class="section-subtitle">Overview of your platform activity at a glance.</p>
      </div>
    </div>

    <div v-if="loading" class="loading">Loading...</div>

    <template v-else>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon users-icon">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ stats.total_users || 0 }}</span>
            <span class="stat-label">Total Users</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon categories-icon">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/></svg>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ stats.total_categories || 0 }}</span>
            <span class="stat-label">Categories</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon comments-icon">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ stats.total_comments || 0 }}</span>
            <span class="stat-label">Comments</span>
          </div>
        </div>

        <RouterLink to="/reports" class="stat-card stat-link">
          <div class="stat-icon reports-icon" :class="{ 'has-alert': stats.pending_reports > 0 }">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span v-if="stats.pending_reports > 0" class="alert-dot"></span>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ stats.pending_reports || 0 }}</span>
            <span class="stat-label">Pending Reports</span>
          </div>
        </RouterLink>
      </div>

      <div class="recent-grid">
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Recent Users</h3>
            <RouterLink to="/users" class="card-link">View all</RouterLink>
          </div>
          <div class="recent-list">
            <div v-for="u in recentUsers" :key="u.id" class="recent-item">
              <div class="recent-avatar">{{ (u.name || '?')[0].toUpperCase() }}</div>
              <div class="recent-info">
                <span class="recent-name">{{ u.name }}</span>
                <span class="recent-meta">{{ u.email }}</span>
              </div>
              <span class="recent-date">{{ formatDate(u.created_at) }}</span>
            </div>
            <div v-if="!recentUsers.length" class="recent-empty">No users yet</div>
          </div>
        </div>

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Recent Reports</h3>
            <RouterLink to="/reports" class="card-link">View all</RouterLink>
          </div>
          <div class="recent-list">
            <div v-for="r in recentReports" :key="r.id" class="recent-item">
              <div class="recent-icon" :class="{ 'report-alert': r.status === 'pending' }">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              </div>
              <div class="recent-info">
                <span class="recent-name">{{ r.product?.name || '-' }}</span>
                <span class="recent-meta">{{ reasonLabel(r.reason) }} · {{ r.user?.name || '-' }}</span>
              </div>
              <span class="recent-date">{{ formatDate(r.created_at) }}</span>
            </div>
            <RouterLink v-if="!recentReports.length" to="/reports" class="recent-empty recent-empty-link">No reports yet</RouterLink>
          </div>
        </div>
      </div>
    </template>

    <AnalyticsView embedded />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { getDashboard } from '../services/admin'
import AnalyticsView from './AnalyticsView.vue'

const loading = ref(true)
const stats = ref({})
const recentUsers = ref([])
const recentReports = ref([])

onMounted(async () => {
  try {
    const res = await getDashboard()
    stats.value = res.data.stats
    recentUsers.value = res.data.recent_users
    recentReports.value = res.data.recent_reports || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

function reasonLabel(reason) {
  const labels = {
    spam: 'Spam',
    fraud: 'Fraud / Scam',
    fake: 'Counterfeit product',
    inappropriate: 'Inappropriate content',
    duplicate: 'Duplicate listing',
    other: 'Other',
  }
  return labels[reason] || reason
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<style scoped>
.dashboard {
  --green: #10B981;
  --blue: #3B82F6;
}

.insights-head {
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1.35rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #0F172A;
  margin: 0;
}

.section-subtitle {
  margin: 0.3rem 0 0;
  font-size: 0.85rem;
  color: #64748B;
}

.loading {
  padding: 3rem;
  text-align: center;
  color: #94A3B8;
  font-size: 0.9rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.04);
  border-radius: 16px;
  padding: 1.35rem;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 8px 24px rgba(15, 23, 42, 0.06);
  transition: box-shadow 0.2s, transform 0.2s;
}

.stat-card:hover {
  box-shadow: 0 12px 32px rgba(15, 23, 42, 0.1);
  transform: translateY(-2px);
}

.stat-link {
  text-decoration: none;
  color: inherit;
}

.stat-icon {
  position: relative;
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.alert-dot {
  position: absolute;
  top: -3px;
  right: -3px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #EF4444;
  border: 2px solid #fff;
}

.users-icon { background: rgba(99, 102, 241, 0.1); color: #6366F1; }
.categories-icon { background: rgba(245, 158, 11, 0.12); color: #D97706; }
.comments-icon { background: rgba(14, 165, 233, 0.12); color: #0284C7; }
.reports-icon { background: rgba(239, 68, 68, 0.1); color: #DC2626; }
.reports-icon.has-alert { background: #EF4444; color: #fff; }

.stat-info {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  min-width: 0;
}

.stat-value {
  font-size: 1.55rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: #0F172A;
  line-height: 1.15;
}

.stat-label {
  font-size: 0.78rem;
  font-weight: 500;
  color: #64748B;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.recent-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

.card {
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.04);
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 8px 24px rgba(15, 23, 42, 0.06);
}

.card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #EEF2F7;
}

.card-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: #0F172A;
  margin: 0;
}

.card-link {
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--green);
  text-decoration: none;
}

.card-link:hover {
  text-decoration: underline;
}

.recent-list {
  display: flex;
  flex-direction: column;
}

.recent-item {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 0.65rem 0;
  border-bottom: 1px solid #F1F5F9;
}

.recent-item:last-child {
  border-bottom: none;
}

.recent-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: linear-gradient(135deg, #10B981, #0EA5E9);
  color: #fff;
  display: grid;
  place-items: center;
  font-size: 0.85rem;
  font-weight: 700;
  flex-shrink: 0;
}

.recent-icon {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  background: #F1F5F9;
  display: grid;
  place-items: center;
  color: #64748B;
  flex-shrink: 0;
}

.recent-icon.report-alert {
  background: rgba(239, 68, 68, 0.1);
  color: #DC2626;
}

.recent-info {
  flex: 1;
  min-width: 0;
}

.recent-name {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: #0F172A;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.recent-meta {
  display: block;
  font-size: 0.75rem;
  color: #94A3B8;
  margin-top: 0.1rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.recent-date {
  font-size: 0.72rem;
  color: #A7B4C4;
  white-space: nowrap;
}

.recent-empty {
  padding: 1.6rem;
  text-align: center;
  color: #94A3B8;
  font-size: 0.82rem;
}

.recent-empty-link {
  text-decoration: none;
  display: block;
}

@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 900px) {
  .recent-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 560px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>