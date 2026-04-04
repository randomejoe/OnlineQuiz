<script setup>
import { reactive, ref, watch } from 'vue'
import QuestionForm from '../QuestionForm/QuestionForm.vue'

const props = defineProps({
  initialQuiz: {
    type: Object,
    default: null,
  },
  submitLabel: {
    type: String,
    default: 'Save Quiz',
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['submit', 'cancel'])

const form = reactive({
  title: '',
  description: '',
  subject: '',
  difficulty: 'medium',
  time_limit_minutes: 10,
})

const questions = ref([])
const error = ref('')

function createQuestion(type = 'multiple_choice') {
  if (type === 'true_false') {
    return {
      id: null,
      type,
      question_text: '',
      points: 1,
      options: [
        { id: null, option_text: 'True', is_correct: true, match_type: 'exact', match_threshold: null },
        { id: null, option_text: 'False', is_correct: false, match_type: 'exact', match_threshold: null },
      ],
    }
  }

  if (type === 'short_answer') {
    return {
      id: null,
      type,
      question_text: '',
      points: 1,
      options: [
        { id: null, option_text: '', is_correct: true, match_type: 'exact', match_threshold: null },
      ],
    }
  }

  return {
    id: null,
    type: 'multiple_choice',
    question_text: '',
    points: 1,
    options: [
      { id: null, option_text: '', is_correct: true, match_type: 'exact', match_threshold: null },
      { id: null, option_text: '', is_correct: false, match_type: 'exact', match_threshold: null },
      { id: null, option_text: '', is_correct: false, match_type: 'exact', match_threshold: null },
      { id: null, option_text: '', is_correct: false, match_type: 'exact', match_threshold: null },
    ],
  }
}

function normalizeQuestion(question = {}) {
  const type = question.type || 'multiple_choice'
  const nextQuestion = createQuestion(type)

  nextQuestion.id = question.id ?? null
  nextQuestion.question_text = question.question_text || ''
  nextQuestion.points = Number(question.points || 1)

  if (Array.isArray(question.options) && question.options.length > 0) {
    if (type === 'multiple_choice') {
      nextQuestion.options = Array.from({ length: 4 }, (_, index) => ({
        id: question.options[index]?.id ?? null,
        option_text: question.options[index]?.option_text || '',
        is_correct: Boolean(question.options[index]?.is_correct),
        match_type: 'exact',
        match_threshold: null,
      }))

      if (!nextQuestion.options.some((option) => option.is_correct)) {
        nextQuestion.options[0].is_correct = true
      }
    } else if (type === 'true_false') {
      const trueOption = question.options.find((option) => String(option.option_text).toLowerCase() === 'true')
      const falseOption = question.options.find((option) => String(option.option_text).toLowerCase() === 'false')
      const isTrueCorrect = question.options.some(
        (option) => option.is_correct && String(option.option_text).toLowerCase() === 'true'
      )

      nextQuestion.options = [
        {
          id: trueOption?.id ?? null,
          option_text: 'True',
          is_correct: isTrueCorrect || !question.options.some((option) => option.is_correct),
          match_type: 'exact',
          match_threshold: null,
        },
        {
          id: falseOption?.id ?? null,
          option_text: 'False',
          is_correct: !isTrueCorrect && question.options.some((option) => option.is_correct),
          match_type: 'exact',
          match_threshold: null,
        },
      ]
    } else {
      nextQuestion.options = [
        {
          id: question.options[0]?.id ?? null,
          option_text: question.options[0]?.option_text || '',
          is_correct: true,
          match_type: 'exact',
          match_threshold: null,
        },
      ]
    }
  }

  return nextQuestion
}

function applyInitialQuiz() {
  if (!props.initialQuiz) {
    form.title = ''
    form.description = ''
    form.subject = ''
    form.difficulty = 'medium'
    form.time_limit_minutes = 10
    questions.value = [createQuestion()]
    return
  }

  form.title = props.initialQuiz.title || ''
  form.description = props.initialQuiz.description || ''
  form.subject = props.initialQuiz.subject || ''
  form.difficulty = props.initialQuiz.difficulty || 'medium'
  form.time_limit_minutes = Number(props.initialQuiz.time_limit_minutes || 10)

  const incomingQuestions = Array.isArray(props.initialQuiz.questions) ? props.initialQuiz.questions : []
  questions.value = incomingQuestions.length > 0 ? incomingQuestions.map(normalizeQuestion) : [createQuestion()]
}

watch(() => props.initialQuiz, applyInitialQuiz, { immediate: true })

function addQuestion() {
  questions.value.push(createQuestion())
}

function removeQuestion(index) {
  questions.value.splice(index, 1)
  if (questions.value.length === 0) {
    questions.value.push(createQuestion())
  }
}

function updateQuestion(index, question) {
  questions.value[index] = question
}

function serializeQuestion(question, index) {
  const payload = {
    ...(question.id ? { id: question.id } : {}),
    type: question.type,
    question_text: question.question_text.trim(),
    order: index + 1,
    points: Math.max(1, Number(question.points || 1)),
    options: [],
  }

  if (question.type === 'multiple_choice') {
    const correctIndex = question.options.findIndex((entry) => entry.is_correct)
    payload.options = question.options.map((option, optionIndex) => ({
      ...(option.id ? { id: option.id } : {}),
      option_text: String(option.option_text || '').trim(),
      is_correct: optionIndex === (correctIndex === -1 ? 0 : correctIndex),
    }))
  } else if (question.type === 'true_false') {
    const trueOption = question.options.find((option) => option.option_text === 'True')
    const falseOption = question.options.find((option) => option.option_text === 'False')
    const correctIndex = question.options.findIndex((option) => option.is_correct)
    const normalizedCorrectIndex = correctIndex === -1 ? 0 : correctIndex

    payload.options = [
      { ...(trueOption?.id ? { id: trueOption.id } : {}), option_text: 'True', is_correct: normalizedCorrectIndex === 0 },
      { ...(falseOption?.id ? { id: falseOption.id } : {}), option_text: 'False', is_correct: normalizedCorrectIndex === 1 },
    ]
  } else {
    payload.options = [
      {
        ...(question.options[0]?.id ? { id: question.options[0].id } : {}),
        option_text: String(question.options[0]?.option_text || '').trim(),
        is_correct: true,
        match_type: 'exact',
        match_threshold: null,
      },
    ]
  }

  return payload
}

function validateBeforeSubmit() {
  if (!form.title.trim()) {
    return 'Quiz title is required.'
  }

  if (!form.subject.trim()) {
    return 'Quiz subject is required.'
  }

  if (questions.value.length === 0) {
    return 'At least one question is required.'
  }

  for (let index = 0; index < questions.value.length; index += 1) {
    const question = questions.value[index]
    if (!question.question_text.trim()) {
      return `Question ${index + 1} text is required.`
    }

    if (question.type === 'multiple_choice') {
      const hasEmptyOption = question.options.some((option) => !String(option.option_text || '').trim())
      if (hasEmptyOption) {
        return `Question ${index + 1} must have 4 filled options.`
      }
    }

    if (question.type === 'short_answer') {
      if (!String(question.options[0]?.option_text || '').trim()) {
        return `Question ${index + 1} needs a correct answer.`
      }
    }
  }

  return ''
}

function handleSubmit() {
  error.value = ''

  const validationError = validateBeforeSubmit()
  if (validationError) {
    error.value = validationError
    return
  }

  const payload = {
    title: form.title.trim(),
    description: form.description.trim(),
    subject: form.subject.trim(),
    difficulty: form.difficulty,
    time_limit_minutes: Math.max(1, Number(form.time_limit_minutes || 1)),
    questions: questions.value.map(serializeQuestion),
  }

  emit('submit', payload)
}
</script>

<template>
  <form class="space-y-5" @submit.prevent="handleSubmit">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
      <label class="block text-sm font-medium text-slate-700 sm:col-span-2">
        Title
        <input v-model.trim="form.title" type="text" class="field mt-1" required />
      </label>

      <label class="block text-sm font-medium text-slate-700 sm:col-span-2">
        Description
        <textarea v-model="form.description" rows="3" class="field mt-1" />
      </label>

      <label class="block text-sm font-medium text-slate-700">
        Subject
        <input v-model.trim="form.subject" type="text" class="field mt-1" required />
      </label>

      <label class="block text-sm font-medium text-slate-700">
        Difficulty
        <select v-model="form.difficulty" class="field mt-1">
          <option value="easy">Easy</option>
          <option value="medium">Medium</option>
          <option value="hard">Hard</option>
        </select>
      </label>

      <label class="block text-sm font-medium text-slate-700 sm:col-span-2">
        Time limit (minutes)
        <input v-model.number="form.time_limit_minutes" type="number" min="1" class="field mt-1" />
      </label>
    </div>

    <div class="space-y-3">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-slate-900">Questions</h2>
        <button type="button" class="btn-secondary" @click="addQuestion">Add Question</button>
      </div>

      <QuestionForm
        v-for="(question, index) in questions"
        :key="question.id ?? index"
        :model-value="question"
        :index="index"
        @update:model-value="updateQuestion(index, $event)"
        @remove="removeQuestion(index)"
      />
    </div>

    <p v-if="error" class="status-error">{{ error }}</p>

    <div class="flex flex-wrap justify-end gap-2">
      <button type="button" class="btn-secondary" :disabled="loading" @click="emit('cancel')">Cancel</button>
      <button type="submit" class="btn-primary" :disabled="loading">
        {{ loading ? 'Saving...' : submitLabel }}
      </button>
    </div>
  </form>
</template>
