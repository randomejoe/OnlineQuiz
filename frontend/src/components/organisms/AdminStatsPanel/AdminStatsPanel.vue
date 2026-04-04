<script setup>
import { computed } from 'vue'
import { formatPercentage } from '../../../utils/format'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({}),
  },
})

const cards = computed(() => [
  {
    label: 'Total Students',
    value: Number(props.stats.total_students || 0),
  },
  {
    label: 'Total Quizzes',
    value: Number(props.stats.total_quizzes || 0),
  },
  {
    label: 'Total Attempts',
    value: Number(props.stats.total_attempts || 0),
  },
  {
    label: 'Avg Score',
    value: `${formatPercentage(props.stats.average_score_percentage)}%`,
  },
])
</script>

<template>
  <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <article v-for="card in cards" :key="card.label" class="panel-soft p-4">
      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ card.label }}</p>
      <p class="mt-2 text-2xl font-bold text-slate-900">{{ card.value }}</p>
    </article>
  </div>
</template>
