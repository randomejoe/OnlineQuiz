<template>
  <article class="panel p-4 sm:p-6">
    <p class="mb-4 text-base font-semibold text-slate-900 sm:text-lg">{{ question.question_text }}</p>
    <div v-if="feedback" class="mb-4 rounded-lg border px-3 py-2 text-sm" :class="feedbackClass">
      {{ feedbackMessage }}
    </div>

    <div v-if="question.type === 'short_answer'" class="space-y-2">
      <label class="block text-sm font-medium text-slate-700" :for="`short-answer-${question.id}`">Your answer</label>
      <input
        :id="`short-answer-${question.id}`"
        type="text"
        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 outline-none ring-blue-500 transition focus:border-blue-500 focus:ring-2"
        :value="answerText"
        placeholder="Type your answer..."
        :disabled="isLocked"
        @input="handleShortAnswerInput"
      />
    </div>

    <div v-else class="space-y-2">
      <label
        v-for="option in question.options"
        :key="option.id"
        class="flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition"
        :class="selectedOptionId === option.id ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:bg-slate-50'"
      >
        <input
          type="radio"
          class="mt-1 h-4 w-4 accent-blue-600"
          :name="`question-${question.id}`"
          :value="option.id"
          :checked="selectedOptionId === option.id"
          :disabled="isLocked"
          @change="handleSelect(option.id, option.option_text)"
        />
        <span class="text-sm text-slate-700">{{ option.option_text }}</span>
      </label>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  question: {
    type: Object,
    required: true,
  },
  selectedOptionId: {
    type: Number,
    default: null,
  },
  answerText: {
    type: String,
    default: '',
  },
  feedback: {
    type: Object,
    default: null,
  },
  isLocked: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['answer-selected'])

const feedbackMessage = computed(() => {
  if (!props.feedback) return ''
  return props.feedback.is_correct ? 'Correct answer.' : 'Incorrect answer.'
})

const feedbackClass = computed(() => {
  if (!props.feedback) return ''
  return props.feedback.is_correct ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800'
})

function handleSelect(optionId, optionText) {
  emit('answer-selected', {
    questionId: props.question.id,
    optionId,
    answerText: optionText,
  })
}

function handleShortAnswerInput(event) {
  emit('answer-selected', {
    questionId: props.question.id,
    optionId: null,
    answerText: event.target.value,
  })
}
</script>
