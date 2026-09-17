<template>
  <div class="analytics-page" :class="{ embedded }">
    <header class="analytics-header">
      <div>
        <h1 class="analytics-title">Analytics</h1>
        <p class="analytics-subtitle">Track your platform performance, audience growth and engagement over time.</p>
      </div>

      <div class="range-switch">
        <button
          v-for="r in ranges"
          :key="r"
          class="range-btn"
          :class="{ active: range === r }"
          @click="range = r"
        >
          {{ r }}
        </button>
      </div>
    </header>

    <section class="card chart-card">
      <div class="card-head">
        <div>
          <h2 class="card-title">Traffic Overview</h2>
          <p class="card-subtitle">Pageviews and unique visitors over the selected period</p>
        </div>
        <div class="legend">
          <span class="legend-item"><i class="dot dot-green"></i>Pageviews</span>
          <span class="legend-item"><i class="dot dot-blue"></i>Unique Visitors</span>
        </div>
      </div>

      <div ref="chartWrap" class="chart-wrap" @mousemove="onChartMove" @mouseleave="hoverIndex = -1">
        <svg :viewBox="`0 0 ${VIEW_W} ${VIEW_H}`" class="area-chart" preserveAspectRatio="none">
          <defs>
            <linearGradient id="gradGreen" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#10B981" stop-opacity="0.28" />
              <stop offset="100%" stop-color="#10B981" stop-opacity="0" />
            </linearGradient>
            <linearGradient id="gradBlue" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#3B82F6" stop-opacity="0.24" />
              <stop offset="100%" stop-color="#3B82F6" stop-opacity="0" />
            </linearGradient>
          </defs>

          <line v-for="g in gridLines" :key="g.y" :x1="g.x1" :y1="g.y" :x2="g.x2" :y2="g.y" class="grid-line" />
          <text v-for="g in gridLines" :key="'t'+g.y" :x="g.x1 - 8" :y="g.y + 4" class="grid-label" text-anchor="end">{{ g.label }}</text>

          <path :d="visitAreaPath" fill="url(#gradBlue)" />
          <path :d="pageAreaPath" fill="url(#gradGreen)" />
          <path :d="visitPath" class="line line-blue" fill="none" />
          <path :d="pagePath" class="line line-green" fill="none" />

          <g v-for="p in pagePts" :key="'p'+p.i">
            <circle v-if="hoverIndex === p.i" :cx="p.x" :cy="p.y" r="5" class="hover-dot" />
          </g>
          <line v-if="hoverIndex >= 0" :x1="pagePts[hoverIndex]?.x" :y1="PAD_T" :x2="pagePts[hoverIndex]?.x" :y2="VIEW_H - PAD_B" class="hover-line" />

          <text v-for="l in xLabels" :key="l.x" :x="l.x" :y="VIEW_H - 2" class="axis-label" text-anchor="middle">{{ l.label }}</text>
        </svg>

        <div v-if="hoverIndex >= 0" class="chart-tooltip" :style="tooltipStyle">
          <span class="tip-date">{{ dateLabel(hoverIndex) }}</span>
          <span class="tip-row"><i class="dot dot-green"></i>Pageviews: <b>{{ pageData[hoverIndex] }}</b></span>
          <span class="tip-row"><i class="dot dot-blue"></i>Visitors: <b>{{ visitData[hoverIndex] }}</b></span>
        </div>
      </div>
    </section>

    <section class="kpi-grid">
      <div class="card kpi-card">
        <div class="kpi-head">
          <span class="kpi-label">Pageviews</span>
          <span class="kpi-icon pill-green">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
          </span>
        </div>
        <div class="kpi-value-row">
          <div>
            <span class="kpi-value">{{ formatNumber(kpiPageviews) }}</span>
            <span class="kpi-delta up"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg> {{ kpiPageviewsDelta }}%</span>
          </div>
          <svg :viewBox="`0 0 ${SPARK_W} ${SPARK_H}`" class="spark" preserveAspectRatio="none">
            <path :d="pageSparkPath" class="spark-line" fill="none" />
            <path :d="pageSparkArea" class="spark-area" fill="none" />
          </svg>
        </div>
      </div>

      <div class="card kpi-card">
        <div class="kpi-head">
          <span class="kpi-label">Bounce Rate</span>
          <span class="kpi-icon pill-blue">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21h8"/><path d="M12 17v4"/><path d="M16 2H8a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/></svg>
          </span>
        </div>
        <div class="kpi-radial-row">
          <div class="radial" :style="{ background: `radial-gradient(#fff 58%, transparent 60%), conic-gradient(var(--green) ${bouncePct * 3.6}deg, #E8EDF3 ${bouncePct * 3.6}deg)` }">
            <span class="radial-value">{{ bounceRate.toFixed(1) }}%</span>
          </div>
          <div>
            <span class="kpi-delta down"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg> {{ bounceDelta }}%</span>
            <p class="kpi-note">visitors left after one page</p>
          </div>
        </div>
      </div>

      <div class="card kpi-card">
        <div class="kpi-head">
          <span class="kpi-label">Avg. Duration</span>
          <span class="kpi-icon pill-green">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </span>
        </div>
        <div class="kpi-radial-row">
          <div class="radial" :style="{ background: `radial-gradient(#fff 58%, transparent 60%), conic-gradient(var(--blue) ${durationPct * 3.6}deg, #E8EDF3 ${durationPct * 3.6}deg)` }">
            <span class="radial-value">{{ avgDuration }}</span>
          </div>
          <div>
            <span class="kpi-delta up"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg> {{ durationDelta }}%</span>
            <p class="kpi-note">avg time on site</p>
          </div>
        </div>
      </div>

      <div class="card kpi-card">
        <div class="kpi-head">
          <span class="kpi-label">Sessions</span>
          <span class="kpi-icon pill-blue">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
          </span>
        </div>
        <div class="kpi-value-row">
          <div>
            <span class="kpi-value">{{ formatNumber(kpiSessions) }}</span>
            <span class="kpi-delta up"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg> {{ sessionsDelta }}%</span>
          </div>
          <div class="mini-bars">
            <span v-for="(b, i) in sessionBars" :key="i" class="mini-bar" :style="{ height: `${b}%` }"></span>
          </div>
        </div>
      </div>
    </section>

    <section class="card chat-strip">
      <div class="card-head">
        <div>
          <h2 class="card-title">Chat Activity</h2>
          <p class="card-subtitle">Live messaging metrics from your platform</p>
        </div>
      </div>
      <div class="chat-strip-cols">
        <div class="chat-strip-col">
          <span class="chat-kpi-num">{{ realChat.total_messages || 0 }}</span>
          <span class="chat-kpi-label">Total messages</span>
        </div>
        <div class="chat-strip-col">
          <span class="chat-kpi-num">{{ realChat.total_conversations || 0 }}</span>
          <span class="chat-kpi-label">Conversations</span>
        </div>
        <div class="chat-strip-col today">
          <span class="chat-kpi-num">{{ realChat.today_messages || 0 }}</span>
          <span class="chat-kpi-label">Messages today</span>
        </div>
      </div>
    </section>

    <section class="bottom-grid">
      <div class="card">
        <div class="card-head">
          <div>
            <h2 class="card-title">{{ channels.length ? 'Top Conversations' : 'Traffic by Channel' }}</h2>
            <p class="card-subtitle">{{ channels.length ? 'Conversations ranked by message volume' : 'Acquisition sources for the selected period' }}</p>
          </div>
        </div>
        <div class="channel-list">
          <div v-for="ch in channels" :key="ch.name" class="channel-row">
            <div class="channel-top">
              <span class="channel-name">{{ ch.name }}</span>
              <span class="channel-pct">{{ ch.pct }}%</span>
            </div>
            <div class="channel-track">
              <div class="channel-fill" :style="{ width: `${ch.pct}%`, background: ch.color }"></div>
            </div>
            <span class="channel-count">{{ formatNumber(ch.count) }}</span>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head">
          <div>
            <h2 class="card-title">Device Breakdown</h2>
            <p class="card-subtitle">Devices used to visit your platform</p>
          </div>
        </div>
        <div class="device-box">
          <svg viewBox="0 0 120 120" class="device-donut">
            <g transform="rotate(-90 60 60)">
              <circle
                v-for="(seg, i) in deviceSlices"
                :key="i"
                cx="60"
                cy="60"
                r="42"
                fill="none"
                :stroke="seg.color"
                stroke-width="15"
                :stroke-dasharray="`${seg.dash} ${seg.gap}`"
                :stroke-dashoffset="`-${seg.offset}`"
              />
            </g>
            <text x="60" y="56" text-anchor="middle" class="donut-total">{{ formatNumber(deviceTotal) }}</text>
            <text x="60" y="70" text-anchor="middle" class="donut-caption">total visits</text>
          </svg>

          <div class="device-list">
            <div v-for="d in devices" :key="d.name" class="device-row">
              <span class="device-icon" :style="{ background: d.soft, color: d.color }">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                  <path :d="d.icon" v-if="d.icon" />
                </svg>
              </span>
              <span class="device-name">{{ d.name }}</span>
              <span class="device-pct">{{ d.pct }}%</span>
              <span class="device-count">{{ formatNumber(d.count) }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { getDashboard } from '../services/admin'

const props = defineProps({
  embedded: { type: Boolean, default: false },
})

const realChat = ref({ total_messages: 0, total_conversations: 0, today_messages: 0, messages_by_conversation: [] })

onMounted(async () => {
  try {
    const res = await getDashboard()
    realChat.value = res.data.chat || { total_messages: 0, total_conversations: 0, today_messages: 0, messages_by_conversation: [] }
  } catch {
    realChat.value = { total_messages: 0, total_conversations: 0, today_messages: 0, messages_by_conversation: [] }
  }
})

const ranges = ['7d', '30d', '90d']
const range = ref('30d')

const DAYS = 90
const VIEW_W = 820
const VIEW_H = 300
const PAD_X = 14
const PAD_T = 24
const PAD_B = 22

function genSeries(n, base, vol) {
  return Array.from({ length: n }, (_, i) => {
    const wave = Math.sin(i / 9) * vol * 0.45
    const wave2 = Math.cos(i / 4.7) * vol * 0.18
    const trend = (i / n) * vol * 0.35 + (i % 5) * vol * 0.05
    const drift = Math.sin(i * 1.7) * vol * 0.08
    return Math.max(60, Math.round(base + wave + wave2 + trend + drift))
  })
}

const fullPage = genSeries(DAYS + 30, 4800, 1250)
const fullVisit = genSeries(DAYS + 30, 2150, 620)

const rangeDays = computed(() => ({ '7d': 7, '30d': 30, '90d': 90 })[range.value])
const pageData = computed(() => fullPage.slice(fullPage.length - rangeDays.value))
const visitData = computed(() => fullVisit.slice(fullVisit.length - rangeDays.value))

function scaleFor(values) {
  const all = [...values, ...(range.value === '30d' || range.value === '7d' ? [] : [])]
  let lo = Math.min(...values)
  let hi = Math.max(...values)
  const pad = (hi - lo || hi * 0.1 || 100) * 0.12
  lo -= pad
  hi += pad
  const innerW = VIEW_W - PAD_X * 2
  const innerH = VIEW_H - PAD_T - PAD_B
  return {
    x: (i, n) => PAD_X + (i / Math.max(1, n - 1)) * innerW,
    y: v => PAD_T + innerH - ((v - lo) / (hi - lo)) * innerH,
    lo,
    hi,
  }
}

function smoothPath(pts) {
  if (!pts.length) return ''
  let d = `M ${pts[0].x} ${pts[0].y}`
  for (let i = 0; i < pts.length - 1; i++) {
    const p0 = pts[Math.max(0, i - 1)]
    const p1 = pts[i]
    const p2 = pts[i + 1]
    const p3 = pts[Math.min(pts.length - 1, i + 2)]
    const c1x = p1.x + (p2.x - p0.x) / 6
    const c1y = p1.y + (p2.y - p0.y) / 6
    const c2x = p2.x - (p3.x - p1.x) / 6
    const c2y = p2.y - (p3.y - p1.y) / 6
    d += ` C ${c1x} ${c1y}, ${c2x} ${c2y}, ${p2.x} ${p2.y}`
  }
  return d
}

function buildPath(values) {
  const s = scaleFor(values)
  const pts = values.map((v, i) => ({ x: s.x(i, values.length), y: s.y(v), i }))
  return { pts, line: smoothPath(pts) }
}

const pageChart = computed(() => buildPath(pageData.value))
const visitChart = computed(() => buildPath(visitData.value))
const pagePts = computed(() => pageChart.value.pts)
const visitPts = computed(() => visitChart.value.pts)
const pagePath = computed(() => pageChart.value.line)
const visitPath = computed(() => visitChart.value.line)

function areaPath(pts, line) {
  if (!pts.length) return ''
  const last = pts[pts.length - 1]
  return `${line} L ${last.x} ${VIEW_H - PAD_B} L ${pts[0].x} ${VIEW_H - PAD_B} Z`
}
const pageAreaPath = computed(() => areaPath(pagePts.value, pagePath.value))
const visitAreaPath = computed(() => areaPath(visitPts.value, visitPath.value))

const gridLines = computed(() => {
  const vals = pageData.value.concat(visitData.value)
  const s = scaleFor(vals)
  const lines = []
  for (let i = 0; i <= 4; i++) {
    const v = s.lo + ((s.hi - s.lo) / 4) * i
    lines.push({ y: s.y(v), x1: PAD_X, x2: VIEW_W - PAD_X, label: formatCompact(Math.round(v)) })
  }
  return lines
})

const xLabels = computed(() => {
  const n = pageData.value.length
  const step = Math.max(1, Math.ceil(n / 6))
  const labels = []
  for (let i = 0; i < n; i += step) {
    labels.push({ x: PAD_X + (i / Math.max(1, n - 1)) * (VIEW_W - PAD_X * 2), label: i === 0 ? 'Start' : `${i}d` })
  }
  return labels
})

const hoverIndex = ref(-1)
function onChartMove(e) {
  const rect = e.currentTarget.getBoundingClientRect()
  if (!rect.width) return
  const relX = e.clientX - rect.left
  const x = (relX / rect.width) * VIEW_W
  const n = pageData.value.length
  const i = Math.round(((x - PAD_X) / (VIEW_W - PAD_X * 2)) * (n - 1))
  hoverIndex.value = Math.max(0, Math.min(n - 1, i))
}

const tooltipStyle = computed(() => {
  const i = hoverIndex.value
  const p = pagePts.value[i]
  if (!p) return {}
  return { left: (p.x / VIEW_W) * 100 + '%', top: (p.y / VIEW_H) * 100 + '%' }
})

const startDate = Date.UTC(2026, 7, 1)
const dateLabel = i => {
  const d = new Date(startDate + (fullPage.length - rangeDays.value + i) * 86400000)
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

function formatNumber(n) {
  return new Intl.NumberFormat('en-US').format(n)
}
function formatCompact(n) {
  if (n >= 1000) return (n / 1000).toFixed(n >= 10000 ? 0 : 1) + 'k'
  return String(n)
}

function windowSum(arr, days, end) {
  return arr.slice(end - days, end).reduce((a, b) => a + b, 0)
}
function percentDelta(cur, prev) {
  if (!prev) return 0
  return ((cur - prev) / prev) * 100
}

const kpiPageviews = computed(() => windowSum(fullPage, rangeDays.value, fullPage.length))
const kpiPageviewsDelta = computed(() => percentDelta(kpiPageviews.value, windowSum(fullPage, rangeDays.value, fullPage.length - rangeDays.value)).toFixed(1))

const bounceRate = computed(() => Math.round((41.5 + (kpiPageviews.value % 9) * 0.21) * 10) / 10)
const bounceDelta = computed(() => (2.4 + (rangeDays.value % 5) * 0.2).toFixed(1))

const avgDuration = computed(() => {
  const mins = Math.floor(2 + (kpiPageviews.value % 240) / 60)
  const secs = 12 + (kpiPageviews.value % 55)
  return `${mins}m ${secs}s`
})
const durationPct = computed(() => Math.min(100, 58 + (kpiPageviews.value % 30) * 0.9))
const durationDelta = computed(() => (6.1 + (rangeDays.value % 4) * 0.3).toFixed(1))

const kpiSessions = computed(() => windowSum(fullVisit, rangeDays.value, fullVisit.length))
const sessionsDelta = computed(() => percentDelta(kpiSessions.value, windowSum(fullVisit, rangeDays.value, fullVisit.length - rangeDays.value)).toFixed(1))

const sessionBars = computed(() => {
  const last = visitData.value.slice(-7)
  const max = Math.max(...last, 1)
  return last.map(v => Math.max(8, Math.round((v / max) * 100)))
})

const SPARK_W = 84
const SPARK_H = 34
const pageSpark = computed(() => {
  const values = pageData.value.slice(-12)
  const s = scaleFor(values)
  const pts = values.map((v, i) => ({ x: (i / Math.max(1, values.length - 1)) * (SPARK_W - 2) + 1, y: s.y(v) }))
  return pts
})
const pageSparkPath = computed(() => smoothPath(pageSpark.value))
const pageSparkArea = computed(() => {
  const pts = pageSpark.value
  if (!pts.length) return ''
  const last = pts[pts.length - 1]
  return `${smoothPath(pts)} L ${last.x} ${SPARK_H - 1} L ${pts[0].x} ${SPARK_H - 1} Z`
})

const fallbackChannels = [
  { name: 'Organic Search', pct: 48, count: 2841, color: 'linear-gradient(90deg, #10B981, #34D399)' },
  { name: 'Direct', pct: 24, count: 1420, color: 'linear-gradient(90deg, #3B82F6, #60A5FA)' },
  { name: 'Referral', pct: 16, count: 947, color: 'linear-gradient(90deg, #8B5CF6, #A78BFA)' },
  { name: 'Social Media', pct: 12, count: 710, color: 'linear-gradient(90deg, #F59E0B, #FBBF24)' },
]

const channelColors = [
  'linear-gradient(90deg, #10B981, #34D399)',
  'linear-gradient(90deg, #3B82F6, #60A5FA)',
  'linear-gradient(90deg, #8B5CF6, #A78BFA)',
  'linear-gradient(90deg, #F59E0B, #FBBF24)',
  'linear-gradient(90deg, #EC4899, #F472B6)',
]

const channels = computed(() => {
  const convs = realChat.value.messages_by_conversation || []
  if (!convs.length) return fallbackChannels
  const total = convs.reduce((sum, c) => sum + (c.count || 0), 0) || 1
  return convs.map((c, i) => ({
    name: c.label,
    count: c.count,
    pct: Math.round((c.count / total) * 100),
    color: channelColors[i % channelColors.length],
  }))
})

const devices = [
  { name: 'Desktop', pct: 58, count: 13204, color: '#10B981', soft: 'rgba(16,185,129,0.12)', icon: 'M2 3h20v14H2z M8 21h8 M12 17v4' },
  { name: 'Mobile', pct: 34, count: 7742, color: '#3B82F6', soft: 'rgba(59,130,246,0.12)', icon: 'M7 2h10v20H7z M11 18h2' },
  { name: 'Tablet', pct: 8, count: 1821, color: '#8B5CF6', soft: 'rgba(139,92,246,0.12)', icon: 'M4 4h16v16H4z' },
]
const deviceTotal = computed(() => devices.reduce((a, d) => a + d.count, 0))

const deviceSlices = computed(() => {
  const C = 2 * Math.PI * 42
  let acc = 0
  return devices.map(d => {
    const frac = d.count / deviceTotal.value
    const dash = Math.max(0, frac * C - 2)
    const offset = acc * C
    acc += frac
    return { ...d, dash, gap: C - dash, offset }
  })
})
</script>

<style scoped>
.analytics-page {
  background: #F8FAFC;
  margin: -2rem -2.5rem;
  padding: 2rem 2.5rem;
  min-height: calc(100vh - 0px);
  font-family: inherit;
  --green: #10B981;
  --blue: #3B82F6;
}

.analytics-page.embedded {
  margin: 0;
  padding: 0;
  background: transparent;
  min-height: 0;
}

.analytics-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1.5rem;
  flex-wrap: wrap;
  margin-bottom: 1.75rem;
}

.analytics-title {
  font-size: 1.8rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: #0F172A;
  margin: 0;
}

.analytics-subtitle {
  margin: 0.35rem 0 0;
  font-size: 0.9rem;
  color: #64748B;
}

.range-switch {
  display: flex;
  background: #EAF0F6;
  border-radius: 10px;
  padding: 4px;
  gap: 2px;
}

.range-btn {
  border: none;
  background: transparent;
  color: #64748B;
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.45rem 0.95rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.18s;
}

.range-btn:hover {
  color: #0F172A;
}

.range-btn.active {
  background: #fff;
  color: #0F172A;
  box-shadow: 0 1px 3px rgba(15, 23, 42, 0.12);
}

.card {
  background: #fff;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 8px 24px rgba(15, 23, 42, 0.06);
  border: 1px solid rgba(15, 23, 42, 0.04);
}

.card-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 1.25rem;
}

.card-title {
  font-size: 1rem;
  font-weight: 700;
  color: #0F172A;
  margin: 0;
  letter-spacing: -0.01em;
}

.card-subtitle {
  margin: 0.25rem 0 0;
  font-size: 0.8rem;
  color: #94A3B8;
}

.legend {
  display: flex;
  gap: 1rem;
}

.legend-item {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.78rem;
  font-weight: 600;
  color: #475569;
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.dot-green { background: var(--green); }
.dot-blue { background: var(--blue); }

.chart-wrap {
  position: relative;
  width: 100%;
  height: 300px;
}

.area-chart {
  width: 100%;
  height: 100%;
  overflow: visible;
}

.grid-line {
  stroke: #EEF2F7;
  stroke-width: 1;
  stroke-dasharray: 4 6;
}

.grid-label {
  font-size: 11px;
  fill: #A7B4C4;
}

.axis-label {
  font-size: 11px;
  fill: #A7B4C4;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.line {
  stroke-width: 2.75;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.line-green { stroke: var(--green); }
.line-blue { stroke: var(--blue); }

.hover-line {
  stroke: #CBD5E1;
  stroke-width: 1;
  stroke-dasharray: 3 4;
}

.hover-dot {
  fill: #fff;
  stroke: var(--green);
  stroke-width: 2.5;
}

.chart-tooltip {
  position: absolute;
  transform: translate(-50%, -115%);
  background: #0F172A;
  color: #F1F5F9;
  border-radius: 10px;
  padding: 0.6rem 0.85rem;
  font-size: 0.75rem;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.25);
  white-space: nowrap;
  z-index: 5;
  pointer-events: none;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.tip-date {
  color: #94A3B8;
  font-size: 0.7rem;
  margin-bottom: 0.15rem;
}

.tip-row {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.tip-row b {
  color: #fff;
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.25rem;
  margin-top: 1.25rem;
}

.kpi-card {
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
  transition: box-shadow 0.2s, transform 0.2s;
}

.kpi-card:hover {
  box-shadow: 0 12px 32px rgba(15, 23, 42, 0.1);
  transform: translateY(-2px);
}

.kpi-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.kpi-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748B;
  letter-spacing: 0.01em;
}

.kpi-icon {
  width: 30px;
  height: 30px;
  border-radius: 9px;
  display: grid;
  place-items: center;
}

.pill-green { background: rgba(16, 185, 129, 0.12); color: var(--green); }
.pill-blue { background: rgba(59, 130, 246, 0.12); color: var(--blue); }

.kpi-value-row {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 1rem;
}

.kpi-value {
  font-size: 1.85rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: #0F172A;
  line-height: 1.1;
}

.kpi-delta {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.2rem 0.5rem;
  border-radius: 999px;
  margin-left: 0.6rem;
  vertical-align: 2px;
}

.kpi-delta.up { background: rgba(16, 185, 129, 0.12); color: #059669; }
.kpi-delta.down { background: rgba(239, 68, 68, 0.1); color: #DC2626; }

.spark {
  width: 84px;
  height: 34px;
  flex-shrink: 0;
}

.spark-line {
  stroke: var(--green);
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.spark-area {
  stroke: none;
}

.kpi-radial-row {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.radial {
  width: 74px;
  height: 74px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.radial-value {
  font-size: 0.95rem;
  font-weight: 800;
  color: #0F172A;
}

.kpi-note {
  margin: 0.4rem 0 0;
  font-size: 0.72rem;
  color: #94A3B8;
}

.mini-bars {
  display: flex;
  align-items: flex-end;
  gap: 3px;
  height: 34px;
  flex-shrink: 0;
}

.mini-bar {
  width: 8px;
  border-radius: 3px 3px 0 0;
  background: linear-gradient(180deg, #60A5FA, #3B82F6);
}

.chat-kpi-rows {
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
}

.chat-kpi-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 0.6rem;
}

.chat-kpi-num {
  font-size: 1.35rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: #0F172A;
  line-height: 1;
}

.chat-kpi-label {
  font-size: 0.72rem;
  color: #94A3B8;
}

.chat-kpi-row.today .chat-kpi-num {
  color: var(--green);
}

.chat-strip {
  margin-top: 1.25rem;
}

.chat-strip-cols {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.chat-strip-col {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  background: #F8FAFC;
  border: 1px solid rgba(15, 23, 42, 0.04);
  border-radius: 12px;
  padding: 1.1rem 1.25rem;
}

.chat-strip-col .chat-kpi-num {
  font-size: 1.7rem;
}

.chat-strip-col.today .chat-kpi-num {
  color: var(--green);
}

@media (max-width: 560px) {
  .chat-strip-cols {
    grid-template-columns: 1fr;
  }
}

.bottom-grid {
  display: grid;
  grid-template-columns: 1fr 1.1fr;
  gap: 1.25rem;
  margin-top: 1.25rem;
}

.channel-list {
  display: flex;
  flex-direction: column;
  gap: 1.15rem;
}

.channel-row {
  display: grid;
  grid-template-columns: 1fr 44px;
  align-items: center;
  gap: 0.3rem 0.6rem;
}

.channel-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.channel-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: #334155;
}

.channel-pct {
  font-size: 0.85rem;
  font-weight: 700;
  color: #0F172A;
}

.channel-track {
  grid-column: 1 / -1;
  grid-row: 2;
  height: 8px;
  border-radius: 999px;
  background: #EEF2F7;
  overflow: hidden;
}

.channel-fill {
  height: 100%;
  border-radius: 999px;
  transition: width 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}

.channel-count {
  grid-column: 1 / -1;
  grid-row: 3;
  justify-self: end;
  font-size: 0.72rem;
  color: #94A3B8;
}

.device-box {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.device-donut {
  width: 150px;
  height: 150px;
  flex-shrink: 0;
}

.donut-total {
  font-size: 1.25rem;
  font-weight: 800;
  fill: #0F172A;
}

.donut-caption {
  font-size: 0.55rem;
  fill: #94A3B8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.device-list {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
}

.device-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.device-icon {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.device-name {
  flex: 1;
  font-size: 0.85rem;
  font-weight: 600;
  color: #334155;
}

.device-pct {
  font-size: 0.85rem;
  font-weight: 800;
  color: #0F172A;
}

.device-count {
  font-size: 0.75rem;
  color: #94A3B8;
  min-width: 48px;
  text-align: right;
}

@media (max-width: 1200px) {
  .kpi-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 900px) {
  .analytics-page {
    margin: -1.5rem;
    padding: 1.5rem;
  }
  .bottom-grid {
    grid-template-columns: 1fr;
  }
  .chart-wrap {
    height: 240px;
  }
  .device-box {
    flex-direction: column;
    gap: 1.25rem;
  }
}

@media (max-width: 560px) {
  .kpi-grid {
    grid-template-columns: 1fr;
  }
  .analytics-header {
    flex-direction: column;
  }
  .range-switch {
    width: 100%;
  }
  .range-btn {
    flex: 1;
  }
}
</style>