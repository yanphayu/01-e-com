<template>
  <div class="notifications-page">
    <h1 class="page-title">{{ t('nav.notifications') }}</h1>

    <div v-if="loading" class="loading">{{ t('product.loading') }}</div>

    <template v-else>
      <div v-if="unreadCount > 0" class="notif-actions">
        <button class="btn btn-ghost" @click="markAllRead">{{ t('nav.markAllRead') }}</button>
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
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { t } from '../i18n'
import { getNotifications, getUnreadCount, markAsRead, markAllAsRead } from '../services/notifications'

const router = useRouter()
const notifications = ref([])
const unreadCount = ref(0)
const loading = ref(true)

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
  max-width: 720px;
  margin: 0 auto;
  padding: 2rem 1.5rem 3rem;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
}

.notif-actions {
  margin-bottom: 1rem;
}

.empty {
  text-align: center;
  color: #888;
  padding: 3rem 0;
}

.notif-list {
  display: flex;
  flex-direction: column;
}

.notif-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.875rem 0;
  border-bottom: 1px solid #eee;
  cursor: pointer;
  transition: background 0.15s;
}

.notif-item:hover {
  background: #f9f9f9;
}

.notif-item.unread {
  background: #f0fdf4;
}

.notif-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.notif-avatar-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #e0e0e0;
  color: #555;
  font-weight: 600;
  font-size: 0.875rem;
}

.notif-content {
  flex: 1;
  min-width: 0;
}

.notif-text {
  font-size: 0.9rem;
  line-height: 1.4;
  margin: 0;
}

.notif-time {
  font-size: 0.75rem;
  color: #999;
  margin-top: 0.25rem;
  display: block;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: background 0.15s;
}

.btn-ghost {
  background: transparent;
  color: #16a34a;
}

.btn-ghost:hover {
  background: #f0fdf4;
}

@media (max-width: 480px) {
  .notifications-page {
    padding: 1.5rem 1rem 2rem;
  }
  .page-title {
    font-size: 1.25rem;
  }
}
</style>
