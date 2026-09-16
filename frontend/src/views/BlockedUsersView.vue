<template>
  <main class="blocked-page container">
    <div class="blocked-header">
      <RouterLink to="/settings" class="btn btn-ghost btn-icon">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
      </RouterLink>
      <h1 class="blocked-title">{{ t('settings.blockedUsers') }}</h1>
    </div>

    <div v-if="loading" class="blocked-state">{{ t('product.loading') }}</div>

    <template v-else>
      <div v-if="blockedUsers.length === 0" class="blocked-state">
        <p>{{ t('blocked.empty') }}</p>
      </div>

      <div v-else class="blocked-list">
        <div v-for="user in blockedUsers" :key="user.id" class="blocked-item">
          <img v-if="user.profile?.avatar" :src="user.profile.avatar" class="blocked-avatar" :alt="user.name" />
          <span v-else class="blocked-avatar blocked-avatar-fallback">{{ (user.name || '?')[0] }}</span>
          <div class="blocked-info">
            <RouterLink :to="`/users/${user.id}`" class="blocked-name">{{ user.name }}</RouterLink>
            <span class="blocked-id">@{{ user.id }}</span>
          </div>
          <button class="btn btn-ghost btn-unblock" :disabled="unblockingId === user.id" @click="onUnblock(user.id)">
            {{ t('blocked.unblock') }}
          </button>
        </div>
      </div>
    </template>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { t } from '../i18n'
import { getBlockedUsers, unblockUser } from '../services/chat'

const blockedUsers = ref([])
const loading = ref(true)
const unblockingId = ref(null)

onMounted(async () => {
  try {
    const res = await getBlockedUsers()
    blockedUsers.value = res.data || []
  } catch {
    // ignore
  } finally {
    loading.value = false
  }
})

async function onUnblock(userId) {
  unblockingId.value = userId
  try {
    await unblockUser(userId)
    blockedUsers.value = blockedUsers.value.filter((u) => u.id !== userId)
  } catch {
    // ignore
  } finally {
    unblockingId.value = null
  }
}
</script>

<style scoped>
.blocked-page {
  max-width: 720px;
  padding: 1.5rem 1rem 2rem;
}

.blocked-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

.blocked-header .btn-icon {
  padding: 0.5rem 0.65rem;
}

.blocked-title {
  font-size: 1.35rem;
  font-weight: 700;
  margin: 0;
}

.blocked-state {
  text-align: center;
  color: var(--text-muted);
  padding: 3rem 0;
  font-size: 0.9rem;
}

.blocked-list {
  display: flex;
  flex-direction: column;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 0.5rem;
}

.blocked-item {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.75rem 0.9rem;
  border-radius: var(--radius-md);
}

.blocked-item:hover {
  background: var(--surface-2);
}

.blocked-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.blocked-avatar-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--surface-2);
  color: var(--text-muted);
  font-weight: 600;
  font-size: 1rem;
}

.blocked-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}

.blocked-name {
  font-weight: 600;
  font-size: 0.95rem;
  color: var(--text);
  text-decoration: none;
}

.blocked-name:hover {
  color: var(--accent);
}

.blocked-id {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.btn-unblock {
  flex-shrink: 0;
}

.btn-unblock:disabled {
  opacity: 0.6;
  cursor: default;
}
</style>