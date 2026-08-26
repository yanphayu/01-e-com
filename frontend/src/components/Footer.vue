<template>
  <footer class="footer">
    <div class="container footer-inner">
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

      <nav class="footer-links">
        <RouterLink to="/">{{ t('footer.home') }}</RouterLink>
        <RouterLink to="/register">{{ t('footer.getStarted') }}</RouterLink>
        <RouterLink to="/login">{{ t('footer.signIn') }}</RouterLink>
      </nav>

      <p class="copyright">{{ t('footer.rights', { year }) }}</p>
    </div>
  </footer>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import { t } from '../i18n'

const year = new Date().getFullYear()
</script>

<style scoped>
.footer {
  border-top: 1px solid var(--border);
  background: var(--surface);
}

.footer-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
  padding-top: 1.6rem;
  padding-bottom: 1.6rem;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  text-decoration: none;
  color: var(--text);
  font-family: var(--font-serif);
  font-weight: 600;
  font-size: 1.2rem;
  letter-spacing: 0.14em;
}

.brand-mark {
  width: 24px;
  height: 24px;
  color: var(--accent);
}

.footer-links {
  display: flex;
  gap: 1.5rem;
}

.footer-links a {
  color: var(--text-muted);
  font-size: 0.92rem;
  font-weight: 500;
  text-decoration: none;
}

.footer-links a:hover {
  color: var(--text);
}

.copyright {
  width: 100%;
  text-align: center;
  font-size: 0.82rem;
  color: var(--text-muted);
  border-top: 1px solid var(--border);
  padding-top: 1.1rem;
  margin-top: 0.4rem;
}

@media (max-width: 600px) {
  .footer-inner {
    flex-direction: column;
    text-align: center;
  }
}
</style>
