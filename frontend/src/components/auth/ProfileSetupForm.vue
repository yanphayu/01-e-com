<template>
  <form class="edit-profile" @submit.prevent="handleSubmit">
    <div class="edit-alerts">
      <p v-if="error" class="alert alert-error">{{ error }}</p>
      <p v-if="success" class="alert alert-success">{{ success }}</p>
    </div>

    <div class="edit-top">
      <div class="edit-top-cover">
        <img v-if="coverPreview" :src="coverPreview" class="cover-photo-img" alt="" />
        <div v-else class="cover-photo-empty">
          <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
            <circle cx="12" cy="13" r="4"/>
          </svg>
        </div>
        <div class="cover-photo-overlay">
          <label class="cover-upload-btn" :class="{ 'is-uploading': coverUploading }">
            <input
              type="file"
              accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
              :disabled="coverUploading"
              @change="onCoverChange"
            />
            <svg v-if="!coverUploading" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
              <circle cx="12" cy="13" r="4"/>
            </svg>
            <span v-if="coverUploading" class="spinner" />
            {{ coverUploading ? t('auth.uploadingCover') : coverName || (coverPreview ? t('auth.changePhoto') : t('auth.addPhoto')) }}
          </label>
        </div>
        <p v-if="coverError" class="cover-error">{{ coverError }}</p>
      </div>

      <div class="edit-top-avatar">
        <div class="edit-avatar-wrap">
          <img v-if="avatarPreview" :src="avatarPreview" class="edit-avatar-img" alt="" />
          <span v-else class="edit-avatar-fallback">{{ initial }}</span>
          <label class="avatar-camera-btn" :class="{ 'is-uploading': avatarUploading }">
            <input
              type="file"
              accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
              :disabled="avatarUploading"
              @change="onAvatarChange"
            />
            <svg v-if="avatarUploading" class="camera-spinner" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>
            <svg v-else viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 20h9"/>
              <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
            </svg>
          </label>
        </div>
        <p v-if="avatarError" class="field-error">{{ avatarError }}</p>
      </div>

      <div class="edit-top-caption">
        <h1 class="edit-top-title">{{ t('auth.editProfileTitle') }}</h1>
        <p class="edit-top-sub">{{ t('auth.profileSub') }}</p>
      </div>
    </div>

    <div class="edit-section">
      <h2 class="edit-section-title">{{ t('auth.basicInfo') }}</h2>
      <div class="edit-section-body">
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
      </div>
    </div>

    <div class="edit-section">
      <h2 class="edit-section-title">{{ t('auth.contactInfo') }}</h2>
      <div class="edit-section-body">
        <div class="phone-field">
          <label>{{ t('auth.phone') }}</label>
          <div class="phone-input-group">
            <div class="country-select-wrap">
              <button type="button" class="country-select-btn" @click="showCountryPicker = !showCountryPicker">
                <span class="country-flag">{{ selectedCountry.flag }}</span>
                <span class="country-code">{{ selectedCountry.dial }}</span>
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="6 9 12 15 18 9"/>
                </svg>
              </button>
              <div v-if="showCountryPicker" class="country-picker-dropdown">
                <input
                  v-model="countrySearch"
                  type="text"
                  class="country-search"
                  :placeholder="t('auth.searchCountry')"
                  @click.stop
                />
                <div class="country-list">
                  <button
                    v-for="c in filteredCountries"
                    :key="c.code"
                    type="button"
                    class="country-option"
                    :class="{ active: c.code === selectedCountry.code }"
                    @click="selectCountry(c)"
                  >
                    <span class="country-flag">{{ c.flag }}</span>
                    <span class="country-name">{{ c.name }}</span>
                    <span class="country-dial">{{ c.dial }}</span>
                  </button>
                </div>
              </div>
            </div>
            <input
              v-model="phone"
              type="tel"
              class="phone-input"
              :placeholder="t('auth.phonePlaceholder')"
              autocomplete="tel"
              @blur="validatePhone"
            />
          </div>
          <p v-if="errors.phone" class="field-error">{{ errors.phone }}</p>
        </div>

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
      </div>
    </div>

    <div class="edit-section">
      <h2 class="edit-section-title">{{ t('auth.socialLinksTitle') }}</h2>
      <div class="edit-section-body">
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
      </div>
    </div>

    <div class="edit-actions">
      <BaseButton type="submit" variant="primary" :loading="loading">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
          <polyline points="17 21 17 13 7 13 7 21"/>
          <polyline points="7 3 7 8 15 8"/>
        </svg>
        {{ loading ? t('auth.updating') : t('auth.update') }}
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
    </div>

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
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { updateProfile, uploadAvatar, uploadCoverImage, deleteAccount } from '../../services/auth'
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
const profile = stored.profile || {}
const addressData = profile.address || {}

const initial = computed(() => (stored.name || '?').trim().charAt(0).toUpperCase())

const countries = [
  { code: 'KH', name: 'Cambodia', flag: '\u{1F1F0}\u{1F1ED}', dial: '+855' },
  { code: 'US', name: 'United States', flag: '\u{1F1FA}\u{1F1F8}', dial: '+1' },
  { code: 'GB', name: 'United Kingdom', flag: '\u{1F1EC}\u{1F1E7}', dial: '+44' },
  { code: 'TH', name: 'Thailand', flag: '\u{1F1F9}\u{1F1ED}', dial: '+66' },
  { code: 'VN', name: 'Vietnam', flag: '\u{1F1FB}\u{1F1F3}', dial: '+84' },
  { code: 'LA', name: 'Laos', flag: '\u{1F1F1}\u{1F1E6}', dial: '+856' },
  { code: 'MY', name: 'Malaysia', flag: '\u{1F1F2}\u{1F1FE}', dial: '+60' },
  { code: 'SG', name: 'Singapore', flag: '\u{1F1F8}\u{1F1EC}', dial: '+65' },
  { code: 'PH', name: 'Philippines', flag: '\u{1F1F5}\u{1F1ED}', dial: '+63' },
  { code: 'ID', name: 'Indonesia', flag: '\u{1F1EE}\u{1F1E9}', dial: '+62' },
  { code: 'JP', name: 'Japan', flag: '\u{1F1EF}\u{1F1F5}', dial: '+81' },
  { code: 'KR', name: 'South Korea', flag: '\u{1F1F0}\u{1F1F7}', dial: '+82' },
  { code: 'CN', name: 'China', flag: '\u{1F1E8}\u{1F1F3}', dial: '+86' },
  { code: 'IN', name: 'India', flag: '\u{1F1EE}\u{1F1F3}', dial: '+91' },
  { code: 'AU', name: 'Australia', flag: '\u{1F1E6}\u{1F1FA}', dial: '+61' },
  { code: 'FR', name: 'France', flag: '\u{1F1EB}\u{1F1F7}', dial: '+33' },
  { code: 'DE', name: 'Germany', flag: '\u{1F1E9}\u{1F1EA}', dial: '+49' },
  { code: 'ES', name: 'Spain', flag: '\u{1F1EA}\u{1F1F8}', dial: '+34' },
  { code: 'IT', name: 'Italy', flag: '\u{1F1EE}\u{1F1F9}', dial: '+39' },
  { code: 'BR', name: 'Brazil', flag: '\u{1F1E7}\u{1F1F7}', dial: '+55' },
  { code: 'CA', name: 'Canada', flag: '\u{1F1E8}\u{1F1E6}', dial: '+1' },
  { code: 'NZ', name: 'New Zealand', flag: '\u{1F1F3}\u{1F1FF}', dial: '+64' },
  { code: 'ZA', name: 'South Africa', flag: '\u{1F1FF}\u{1F1E6}', dial: '+27' },
  { code: 'AE', name: 'UAE', flag: '\u{1F1E6}\u{1F1EA}', dial: '+971' },
  { code: 'SA', name: 'Saudi Arabia', flag: '\u{1F1F8}\u{1F1E6}', dial: '+966' },
  { code: 'TR', name: 'Turkey', flag: '\u{1F1F9}\u{1F1F7}', dial: '+90' },
  { code: 'RU', name: 'Russia', flag: '\u{1F1F7}\u{1F1FA}', dial: '+7' },
]

const firstName = ref(stored.first_name || storedNameParts[0] || '')
const lastName = ref(stored.last_name || storedNameParts.slice(1).join(' ') || '')
const phoneRaw = profile.phone || ''
const matchedCountry = countries.find(c => phoneRaw.startsWith(c.dial))
const phone = ref(matchedCountry ? phoneRaw.slice(matchedCountry.dial.length) : phoneRaw)
const phoneCountryCode = ref(matchedCountry?.dial || '+855')
const showCountryPicker = ref(false)
const countrySearch = ref('')

const selectedCountry = computed(() => {
  return countries.find(c => c.dial === phoneCountryCode.value) || countries[0]
})

const filteredCountries = computed(() => {
  if (!countrySearch.value) return countries
  const q = countrySearch.value.toLowerCase()
  return countries.filter(c => c.name.toLowerCase().includes(q) || c.dial.includes(q) || c.code.toLowerCase().includes(q))
})

function selectCountry(c) {
  phoneCountryCode.value = c.dial
  showCountryPicker.value = false
  countrySearch.value = ''
}

function closeCountryPicker(e) {
  if (!e.target.closest('.country-select-wrap')) {
    showCountryPicker.value = false
  }
}

onMounted(() => document.addEventListener('click', closeCountryPicker))
onBeforeUnmount(() => document.removeEventListener('click', closeCountryPicker))
const birthDate = ref(profile.birth_date || '')
const address = ref(addressData.address || '')
const latitude = ref(addressData.latitude ?? null)
const longitude = ref(addressData.longitude ?? null)
const locError = ref(null)
const locating = ref(false)
const avatar = ref(profile.avatar || '')
const avatarName = ref('')
const avatarPreview = ref(profile.avatar || null)
const avatarUploading = ref(false)
const avatarError = ref(null)
const coverPreview = ref(profile.cover_image || null)
const coverName = ref('')
const coverUploading = ref(false)
const coverError = ref(null)
const facebook = ref(profile.facebook || '')
const instagram = ref(profile.instagram || '')
const twitter = ref(profile.twitter || '')
const loading = ref(false)
const error = ref(null)
const success = ref(null)
const confirmingDelete = ref(false)
const deleting = ref(false)
const errors = reactive({ firstName: null, lastName: null, phone: null })

const ALLOWED_AVATAR_TYPES = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']

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
    const profile = data.profile || {}
    const addressData = profile.address || {}
    localStorage.setItem('user', JSON.stringify({
      ...data,
      ...payload,
      profile: {
        ...profile,
        ...payload,
        address: {
          ...addressData,
          address: payload.address,
          latitude: payload.latitude,
          longitude: payload.longitude
        }
      }
    }))
    emit('saved')
    if (props.redirectOnSave) router.push('/')
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

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
    avatar.value = data.data.profile?.avatar
    const stored = JSON.parse(localStorage.getItem('user') || '{}')
    const profile = stored.profile || {}
    localStorage.setItem('user', JSON.stringify({ ...stored, profile: { ...profile, avatar: data.data.profile?.avatar } }))
  } catch (err) {
    avatarError.value = err.message
    avatarPreview.value = profile.avatar || null
    avatarName.value = ''
  } finally {
    avatarUploading.value = false
    e.target.value = ''
  }
}

async function onCoverChange(e) {
  const file = e.target.files?.[0]
  if (!file) return

  coverError.value = null

  if (!ALLOWED_AVATAR_TYPES.includes(file.type)) {
    coverError.value = t('auth.avatarTypeInvalid')
    e.target.value = ''
    return
  }

  coverName.value = file.name
  coverPreview.value = URL.createObjectURL(file)
  coverUploading.value = true

  try {
    const data = await uploadCoverImage(file)
    const stored = JSON.parse(localStorage.getItem('user') || '{}')
    const profile = stored.profile || {}
    localStorage.setItem('user', JSON.stringify({ ...stored, profile: { ...profile, cover_image: data.data.profile?.cover_image } }))
  } catch (err) {
    coverError.value = err.message
    coverPreview.value = profile.cover_image || null
    coverName.value = ''
  } finally {
    coverUploading.value = false
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
        const lang = getLocale() === 'kh' ? 'km' : getLocale()
        const res = await fetch(
          `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=${lang}`
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
    phone: phone.value ? `${phoneCountryCode.value}${phone.value}` : '',
    phone_country_code: phoneCountryCode.value,
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
.edit-profile {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.edit-alerts {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.edit-top {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  position: relative;
  padding-bottom: 5.5rem;
}

.edit-top-cover {
  height: 240px;
  background: var(--surface-2);
  position: relative;
  overflow: hidden;
}

.cover-photo-img,
.cover-photo-empty {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cover-photo-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  background: linear-gradient(180deg, var(--surface-2), var(--surface-3));
}

.cover-photo-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, transparent 55%, rgba(0, 0, 0, 0.35) 100%);
  display: flex;
  align-items: flex-end;
  justify-content: flex-end;
  padding: 0.75rem;
}

.cover-upload-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.45rem 0.85rem;
  background: rgba(255, 255, 255, 0.92);
  color: #050505;
  font-size: 0.85rem;
  font-weight: 600;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.15s;
}

.cover-upload-btn:hover {
  background: #fff;
}

.cover-upload-btn input {
  display: none;
}

.cover-upload-btn.is-uploading {
  cursor: wait;
  opacity: 0.7;
}

.cover-error {
  position: absolute;
  bottom: 0.5rem;
  left: 0.75rem;
  background: var(--danger);
  color: #fff;
  font-size: 0.8rem;
  padding: 0.35rem 0.85rem;
  border-radius: 6px;
  margin: 0;
  z-index: 2;
}

.edit-top-avatar {
  position: absolute;
  bottom: 2rem;
  left: 1.25rem;
  z-index: 2;
}

.edit-avatar-wrap {
  position: relative;
  width: 140px;
  height: 140px;
  flex-shrink: 0;
}

.edit-avatar-img,
.edit-avatar-fallback {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid var(--surface);
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.08), 0 4px 16px rgba(0, 0, 0, 0.2);
}

.edit-avatar-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--accent);
  color: #fff;
  font-size: 3.2rem;
  font-weight: 700;
}

.avatar-camera-btn {
  position: absolute;
  bottom: 6px;
  right: 6px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #e4e6eb;
  color: #050505;
  display: grid;
  place-items: center;
  cursor: pointer;
  border: 4px solid var(--surface);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  transition: background 0.15s;
}

.avatar-camera-btn:hover {
  background: #d8dadf;
}

.avatar-camera-btn input {
  display: none;
}

.avatar-camera-btn.is-uploading {
  cursor: wait;
  opacity: 0.8;
}

.camera-spinner {
  animation: spin 0.7s linear infinite;
}

.edit-top-caption {
  position: absolute;
  bottom: 0.75rem;
  left: 11.5rem;
  z-index: 2;
}

.edit-top-title {
  margin: 0;
  font-size: 1.35rem;
  font-weight: 700;
  color: #050505;
}

.edit-top-sub {
  margin: 0.1rem 0 0;
  font-size: 0.85rem;
  color: var(--text-muted);
}

.edit-section {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.edit-section-title {
  margin: 0;
  padding: 0.9rem 1.25rem;
  font-size: 1rem;
  font-weight: 700;
  color: #050505;
  border-bottom: 1px solid var(--border);
  background: var(--surface-2);
}

.edit-section-body {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.edit-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.edit-actions .skip {
  margin-left: auto;
}

.row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
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

.phone-field label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text);
  margin-bottom: 0.35rem;
}

.phone-input-group {
  display: flex;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  overflow: hidden;
  transition: border-color 0.15s;
}

.phone-input-group:focus-within {
  border-color: var(--accent);
}

.country-select-wrap {
  position: relative;
}

.country-select-btn {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.6rem 0.65rem;
  background: var(--surface-2);
  border: none;
  border-right: 1px solid var(--border);
  cursor: pointer;
  font-size: 0.9rem;
  color: var(--text);
  height: 100%;
  transition: background 0.15s;
}

.country-select-btn:hover {
  background: var(--surface-3, var(--surface-2));
}

.country-flag {
  font-size: 1.1rem;
}

.country-code {
  font-weight: 500;
}

.phone-input {
  flex: 1;
  border: none;
  padding: 0.6rem 0.75rem;
  font: inherit;
  font-size: 0.9rem;
  color: var(--text);
  background: transparent;
  outline: none;
  min-width: 0;
}

.phone-input::placeholder {
  color: var(--text-muted);
}

.country-picker-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  z-index: 100;
  width: 260px;
  max-height: 300px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

.country-search {
  width: 100%;
  padding: 0.6rem 0.75rem;
  border: none;
  border-bottom: 1px solid var(--border);
  font: inherit;
  font-size: 0.85rem;
  background: var(--surface);
  outline: none;
  color: var(--text);
}

.country-search::placeholder {
  color: var(--text-muted);
}

.country-list {
  max-height: 240px;
  overflow-y: auto;
}

.country-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.55rem 0.75rem;
  border: none;
  background: transparent;
  cursor: pointer;
  font: inherit;
  font-size: 0.85rem;
  color: var(--text);
  text-align: left;
  transition: background 0.1s;
}

.country-option:hover {
  background: var(--surface-2);
}

.country-option.active {
  background: var(--accent-soft);
  color: var(--accent);
}

.country-option .country-name {
  flex: 1;
}

.country-option .country-dial {
  color: var(--text-muted);
  font-size: 0.8rem;
}

.field-error {
  font-size: 0.8rem;
  color: var(--danger);
  margin: 0.25rem 0 0;
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

@media (max-width: 560px) {
  .edit-profile {
    max-width: 100%;
  }

  .edit-top-cover {
    height: 180px;
  }

  .edit-top {
    padding-bottom: 5rem;
  }

  .edit-avatar-wrap {
    width: 112px;
    height: 112px;
  }

  .edit-top-caption {
    left: 9.5rem;
    bottom: 0.6rem;
  }

  .edit-top-title {
    font-size: 1.1rem;
  }

  .edit-top-sub {
    display: none;
  }
}

@media (max-width: 420px) {
  .row {
    grid-template-columns: 1fr;
  }
}
</style>
