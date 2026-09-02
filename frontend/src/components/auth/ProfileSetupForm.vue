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
      v-model="birthDate"
      :label="t('auth.birthDate')"
      type="date"
      :placeholder="t('auth.birthDatePlaceholder')"
      autocomplete="bday"
    />

    <div class="field">
      <label>{{ t('auth.address') }}</label>
      <button type="button" class="loc-btn" :disabled="locating" @click="getLocation">
        <svg v-if="!locating" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
          <circle cx="12" cy="10" r="3" />
        </svg>
        <span v-if="locating" class="spinner" />
        {{ locating ? t('auth.gettingLocation') : t('auth.getLocation') }}
      </button>
      <p v-if="address" class="loc-result">{{ address }}</p>
      <p v-if="locError" class="field-error">{{ locError }}</p>
    </div>

    <div class="field">
      <label>{{ t('auth.avatar') }}</label>
      <label class="file-btn" :class="{ 'file-btn-loading': avatarUploading }">
        <input
          type="file"
          accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
          :disabled="avatarUploading"
          @change="onAvatarChange"
        />
        <svg v-if="!avatarUploading" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 5v14" />
          <path d="M5 12h14" />
        </svg>
        <span v-if="avatarUploading" class="spinner" />
        {{ avatarUploading ? t('auth.avatarUploading') : avatarName || t('auth.avatarPick') }}
      </label>
      <p v-if="avatarError" class="field-error">{{ avatarError }}</p>
      <img v-if="avatarPreview" :src="avatarPreview" class="avatar-preview" alt="Avatar preview" />
    </div>

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
      {{ loading ? t('auth.savingProfile') : t('auth.saveProfile') }}
    </BaseButton>

    <button v-if="!redirectOnSave" type="button" class="skip" :disabled="loading" @click="emit('cancel')">
      {{ t('auth.cancelEdit') }}
    </button>

    <button
      v-if="!redirectOnSave"
      type="button"
      class="danger-btn"
      :disabled="loading || deleting"
      @click="confirmingDelete = true"
    >
      {{ t('auth.deleteProfile') }}
    </button>

    <BaseModal
      v-model="confirmingDelete"
      :title="t('auth.deleteConfirmTitle')"
      size="sm"
    >
      <p class="modal-text">{{ t('auth.deleteConfirmBody') }}</p>
      <template #footer>
        <BaseButton variant="ghost" :disabled="deleting" @click="confirmingDelete = false">
          {{ t('auth.cancelEdit') }}
        </BaseButton>
        <BaseButton variant="primary" class="btn-danger-solid" :loading="deleting" @click="handleDelete">
          {{ deleting ? t('auth.deletingAccount') : t('auth.deleteAccount') }}
        </BaseButton>
      </template>
    </BaseModal>
  </form>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { updateProfile, uploadAvatar, deleteAccount } from '../../services/auth'
import { t, getLocale } from '../../i18n'
import BaseInput from '../../design-system/BaseInput.vue'
import BaseButton from '../../design-system/BaseButton.vue'
import BaseModal from '../../design-system/BaseModal.vue'

const props = defineProps({ redirectOnSave: { type: Boolean, default: true } })
const emit = defineEmits(['saved', 'cancel'])

const router = useRouter()

function readStoredUser() {
  try {
    return JSON.parse(localStorage.getItem('user') || '{}')
  } catch {
    return {}
  }
}

const stored = readStoredUser()
const storedNameParts = (stored.name || '').trim().split(/\s+/).filter(Boolean)

const firstName = ref(stored.first_name || storedNameParts[0] || '')
const lastName = ref(stored.last_name || storedNameParts.slice(1).join(' ') || '')
const phone = ref(stored.phone || '')
const birthDate = ref(stored.birth_date || '')
const address = ref(stored.address || '')
const latitude = ref(stored.latitude ?? null)
const longitude = ref(stored.longitude ?? null)
const locError = ref(null)
const locating = ref(false)
const avatar = ref(stored.avatar || '')
const avatarName = ref('')
const avatarPreview = ref(stored.avatar || null)
const avatarUploading = ref(false)
const avatarError = ref(null)
const facebook = ref(stored.facebook || '')
const instagram = ref(stored.instagram || '')
const twitter = ref(stored.twitter || '')
const loading = ref(false)
const error = ref(null)
const success = ref(null)
const confirmingDelete = ref(false)
const deleting = ref(false)
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
    const data = JSON.parse(localStorage.getItem('user') || '{}')
    localStorage.setItem('user', JSON.stringify({ ...data, ...payload }))
    emit('saved')
    if (redirectOnSave) router.push('/')
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

const ALLOWED_AVATAR_TYPES = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']

async function onAvatarChange(e) {
  const file = e.target.files?.[0]
  if (!file) return

  avatarError.value = null

  if (!ALLOWED_AVATAR_TYPES.includes(file.type)) {
    avatarError.value = t('auth.avatarTypeInvalid')
    e.target.value = ''
    return
  }

  avatarName.value = file.name
  avatarPreview.value = URL.createObjectURL(file)
  avatarUploading.value = true

  try {
    const data = await uploadAvatar(file)
    avatar.value = data.data.avatar
    const stored = JSON.parse(localStorage.getItem('user') || '{}')
    localStorage.setItem('user', JSON.stringify({ ...stored, avatar: data.data.avatar }))
  } catch (err) {
    avatarError.value = err.message
    avatarPreview.value = null
    avatarName.value = ''
  } finally {
    avatarUploading.value = false
    e.target.value = ''
  }
}

async function getLocation() {
  if (!navigator.geolocation) {
    locError.value = t('auth.locationUnsupported')
    return
  }

  locating.value = true
  locError.value = null

  navigator.geolocation.getCurrentPosition(
    async (position) => {
      const { latitude: lat, longitude: lng } = position.coords
      latitude.value = lat
      longitude.value = lng
      try {
        const res = await fetch(
          `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=${getLocale()}`
        )
        const data = await res.json()
        address.value = data.display_name || `${lat}, ${lng}`
      } catch {
        address.value = `${latitude}, ${longitude}`
      } finally {
        locating.value = false
      }
    },
    () => {
      locError.value = t('auth.locationDenied')
      locating.value = false
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  )
}

async function handleSubmit() {
  if (!validate()) return
  await submit({
    first_name: firstName.value.trim(),
    last_name: lastName.value.trim(),
    phone: phone.value,
    birth_date: birthDate.value,
    address: address.value,
    latitude: latitude.value,
    longitude: longitude.value,
    facebook: facebook.value.trim(),
    instagram: instagram.value.trim(),
    twitter: twitter.value.trim(),
  })
}

async function handleDelete() {
  deleting.value = true
  error.value = null

  try {
    await deleteAccount()
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/register')
  } catch (err) {
    error.value = err.message
    confirmingDelete.value = false
  } finally {
    deleting.value = false
  }
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
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text-muted);
  font: inherit;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  padding: 0.62rem 1rem;
  border-radius: var(--radius-sm);
  transition: color 0.15s, border-color 0.15s;
}

.skip:hover:not(:disabled) {
  color: var(--text);
  border-color: var(--border-strong);
}

.skip:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.danger-btn {
  background: transparent;
  border: 1px solid var(--danger);
  color: var(--danger);
  font: inherit;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  padding: 0.62rem 1rem;
  border-radius: var(--radius-sm);
  transition: background 0.15s, color 0.15s;
}

.danger-btn:hover:not(:disabled) {
  background: var(--danger-soft);
}

.danger-btn:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-danger-solid {
  background: var(--danger) !important;
}

.btn-danger-solid:hover:not(:disabled) {
  background: color-mix(in srgb, var(--danger) 88%, #000 12%) !important;
}

.modal-text {
  color: var(--text-muted);
  line-height: 1.5;
  margin: 0;
}

.loc-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  padding: 0.6rem 0.85rem;
  border: 1px dashed var(--accent);
  border-radius: var(--radius-sm);
  background: var(--accent-soft);
  color: var(--accent);
  font: inherit;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: background 0.15s;
}

.loc-btn:hover {
  background: color-mix(in srgb, var(--accent-soft) 80%, var(--accent) 20%);
}

.loc-btn:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.loc-result {
  font-size: 0.8rem;
  color: var(--text-muted);
  line-height: 1.4;
  word-break: break-word;
}

.file-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  padding: 0.6rem 0.85rem;
  border: 1px dashed var(--accent);
  border-radius: var(--radius-sm);
  background: var(--accent-soft);
  color: var(--accent);
  font: inherit;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: background 0.15s;
}

.file-btn:hover {
  background: color-mix(in srgb, var(--accent-soft) 80%, var(--accent) 20%);
}

.file-btn input {
  display: none;
}

.file-btn-loading {
  cursor: not-allowed;
  opacity: 0.6;
}

.avatar-preview {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  object-fit: cover;
  margin-top: 0.25rem;
}

.spinner {
  width: 14px;
  height: 14px;
  border: 2px solid currentColor;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 420px) {
  .row {
    grid-template-columns: 1fr;
  }
}
</style>
