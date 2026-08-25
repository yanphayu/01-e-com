<template>
  <nav class="navbar">
    <div class="container">
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
        <template v-if="isAuthenticated">
          <button class="btn btn-ghost" @click="logout">Logout</button>
        </template>
        <template v-else>
          <RouterLink to="/login" class="link">Login</RouterLink>
          <RouterLink to="/register" class="btn btn-primary">Register</RouterLink>
        </template>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'

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
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-bottom: 1px solid var(--border);
  box-shadow: 0 6px 20px -16px rgba(33, 28, 22, 0.4);
}

.container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  max-width: 1080px;
  width: 100%;
  margin: 0 auto;
  padding: 0.9rem 1.5rem;
}

.brand {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  text-decoration: none;
  color: var(--text);
  font-family: var(--font-serif);
  font-weight: 600;
  font-size: 1.35rem;
  letter-spacing: 0.12em;
}

.brand-mark {
  width: 28px;
  height: 28px;
  color: var(--accent);
}

.links {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.link {
  text-decoration: none;
  color: var(--text-muted);
  font-weight: 500;
  font-size: 0.95rem;
  padding: 0.5rem 0.875rem;
  border-radius: var(--radius-sm);
}

.link:hover {
  color: var(--text);
  background: rgba(33, 28, 22, 0.04);
}

.link.router-link-active {
  color: var(--accent);
  background: var(--accent-soft);
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 0.95rem;
  font-weight: 600;
  padding: 0.5rem 1.1rem;
  border-radius: var(--radius-sm);
  border: none;
  cursor: pointer;
}

.btn-ghost {
  background: transparent;
  color: var(--text-muted);
  border: 1px solid var(--border);
}

.btn-ghost:hover {
  color: var(--text);
  background: rgba(33, 28, 22, 0.04);
}

.btn-primary {
  background: var(--accent);
  color: #ffffff;
  text-decoration: none;
  box-shadow: var(--shadow-sm);
}

.btn-primary:hover {
  background: var(--accent-dark);
}

@media (max-width: 480px) {
  .container {
    padding: 0.75rem 1.25rem;
  }
}
</style>
