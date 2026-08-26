<template>
  <component
    :is="tag"
    :to="to"
    :type="tag === 'button' ? type : undefined"
    :disabled="disabled || loading"
    :class="classes"
    @click="$emit('click', $event)"
  >
    <slot />
  </component>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'

const props = defineProps({
  variant: { type: String, default: 'primary' },
  size: { type: String, default: 'md' },
  block: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  type: { type: String, default: 'button' },
  to: { type: [String, Object], default: null },
})

defineEmits(['click'])

const tag = computed(() => (props.to ? RouterLink : 'button'))

const classes = computed(() => [
  'btn',
  `btn-${props.variant}`,
  `btn-${props.size}`,
  { 'btn-block': props.block },
])
</script>

<style scoped>
.btn-sm {
  padding: 0.42rem 0.8rem;
  font-size: 0.85rem;
}
.btn-md {
  padding: 0.45rem 0.9rem;
  font-size: 0.88rem;
}
.btn-lg {
  padding: 0.7rem 1.35rem;
  font-size: 0.97rem;
}
</style>
