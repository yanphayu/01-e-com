<template>
  <div class="auth-split">
    <aside class="auth-aside">
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

      <div class="aside-copy">
        <h2>{{ t('authLayout.tagline1') }}<br />{{ t('authLayout.tagline2') }}</h2>
        <p>{{ t('authLayout.sub') }}</p>
        <ul class="aside-list">
          <li>
            <span class="check">✓</span>
            {{ t('authLayout.b1') }}
          </li>
          <li>
            <span class="check">✓</span>
            {{ t('authLayout.b2') }}
          </li>
          <li>
            <span class="check">✓</span>
            {{ t('authLayout.b3') }}
          </li>
        </ul>
      </div>

      <p class="aside-foot">{{ t('authLayout.rights', { year }) }}</p>
    </aside>

    <main class="auth-main">
      <div class="auth-form-side">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import { t } from '../i18n'

const year = new Date().getFullYear()
</script>

<style scoped>
.auth-split {
  display: grid;
  grid-template-columns: 1.05fr 1fr;
  min-height: 100vh;
}

.auth-aside {
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 1.5rem;
  padding: 2.25rem 2.5rem;
  background:
    radial-gradient(900px 500px at 20% 0%, var(--accent-soft), transparent 60%),
    linear-gradient(160deg, #e7f4ec, #d6ebe0);
  color: var(--text);
  border-right: 1px solid var(--border);
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  text-decoration: none;
  color: var(--text);
  font-family: var(--font-serif);
  font-weight: 600;
  font-size: 1.4rem;
  letter-spacing: 0.14em;
}

.brand-mark {
  width: 28px;
  height: 28px;
  color: var(--accent);
}

.aside-copy {
  max-width: 420px;
}

.aside-copy h2 {
  font-family: var(--font-serif);
  font-size: clamp(1.7rem, 3vw, 2.3rem);
  line-height: 1.14;
  font-weight: 600;
}

.aside-copy p {
  margin-top: 0.85rem;
  color: var(--text-muted);
  font-size: 0.95rem;
}

.aside-list {
  list-style: none;
  margin-top: 1.4rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.aside-list li {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  font-size: 0.92rem;
}

.check {
  display: grid;
  place-items: center;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: var(--accent);
  color: #fff;
  font-size: 0.75rem;
  font-weight: 700;
  flex-shrink: 0;
}

.aside-foot {
  font-size: 0.82rem;
  color: var(--text-muted);
}

.auth-main {
  display: grid;
  place-items: center;
  padding: 2.5rem 1.5rem;
  background: var(--bg);
}

.auth-form-side {
  width: 100%;
  max-width: 430px;
}

@media (max-width: 880px) {
  .auth-split {
    grid-template-columns: 1fr;
  }

  .auth-aside {
    display: none;
  }
}
</style>
