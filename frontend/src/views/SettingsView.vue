<template>
  <main class="settings-page container">
    <div class="settings-header">
      <RouterLink to="/profile" class="btn btn-ghost btn-icon">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
      </RouterLink>
      <h1 class="settings-title">{{ t('settings.title') }}</h1>
    </div>

    <div class="settings-list">
      <RouterLink to="/settings/blocked" class="settings-item">
        <span class="settings-icon">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="9"/>
            <line x1="3" y1="3" x2="21" y2="21"/>
          </svg>
        </span>
        <span class="settings-info">
          <span class="settings-label">{{ t('settings.blockedUsers') }}</span>
          <span class="settings-desc">{{ t('settings.blockedDesc') }}</span>
        </span>
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="settings-chevron">
          <polyline points="9 18 15 12 9 6"/>
        </svg>
      </RouterLink>

      <div class="settings-item">
        <span class="settings-icon">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="9"/>
            <path d="M3 12h18"/>
            <path d="M12 3a14 14 0 0 1 0 18a14 14 0 0 1 0-18Z"/>
          </svg>
        </span>
        <span class="settings-info">
          <span class="settings-label">{{ t('settings.language') }}</span>
          <div class="settings-locale"><LocaleSwitcher /></div>
        </span>
      </div>

      <button class="settings-item settings-danger" @click="logout">
        <span class="settings-icon">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </span>
        <span class="settings-info">
          <span class="settings-label">{{ t('nav.logout') }}</span>
          <span class="settings-desc">{{ t('settings.logoutDesc') }}</span>
        </span>
      </button>
    </div>
  </main>
</template>

<script setup>
import { RouterLink, useRouter } from 'vue-router'
import { t } from '../i18n'
import LocaleSwitcher from '../components/LocaleSwitcher.vue'

const router = useRouter()

function logout() {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}
</script>

<style scoped>
.settings-page {
  max-width: 720px;
  padding: 1.5rem 1rem 2rem;
}

.settings-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

.settings-header .btn-icon {
  padding: 0.5rem 0.65rem;
}

.settings-title {
  font-size: 1.35rem;
  font-weight: 700;
  margin: 0;
}

.settings-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 0.5rem;
}

.settings-item {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.85rem 0.9rem;
  border: none;
  background: none;
  border-radius: var(--radius-md);
  cursor: pointer;
  text-decoration: none;
  color: var(--text);
  text-align: left;
  font: inherit;
  transition: background 0.12s;
  width: 100%;
}

.settings-item:hover {
  background: var(--surface-2);
}

.settings-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: var(--surface-2);
  color: var(--accent);
  flex-shrink: 0;
}

.settings-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}

.settings-label {
  font-size: 0.95rem;
  font-weight: 600;
}

.settings-desc {
  font-size: 0.78rem;
  color: var(--text-muted);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.settings-chevron {
  color: var(--text-muted);
  flex-shrink: 0;
}

.settings-danger .settings-icon {
  background: var(--danger-soft);
  color: var(--danger);
}

.settings-danger .settings-label {
  color: var(--danger);
}

.settings-locale {
  margin-top: 0.25rem;
}
</style>