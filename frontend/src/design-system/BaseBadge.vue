<template>
  <span :class="['badge', `badge-${variant}`, `badge-${size}`]">
    <slot />
  </span>
</template>

<script setup>
defineProps({
  variant: { type: String, default: 'neutral' },
  size: { type: String, default: 'md' },
})
</script>

<style scoped>
.badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-weight: 600;
  border-radius: 999px;
  line-height: 1;
}

.badge-sm {
  font-size: 0.72rem;
  padding: 0.25rem 0.55rem;
}
.badge-md {
  font-size: 0.8rem;
  padding: 0.35rem 0.7rem;
}

.badge-neutral {
  background: var(--surface-2);
  color: var(--text-muted);
  border: 1px solid var(--border);
}
.badge-accent {
  background: var(--accent-soft);
  color: var(--accent);
}
.badge-success {
  background: var(--success-soft);
  color: var(--success);
}
.badge-danger {
  background: var(--danger-soft);
  color: var(--danger);
}
</style>
