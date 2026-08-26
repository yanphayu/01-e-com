<template>
  <div ref="root" class="locale">
    <button
      type="button"
      class="locale-trigger"
      :aria-label="t('locale.label')"
      @click="open = !open"
    >
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="9" />
        <path d="M3 12h18" />
        <path d="M12 3a14 14 0 0 1 0 18a14 14 0 0 1 0-18Z" />
      </svg>
      <span class="locale-code">{{ getLocale().toUpperCase() }}</span>
    </button>

    <ul v-if="open" class="locale-menu" role="menu">
      <li v-for="lang in SUPPORTED" :key="lang">
        <button
          type="button"
          class="locale-item"
          :class="{ active: getLocale() === lang }"
          role="menuitem"
          @click="choose(lang)"
        >
          <span>{{ t(`locale.${lang}`) }}</span>
          <span v-if="getLocale() === lang" class="check">✓</span>
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { t, setLocale, getLocale, SUPPORTED } from '../i18n'

const open = ref(false)
const root = ref(null)

function choose(lang) {
  setLocale(lang)
  open.value = false
}

function onClickOutside(e) {
  if (root.value && !root.value.contains(e.target)) open.value = false
}

onMounted(() => document.addEventListener('click', onClickOutside))
onUnmounted(() => document.removeEventListener('click', onClickOutside))
</script>

<style scoped>
.locale {
  position: relative;
}

.locale-trigger {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: none;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  padding: 0.4rem 0.6rem;
  font-size: 0.85rem;
  color: var(--text-muted);
  cursor: pointer;
}

.locale-trigger:hover {
  color: var(--text);
  border-color: var(--border-strong);
}

.locale-code {
  font-size: 0.78rem;
  font-weight: 600;
  letter-spacing: 0.04em;
}

.locale-menu {
  position: absolute;
  top: calc(100% + 0.5rem);
  right: 0;
  min-width: 150px;
  list-style: none;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  box-shadow: var(--shadow-md);
  padding: 0.35rem;
  z-index: 60;
}

.locale-item {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  background: none;
  border: none;
  border-radius: var(--radius-sm);
  padding: 0.55rem 0.65rem;
  font: inherit;
  font-size: 0.9rem;
  color: var(--text);
  cursor: pointer;
  text-align: left;
}

.locale-item:hover {
  background: var(--surface-2);
}

.locale-item.active {
  color: var(--accent);
  font-weight: 600;
}

.check {
  color: var(--accent);
  font-weight: 700;
}
</style>
