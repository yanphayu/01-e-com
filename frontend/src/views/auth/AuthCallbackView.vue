<template>
  <main class="auth-page">
    <div class="callback-box">
      <div class="spinner"></div>
      <p>{{ t('auth.signingIn') }}</p>
    </div>
  </main>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { t } from '../../i18n'
import { getUser } from '../../services/auth'

const route = useRoute()
const router = useRouter()

onMounted(async () => {
  const token = route.query.token
  const isNew = route.query.new === '1'

  if (!token) {
    router.replace({ name: 'login' })
    return
  }

  localStorage.setItem('token', token)

  try {
    const data = await getUser()
    localStorage.setItem('user', JSON.stringify(data.data))
    window.dispatchEvent(new Event('auth-changed'))
  } catch {
    localStorage.removeItem('token')
    router.replace({ name: 'login' })
    return
  }

  router.replace(isNew ? { name: 'profile-setup' } : { name: 'home' })
})
</script>

<style scoped>
.callback-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  color: var(--text-muted);
}

.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid var(--border);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>