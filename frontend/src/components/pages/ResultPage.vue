<template>
  <component :is="layoutComponent">
    <section class="page-shell max-w-5xl">
      <p v-if="error" class="status-error">{{ error }}</p>

      <div v-if="loading" class="status-info">Loading result...</div>

      <template v-else-if="result">
        <header class="space-y-2">
          <h1 class="title-xl">{{ result.quiz_title || 'Quiz Result' }}</h1>
          <ScoreBadge :percentage="result.percentage" />
        </header>

        <ResultSummary
          :percentage="result.percentage"
          :correct-count="correctCount"
          :total-questions="result.answers?.length || 0"
          :score="result.score"
          :total-points="result.total_points"
          :time-taken-seconds="result.time_taken_seconds || 0"
        />

        <section class="space-y-3">
          <h2 class="text-xl font-semibold text-slate-900">Question Review</h2>
          <AttemptReview :answers="result.answers || []" />
        </section>

        <div v-if="!authStore.isAdmin" class="flex flex-wrap gap-2 pb-4">
          <button type="button" class="btn-primary" @click="router.push('/quizzes')">Try Another Quiz</button>
          <button type="button" class="btn-secondary" @click="router.push('/history')">View History</button>
        </div>
      </template>
    </section>
  </component>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useAttemptStore } from '../../stores/attempt'
import ScoreBadge from '../atoms/ScoreBadge/ScoreBadge.vue'
import ResultSummary from '../molecules/ResultSummary/ResultSummary.vue'
import AttemptReview from '../organisms/AttemptReview/AttemptReview.vue'
import AdminLayout from '../templates/AdminLayout/AdminLayout.vue'
import StudentLayout from '../templates/StudentLayout/StudentLayout.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const attemptStore = useAttemptStore()

const loading = ref(false)
const error = ref('')

const result = computed(() => attemptStore.result)
const layoutComponent = computed(() => (authStore.isAdmin ? AdminLayout : StudentLayout))
const correctCount = computed(() => (result.value?.answers || []).filter((answer) => answer.is_correct).length)

async function loadResult() {
  try {
    loading.value = true
    error.value = ''
    await attemptStore.fetchResult(route.params.attemptId)
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to load result'
  } finally {
    loading.value = false
  }
}

onMounted(loadResult)
</script>
