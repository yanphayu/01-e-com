<template>
  <div class="auth-split">
    <aside class="auth-aside">
      <div class="aside-cards" aria-hidden="true">
        <div v-for="card in cards" :key="card" class="float-card">
          <span class="card-thumb"></span>
          <span class="card-line"></span>
          <span class="card-line card-line-short"></span>
        </div>
      </div>

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
        <h2>{{ copy.headline_1 }}<br />{{ copy.headline_2 }}</h2>
        <p>{{ copy.sub }}</p>
        <ul class="aside-list">
          <li>
            <span class="check">✓</span>
            {{ copy.bullet_1 }}
          </li>
          <li>
            <span class="check">✓</span>
            {{ copy.bullet_2 }}
          </li>
          <li>
            <span class="check">✓</span>
            {{ copy.bullet_3 }}
          </li>
        </ul>
      </div>

      <p class="aside-foot">{{ copy.footer }}</p>
    </aside>

    <main class="auth-main">
      <div class="auth-form-side">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { t } from '../i18n'
import { getAuthPanel } from '../services/auth'

const year = new Date().getFullYear()
const cards = [1, 2, 3, 4, 5, 6]

const overrides = ref({})

const copy = computed(() => ({
  headline_1: overrides.value.headline_1 || t('authLayout.tagline1'),
  headline_2: overrides.value.headline_2 || t('authLayout.tagline2'),
  sub: overrides.value.sub || t('authLayout.sub'),
  bullet_1: overrides.value.bullet_1 || t('authLayout.b1'),
  bullet_2: overrides.value.bullet_2 || t('authLayout.b2'),
  bullet_3: overrides.value.bullet_3 || t('authLayout.b3'),
  footer: (overrides.value.footer || t('authLayout.rights')).replace('{year}', year),
}))

onMounted(async () => {
  try {
    const res = await getAuthPanel()
    overrides.value = res.data || {}
  } catch {
    overrides.value = {}
  }
})
</script>

<style scoped>
.auth-split {
  display: grid;
  grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr);
  min-height: 100vh;
  min-height: 100dvh;
}

.auth-aside {
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 1.5rem;
  overflow: hidden;
  padding: 2.25rem 2.5rem;
  border-right: 1px solid var(--border);
  background:
    radial-gradient(900px 500px at 20% 0%, var(--accent-soft), transparent 60%),
    linear-gradient(160deg, var(--surface-2), var(--surface-3));
  color: var(--text);
}

.auth-aside::after {
  content: '';
  position: absolute;
  right: -100px;
  bottom: -100px;
  width: 360px;
  height: 360px;
  border: 1px solid var(--border);
  border-radius: 50%;
  box-shadow: 0 0 0 48px var(--accent-soft), 0 0 0 96px var(--surface-2);
  pointer-events: none;
}

.aside-cards {
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
}

.float-card {
  position: absolute;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 0.7rem;
  border: 1px solid var(--border);
  border-radius: 14px;
  background: var(--surface);
  box-shadow: var(--shadow-md);
  opacity: 0.5;
  animation: card-drift var(--dur) ease-in-out var(--delay) infinite alternate;
}

.float-card:nth-child(1) {
  top: 9%;
  right: 7%;
  width: 158px;
  height: 196px;
  --dur: 13s;
  --delay: -2s;
  --rot: -5deg;
  --dx: 26px;
  --dy: -34px;
  opacity: 0.55;
}

.float-card:nth-child(2) {
  top: 46%;
  right: 26%;
  width: 122px;
  height: 152px;
  --dur: 17s;
  --delay: -7s;
  --rot: 6deg;
  --dx: -22px;
  --dy: 26px;
  opacity: 0.4;
}

.float-card:nth-child(3) {
  bottom: 12%;
  right: 5%;
  width: 140px;
  height: 172px;
  --dur: 15s;
  --delay: -11s;
  --rot: 4deg;
  --dx: -18px;
  --dy: -24px;
  opacity: 0.45;
}

.float-card:nth-child(4) {
  bottom: 26%;
  left: 5%;
  width: 108px;
  height: 136px;
  --dur: 19s;
  --delay: -4s;
  --rot: -7deg;
  --dx: 20px;
  --dy: 30px;
  opacity: 0.35;
}

.float-card:nth-child(5) {
  top: 6%;
  left: 3%;
  width: 98px;
  height: 124px;
  --dur: 21s;
  --delay: -14s;
  --rot: 8deg;
  --dx: 16px;
  --dy: 22px;
  opacity: 0.3;
}

.float-card:nth-child(6) {
  bottom: 5%;
  right: 32%;
  width: 88px;
  height: 112px;
  --dur: 23s;
  --delay: -9s;
  --rot: -4deg;
  --dx: -14px;
  --dy: -18px;
  opacity: 0.3;
}

.card-thumb {
  display: block;
  height: 62%;
  border-radius: 9px;
  background: linear-gradient(140deg, var(--accent-soft), var(--surface-2));
}

.float-card:nth-child(2) .card-thumb,
.float-card:nth-child(4) .card-thumb,
.float-card:nth-child(6) .card-thumb {
  height: 52%;
}

.card-line {
  display: block;
  height: 7px;
  border-radius: 999px;
  background: var(--border);
}

.card-line-short {
  width: 55%;
  background: var(--border-strong);
}

@keyframes card-drift {
  from {
    transform: translate3d(0, 0, 0) rotate(var(--rot, 0deg));
  }

  to {
    transform: translate3d(var(--dx, 20px), var(--dy, -24px), 0) rotate(calc(var(--rot, 0deg) * -1));
  }
}

@media (prefers-reduced-motion: reduce) {
  .float-card {
    animation: none;
  }
}

@media (max-width: 1180px) {
  .float-card:nth-child(2),
  .float-card:nth-child(4),
  .float-card:nth-child(5),
  .float-card:nth-child(6) {
    display: none;
  }

  .float-card:nth-child(1),
  .float-card:nth-child(3) {
    opacity: 0.4;
  }
}

.brand {
  position: relative;
  z-index: 1;
  display: inline-flex;
  align-items: center;
  gap: 0.65rem;
  width: fit-content;
  color: var(--text);
  font-family: var(--font-mono);
  font-size: 1.25rem;
  font-weight: 700;
  letter-spacing: 0.16em;
  text-decoration: none;
}

.brand-mark {
  width: 30px;
  height: 30px;
  color: var(--primary);
}

.aside-copy {
  position: relative;
  z-index: 1;
  max-width: 440px;
}

.aside-copy h2 {
  margin: 0;
  font-family: var(--font-mono);
  font-size: clamp(1.7rem, 3vw, 2.35rem);
  font-weight: 700;
  letter-spacing: -0.05em;
  line-height: 1.2;
}

.aside-copy p {
  margin-top: 1rem;
  color: var(--text-muted);
  font-size: 0.95rem;
}

.aside-list {
  display: flex;
  flex-direction: column;
  gap: 0.7rem;
  margin: 1.5rem 0 0;
  padding: 0;
  list-style: none;
}

.aside-list li {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  color: var(--text);
  font-size: 0.9rem;
}

.check {
  display: grid;
  place-items: center;
  width: 24px;
  height: 24px;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--primary);
  color: var(--on-primary);
  font-family: var(--font-mono);
  font-size: 0.72rem;
  font-weight: 700;
}

.aside-foot {
  position: relative;
  z-index: 1;
  margin: 0;
  color: var(--text-muted);
  font-family: var(--font-mono);
  font-size: 0.75rem;
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

@media (max-width: 480px) {
  .auth-main {
    align-items: start;
    padding: 1.25rem 1rem;
  }
}
</style>
