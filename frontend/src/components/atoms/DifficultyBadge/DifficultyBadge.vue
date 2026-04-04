<template>
  <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide" :class="classes">
    {{ normalizedDifficulty }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  difficulty: {
    type: String,
    default: 'medium',
  },
})

const normalizedDifficulty = computed(() => {
  const value = String(props.difficulty || 'medium').trim().toLowerCase()
  if (['easy', 'medium', 'hard'].includes(value)) {
    return value
  }
  return 'medium'
})

const classes = computed(() => {
  if (normalizedDifficulty.value === 'easy') return 'bg-emerald-100 text-emerald-700'
  if (normalizedDifficulty.value === 'hard') return 'bg-rose-100 text-rose-700'
  return 'bg-amber-100 text-amber-700'
})
</script>
