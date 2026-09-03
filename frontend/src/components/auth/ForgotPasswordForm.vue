<template>
  <form class="auth-card stack" autocomplete="off" @submit.prevent="handleSubmit">
    <div class="auth-head">
      <h1>{{ t('auth.forgotPasswordTitle') }}</h1>
      <p>{{ t('auth.forgotPasswordSub') }}</p>
    </div>

    <p v-if="error" class="alert alert-error">{{ error }}</p>
    <p v-if="success" class="alert alert-success">{{ t('auth.codeSent') }}</p>

    <BaseInput
      v-model="email"
      :label="t('auth.email')"
      type="email"
      :placeholder="t('auth.emailPlaceholder')"
      autocomplete="off"
    />

    <BaseButton type="submit" variant="primary" block :loading="loading">
      {{ loading ? t('auth.sendingCode') : t('auth.sendCode') }}
    </BaseButton>

    <p class="auth-foot">
      <RouterLink to="/login">{{ t('auth.backToLogin') }}</RouterLink>
    </p>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { forgotPassword } from '../../services/auth'
import { t } from '../../i18n'
import BaseInput from '../../design-system/BaseInput.vue'
import BaseButton from '../../design-system/BaseButton.vue'

const router = useRouter()

const email = ref('')
const loading = ref(false)
const error = ref(null)
const success = ref(false)

async function handleSubmit() {
  loading.value = true
  error.value = null
  success.value = false

  try {
    await forgotPassword({ email: email.value })
    success.value = true
    sessionStorage.setItem('resetEmail', email.value)
    setTimeout(() => router.push('/reset-password'), 1500)
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}
</script>
