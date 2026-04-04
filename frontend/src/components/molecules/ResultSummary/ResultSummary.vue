<template>
  <section class="panel p-6">
    <div class="flex flex-col items-center gap-4 text-center sm:flex-row sm:justify-between sm:text-left">
      <div class="relative flex h-24 w-24 items-center justify-center rounded-full border-8" :class="ringClass">
        <span class="text-xl font-bold">{{ roundedPercentage }}%</span>
      </div>

      <div class="grid w-full grid-cols-2 gap-3 sm:max-w-md">
        <div class="rounded-lg bg-slate-50 p-3">
          <p class="text-xs text-slate-500">Correct</p>
          <p class="text-lg font-semibold">{{ correctCount }}/{{ totalQuestions }}</p>
        </div>
        <div class="rounded-lg bg-slate-50 p-3">
          <p class="text-xs text-slate-500">Score</p>
          <p class="text-lg font-semibold">{{ score }}/{{ totalPoints }}</p>
        </div>
        <div class="col-span-2 rounded-lg bg-slate-50 p-3">
          <p class="text-xs text-slate-500">Time Taken</p>
          <p class="text-lg font-semibold">{{ formattedTime }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  percentage: {
    type: Number,
    default: 0,
  },
  correctCount: {
    type: Number,
    default: 0,
  },
  totalQuestions: {
    type: Number,
    default: 0,
  },
  score: {
    type: Number,
    default: 0,
  },
  totalPoints: {
    type: Number,
    default: 0,
  },
  timeTakenSeconds: {
    type: Number,
    default: 0,
  },
})

const roundedPercentage = computed(() => Math.round(Number(props.percentage) || 0))

const ringClass = computed(() =>
  roundedPercentage.value >= 60 ? 'border-emerald-500 text-emerald-700' : 'border-rose-500 text-rose-700'
)

const formattedTime = computed(() => {
  const seconds = Math.max(0, Number(props.timeTakenSeconds) || 0)
  const minutes = Math.floor(seconds / 60)
  const remainder = seconds % 60
  return `${minutes}m ${String(remainder).padStart(2, '0')}s`
})
</script>
