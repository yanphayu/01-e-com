<template>
  <div class="app">
    <Navbar v-if="!isAuthPage" />
    <main class="app-main">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import Navbar from './components/Navbar.vue'

const route = useRoute()
const isAuthPage = computed(() =>
  ['login', 'register', 'verify-email'].includes(route.name)
)
</script>

<style scoped>
.app {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.app-main {
  flex: 1;
}

@media (max-width: 768px) {
  .app-main {
    padding-bottom: 70px;
  }
}
</style>
