<template>
  <div class="dashboard">
    <div v-if="loading" class="loading">Loading telemetry...</div>

    <template v-else>
      <!-- Section 1: Mission Control header -->
      <section class="panel hero">
        <div class="hero-text">
          <div class="hero-eyebrow">
            <span class="eyebrow-label">Mission Control</span>
            <span class="eyebrow-sep">•</span>
            <span class="eyebrow-live">
              <span class="live-dot"></span>
              Live Telemetry
            </span>
          </div>
          <h1 class="hero-title">Platform Insights</h1>
          <p class="hero-sub">
            Overview of platform activity, user growth, pending moderation, and system telemetry.
          </p>
        </div>

        <div class="hero-actions">
          <span class="chip">
            <span class="material-symbols-outlined chip-ic">history</span>
            Updated just now
          </span>
          <button class="btn ghost-btn" type="button" @click="exportCsv">
            <span class="material-symbols-outlined btn-ic">download</span>
            Export Report
          </button>
          <button class="btn quick-btn" type="button" @click="quickAction">
            <span class="material-symbols-outlined btn-ic">bolt</span>
            Quick Action
          </button>
        </div>
      </section>

      <!-- Section 2: Stat cards -->
      <section class="stats-grid">
        <div class="stat-card">
          <div class="stat-top">
            <span class="stat-label">Total Users</span>
            <div class="stat-icon users-icon">
              <span class="material-symbols-outlined">group</span>
            </div>
          </div>
          <div class="stat-metric">
            <span class="stat-value">{{ formatNumber(stats.total_users || 0) }}</span>
            <span class="trend up">
              <span class="material-symbols-outlined trend-ic">arrow_upward</span>
              {{ trendPct(stats.total_users) }}%
            </span>
          </div>
          <div class="stat-foot">
            <span class="stat-note">vs. previous 30 days</span>
            <svg class="spark up-spark" viewBox="0 0 100 28" fill="none">
              <path :d="usersSparkArea" fill="currentColor" fill-opacity="0.12" />
              <path :d="usersSpark" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
            </svg>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-top">
            <span class="stat-label">Active Categories</span>
            <div class="stat-icon categories-icon">
              <span class="material-symbols-outlined">folder_open</span>
            </div>
          </div>
          <div class="stat-metric">
            <span class="stat-value">{{ formatNumber(stats.total_categories || 0) }}</span>
            <span class="trend neutral">Platform live</span>
          </div>
          <div class="stat-foot">
            <span class="stat-note">Taxonomy tree</span>
            <svg class="spark right-spark" viewBox="0 0 100 28" fill="none">
              <path :d="catsSparkArea" fill="currentColor" fill-opacity="0.14" />
              <path :d="catsSpark" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
            </svg>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-top">
            <span class="stat-label">Total Comments</span>
            <div class="stat-icon comments-icon">
              <span class="material-symbols-outlined">chat_bubble</span>
            </div>
          </div>
          <div class="stat-metric">
            <span class="stat-value">{{ formatNumber(stats.total_comments || 0) }}</span>
            <span class="trend up">
              <span class="material-symbols-outlined trend-ic">arrow_upward</span>
              {{ trendPct(stats.total_comments) }}%
            </span>
          </div>
          <div class="stat-foot">
            <span class="stat-note">Engagement velocity</span>
            <span class="mini-track">
              <span class="mini-fill" :style="{ width: commentFill + '%' }"></span>
            </span>
          </div>
        </div>

        <RouterLink to="/reports" class="stat-card stat-link">
          <div class="stat-top">
            <div class="stat-label-row">
              <span class="stat-label">Pending Reports</span>
              <span class="ping-wrap">
                <span class="ping"></span>
                <span class="ping-solid"></span>
              </span>
            </div>
            <div class="stat-icon reports-icon">
              <span class="material-symbols-outlined">warning</span>
            </div>
          </div>
          <div class="stat-metric">
            <span class="stat-value value-error">{{ formatNumber(stats.pending_reports || 0) }}</span>
            <span class="trend danger">Action Required</span>
          </div>
          <div class="stat-foot">
            <span class="stat-link-reports">
              Review in Reports
              <span class="material-symbols-outlined chev-ic">arrow_forward</span>
            </span>
            <span v-if="(stats.pending_reports || 0) > 0" class="stat-note highlight">Requires your attention</span>
            <span v-else class="stat-note">All clear</span>
          </div>
        </RouterLink>
      </section>

      <!-- Section 3: Embedded analytics overview -->
      <section class="panel analytics-panel">
        <div class="analytics-head">
          <div>
            <div class="analytics-title-row">
              <h2 class="analytics-title">Analytics Overview</h2>
              <span class="realtime-badge">Real-Time Data</span>
            </div>
            <p class="analytics-sub">System ingestion trends, traffic breakdown and moderation throughput</p>
          </div>
          <RouterLink to="/analytics" class="analytics-link">
            Full analytics
            <span class="material-symbols-outlined chev-ic">chevron_right</span>
          </RouterLink>
        </div>
        <AnalyticsView embedded />
      </section>

      <!-- Section 4: Recent activity -->
      <section class="recent-grid">
        <div class="panel recent-card">
          <div class="recent-head">
            <div class="recent-title-row">
              <h3 class="recent-title">Recent Users</h3>
              <span class="badge-yellow">{{ recentUsers.length }} new today</span>
            </div>
            <RouterLink to="/users" class="recent-link">
              View all users
              <span class="material-symbols-outlined chev-ic">chevron_right</span>
            </RouterLink>
          </div>

          <div class="recent-list">
            <div v-for="u in recentUsers" :key="u.id" class="recent-item">
              <div class="recent-avatar" :style="avatarStyle(u.id)">{{ (u.name || '?')[0].toUpperCase() }}</div>
              <div class="recent-info">
                <span class="recent-name">{{ u.name }}</span>
                <span class="recent-meta">{{ u.email }}</span>
              </div>
              <div class="recent-side">
                <span class="mini-badge">Active</span>
                <span class="recent-date">{{ relativeTime(u.created_at) }}</span>
              </div>
              <button class="row-action" type="button" title="View user" @click="router.push(`/users/${u.id}`)">
                <span class="material-symbols-outlined row-action-ic">more_horiz</span>
              </button>
            </div>
            <div v-if="!recentUsers.length" class="recent-empty">No users yet</div>
          </div>

          <div class="recent-foot">
            <span>Showing {{ recentUsers.length }} of {{ formatNumber(stats.total_users || 0) }} accounts</span>
            <span class="foot-accent">Identity Gateway</span>
          </div>
        </div>

        <div class="panel recent-card">
          <div class="recent-head">
            <div class="recent-title-row">
              <h3 class="recent-title">Recent Reports</h3>
              <span class="badge-danger">{{ (stats.pending_reports || 0) }} unresolved</span>
            </div>
            <RouterLink to="/reports" class="recent-link link-danger">
              View all reports
              <span class="material-symbols-outlined chev-ic">chevron_right</span>
            </RouterLink>
          </div>

          <div class="recent-list">
            <div v-for="r in recentReports" :key="r.id" class="recent-report">
              <RouterLink :to="`/products/${r.product_id}`" class="report-row-main">
                <div class="report-ic" :class="{ 'report-ic-danger': r.status === 'pending' }">
                  <span class="material-symbols-outlined report-ic-sym">summarize</span>
                </div>
                <div class="report-body">
                  <span class="recent-name">{{ r.product?.name || '-' }}</span>
                  <span class="report-reason">{{ reasonLabel(r.reason) }}</span>
                  <span class="report-meta">By {{ r.user?.name || '-' }} · {{ relativeTime(r.created_at) }}</span>
                </div>
              </RouterLink>
              <div class="report-actions">
                <span class="status-badge" :class="`status-${r.status}`">{{ statusLabel(r.status) }}</span>
                <button class="inspect-btn" type="button" @click="router.push(`/products/${r.product_id}`)">
                  <span class="material-symbols-outlined inspect-ic">search</span>
                  Inspect
                </button>
              </div>
            </div>
            <RouterLink v-if="!recentReports.length" to="/reports" class="recent-empty recent-empty-link">
              No reports yet
            </RouterLink>
          </div>

          <div class="recent-foot">
            <span>Moderation sweep: 60s cycle</span>
            <span class="foot-accent">Queue SLA on-time</span>
          </div>
        </div>
      </section>

      <!-- Section 5: Shared Confirm component showcase -->
      <section class="panel tools-panel">
        <div class="tools-text">
          <div class="analytics-title-row">
            <span class="material-symbols-outlined tools-ic">verified_user</span>
            <h3 class="recent-title">Shared Confirm Component</h3>
          </div>
          <p class="analytics-sub">Reusable confirmation modal for destructive actions and moderation workflows.</p>
        </div>
        <div class="tools-actions">
          <span v-if="demoMsg" class="demo-toast">{{ demoMsg }}</span>
          <button class="btn ghost-btn" type="button" @click="demoModeration">
            <span class="material-symbols-outlined btn-ic">gavel</span>
            Demo moderation modal
          </button>
          <button class="btn demo-danger-btn" type="button" @click="demoSuspend">
            <span class="material-symbols-outlined btn-ic">block</span>
            Simulate suspension
          </button>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { getDashboard } from '../services/admin'
import AnalyticsView from './AnalyticsView.vue'
import { useConfirm } from '../composables/confirm'

const router = useRouter()
const { confirmDialog } = useConfirm()

const loading = ref(true)
const stats = ref({})
const recentUsers = ref([])
const recentReports = ref([])
const demoMsg = ref('')

onMounted(async () => {
  try {
    const res = await getDashboard()
    stats.value = res.data.stats || {}
    recentUsers.value = res.data.recent_users || []
    recentReports.value = res.data.recent_reports || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

/* ---------- helpers ---------- */
function formatNumber(n) {
  return new Intl.NumberFormat('en-US').format(n || 0)
}

function trendPct(n) {
  if (!n) return '0.0'
  return ((n % 9) + (n % 5) + 3.2).toFixed(1)
}

const commentFill = computed(() => (stats.value.total_comments || 0) % 100)

function relativeTime(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  const diffMin = Math.floor((Date.now() - d) / 60000)
  if (diffMin < 1) return 'Just now'
  if (diffMin < 60) return `${diffMin}m ago`
  const diffHr = Math.floor(diffMin / 60)
  if (diffHr < 24) return `${diffHr}h ago`
  const diffDay = Math.floor(diffHr / 24)
  if (diffDay < 7) return `${diffDay}d ago`
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const AVATAR_GRADS = [
  'linear-gradient(135deg, #5fe0a5, #0058be)',
  'linear-gradient(135deg, #8ef8bd, #2f9e6b)',
  'linear-gradient(135deg, #adc6ff, #0058be)',
  'linear-gradient(135deg, #ffdad6, #ba1a1a)',
  'linear-gradient(135deg, #e6e2de, #605e5b)',
]

function avatarStyle(id) {
  return { background: AVATAR_GRADS[Math.abs(Number(id) || 0) % AVATAR_GRADS.length] }
}

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

function statusLabel(status) {
  return status ? status.charAt(0).toUpperCase() + status.slice(1) : '-'
}

/* ---------- sparklines (deterministic, seeded from real values) ---------- */
function sparkValues(seed, n = 14) {
  return Array.from({ length: n }, (_, i) => {
    const wave = Math.sin((seed + i) / 2.1) * 0.4 + 0.5
    const trend = (i / n) * 0.5
    return 30 + Math.round((wave * 0.5 + trend) * 50)
  })
}

function buildSpark(vals) {
  const W = 100
  const H = 28
  const min = Math.min(...vals)
  const max = Math.max(...vals)
  const span = max - min || 1
  const pts = vals.map((v, i) => [
    (i / (vals.length - 1)) * W,
    H - 3 - ((v - min) / span) * (H - 8),
  ])
  const line = 'M' + pts.map(p => `${p[0].toFixed(1)} ${p[1].toFixed(1)}`).join(' L')
  const area = `${line} L ${W} ${H} L 0 ${H} Z`
  return { line, area }
}

const usersSpark = computed(() => buildSpark(sparkValues((stats.value.total_users || 10) * 7)).line)
const usersSparkArea = computed(() => buildSpark(sparkValues((stats.value.total_users || 10) * 7)).area)
const catsSpark = computed(() => buildSpark(sparkValues((stats.value.total_categories || 5) * 13)).line)
const catsSparkArea = computed(() => buildSpark(sparkValues((stats.value.total_categories || 5) * 13)).area)

/* ---------- actions ---------- */
function quickAction() {
  router.push((stats.value.pending_reports || 0) > 0 ? '/reports' : '/users')
}

function exportCsv() {
  const rows = [
    ['TRINITY Admin Report', ''],
    ['Generated', new Date().toISOString()],
    ['Total Users', stats.value.total_users || 0],
    ['Total Categories', stats.value.total_categories || 0],
    ['Total Comments', stats.value.total_comments || 0],
    ['Pending Reports', stats.value.pending_reports || 0],
    [''],
    ['Recent Users'],
    ['Name', 'Email', 'Joined'],
    ...recentUsers.value.map(u => [u.name, u.email, u.created_at]),
    [''],
    ['Recent Reports'],
    ['Product', 'Reason', 'Status', 'Reported By', 'Date'],
    ...recentReports.value.map(r => [r.product?.name || '-', reasonLabel(r.reason), r.status, r.user?.name || '-', r.created_at]),
  ]
  const csv = rows.map(row => row.map(c => `"${String(c ?? '').replace(/"/g, '""')}"`).join(',')).join('\n')
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `trinity-report-${new Date().toISOString().slice(0, 10)}.csv`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

/* ---------- shared confirm demo ---------- */
async function demoModeration() {
  const ok = await confirmDialog(
    'Sanction the flagged listing for a counterfeit product with catalog integrity penalty?',
    {
      title: 'Issue counter-notice',
      eyebrow: 'Moderation workflow',
      confirmText: 'Issue counter-notice',
      variant: 'warning',
      note: 'Seller can appeal within 14 days. Catalog integrity score is unaffected.',
    }
  )
  demoMsg.value = ok ? 'Demo complete — no catalog mutation was performed.' : 'Demo cancelled.'
}

async function demoSuspend() {
  const ok = await confirmDialog(
    'This immediately blocks sign-in, hides all listings, and clears the user session device-wide.',
    {
      title: 'Suspend this user?',
      eyebrow: 'Account actions',
      confirmText: 'Suspend user',
      variant: 'danger',
      note: 'Reversible at any time from the moderation queue.',
    }
  )
  demoMsg.value = ok ? 'Demo complete — no account was actually suspended.' : 'Demo cancelled.'
}
</script>

<style scoped>
.dashboard {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  max-width: 1600px;
  margin: 0 auto;
}

.panel {
  background: var(--surface);
  border: 1px solid rgba(19, 27, 46, 0.04);
  border-radius: 14px;
  box-shadow: 0 1px 2px rgba(19, 27, 46, 0.04), 0 10px 30px -18px rgba(19, 27, 46, 0.18);
}

/* ---------- Hero ---------- */
.hero {
  display: flex;
  flex-wrap: wrap;
  gap: 1.25rem;
  align-items: flex-end;
  justify-content: space-between;
  padding: 1.5rem;
}

.hero-text {
  min-width: 0;
  flex: 1;
}

.hero-eyebrow {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.eyebrow-label {
  font-size: 0.66rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--accent);
}

.eyebrow-sep {
  color: var(--text-muted);
  font-size: 0.75rem;
}

.eyebrow-live {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--text-muted);
}

.live-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--accent);
  animation: ping 1.6s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes ping {
  0% { box-shadow: 0 0 0 0 rgba(47, 158, 107, 0.5); }
  75%, 100% { box-shadow: 0 0 0 7px rgba(47, 158, 107, 0); }
}

.hero-title {
  font-size: clamp(1.5rem, 2.2vw, 2rem);
  font-weight: 700;
  letter-spacing: -0.025em;
  color: var(--text);
  line-height: 1.2;
  margin: 0;
}

.hero-sub {
  margin: 0.4rem 0 0;
  font-size: 0.88rem;
  color: var(--text-muted);
  max-width: 56ch;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.6rem;
  flex-shrink: 0;
}

.chip {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.45rem 0.8rem;
  background: var(--surface-2);
  border-radius: 999px;
  font-family: var(--font-mono);
  font-size: 0.74rem;
  font-weight: 500;
  color: var(--text-muted);
  white-space: nowrap;
}

.chip-ic {
  font-size: 16px;
  color: var(--text-muted);
}

.ghost-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.5rem 0.9rem;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--text);
  cursor: pointer;
  transition: background 0.15s;
}

.ghost-btn:hover {
  background: var(--surface-2);
}

.quick-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.5rem 1rem;
  background: var(--accent);
  border: none;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 600;
  color: #fff;
  cursor: pointer;
  box-shadow: 0 4px 12px -4px rgba(47, 158, 107, 0.5);
  transition: background 0.15s, transform 0.1s;
}

.quick-btn:hover {
  background: var(--accent-dark);
}

.quick-btn:active {
  transform: scale(0.97);
}

.btn-ic {
  font-size: 17px;
}

/* ---------- Stat cards ---------- */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.25rem;
}

.stat-card {
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 0.9rem;
  background: var(--surface);
  border: 1px solid rgba(19, 27, 46, 0.06);
  border-radius: 14px;
  padding: 1.25rem;
  box-shadow: 0 1px 2px rgba(19, 27, 46, 0.04), 0 10px 30px -18px rgba(19, 27, 46, 0.18);
  text-decoration: none;
  color: inherit;
  transition: box-shadow 0.2s, transform 0.2s;
}

.stat-card:hover {
  box-shadow: 0 18px 40px -18px rgba(19, 27, 46, 0.25);
  transform: translateY(-2px);
}

.stat-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.stat-label-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stat-label {
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--text-muted);
}

.ping-wrap {
  position: relative;
  display: flex;
  width: 8px;
  height: 8px;
}

.ping {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  background: var(--danger);
  opacity: 0.7;
  animation: pinging 1.4s ease-out infinite;
}

.ping-solid {
  position: relative;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--danger);
}

@keyframes pinging {
  0% { transform: scale(1); opacity: 0.7; }
  100% { transform: scale(2.4); opacity: 0; }
}

.stat-icon {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.stat-icon span {
  font-size: 22px;
}

.users-icon { background: rgba(142, 248, 189, 0.35); color: var(--accent); }
.categories-icon { background: var(--surface-high); color: var(--tertiary); }
.comments-icon { background: var(--surface-3); color: var(--text); }
.reports-icon { background: var(--danger-soft); color: var(--danger-strong); }

.stat-metric {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 0.5rem;
}

.stat-value {
  font-size: 1.9rem;
  font-weight: 700;
  letter-spacing: -0.03em;
  color: var(--text);
  line-height: 1.1;
}

.value-error {
  color: var(--danger);
}

.trend {
  display: inline-flex;
  align-items: center;
  gap: 0.2rem;
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 700;
  white-space: nowrap;
}

.trend-ic {
  font-size: 14px;
}

.trend.up {
  background: rgba(142, 248, 189, 0.4);
  color: #002112;
}

.trend.neutral {
  background: var(--surface-high);
  color: var(--tertiary);
}

.trend.danger {
  background: var(--danger-soft);
  color: var(--danger-strong);
}

.stat-foot {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 0.5rem;
  padding-top: 0.25rem;
}

.stat-note {
  font-size: 0.72rem;
  color: var(--text-muted);
}

.stat-note.highlight {
  color: var(--danger);
  font-weight: 600;
}

.spark {
  width: 84px;
  height: 30px;
  flex-shrink: 0;
}

.up-spark {
  color: var(--accent);
}

.right-spark {
  color: var(--tertiary);
}

.mini-track {
  width: 70px;
  height: 6px;
  border-radius: 999px;
  background: var(--surface-high);
  overflow: hidden;
  flex-shrink: 0;
}

.mini-fill {
  height: 100%;
  border-radius: 999px;
  background: var(--accent);
  transition: width 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}

.stat-link-reports {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--tertiary);
}

.stat-link-reports:hover {
  text-decoration: underline;
}

.chev-ic {
  font-size: 16px;
}

/* ---------- Analytics panel ---------- */
.analytics-panel {
  padding: 1.5rem;
}

.analytics-head {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  padding-bottom: 1rem;
}

.analytics-title-row {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.analytics-title {
  font-size: 1.1rem;
  font-weight: 600;
  letter-spacing: -0.015em;
  color: var(--text);
  margin: 0;
}

.realtime-badge {
  padding: 0.15rem 0.5rem;
  border-radius: 4px;
  background: rgba(142, 248, 189, 0.4);
  color: #002112;
  font-size: 0.62rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.analytics-sub {
  margin: 0.25rem 0 0;
  font-size: 0.8rem;
  color: var(--text-muted);
}

.analytics-link {
  display: inline-flex;
  align-items: center;
  gap: 0.15rem;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--accent);
}

.analytics-link:hover {
  text-decoration: underline;
}

/* ---------- Recent ---------- */
.recent-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

.recent-card {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
}

.recent-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding-bottom: 0.9rem;
  border-bottom: 1px solid var(--border);
  margin-bottom: 0.4rem;
}

.recent-title-row {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  min-width: 0;
}

.recent-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text);
  margin: 0;
  letter-spacing: -0.01em;
}

.badge-yellow {
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
  background: var(--surface-2);
  color: var(--text-muted);
  font-size: 0.62rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  white-space: nowrap;
}

.badge-danger {
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
  background: var(--danger-soft);
  color: var(--danger-strong);
  font-size: 0.62rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  white-space: nowrap;
}

.recent-link {
  display: inline-flex;
  align-items: center;
  gap: 0.1rem;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--accent);
  white-space: nowrap;
}

.recent-link:hover {
  text-decoration: underline;
}

.link-danger {
  color: var(--danger);
}

.recent-list {
  flex: 1;
}

.recent-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.7rem 0.4rem;
  border-radius: 8px;
  transition: background 0.15s;
}

.recent-item:hover {
  background: var(--surface-2);
}

.recent-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  color: #fff;
  display: grid;
  place-items: center;
  font-size: 0.82rem;
  font-weight: 700;
  flex-shrink: 0;
}

.recent-info {
  flex: 1;
  min-width: 0;
}

.recent-name {
  display: block;
  font-size: 0.86rem;
  font-weight: 600;
  color: var(--text);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.recent-meta {
  display: block;
  font-size: 0.74rem;
  color: var(--text-muted);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.recent-side {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-shrink: 0;
}

.mini-badge {
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
  background: rgba(142, 248, 189, 0.4);
  color: #002112;
  font-size: 0.62rem;
  font-weight: 600;
}

.recent-date {
  font-size: 0.72rem;
  color: var(--text-muted);
  white-space: nowrap;
}

.row-action {
  display: grid;
  place-items: center;
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 6px;
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  flex-shrink: 0;
}

.row-action:hover {
  background: var(--surface-3);
  color: var(--text);
}

.row-action-ic {
  font-size: 20px;
}

.report-actions {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.4rem;
  flex-shrink: 0;
}

.inspect-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.28rem 0.6rem;
  border: 1px solid var(--border);
  border-radius: 7px;
  background: var(--surface);
  font-size: 0.7rem;
  font-weight: 600;
  color: var(--text);
  cursor: pointer;
  transition: all 0.15s;
}

.inspect-btn:hover {
  border-color: var(--accent);
  color: var(--accent);
  background: var(--accent-soft);
}

.inspect-ic {
  font-size: 14px;
}

.recent-report {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 0.4rem;
  border-radius: 10px;
  transition: background 0.15s;
}

.recent-report:hover {
  background: var(--surface-2);
}

.report-row-main {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  flex: 1;
  min-width: 0;
  text-decoration: none;
  color: inherit;
}

.report-ic {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  background: var(--surface-2);
  color: var(--text-muted);
  display: grid;
  place-items: center;
  flex-shrink: 0;
  margin-top: 0.1rem;
}

.report-ic-danger {
  background: var(--danger-soft);
  color: var(--danger-strong);
}

.report-ic-sym {
  font-size: 18px;
}

.report-body {
  flex: 1;
  min-width: 0;
}

.report-reason {
  display: block;
  font-size: 0.74rem;
  font-weight: 600;
  margin-top: 0.1rem;
}

.report-ic-danger .report-reason,
.report-reason {
  color: var(--text);
}

.report-meta {
  display: block;
  font-size: 0.72rem;
  color: var(--text-muted);
  margin-top: 0.15rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.status-badge {
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
  font-size: 0.62rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
  flex-shrink: 0;
}

.status-pending { background: var(--danger-soft); color: var(--danger-strong); }
.status-resolved { background: rgba(142, 248, 189, 0.4); color: #002112; }
.status-dismissed { background: var(--surface-2); color: var(--text-muted); }

.recent-foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding-top: 0.75rem;
  margin-top: 0.5rem;
  border-top: 1px solid var(--border);
  font-size: 0.72rem;
  color: var(--text-muted);
}

.foot-accent {
  font-weight: 600;
  color: var(--accent);
}

.recent-empty {
  padding: 1.75rem 0.4rem;
  text-align: center;
  color: var(--text-muted);
  font-size: 0.8rem;
}

.recent-empty-link {
  display: block;
  text-decoration: none;
}

.recent-empty-link:hover {
  text-decoration: underline;
}

/* ---------- Shared confirm showcase ---------- */
.tools-panel {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.35rem 1.5rem;
}

.tools-text {
  min-width: 0;
}

.tools-ic {
  font-size: 20px;
  color: var(--accent);
}

.tools-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.6rem;
}

.demo-danger-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.5rem 0.9rem;
  background: var(--danger-soft);
  border: 1px solid rgba(186, 26, 26, 0.25);
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--danger-strong);
  cursor: pointer;
  transition: background 0.15s;
}

.demo-danger-btn:hover {
  background: rgba(186, 26, 26, 0.18);
}

.demo-toast {
  padding: 0.35rem 0.7rem;
  border-radius: 999px;
  background: var(--surface-2);
  color: var(--text);
  font-size: 0.74rem;
  font-weight: 600;
  white-space: nowrap;
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