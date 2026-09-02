<template>
  <form class="auth-card stack" autocomplete="off" @submit.prevent="handleSubmit">
    <div class="auth-head">
      <h1>{{ t('auth.welcome') }}</h1>
      <p>{{ t('auth.welcomeSub') }}</p>
    </div>

    <p v-if="error" class="alert alert-error">{{ error }}</p>

    <BaseInput
      v-model="email"
      :label="t('auth.email')"
      type="email"
      :placeholder="t('auth.emailPlaceholder')"
      autocomplete="off"
      readonly
    />

    <BaseInput
      v-model="password"
      :label="t('auth.password')"
      :type="showPassword ? 'text' : 'password'"
      :placeholder="t('auth.passwordPlaceholder')"
      autocomplete="new-password"
      readonly
    >
      <template #suffix>
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
      </template>
    </BaseInput>

    <BaseButton type="submit" variant="primary" block :loading="loading">
      {{ loading ? t('auth.loggingIn') : t('auth.login') }}
    </BaseButton>

    <p class="auth-foot">
      {{ t('auth.noAccount') }}
      <RouterLink to="/register">{{ t('auth.createOne') }}</RouterLink>
    </p>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { loginUser } from '../../services/auth'
import { t } from '../../i18n'
import BaseInput from '../../design-system/BaseInput.vue'
import BaseButton from '../../design-system/BaseButton.vue'

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
    localStorage.setItem('user', JSON.stringify(data.data))
    router.push('/')
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}
</script>


