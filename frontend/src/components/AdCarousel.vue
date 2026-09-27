<template>
  <div v-if="ads.length" class="ad-shell" :class="`ad-shell--${variant}`">
    <section
      class="ad-carousel"
      :class="`ad-carousel--${variant}`"
      :aria-label="t('ads.label')"
      @mouseenter="paused = true"
      @mouseleave="paused = false"
      @focusin="paused = true"
      @focusout="paused = false"
    >
    <div class="ad-track" :style="{ transform: `translateX(-${index * 100}%)` }">
      <component
        :is="tagFor(ad)"
        v-for="ad in ads"
        :key="ad.id"
        class="ad-slide"
        :to="linkFor(ad)"
        :href="externalFor(ad)"
        :target="externalFor(ad) ? '_blank' : undefined"
        :rel="externalFor(ad) ? 'noopener noreferrer' : undefined"
      >
        <img :src="resolveStorageUrl(ad.image)" :alt="ad.headline || t('ads.label')" class="ad-image" />
        <div class="ad-overlay">
          <p v-if="ad.headline" class="ad-headline">{{ ad.headline }}</p>
          <p v-if="ad.subtext" class="ad-subtext">{{ ad.subtext }}</p>
          <span v-if="ad.button_label" class="ad-button">{{ ad.button_label }}</span>
        </div>
      </component>
    </div>

    <template v-if="ads.length > 1">
      <button type="button" class="ad-arrow ad-arrow--prev" :aria-label="t('ads.previous')" @click="go(-1)">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <polyline points="15 18 9 12 15 6" />
        </svg>
      </button>
      <button type="button" class="ad-arrow ad-arrow--next" :aria-label="t('ads.next')" @click="go(1)">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <polyline points="9 18 15 12 9 6" />
        </svg>
      </button>

      <div class="ad-dots">
        <button
          v-for="(ad, dotIndex) in ads"
          :key="ad.id"
          type="button"
          class="ad-dot"
          :class="{ active: dotIndex === index }"
          :aria-label="`${t('ads.goTo')} ${dotIndex + 1}`"
          :aria-current="dotIndex === index"
          @click="goTo(dotIndex)"
        />
      </div>
    </template>
    </section>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { t } from '../i18n'
import { getAds } from '../services/ads'
import { resolveStorageUrl } from '../services/http'

const props = defineProps({
  placement: { type: String, required: true },
  variant: { type: String, default: 'strip' },
  interval: { type: Number, default: 6000 },
})

const ads = ref([])
const index = ref(0)
const paused = ref(false)
let timer = null

const prefersReducedMotion = computed(
  () => typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches,
)

function externalFor(ad) {
  return /^https?:\/\//i.test(ad.link_url || '')
}

function linkFor(ad) {
  if (!ad.link_url) return undefined
  return externalFor(ad) ? undefined : ad.link_url
}

function tagFor(ad) {
  if (!ad.link_url) return 'div'
  return externalFor(ad) ? 'a' : RouterLink
}

function go(step) {
  goTo((index.value + step + ads.value.length) % ads.value.length)
}

function goTo(target) {
  index.value = target
  startTimer()
}

function startTimer() {
  stopTimer()
  if (ads.value.length < 2 || props.interval <= 0 || prefersReducedMotion.value) return
  timer = window.setInterval(() => {
    if (!paused.value) goTo((index.value + 1) % ads.value.length)
  }, props.interval)
}

function stopTimer() {
  if (timer) {
    window.clearInterval(timer)
    timer = null
  }
}

onMounted(async () => {
  try {
    const res = await getAds(props.placement)
    ads.value = res.data || []
  } catch {
    ads.value = []
  }
  startTimer()
})

onBeforeUnmount(stopTimer)
</script>

<style scoped>
.ad-shell {
  width: 100%;
  max-width: var(--container);
  margin-inline: auto;
  padding-inline: 1.5rem;
}

.ad-shell--hero {
  margin-top: 1rem;
}

.ad-carousel {
  position: relative;
  overflow: hidden;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  background: var(--surface-2);
}

.ad-track {
  display: flex;
  transition: transform 0.45s ease;
}

.ad-slide {
  position: relative;
  flex: 0 0 100%;
  display: block;
  text-decoration: none;
  color: #fff;
}

.ad-image {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.ad-overlay {
  position: absolute;
  inset: auto 0 0 0;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.25rem;
  padding: 1rem 1.25rem;
  background: linear-gradient(180deg, transparent, rgba(0, 0, 0, 0.72));
}

.ad-headline {
  font-family: var(--font-mono);
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.2;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
}

.ad-subtext {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.88);
}

.ad-button {
  margin-top: 0.15rem;
  border-radius: 999px;
  background: #fff;
  padding: 0.3rem 0.85rem;
  color: #111827;
  font-size: 0.75rem;
  font-weight: 700;
}

.ad-arrow {
  position: absolute;
  top: 50%;
  display: grid;
  place-items: center;
  width: 34px;
  height: 34px;
  transform: translateY(-50%);
  border: 1px solid rgba(255, 255, 255, 0.4);
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.35);
  color: #fff;
  cursor: pointer;
  opacity: 0;
  transition: opacity 0.2s;
}

.ad-carousel:hover .ad-arrow,
.ad-arrow:focus-visible {
  opacity: 1;
}

.ad-arrow--prev {
  left: 10px;
}

.ad-arrow--next {
  right: 10px;
}

.ad-dots {
  position: absolute;
  right: 0.9rem;
  bottom: 0.6rem;
  display: flex;
  gap: 0.35rem;
}

.ad-dot {
  width: 8px;
  height: 8px;
  padding: 0;
  border: 1px solid rgba(255, 255, 255, 0.75);
  border-radius: 999px;
  background: transparent;
  cursor: pointer;
}

.ad-dot.active {
  background: #fff;
}

.ad-carousel--strip .ad-slide {
  height: 76px;
}

.ad-carousel--hero .ad-slide {
  aspect-ratio: 21 / 7;
}

.ad-carousel--hero .ad-overlay {
  gap: 0.4rem;
  padding: 1.5rem 1.75rem;
}

.ad-carousel--hero .ad-headline {
  font-size: clamp(1.3rem, 2.6vw, 2rem);
}

.ad-carousel--hero .ad-subtext {
  font-size: 0.95rem;
}

.ad-carousel--hero .ad-button {
  padding: 0.4rem 1.1rem;
  font-size: 0.85rem;
}

@media (max-width: 768px) {
  .ad-shell {
    padding-inline: 1rem;
  }

  .ad-carousel--strip .ad-slide {
    height: 96px;
  }

  .ad-carousel--hero .ad-slide {
    aspect-ratio: 4 / 3;
  }

  .ad-carousel--hero .ad-overlay {
    padding: 1rem;
  }

  .ad-arrow {
    opacity: 1;
  }
}
</style>
