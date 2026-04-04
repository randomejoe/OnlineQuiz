<template>
  <StudentLayout>
    <section class="page-shell">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="title-xl">Browse Quizzes</h1>
          <p class="subtitle">Find a quiz and start practicing.</p>
        </div>
      </div>

      <div class="panel grid grid-cols-1 gap-3 p-4 sm:grid-cols-3">
        <input
          v-model.trim="search"
          type="text"
          placeholder="Search by title, description, subject"
          class="field"
        />

        <select v-model="subjectFilter" class="field">
          <option value="">All subjects</option>
          <option v-for="subject in subjectOptions" :key="subject" :value="subject">{{ subject }}</option>
        </select>

        <select v-model="difficultyFilter" class="field">
          <option value="">All difficulties</option>
          <option value="easy">Easy</option>
          <option value="medium">Medium</option>
          <option value="hard">Hard</option>
        </select>
      </div>

      <p v-if="error" class="status-error">{{ error }}</p>

      <div v-if="loading" class="status-info">Loading quizzes...</div>

      <div v-else-if="paginatedQuizzes.length === 0" class="status-info">No quizzes found.</div>

      <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <QuizCard v-for="quiz in paginatedQuizzes" :key="quiz.id" :quiz="quiz" @start="goToQuiz" />
      </div>

      <div class="flex items-center justify-center gap-2 pb-4">
        <button type="button" class="btn-secondary" :disabled="page <= 1" @click="page -= 1">Previous</button>
        <span class="text-sm text-slate-600">Page {{ page }} / {{ totalPages }}</span>
        <button type="button" class="btn-secondary" :disabled="page >= totalPages" @click="page += 1">Next</button>
      </div>
    </section>
  </StudentLayout>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useQuizStore } from '../../stores/quiz'
import QuizCard from '../organisms/QuizCard/QuizCard.vue'
import StudentLayout from '../templates/StudentLayout/StudentLayout.vue'

const router = useRouter()
const quizStore = useQuizStore()

const loading = ref(false)
const error = ref('')
const search = ref('')
const subjectFilter = ref('')
const difficultyFilter = ref('')
const page = ref(1)
const perPage = 9
const staticDatasetThreshold = 50
const skipNextPageFetch = ref(false)

const subjectOptions = computed(() => {
  const values = new Set(quizStore.quizzes.map((quiz) => quiz.subject).filter(Boolean))
  return Array.from(values).sort((a, b) => a.localeCompare(b))
})

const canUseLocalFallback = computed(() => {
  const total = quizStore.pagination.total
  const loaded = quizStore.quizzes.length
  return total > 0 && total === loaded && loaded <= staticDatasetThreshold
})

const localFilteredQuizzes = computed(() => {
  const searchValue = search.value.toLowerCase()
  return quizStore.quizzes.filter((quiz) => {
    if (subjectFilter.value && quiz.subject !== subjectFilter.value) return false
    if (difficultyFilter.value && quiz.difficulty !== difficultyFilter.value) return false

    if (!searchValue) return true

    const haystack = [quiz.title, quiz.description, quiz.subject].join(' ').toLowerCase()
    return haystack.includes(searchValue)
  })
})

const totalPages = computed(() => {
  if (canUseLocalFallback.value) {
    return Math.max(1, Math.ceil(localFilteredQuizzes.value.length / perPage))
  }

  return Math.max(1, Math.ceil(quizStore.pagination.total / quizStore.pagination.perPage))
})

const paginatedQuizzes = computed(() => {
  if (canUseLocalFallback.value) {
    const start = (page.value - 1) * perPage
    return localFilteredQuizzes.value.slice(start, start + perPage)
  }

  return quizStore.quizzes
})

watch([search, subjectFilter, difficultyFilter], () => {
  if (page.value !== 1) {
    skipNextPageFetch.value = true
    page.value = 1
  }

  if (!canUseLocalFallback.value) {
    loadQuizzes()
  }
})

watch(totalPages, (value) => {
  if (page.value > value) page.value = value
})

watch(page, () => {
  if (skipNextPageFetch.value) {
    skipNextPageFetch.value = false
    return
  }

  if (!canUseLocalFallback.value) {
    loadQuizzes()
  }
})

async function loadQuizzes() {
  try {
    loading.value = true
    error.value = ''
    quizStore.pagination.page = page.value
    quizStore.pagination.perPage = perPage
    await quizStore.fetchQuizzes({
      search: search.value,
      subject: subjectFilter.value,
      difficulty: difficultyFilter.value,
    })
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to load quizzes'
  } finally {
    loading.value = false
  }
}

function goToQuiz(quizId) {
  router.push(`/quizzes/${quizId}`)
}

onMounted(loadQuizzes)
</script>
