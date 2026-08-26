<template>
  <nav class="navbar">
    <div class="container nav-inner">
      <RouterLink to="/" class="brand">
        <svg class="brand-mark" viewBox="138.8 116 322.4 283" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="TRINITY">
          <path d="M 300 130 L 259.5 276.6 L 152.8 385 L 300 346.8 L 447.2 385 L 340.5 276.6 Z" stroke="currentColor" stroke-width="26" stroke-linejoin="miter" stroke-miterlimit="10" />
          <path d="M 259.5 276.6 L 300 346.8 L 340.5 276.6 Z" fill="currentColor" />
          <circle cx="300" cy="130" r="9" fill="currentColor" />
          <circle cx="152.8" cy="385" r="9" fill="currentColor" />
          <circle cx="447.2" cy="385" r="9" fill="currentColor" />
        </svg>
        <span class="brand-name">TRINITY</span>
      </RouterLink>

      <div class="links">
        <LocaleSwitcher />
        <template v-if="isAuthenticated">
          <button class="btn btn-ghost" @click="logout">{{ t('nav.logout') }}</button>
        </template>
        <template v-else>
          <RouterLink to="/login" class="btn btn-ghost">{{ t('nav.login') }}</RouterLink>
          <RouterLink to="/register" class="btn btn-primary">{{ t('nav.register') }}</RouterLink>
        </template>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { t } from '../i18n'
import LocaleSwitcher from './LocaleSwitcher.vue'

const router = useRouter()
const isAuthenticated = ref(!!localStorage.getItem('token'))

function logout() {
  localStorage.removeItem('token')
  isAuthenticated.value = false
  router.push('/login')
}
</script>

<style scoped>
.navbar {
  position: sticky;
  top: 0;
  z-index: 50;
  background: var(--navbar-bg);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border-bottom: 1px solid var(--border);
}

.nav-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 0.85rem;
  padding-bottom: 0.85rem;
}

.brand {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  text-decoration: none;
  color: var(--text);
  font-family: var(--font-serif);
  font-weight: 600;
  font-size: 1.3rem;
  letter-spacing: 0.14em;
}

.brand-mark {
  width: 26px;
  height: 26px;
  color: var(--accent);
}

.links {
  display: flex;
  gap: 0.6rem;
  align-items: center;
}

.links .btn {
  padding: 0.4rem 0.85rem;
  font-size: 0.85rem;
}

.link {
  text-decoration: none;
  color: var(--text-muted);
  font-weight: 500;
  font-size: 0.95rem;
  padding: 0.5rem 0.85rem;
  border-radius: var(--radius-sm);
}

.link:hover {
  color: var(--text);
  background: rgba(28, 27, 25, 0.04);
}

.link.router-link-active {
  color: var(--accent);
  background: var(--accent-soft);
}

@media (max-width: 480px) {
  .brand-name {
    display: none;
  }
}
</style>
