<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
  index: {
    type: Number,
    required: true,
  },
})

const emit = defineEmits(['update:modelValue', 'remove'])

const question = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})

function createShortAnswerOption() {
  return {
    id: null,
    option_text: '',
    is_correct: true,
    match_type: 'exact',
    match_threshold: null,
  }
}

function normalizeOptions(type, sourceOptions = []) {
  if (type === 'multiple_choice') {
    const options = Array.from({ length: 4 }, (_, index) => {
      const current = sourceOptions[index]
      return {
        id: current?.id ?? null,
        option_text: current?.option_text || '',
        is_correct: Boolean(current?.is_correct),
        match_type: 'exact',
        match_threshold: null,
      }
    })

    if (!options.some((option) => option.is_correct)) {
      options[0].is_correct = true
    }

    return options
  }

  if (type === 'true_false') {
    const trueOption = sourceOptions.find((option) => String(option.option_text).toLowerCase() === 'true')
    const falseOption = sourceOptions.find((option) => String(option.option_text).toLowerCase() === 'false')
    const hasTrueAsCorrect = sourceOptions.some(
      (option) => option.is_correct && String(option.option_text).toLowerCase() === 'true'
    )

    return [
      {
        id: trueOption?.id ?? null,
        option_text: 'True',
        is_correct: hasTrueAsCorrect || !sourceOptions.some((option) => option.is_correct),
        match_type: 'exact',
        match_threshold: null,
      },
      {
        id: falseOption?.id ?? null,
        option_text: 'False',
        is_correct: !hasTrueAsCorrect && sourceOptions.some((option) => option.is_correct),
        match_type: 'exact',
        match_threshold: null,
      },
    ]
  }

  if (sourceOptions.length > 0) {
    return [
      {
        id: sourceOptions[0]?.id ?? null,
        option_text: sourceOptions[0]?.option_text || '',
        is_correct: true,
        match_type: 'exact',
        match_threshold: null,
      },
    ]
  }

  return [createShortAnswerOption()]
}

function updateQuestion(patch) {
  question.value = { ...question.value, ...patch }
}

function updateType(type) {
  updateQuestion({
    type,
    options: normalizeOptions(type, question.value.options),
  })
}

function updateOptionField(index, field, value) {
  const options = [...question.value.options]
  const current = options[index] || createShortAnswerOption()
  const nextOption = { ...current, [field]: value }

  options[index] = nextOption
  updateQuestion({ options })
}

function updateCorrectOption(index) {
  const options = question.value.options.map((option, optionIndex) => ({
    ...option,
    is_correct: optionIndex === index,
  }))
  updateQuestion({ options })
}

</script>

<template>
  <article class="panel space-y-4 p-4">
    <div class="flex items-center justify-between">
      <h3 class="text-sm font-semibold text-slate-900">Question {{ index + 1 }}</h3>
      <button type="button" class="btn border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50" @click="emit('remove')">
        Remove
      </button>
    </div>

    <label class="block text-sm font-medium text-slate-700">
      Question text
      <textarea
        :value="question.question_text"
        rows="2"
        class="field mt-1"
        @input="updateQuestion({ question_text: $event.target.value })"
      />
    </label>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
      <label class="block text-sm font-medium text-slate-700">
        Type
        <select :value="question.type" class="field mt-1" @change="updateType($event.target.value)">
          <option value="multiple_choice">Multiple Choice</option>
          <option value="true_false">True/False</option>
          <option value="short_answer">Short Answer</option>
        </select>
      </label>

      <label class="block text-sm font-medium text-slate-700">
        Points
        <input
          :value="question.points"
          type="number"
          min="1"
          class="field mt-1"
          @input="updateQuestion({ points: Number($event.target.value || 1) })"
        />
      </label>
    </div>

    <div class="space-y-2">
      <p class="text-sm font-medium text-slate-700">Answer setup</p>

      <div v-if="question.type === 'multiple_choice'" class="space-y-2">
        <div
          v-for="(option, optionIndex) in question.options"
          :key="option.id ?? `mc-${optionIndex}`"
          class="flex items-center gap-2"
        >
          <input type="radio" :checked="option.is_correct" class="h-4 w-4 accent-blue-600" @change="updateCorrectOption(optionIndex)" />
          <input
            :value="option.option_text"
            type="text"
            :placeholder="`Option ${optionIndex + 1}`"
            class="field"
            @input="updateOptionField(optionIndex, 'option_text', $event.target.value)"
          />
        </div>
      </div>

      <div v-else-if="question.type === 'true_false'" class="space-y-2">
        <label
          v-for="(option, optionIndex) in question.options"
          :key="option.id ?? `tf-${optionIndex}`"
          class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700"
        >
          <input type="radio" :checked="option.is_correct" class="h-4 w-4 accent-blue-600" @change="updateCorrectOption(optionIndex)" />
          <span>{{ option.option_text }}</span>
        </label>
      </div>

      <div v-else class="space-y-3">
        <label class="block text-sm font-medium text-slate-700">
          Correct answer
          <input
            :value="question.options[0]?.option_text || ''"
            type="text"
            placeholder="Expected answer"
            class="field mt-1"
            @input="updateOptionField(0, 'option_text', $event.target.value)"
          />
        </label>
      </div>
    </div>
  </article>
</template>
