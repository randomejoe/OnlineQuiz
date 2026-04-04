<template>
  <div
    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-semibold"
    :class="isCritical ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700'"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      class="h-4 w-4"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
      aria-hidden="true"
    >
      <circle cx="12" cy="12" r="9" />
      <path d="M12 7v5l3 2" />
    </svg>
    <span>{{ formattedTime }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  secondsRemaining: {
    type: Number,
    required: true,
  },
})

const normalizedSeconds = computed(() => Math.max(0, Number(props.secondsRemaining) || 0))

const isCritical = computed(() => normalizedSeconds.value < 60)

const formattedTime = computed(() => {
  const minutes = Math.floor(normalizedSeconds.value / 60)
  const seconds = normalizedSeconds.value % 60
  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
})
</script>
