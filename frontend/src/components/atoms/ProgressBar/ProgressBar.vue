<template>
  <div class="w-full">
    <div class="mb-2 flex items-center justify-between text-sm text-slate-600">
      <span>Question {{ current }} of {{ total }}</span>
      <span>{{ percentage }}%</span>
    </div>
    <div class="h-2 overflow-hidden rounded-full bg-slate-200">
      <div class="h-full rounded-full bg-blue-600 transition-all duration-300" :style="{ width: `${percentage}%` }" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  current: {
    type: Number,
    required: true,
  },
  total: {
    type: Number,
    required: true,
  },
})

const percentage = computed(() => {
  const total = Math.max(1, Number(props.total) || 1)
  const current = Math.min(Math.max(1, Number(props.current) || 1), total)
  return Math.round((current / total) * 100)
})
</script>
