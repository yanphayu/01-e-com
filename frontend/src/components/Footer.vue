<template>
  <footer class="footer">
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
    </div>
  </footer>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>

<style scoped>
.footer {
  border-top: 1px solid var(--border);
  background: var(--navbar-bg);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}

.container {
  max-width: 1080px;
  width: 100%;
  margin: 0 auto;
  padding: 1.5rem 1.5rem;
  display: flex;
  justify-content: center;
  align-items: center;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 0.7rem;
  text-decoration: none;
  color: var(--text);
  font-family: var(--font-serif);
  font-weight: 600;
  font-size: 1.25rem;
  letter-spacing: 0.12em;
}

.brand-mark {
  width: 26px;
  height: 26px;
  color: var(--accent);
}
</style>
