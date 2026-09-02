<template>
  <form class="auth-card stack verify-email-form" @submit.prevent="handleSubmit">
    <div class="auth-head">
      <div class="mail-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="5" width="18" height="14" rx="2" />
          <path d="m3 7 9 6 9-6" />
        </svg>
      </div>
      <h1>{{ t('auth.verifyTitle') }}</h1>
      <p>
        {{ t('auth.verifySub') }}
        <strong>{{ email || t('auth.verifySubYour') }}</strong>.
      </p>
    </div>

    <p v-if="error" class="alert alert-error">{{ error }}</p>
    <p v-if="success" class="alert alert-success">{{ t('auth.verified') }}</p>

    <p v-if="expired" class="alert alert-error">{{ t('auth.expiredMsg') }}</p>
    <p v-else class="expiry">{{ t('auth.codeExpiresIn') }} {{ formatTime(codeExpiry) }}</p>

    <div class="otp" :class="{ 'otp-error': error }">
      <input
        v-for="(digit, i) in digits"
        :key="i"
        :ref="(el) => (inputs[i] = el)"
        v-model="digits[i]"
        type="text"
        inputmode="numeric"
        maxlength="1"
        autocomplete="one-time-code"
        :disabled="success"
        @input="onInput(i)"
        @keydown.backspace="onBackspace(i, $event)"
        @paste="onPaste"
      />
    </div>

    <button
      type="submit"
      class="btn btn-primary btn-block"
      :disabled="loading || success || expired || codeLength !== 6"
    >
      {{ loading ? t('auth.verifying') : success ? t('auth.verifiedBtn') : t('auth.verifyEmail') }}
    </button>

    <p class="auth-foot">
      {{ t('auth.didntGet') }}
      <button type="button" class="resend" :disabled="countdown > 0" @click="resend">
        {{ countdown > 0 ? `${t('auth.resendIn')} ${countdown}s` : t('auth.resend') }}
      </button>
    </p>

    <RouterLink to="/register" class="back-link" @click="goBack">{{ t('auth.useDifferentEmail') }}</RouterLink>
  </form>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { verifyEmail, resendVerification } from '../../services/auth'
import { t } from '../../i18n'

const router = useRouter()

const OTP_TTL = 60

const email = ref(sessionStorage.getItem('verifyEmail') || '')
const userId = sessionStorage.getItem('verifyUserId')
const digits = reactive(['', '', '', '', '', ''])
const inputs = ref([])

const loading = ref(false)
const error = ref(null)
const success = ref(false)
const expired = ref(false)
const codeExpiry = ref(OTP_TTL)
const countdown = ref(0)
let expiryTimer = null
let resendTimer = null
let redirectTimer = null

const code = computed(() => digits.join(''))
const codeLength = computed(() => code.value.length)

function formatTime(seconds) {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${m}:${String(s).padStart(2, '0')}`
}

function focus(i) {
  inputs.value[i]?.focus()
}

function onInput(i) {
  error.value = null
  digits[i] = digits[i].replace(/\D/g, '').slice(-1)
  if (digits[i] && i < 5) focus(i + 1)
}

function onBackspace(i, e) {
  if (digits[i]) return
  if (i > 0) {
    e.preventDefault()
    focus(i - 1)
  }
}

function onPaste(e) {
  const text = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6)
  if (!text) return
  e.preventDefault()
  for (let i = 0; i < 6; i++) digits[i] = text[i] ?? ''
  focus(Math.min(text.length, 5))
}

async function handleSubmit() {
  if (codeLength.value !== 6 || expired.value) return
  loading.value = true
  error.value = null
  success.value = false

  try {
    const data = await verifyEmail({ user_id: userId, email: email.value, otp: code.value })
    success.value = true
    stopExpiry()
    sessionStorage.removeItem('verifyEmail')
    if (data.token) {
      localStorage.setItem('token', data.token)
    }
    redirectTimer = setTimeout(() => router.push('/profile-setup'), 1500)
  } catch (err) {
    if (/expired/i.test(err.message)) {
      expired.value = true
      error.value = null
    } else {
      error.value = err.message
    }
    digits.fill('')
    focus(0)
  } finally {
    loading.value = false
  }
}

async function resend() {
  if (!email.value) {
    error.value = 'Enter your email first.'
    return
  }
  error.value = null
  try {
    await resendVerification({ user_id: userId, email: email.value })
    startCountdown(OTP_TTL)
    startExpiry()
  } catch (err) {
    error.value = err.message
  }
}

function startExpiry() {
  expired.value = false
  codeExpiry.value = OTP_TTL
  clearInterval(expiryTimer)
  expiryTimer = setInterval(() => {
    if (codeExpiry.value > 0) {
      codeExpiry.value -= 1
      if (codeExpiry.value === 0) {
        expired.value = true
      }
    }
  }, 1000)
}

function stopExpiry() {
  clearInterval(expiryTimer)
}

function startCountdown(seconds) {
  countdown.value = seconds
  clearInterval(resendTimer)
  if (seconds <= 0) return
  resendTimer = setInterval(() => {
    countdown.value -= 1
    if (countdown.value <= 0) clearInterval(resendTimer)
  }, 1000)
}

function goBack() {
  sessionStorage.removeItem('verifyEmail')
  router.push('/register')
}

onMounted(() => {
  focus(0)
  startExpiry()
  startCountdown(OTP_TTL)
})
onUnmounted(() => {
  clearInterval(expiryTimer)
  clearInterval(resendTimer)
  clearTimeout(redirectTimer)
})
</script>

<style scoped>
.verify-email-form {
  text-align: center;
}

.mail-icon {
  width: 50px;
  height: 50px;
  margin: 0 auto 0.85rem;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: var(--accent-soft);
  color: var(--accent);
}

.verify-email-form .expiry {
  font-size: 0.82rem;
  color: var(--text-muted);
  margin-top: 0;
}

.otp {
  display: flex;
  gap: 0.45rem;
  justify-content: center;
}

.otp input {
  flex: 1 1 0;
  min-width: 0;
  max-width: 46px;
  height: 50px;
  padding: 0;
  text-align: center;
  font-size: 1.25rem;
  font-weight: 600;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface);
  color: var(--text);
  outline: none;
}

.otp input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-soft);
}

.otp.otp-error input {
  border-color: var(--danger);
}

.resend {
  background: none;
  border: none;
  color: var(--accent);
  font: inherit;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
}

.resend:disabled {
  color: var(--text-muted);
  cursor: not-allowed;
}

.back-link {
  display: inline-block;
  margin-top: 0.4rem;
  color: var(--text-muted);
  font-size: 0.85rem;
  text-decoration: none;
}

.back-link:hover {
  color: var(--text);
}
</style>
