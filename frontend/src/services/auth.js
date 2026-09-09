import { request, API_URL } from './http'

export function googleRedirectUrl() {
  return `${API_URL}/auth/google/redirect`
}

export function registerUser(payload) {
  return request('/register', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function loginUser(payload) {
  return request('/login', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function verifyEmail(payload) {
  return request('/verify', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function resendVerification(payload) {
  return request('/resend', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function forgotPassword(payload) {
  return request('/forgot-password', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function resetPassword(payload) {
  return request('/reset-password', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function updateProfile(payload) {
  return request('/profile', {
    method: 'PUT',
    body: JSON.stringify(payload),
  })
}

export function getUser() {
  return request('/me')
}

export function uploadAvatar(file) {
  const form = new FormData()
  form.append('avatar', file)
  return request('/avatar', {
    method: 'POST',
    body: form,
  })
}

export function uploadCoverImage(file) {
  const form = new FormData()
  form.append('cover_image', file)
  return request('/cover-image', {
    method: 'POST',
    body: form,
  })
}

export function sendDeleteOtp() {
  return request('/profile/delete-otp', {
    method: 'POST',
    body: JSON.stringify({}),
  })
}

export function deleteAccount(payload) {
  return request('/profile', {
    method: 'DELETE',
    body: JSON.stringify(payload),
  })
}
