<template>
  <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
      <div>
        <p class="text-slate-500">Subject</p>
        <p class="font-semibold text-slate-800">{{ subject }}</p>
      </div>
      <div>
        <p class="text-slate-500">Difficulty</p>
        <DifficultyBadge :difficulty="difficulty" />
      </div>
      <div>
        <p class="text-slate-500">Time Limit</p>
        <p class="font-semibold text-slate-800">{{ timeLimitMinutes }} min</p>
      </div>
      <div>
        <p class="text-slate-500">Questions</p>
        <p class="font-semibold text-slate-800">{{ questionCount }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import DifficultyBadge from '../../atoms/DifficultyBadge/DifficultyBadge.vue'

defineProps({
  subject: {
    type: String,
    default: '-',
  },
  difficulty: {
    type: String,
    default: 'medium',
  },
  timeLimitMinutes: {
    type: Number,
    default: 0,
  },
  questionCount: {
    type: Number,
    default: 0,
  },
})
</script>
