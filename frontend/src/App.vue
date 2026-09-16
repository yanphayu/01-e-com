<template>
  <div class="app" :class="{ 'app-chat': route.name === 'chat' }">
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

.app-chat {
  height: 100dvh;
}

.app-chat .app-main {
  flex: 1;
  min-height: 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

@media (max-width: 1024px) {
  .app-main {
    padding-bottom: 70px;
  }
}
</style>
