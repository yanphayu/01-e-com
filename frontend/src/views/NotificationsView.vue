<template>
  <div class="notifications-page page-shell">
    <h1 class="page-title section-title">{{ t('nav.notifications') }}</h1>

    <div v-if="loading" class="loading">{{ t('product.loading') }}</div>

    <template v-else>
      <div v-if="unreadCount > 0 || readCount > 0" class="notif-actions">
        <button v-if="readCount > 0" class="btn btn-ghost" @click="clearRead">
          {{ t('nav.clearRead') }}
        </button>
        <button v-if="unreadCount > 0" class="btn btn-ghost" @click="markAllRead">{{ t('nav.markAllRead') }}</button>
      </div>

      <div v-if="notifications.length === 0" class="empty">
        {{ t('nav.noNotifications') }}
      </div>

      <div v-else class="notif-list">
        <div
          v-for="n in notifications"
          :key="n.id"
          class="notif-item"
          :class="{ unread: !n.read_at }"
          @click="onNotifClick(n)"
        >
          <img v-if="n.data.user?.avatar" :src="resolveStorageUrl(n.data.user.avatar)" class="notif-avatar" alt="" />
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
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { t } from '../i18n'
import { getNotifications, getUnreadCount, markAsRead, markAllAsRead, clearReadNotifications } from '../services/notifications'
import { resolveStorageUrl } from '../services/http'

const router = useRouter()
const notifications = ref([])
const unreadCount = ref(0)
const loading = ref(true)

const readCount = computed(() => notifications.value.filter(n => n.read_at).length)

onMounted(async () => {
  try {
    const [notifs, count] = await Promise.all([getNotifications(), getUnreadCount()])
    notifications.value = notifs.data || []
    unreadCount.value = count.data?.count || 0
  } catch {
    // ignore
  } finally {
    loading.value = false
  }
})

async function onNotifClick(n) {
  if (!n.read_at) {
    await markAsRead(n.id)
    n.read_at = new Date().toISOString()
    unreadCount.value = Math.max(0, unreadCount.value - 1)
    window.dispatchEvent(new Event('notifications-read'))
  }
  if (n.data.product_id) {
    router.push(`/products/${n.data.product_id}`)
  }
}

async function markAllRead() {
  await markAllAsRead()
  notifications.value.forEach(n => { n.read_at = new Date().toISOString() })
  unreadCount.value = 0
  window.dispatchEvent(new Event('notifications-read'))
}

async function clearRead() {
  await clearReadNotifications()
  notifications.value = notifications.value.filter(n => !n.read_at)
  window.dispatchEvent(new Event('notifications-read'))
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
</script>

<style scoped>
.notifications-page {
  width: 100%;
  max-width: 760px;
  margin-inline: auto;
}

.page-title {
  margin-bottom: 1.5rem;
}

.notif-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.loading,
.empty {
  padding: 4rem 1.5rem;
  border: 1px dashed var(--border-strong);
  border-radius: var(--radius-md);
  background: var(--surface);
  color: var(--text-muted);
  text-align: center;
}

.notif-list {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  padding: 0.5rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  background: var(--surface);
  box-shadow: var(--shadow-sm);
}

.notif-item {
  display: flex;
  align-items: flex-start;
  gap: 0.85rem;
  padding: 0.9rem 0.8rem;
  border: 1px solid transparent;
  border-radius: var(--radius-sm);
  cursor: pointer;
}

.notif-item:hover {
  background: var(--surface-2);
}

.notif-item.unread {
  border-color: color-mix(in srgb, var(--primary) 20%, transparent);
  background: var(--accent-soft);
  box-shadow: inset 3px 0 0 var(--primary);
}

.notif-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.notif-avatar-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--border);
  background: var(--surface-3);
  color: var(--text-muted);
  font-family: var(--font-mono);
  font-weight: 700;
  font-size: 0.875rem;
}

.notif-content {
  flex: 1;
  min-width: 0;
  padding-top: 0.1rem;
}

.notif-text {
  font-size: 0.9rem;
  line-height: 1.5;
  margin: 0;
  color: var(--text-muted);
}

.notif-text strong {
  color: var(--text);
}

.notif-time {
  display: block;
  margin-top: 0.3rem;
  color: var(--text-muted);
  font-family: var(--font-mono);
  font-size: 0.72rem;
}

.btn-ghost {
  color: var(--primary);
  background: var(--surface);
  border-color: var(--border);
}

.btn-ghost:hover:not(:disabled) {
  color: var(--primary);
  background: var(--accent-soft);
  border-color: color-mix(in srgb, var(--primary) 32%, transparent);
}

@media (max-width: 480px) {
  .notif-item {
    gap: 0.65rem;
    padding: 0.8rem 0.65rem;
  }

  .notif-avatar {
    width: 36px;
    height: 36px;
  }
}
</style>
