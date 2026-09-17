<template>
  <BaseModal
    :model-value="modelValue"
    :title="t('report.title')"
    size="sm"
    @update:model-value="(v) => emit('update:modelValue', v)"
  >
    <form @submit.prevent="submit">
      <div class="field">
        <label for="report-reason">{{ t('report.reason') }}</label>
        <select id="report-reason" v-model="reason" class="input" required>
          <option value="" disabled>{{ t('report.reasonPlaceholder') }}</option>
          <option v-for="r in reasons" :key="r" :value="r">{{ t(`report.reason_${r}`) }}</option>
        </select>
      </div>
      <div class="field">
        <label for="report-details">{{ t('report.details') }}</label>
        <textarea id="report-details" v-model="details" class="input report-details" rows="3" :placeholder="t('report.detailsPlaceholder')"></textarea>
      </div>

      <p v-if="errorMsg" class="field-error">{{ errorMsg }}</p>
      <p v-if="successMsg" class="report-success">{{ successMsg }}</p>

      <div class="report-actions">
        <button type="button" class="btn btn-ghost" :disabled="submitting" @click="emit('update:modelValue', false)">
          {{ t('auth.cancel') }}
        </button>
        <button type="submit" class="btn btn-primary" :disabled="submitting">
          {{ submitting ? t('report.submitting') : t('report.submit') }}
        </button>
      </div>
    </form>
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import { t } from '../i18n'
import { reportProduct } from '../services/reports'
import BaseModal from '../design-system/BaseModal.vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  productId: { type: [Number, String], required: true },
})

const emit = defineEmits(['update:modelValue'])

const reasons = ['spam', 'fraud', 'fake', 'inappropriate', 'duplicate', 'other']
const reason = ref('')
const details = ref('')
const submitting = ref(false)
const errorMsg = ref('')
const successMsg = ref('')

watch(() => props.modelValue, (open) => {
  if (!open) return
  reason.value = ''
  details.value = ''
  errorMsg.value = ''
  successMsg.value = ''
})

async function submit() {
  if (!reason.value) return
  submitting.value = true
  errorMsg.value = ''
  try {
    const res = await reportProduct(props.productId, {
      reason: reason.value,
      details: details.value,
    })
    successMsg.value = res.message || t('report.success')
    setTimeout(() => {
      emit('update:modelValue', false)
      successMsg.value = ''
    }, 1800)
  } catch (e) {
    errorMsg.value = e.message || t('report.failed')
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.report-details {
  resize: vertical;
  min-height: 80px;
}

.report-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
  margin-top: 1rem;
}

.report-success {
  margin-top: 0.75rem;
  font-size: 0.85rem;
  color: var(--success);
  background: var(--success-soft);
  border: 1px solid rgba(47, 158, 107, 0.2);
  border-radius: var(--radius-sm);
  padding: 0.6rem 0.8rem;
}
</style>