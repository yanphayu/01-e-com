<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-header">
        <svg viewBox="138.8 116 322.4 283" fill="none" class="login-logo">
          <path d="M 300 130 L 259.5 276.6 L 152.8 385 L 300 346.8 L 447.2 385 L 340.5 276.6 Z" stroke="currentColor" stroke-width="26" stroke-linejoin="miter" stroke-miterlimit="10" />
          <path d="M 259.5 276.6 L 300 346.8 L 340.5 276.6 Z" fill="currentColor" />
          <circle cx="300" cy="130" r="9" fill="currentColor" />
          <circle cx="152.8" cy="385" r="9" fill="currentColor" />
          <circle cx="447.2" cy="385" r="9" fill="currentColor" />
        </svg>
        <h1>TRINITY Admin</h1>
        <p>Sign in to admin dashboard</p>
      </div>

      <div v-if="error" class="alert alert-error">{{ error }}</div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="field">
          <label>Email</label>
          <input v-model="form.email" type="email" class="input" placeholder="admin@trinity.com" required />
        </div>
        <div class="field">
          <label>Password</label>
          <input v-model="form.password" type="password" class="input" placeholder="Password" required />
        </div>
        <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
          {{ loading ? 'Signing in...' : 'Sign in' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { loginUser } from '../services/auth'

const router = useRouter()
const form = ref({ email: '', password: '' })
const loading = ref(false)
const error = ref('')

async function handleLogin() {
  loading.value = true
  error.value = ''
  try {
    const res = await loginUser(form.value)
    const user = res.data
    if (!user.is_admin) {
      error.value = 'Access denied. Admin account required.'
      return
    }
    localStorage.setItem('admin_token', res.token)
    localStorage.setItem('admin_user', JSON.stringify(user))
    router.push('/')
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  background: var(--bg);
}

.login-card {
  width: 100%;
  max-width: 380px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  padding: 2rem 1.75rem;
}

.login-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.login-logo {
  width: 40px;
  height: 40px;
  color: var(--accent);
  margin-bottom: 0.75rem;
}

.login-header h1 {
  font-size: 1.4rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  color: var(--text);
}

.login-header p {
  margin-top: 0.3rem;
  font-size: 0.85rem;
  color: var(--text-muted);
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.login-form .btn-block {
  margin-top: 0.5rem;
}
</style>
