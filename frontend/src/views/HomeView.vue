<template>
  <div class="home">
    <!-- Hero -->
    <section class="hero">
      <div class="container hero-inner">
        <p class="eyebrow">{{ t('home.eyebrow') }}</p>
        <h1 class="hero-title">
          {{ t('home.heroTitle1') }}<br />
          <span class="accent">{{ t('home.heroTitleAccent') }}</span>
        </h1>
        <p class="hero-sub">
          {{ t('home.heroSub') }}
        </p>
        <div class="hero-actions">
          <RouterLink to="/register" class="btn btn-primary btn-lg">{{ t('home.startShopping') }}</RouterLink>
          <RouterLink to="/login" class="btn btn-ghost btn-lg">{{ t('home.signIn') }}</RouterLink>
        </div>
      </div>
    </section>

    <!-- Categories -->
    <section class="section container">
      <header class="section-head">
        <h2>{{ t('home.shopByCategory') }}</h2>
        <p>{{ t('home.shopByCategorySub') }}</p>
      </header>
      <div class="grid grid-3">
        <article v-for="cat in categories" :key="cat.title" class="card category">
          <div class="thumb" :style="{ background: cat.bg }"></div>
          <h3>{{ cat.title }}</h3>
          <p>{{ cat.items }} {{ t('home.items') }}</p>
        </article>
      </div>
    </section>

    <!-- Featured -->
    <section class="section container">
      <header class="section-head">
        <h2>{{ t('home.featuredPicks') }}</h2>
        <p>{{ t('home.featuredSub') }}</p>
      </header>
      <div class="grid grid-4">
        <article v-for="p in products" :key="p.name" class="card product">
          <div class="thumb product-thumb" :style="{ background: p.bg }"></div>
          <div class="product-body">
            <h3>{{ p.name }}</h3>
            <p class="price">{{ p.price }}</p>
          </div>
        </article>
      </div>
    </section>

    <!-- CTA band -->
    <section class="cta-band">
      <div class="container cta-inner">
        <h2>{{ t('home.ctaTitle') }}</h2>
        <p>{{ t('home.ctaSub') }}</p>
        <RouterLink to="/register" class="btn btn-primary btn-lg">{{ t('home.ctaBtn') }}</RouterLink>
      </div>
    </section>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import { t } from '../i18n'

const categories = [
  { title: 'Apparel', items: 42, bg: 'linear-gradient(135deg, #e7f4ec, #cfe8da)' },
  { title: 'Home', items: 28, bg: 'linear-gradient(135deg, #eef6f0, #d8ebe0)' },
  { title: 'Accessories', items: 19, bg: 'linear-gradient(135deg, #e3f1e8, #c9e3d4)' },
]

const products = [
  { name: 'Linen Overshirt', price: '$68', bg: 'linear-gradient(135deg, #e7f4ec, #cfe8da)' },
  { name: 'Ceramic Mug', price: '$24', bg: 'linear-gradient(135deg, #eef6f0, #d8ebe0)' },
  { name: 'Canvas Tote', price: '$38', bg: 'linear-gradient(135deg, #e3f1e8, #c9e3d4)' },
  { name: 'Wool Throw', price: '$92', bg: 'linear-gradient(135deg, #e9f5ee, #d2e9da)' },
]
</script>

<style scoped>
.home {
  min-height: 100%;
}

/* Hero */
.hero {
  background:
    radial-gradient(1200px 400px at 80% -10%, var(--accent-soft), transparent 60%),
    var(--surface);
  border-bottom: 1px solid var(--border);
}

.hero-inner {
  padding: 4rem 1.5rem 3.25rem;
  max-width: 720px;
}

.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.18em;
  font-size: 0.74rem;
  font-weight: 600;
  color: var(--accent);
  margin-bottom: 0.85rem;
}

.hero-title {
  font-family: var(--font-serif);
  font-size: clamp(2rem, 5vw, 2.9rem);
  line-height: 1.12;
  font-weight: 600;
  letter-spacing: 0.01em;
}

.hero-title .accent {
  color: var(--accent);
}

.hero-sub {
  margin-top: 1rem;
  font-size: 1rem;
  color: var(--text-muted);
  max-width: 520px;
}

.hero-actions {
  margin-top: 1.6rem;
  display: flex;
  gap: 0.65rem;
  flex-wrap: wrap;
}

.btn-lg {
  padding: 0.7rem 1.35rem;
  font-size: 0.97rem;
}

/* Sections */
.section {
  padding: 3rem 1.5rem;
}

.section-head {
  margin-bottom: 1.5rem;
}

.section-head h2 {
  font-family: var(--font-serif);
  font-size: 1.6rem;
  font-weight: 600;
}

.section-head p {
  margin-top: 0.3rem;
  color: var(--text-muted);
}

.grid {
  display: grid;
  gap: 1.25rem;
}

.grid-3 {
  grid-template-columns: repeat(3, 1fr);
}

.grid-4 {
  grid-template-columns: repeat(4, 1fr);
}

.card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  overflow: hidden;
}

.card:hover {
  border-color: var(--border-strong);
}

.thumb {
  height: 130px;
  width: 100%;
}

.category {
  padding: 0;
}

.category h3 {
  font-size: 1rem;
  font-weight: 600;
  padding: 0.9rem 1rem 0.2rem;
}

.category p {
  color: var(--text-muted);
  font-size: 0.83rem;
  padding: 0 1rem 1rem;
}

.product-thumb {
  height: 160px;
}

.product-body {
  padding: 0.9rem 1rem 1.05rem;
}

.product-body h3 {
  font-size: 0.95rem;
  font-weight: 600;
}

.price {
  margin-top: 0.2rem;
  color: var(--accent);
  font-weight: 600;
}

/* CTA band */
.cta-band {
  margin: 0.5rem 0 3rem;
}

.cta-inner {
  background: var(--surface-2);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 2.5rem 2rem;
  text-align: center;
}

.cta-inner h2 {
  font-family: var(--font-serif);
  font-size: 1.6rem;
  font-weight: 600;
}

.cta-inner p {
  margin: 0.5rem 0 1.25rem;
  color: var(--text-muted);
}

@media (max-width: 900px) {
  .grid-4 {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .grid-3 {
    grid-template-columns: 1fr;
  }
  .hero-inner {
    padding-top: 3.5rem;
  }
}
</style>
