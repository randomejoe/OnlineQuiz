<template>
  <article class="panel flex h-full flex-col p-5">
    <div class="mb-3 flex items-center justify-between gap-2">
      <p class="text-sm text-slate-500">{{ quiz.subject || 'General' }}</p>
      <DifficultyBadge :difficulty="quiz.difficulty" />
    </div>

    <h3 class="mb-2 text-lg font-semibold text-slate-900">{{ quiz.title }}</h3>
    <p class="mb-5 line-clamp-3 text-sm text-slate-600">{{ quiz.description || 'No description provided.' }}</p>

    <div class="mt-auto flex items-center justify-between gap-3">
      <p class="text-sm text-slate-500">{{ quiz.time_limit_minutes || 0 }} min</p>
      <button type="button" class="btn-primary" @click="$emit('start', quiz.id)">Start Quiz</button>
    </div>
  </article>
</template>

<script setup>
import DifficultyBadge from '../../atoms/DifficultyBadge/DifficultyBadge.vue'

defineProps({
  quiz: {
    type: Object,
    required: true,
  },
})

defineEmits(['start'])
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
