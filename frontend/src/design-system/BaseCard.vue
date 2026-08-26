<template>
  <component :is="tag" :class="['card', `card-${variant}`, { 'card-pad': padded }]">
    <slot />
  </component>
</template>

<script setup>
defineProps({
  tag: { type: String, default: 'div' },
  variant: { type: String, default: 'default' },
  padded: { type: Boolean, default: true },
})
</script>

<style scoped>
.card-default {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
}

.card-flat {
  background: transparent;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
}

.card-soft {
  background: var(--surface-2);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
}

.card-pad {
  padding: 1.25rem;
}
</style>
