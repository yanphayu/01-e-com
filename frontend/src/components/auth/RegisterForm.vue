<template>
  <form class="auth-card stack" @submit.prevent="handleSubmit">
    <div class="auth-head">
      <h1>{{ t('auth.createAccount') }}</h1>
      <p>{{ t('auth.joinSub') }}</p>
    </div>

    <p v-if="error" class="alert alert-error">{{ error }}</p>
    <p v-if="success" class="alert alert-success">{{ success }}</p>

    <BaseInput
      v-model="email"
      :label="t('auth.email')"
      type="email"
      :placeholder="t('auth.emailPlaceholder')"
      autocomplete="email"
    />

    <BaseInput
      v-model="password"
      :label="t('auth.password')"
      :type="showPassword ? 'text' : 'password'"
      :placeholder="t('auth.passwordPlaceholder')"
      autocomplete="new-password"
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

    <BaseInput
      v-model="passwordConfirmation"
      :label="t('auth.confirmPassword')"
      :type="showConfirmPassword ? 'text' : 'password'"
      :placeholder="t('auth.confirmPlaceholder')"
      autocomplete="new-password"
    >
      <template #suffix>
        <button
          type="button"
          class="toggle"
          tabindex="-1"
          :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
          @click="showConfirmPassword = !showConfirmPassword"
        >
          <svg v-if="!showConfirmPassword" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
      {{ loading ? t('auth.creatingAccount') : t('auth.createAccount') }}
    </BaseButton>

    <div class="auth-divider">
      <span>{{ t('auth.orDivider') }}</span>
    </div>

    <GoogleAuthButton />

    <p class="auth-foot">
      {{ t('auth.alreadyAccount') }}
      <RouterLink to="/login">{{ t('auth.signInLink') }}</RouterLink>
    </p>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { registerUser } from '../../services/auth'
import { t } from '../../i18n'
import BaseInput from '../../design-system/BaseInput.vue'
import BaseButton from '../../design-system/BaseButton.vue'
import GoogleAuthButton from './GoogleAuthButton.vue'

const router = useRouter()

const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const loading = ref(false)
const error = ref(null)
const success = ref(null)

async function handleSubmit() {
  if (password.value !== passwordConfirmation.value) {
    error.value = t('auth.passwordsMismatch')
    return
  }

  loading.value = true
  error.value = null

  try {
    const data = await registerUser({
      name: email.value.split('@')[0],
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })

    sessionStorage.setItem('verifyEmail', email.value)
    sessionStorage.setItem('verifyUserId', data.data.id)
    success.value = t('auth.accountCreated')
    router.push('/verify-email')
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}
</script>


