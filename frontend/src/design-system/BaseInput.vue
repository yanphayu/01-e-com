<template>
  <div class="field">
    <label v-if="label" :for="id">{{ label }}</label>

    <div v-if="$slots.suffix" class="input-wrap">
      <input
        :id="id"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :autocomplete="autocomplete"
        :readonly="isReadonly"
        class="input"
        @focus="onFocus"
        @input="$emit('update:modelValue', $event.target.value)"
      />
      <slot name="suffix" />
    </div>

    <input
      v-else
      :id="id"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :autocomplete="autocomplete"
      :readonly="isReadonly"
      class="input"
      @focus="onFocus"
      @input="$emit('update:modelValue', $event.target.value)"
    />

    <p v-if="error" class="field-error">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  autocomplete: { type: String, default: 'off' },
  error: { type: String, default: '' },
  readonly: { type: Boolean, default: false },
  id: { type: String, default: () => `field-${Math.random().toString(36).slice(2, 9)}` },
})

const emit = defineEmits(['update:modelValue', 'focus'])

const isReadonly = ref(props.readonly)

watch(
  () => props.readonly,
  (val) => {
    isReadonly.value = val
  }
)

function onFocus(e) {
  if (isReadonly.value) isReadonly.value = false
  emit('focus', e)
}
</script>
