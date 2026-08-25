<template>
  <form class="auth-form" @submit.prevent="handleSubmit">
    <h2>Verify email</h2>

    <p class="hint">Enter the verification code sent to your email.</p>

    <p v-if="error" class="error">{{ error }}</p>
    <p v-if="success" class="success">Email verified successfully!</p>

    <div class="field">
      <label for="email">Email</label>
      <input id="email" v-model="email" type="email" required autocomplete="email" />
    </div>

    <div class="field">
      <label for="code">Verification code</label>
      <input id="code" v-model="code" type="text" required inputmode="numeric" />
    </div>

    <button type="submit" class="submit" :disabled="loading">
      {{ loading ? 'Verifying...' : 'Verify' }}
    </button>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { verifyEmail } from '../../services/auth'

const email = ref('')
const code = ref('')
const loading = ref(false)
const error = ref(null)
const success = ref(false)

async function handleSubmit() {
  loading.value = true
  error.value = null
  success.value = false

  try {
    await verifyEmail({
      email: email.value,
      code: code.value,
    })

    success.value = true
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}
</script>


