<template>
  <StudentLayout>
    <section class="page-shell max-w-4xl">
      <p v-if="error" class="status-error">{{ error }}</p>

      <div v-if="loading" class="status-info">Loading quiz...</div>

      <article v-else-if="quiz" class="panel space-y-5 p-5 sm:p-6">
        <div>
          <h1 class="title-xl">{{ quiz.title }}</h1>
          <p class="mt-2 text-slate-600">{{ quiz.description || 'No description provided.' }}</p>
        </div>

        <QuizMeta
          :subject="quiz.subject"
          :difficulty="quiz.difficulty"
          :time-limit-minutes="quiz.time_limit_minutes"
          :question-count="quiz.questions?.length || 0"
        />

        <button type="button" class="btn-primary" :disabled="starting" @click="startQuiz">
          {{ starting ? 'Starting...' : 'Start Quiz' }}
        </button>
      </article>
    </section>
  </StudentLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useQuizStore } from '../../stores/quiz'
import { useAttemptStore } from '../../stores/attempt'
import QuizMeta from '../molecules/QuizMeta/QuizMeta.vue'
import StudentLayout from '../templates/StudentLayout/StudentLayout.vue'

const route = useRoute()
const router = useRouter()
const quizStore = useQuizStore()
const attemptStore = useAttemptStore()

const loading = ref(false)
const starting = ref(false)
const error = ref('')

const quiz = computed(() => quizStore.activeQuiz)

async function loadQuiz() {
  try {
    loading.value = true
    error.value = ''
    await quizStore.fetchQuiz(route.params.id)
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to load quiz'
  } finally {
    loading.value = false
  }
}

async function startQuiz() {
  try {
    starting.value = true
    error.value = ''
    await attemptStore.startAttempt(route.params.id)
    router.push(`/quizzes/${route.params.id}/take`)
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to start quiz'
  } finally {
    starting.value = false
  }
}

onMounted(loadQuiz)
</script>
