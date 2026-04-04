<script setup>
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Please confirm',
  },
  message: {
    type: String,
    default: 'Are you sure you want to continue?',
  },
  confirmLabel: {
    type: String,
    default: 'Confirm',
  },
  cancelLabel: {
    type: String,
    default: 'Cancel',
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue', 'confirm'])

function close() {
  if (props.loading) return
  emit('update:modelValue', false)
}

function confirm() {
  emit('confirm')
}
</script>

<template>
  <teleport to="body">
    <div v-if="modelValue" class="fixed inset-0 z-40 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-950/50" @click="close" />
      <div class="panel relative z-10 w-full max-w-md p-5">
        <h2 class="text-lg font-semibold text-slate-900">{{ title }}</h2>
        <p class="mt-2 text-sm text-slate-600">{{ message }}</p>

        <div class="mt-5 flex justify-end gap-2">
          <button type="button" class="btn-secondary" :disabled="loading" @click="close">{{ cancelLabel }}</button>
          <button type="button" class="btn-danger" :disabled="loading" @click="confirm">
            {{ loading ? 'Working...' : confirmLabel }}
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>
