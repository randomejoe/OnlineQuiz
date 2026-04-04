<template>
  <section class="space-y-3">
    <article
      v-for="(answer, index) in answers"
      :key="`${answer.question_id}-${index}`"
      class="rounded-xl border p-4"
      :class="answer.is_correct ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50'"
    >
      <p class="mb-2 font-semibold text-slate-900">{{ index + 1 }}. {{ answer.question_text }}</p>
      <p class="text-sm text-slate-700">
        Your answer:
        <span class="font-medium">{{ answer.answer_text || 'No answer' }}</span>
      </p>
      <p class="text-sm text-slate-700">
        Correct answer:
        <span class="font-medium">{{ answer.correct_answer || '-' }}</span>
      </p>
      <p class="mt-1 text-xs text-slate-500">Points: {{ answer.points_earned }}/{{ answer.points }}</p>
    </article>
  </section>
</template>

<script setup>
defineProps({
  answers: {
    type: Array,
    default: () => [],
  },
})
</script>
