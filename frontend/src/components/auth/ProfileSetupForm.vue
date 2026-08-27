<template>
  <form class="auth-card stack" @submit.prevent="handleSubmit">
    <div class="auth-head">
      <h1>{{ t('auth.profileTitle') }}</h1>
      <p>{{ t('auth.profileSub') }}</p>
    </div>

    <p v-if="error" class="alert alert-error">{{ error }}</p>
    <p v-if="success" class="alert alert-success">{{ success }}</p>

    <div class="row">
      <BaseInput
        v-model="firstName"
        :label="t('auth.firstName')"
        type="text"
        :placeholder="t('auth.firstNamePlaceholder')"
        autocomplete="given-name"
        :error="errors.firstName"
      />

      <BaseInput
        v-model="lastName"
        :label="t('auth.lastName')"
        type="text"
        :placeholder="t('auth.lastNamePlaceholder')"
        autocomplete="family-name"
        :error="errors.lastName"
      />
    </div>

    <BaseInput
      v-model="phone"
      :label="t('auth.phone')"
      type="tel"
      :placeholder="t('auth.phonePlaceholder')"
      autocomplete="tel"
      :error="errors.phone"
    />

    <BaseInput
      v-model="avatar"
      :label="t('auth.avatar')"
      type="url"
      :placeholder="t('auth.avatarPlaceholder')"
    />

    <div class="section-label">{{ t('auth.socialTitle') }}</div>

    <BaseInput
      v-model="facebook"
      :label="t('auth.facebook')"
      type="url"
      :placeholder="t('auth.facebookPlaceholder')"
    />

    <BaseInput
      v-model="instagram"
      :label="t('auth.instagram')"
      type="url"
      :placeholder="t('auth.instagramPlaceholder')"
    />

    <BaseInput
      v-model="twitter"
      :label="t('auth.twitter')"
      type="url"
      :placeholder="t('auth.twitterPlaceholder')"
    />

    <BaseButton type="submit" variant="primary" block :loading="loading">
      {{ loading ? t('auth.savingProfile') : t('auth.completeProfile') }}
    </BaseButton>

    <button type="button" class="skip" :disabled="loading" @click="skip">
      {{ t('auth.skipForNow') }}
    </button>
  </form>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { updateProfile } from '../../services/auth'
import { t } from '../../i18n'
import BaseInput from '../../design-system/BaseInput.vue'
import BaseButton from '../../design-system/BaseButton.vue'

const router = useRouter()

const firstName = ref('')
const lastName = ref('')
const phone = ref('')
const avatar = ref('')
const facebook = ref('')
const instagram = ref('')
const twitter = ref('')
const loading = ref(false)
const error = ref(null)
const success = ref(null)
const errors = reactive({ firstName: null, lastName: null, phone: null })

function validate() {
  errors.firstName = firstName.value.trim() ? null : t('auth.firstNameRequired')
  errors.lastName = lastName.value.trim() ? null : t('auth.lastNameRequired')

  if (phone.value && !/^[0-9+\-\s]{6,20}$/.test(phone.value)) {
    errors.phone = t('auth.phoneInvalid')
  } else {
    errors.phone = null
  }

  return !errors.firstName && !errors.lastName && !errors.phone
}

async function submit(payload) {
  loading.value = true
  error.value = null
  success.value = null

  try {
    await updateProfile(payload)
    router.push('/')
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  if (!validate()) return
  await submit({
    first_name: firstName.value.trim(),
    last_name: lastName.value.trim(),
    phone: phone.value,
    avatar: avatar.value,
    facebook: facebook.value.trim(),
    instagram: instagram.value.trim(),
    twitter: twitter.value.trim(),
  })
}

async function skip() {
  await submit({ first_name: firstName.value.trim(), last_name: lastName.value.trim() })
}
</script>

<style scoped>
.row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.profile-setup-form {
  text-align: center;
}

.section-label {
  text-align: left;
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--text-muted);
  margin: 0.4rem 0 0.2rem;
}

.skip {
  background: none;
  border: none;
  color: var(--text-muted);
  font: inherit;
  font-size: 0.85rem;
  cursor: pointer;
  padding: 0;
  margin-top: 0.25rem;
}

.skip:hover {
  color: var(--text);
}

.skip:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

@media (max-width: 420px) {
  .row {
    grid-template-columns: 1fr;
  }
}
</style>
