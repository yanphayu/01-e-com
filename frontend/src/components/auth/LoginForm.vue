<template>
  <form class="auth-form" @submit.prevent="handleSubmit">
    <h2>Login</h2>

    <p v-if="error" class="error">{{ error }}</p>

    <div class="field">
      <label for="email">Email</label>
      <input id="email" v-model="email" type="email" required autocomplete="email" />
    </div>

    <div class="field">
      <label for="password">Password</label>
      <div class="input-wrap">
        <input
          id="password"
          v-model="password"
          :type="showPassword ? 'text' : 'password'"
          required
          autocomplete="current-password"
        />
        <button
          type="button"
          class="toggle"
          tabindex="-1"
          :aria-label="showPassword ? 'Hide password' : 'Show password'"
          @click="showPassword = !showPassword"
        >
          <svg v-if="!showPassword" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
            <circle cx="12" cy="12" r="3" />
          </svg>
          <svg v-else viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c6.5 0 10 8 10 8a13.16 13.16 0 0 1-1.67 2.68" />
            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3.5 8 10 8a9.74 9.74 0 0 0 5.39-1.61" />
            <line x1="2" x2="22" y1="2" y2="22" />
          </svg>
        </button>
      </div>
    </div>

    <button type="submit" class="submit" :disabled="loading">
      {{ loading ? 'Logging in...' : 'Login' }}
    </button>

    <p class="switch">
      No account?
      <RouterLink to="/register">Register</RouterLink>
    </p>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { loginUser } from '../../services/auth'

const router = useRouter()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const error = ref(null)

async function handleSubmit() {
  loading.value = true
  error.value = null

  try {
    const data = await loginUser({
      email: email.value,
      password: password.value,
    })

    localStorage.setItem('token', data.token)
    router.push('/')
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}
</script>


