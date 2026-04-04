<template>
  <StudentLayout>
    <section class="page-shell max-w-4xl">
      <p v-if="error" class="status-error">{{ error }}</p>

      <div v-if="!quiz" class="status-info">Loading quiz attempt...</div>

      <template v-else>
        <div class="panel flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
          <ProgressBar :current="currentQuestionNumber" :total="questions.length" />
          <TimerDisplay :seconds-remaining="secondsRemaining" />
        </div>

        <QuestionCard
          v-if="currentQuestion"
          :question="currentQuestion"
          :selected-option-id="attemptStore.selectedOptionIdForQuestion(currentQuestion.id)"
          :answer-text="attemptStore.answerTextForQuestion(currentQuestion.id)"
          :feedback="attemptStore.answerFeedbackForQuestion(currentQuestion.id)"
          :is-locked="Boolean(currentQuestionFeedback)"
          @answer-selected="handleAnswerSelected"
        />
        <div v-else class="status-info">No questions available for this quiz.</div>

        <div class="flex flex-wrap justify-between gap-2">
          <button type="button" class="btn-secondary" :disabled="currentQuestionIndex === 0 || submitting" @click="currentQuestionIndex -= 1">
            Previous
          </button>

          <div class="flex gap-2">
            <button
              type="button"
              class="btn-primary"
              :disabled="submitting || checkingAnswer"
              @click="handlePrimaryAction"
            >
              {{ primaryButtonLabel }}
            </button>
          </div>
        </div>
      </template>
    </section>

    <ConfirmDialog
      v-model="showLeaveDialog"
      title="Leave quiz?"
      message="If you leave now, this attempt will be discarded and not saved."
      confirm-label="Leave quiz"
      cancel-label="Stay"
      :loading="abandoning"
      @confirm="confirmLeaveQuiz"
    />

    <ConfirmDialog
      v-model="showSkipDialog"
      title="Skip this question?"
      message="You have not provided an answer for this question. Do you want to skip it?"
      confirm-label="Skip question"
      cancel-label="Go back"
      @confirm="confirmSkipQuestion"
    />
  </StudentLayout>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { onBeforeRouteLeave, useRoute, useRouter } from 'vue-router'
import { useAttemptStore } from '../../stores/attempt'
import ProgressBar from '../atoms/ProgressBar/ProgressBar.vue'
import TimerDisplay from '../atoms/TimerDisplay/TimerDisplay.vue'
import ConfirmDialog from '../molecules/ConfirmDialog/ConfirmDialog.vue'
import QuestionCard from '../molecules/QuestionCard/QuestionCard.vue'
import StudentLayout from '../templates/StudentLayout/StudentLayout.vue'

const route = useRoute()
const router = useRouter()
const attemptStore = useAttemptStore()

const error = ref('')
const currentQuestionIndex = ref(0)
const secondsRemaining = ref(0)
const timerId = ref(null)
const submitting = ref(false)
const checkingAnswer = ref(false)
const abandoning = ref(false)
const showLeaveDialog = ref(false)
const pendingRoute = ref(null)
const allowNavigation = ref(false)
const showSkipDialog = ref(false)

const quiz = computed(() => attemptStore.activeQuiz)
const questions = computed(() => quiz.value?.questions || [])
const currentQuestion = computed(() => questions.value[currentQuestionIndex.value] || null)
const currentQuestionNumber = computed(() => currentQuestionIndex.value + 1)

function startTimer(totalSeconds) {
  stopTimer()
  secondsRemaining.value = totalSeconds

  timerId.value = setInterval(() => {
    secondsRemaining.value = Math.max(0, secondsRemaining.value - 1)
    if (secondsRemaining.value === 0) {
      handleSubmit(true)
    }
  }, 1000)
}

function stopTimer() {
  if (timerId.value) {
    clearInterval(timerId.value)
    timerId.value = null
  }
}

function handleBeforeUnload(event) {
  if (!attemptStore.hasActiveAttempt || submitting.value || allowNavigation.value) {
    return
  }

  event.preventDefault()
  event.returnValue = ''
}

async function ensureAttempt() {
  const quizId = Number(route.params.id)
  const hasCorrectActiveQuiz = Number(attemptStore.activeQuiz?.id) === quizId

  if (!attemptStore.hasActiveAttempt || !hasCorrectActiveQuiz) {
    await attemptStore.startAttempt(quizId)
  }

  const limitMinutes = Number(attemptStore.activeQuiz?.time_limit_minutes || 0)
  startTimer(limitMinutes * 60)
}

async function handleAnswerSelected(payload) {
  attemptStore.setAnswer(payload.questionId, payload.optionId, payload.answerText)
  attemptStore.clearAnswerFeedback(payload.questionId)
}

function hasAnswer(question) {
  if (!question) return false

  if (question.type === 'short_answer') {
    return String(attemptStore.answerTextForQuestion(question.id) || '').trim().length > 0
  }

  return Number(attemptStore.selectedOptionIdForQuestion(question.id) || 0) > 0
}

const currentQuestionFeedback = computed(() => {
  if (!currentQuestion.value) return null
  return attemptStore.answerFeedbackForQuestion(currentQuestion.value.id)
})

const primaryButtonLabel = computed(() => {
  if (!currentQuestion.value) return 'Next'

  if (!currentQuestionFeedback.value) {
    return currentQuestionIndex.value === questions.value.length - 1 ? 'Submit Quiz' : 'Submit'
  }

  return currentQuestionIndex.value === questions.value.length - 1 ? 'Finish Quiz' : 'Next'
})

async function handlePrimaryAction(forceSubmit = false) {
  if (!currentQuestion.value) return

  if (currentQuestionFeedback.value) {
    if (currentQuestionIndex.value < questions.value.length - 1) {
      currentQuestionIndex.value += 1
    } else {
      await handleSubmit()
    }
    return
  }

  if (!forceSubmit && !hasAnswer(currentQuestion.value)) {
    showSkipDialog.value = true
    return
  }

  try {
    checkingAnswer.value = true
    error.value = ''
    await attemptStore.checkAnswer({
      question_id: currentQuestion.value.id,
      option_id: attemptStore.selectedOptionIdForQuestion(currentQuestion.value.id),
      answer_text: attemptStore.answerTextForQuestion(currentQuestion.value.id),
    })
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to check answer'
  } finally {
    checkingAnswer.value = false
  }
}

async function confirmSkipQuestion() {
  showSkipDialog.value = false
  await handlePrimaryAction(true)
}

async function handleSubmit(isAutoSubmit = false) {
  if (submitting.value) return

  try {
    submitting.value = true
    error.value = ''
    stopTimer()

    const result = await attemptStore.submitAttempt()
    attemptStore.clearActiveAttempt()
    router.push(`/results/${result.id}`)
  } catch (err) {
    error.value = isAutoSubmit
      ? 'Time expired and auto-submit failed. Please submit again.'
      : err instanceof Error
        ? err.message
        : 'Failed to submit attempt'
    submitting.value = false
  }
}

async function confirmLeaveQuiz() {
  if (abandoning.value) return

  try {
    abandoning.value = true
    error.value = ''
    stopTimer()
    await attemptStore.abandonAttempt()
    attemptStore.clearActiveAttempt()
    allowNavigation.value = true
    showLeaveDialog.value = false

    if (pendingRoute.value) {
      const target = pendingRoute.value
      pendingRoute.value = null
      await router.push(target)
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to leave quiz'
  } finally {
    abandoning.value = false
  }
}

onBeforeRouteLeave((to) => {
  if (allowNavigation.value || submitting.value || !attemptStore.hasActiveAttempt) {
    return true
  }

  pendingRoute.value = to
  showLeaveDialog.value = true
  return false
})

onMounted(async () => {
  window.addEventListener('beforeunload', handleBeforeUnload)
  try {
    await ensureAttempt()
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to load quiz attempt'
  }
})

onBeforeUnmount(() => {
  stopTimer()
  window.removeEventListener('beforeunload', handleBeforeUnload)
})
</script>
