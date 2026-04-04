<template>
  <span
    class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold"
    :class="isPassing ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
  >
    {{ normalizedScore }}%
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  percentage: {
    type: Number,
    required: true,
  },
  passThreshold: {
    type: Number,
    default: 60,
  },
})

const normalizedScore = computed(() => {
  const value = Number(props.percentage)
  if (Number.isNaN(value)) return '0.0'
  return value.toFixed(1)
})

const isPassing = computed(() => Number(props.percentage) >= Number(props.passThreshold))
</script>
