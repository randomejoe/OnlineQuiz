<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { get, readJsonResponse } from '../../utils/api'
import { formatDateTime, formatPercentage } from '../../utils/format'
import AdminLayout from '../templates/AdminLayout/AdminLayout.vue'

const route = useRoute()
const router = useRouter()
const loading = ref(false)
const error = ref('')
const quiz = ref(null)
const results = ref([])

const page = ref(1)
const perPage = 10
const total = ref(0)
const totalPages = ref(1)

async function loadResults() {
  try {
    loading.value = true
    error.value = ''

    const quizId = Number(route.params.id)
    const response = await get(`/admin/quizzes/${quizId}/results?page=${page.value}&per_page=${perPage}`)
    const payload = await readJsonResponse(response, 'Failed to load quiz attempts')

    quiz.value = payload.quiz || null
    results.value = Array.isArray(payload.data)
      ? payload.data
      : (Array.isArray(payload.results) ? payload.results : [])
    total.value = Number(payload.meta?.total || results.value.length)
    totalPages.value = Math.max(1, Math.ceil(total.value / perPage))
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to load quiz attempts'
  } finally {
    loading.value = false
  }
}

watch(page, loadResults)
onMounted(loadResults)
</script>

<template>
  <AdminLayout>
    <section class="space-y-5">
      <div>
        <h1 class="title-xl">Quiz Attempts</h1>
        <p class="subtitle">
          {{ quiz?.title ? `Quiz: ${quiz.title}` : 'Attempts for this quiz' }}
        </p>
      </div>

      <p v-if="error" class="status-error">{{ error }}</p>

      <div v-if="loading" class="status-info">Loading attempts...</div>

      <div v-else class="panel overflow-x-auto">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-slate-50 text-slate-600">
            <tr>
              <th class="px-4 py-3 font-medium">Attempt ID</th>
              <th class="px-4 py-3 font-medium">Student</th>
              <th class="px-4 py-3 font-medium">Email</th>
              <th class="px-4 py-3 font-medium">User ID</th>
              <th class="px-4 py-3 font-medium">Score</th>
              <th class="px-4 py-3 font-medium">Percentage</th>
              <th class="px-4 py-3 font-medium">Submitted</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="results.length === 0">
              <td colspan="7" class="px-4 py-5 text-center text-slate-500">No attempts found for this quiz.</td>
            </tr>
            <tr v-for="attempt in results" :key="attempt.attempt_id" class="border-t border-slate-200">
              <td class="px-4 py-3 text-slate-900">
                <button type="button" class="font-medium text-blue-700 hover:underline" @click="router.push(`/results/${attempt.attempt_id}`)">
                  #{{ attempt.attempt_id }}
                </button>
              </td>
              <td class="px-4 py-3 text-slate-900">{{ attempt.student_name || '-' }}</td>
              <td class="px-4 py-3 text-slate-600">{{ attempt.student_email || '-' }}</td>
              <td class="px-4 py-3 text-slate-600">{{ attempt.user_id }}</td>
              <td class="px-4 py-3 text-slate-700">{{ attempt.score }}/{{ attempt.total_points }}</td>
              <td class="px-4 py-3 text-slate-700">{{ formatPercentage(attempt.percentage) }}%</td>
              <td class="px-4 py-3 text-slate-600">{{ formatDateTime(attempt.completed_at || attempt.started_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-end gap-2">
        <button type="button" class="btn-secondary" :disabled="loading || page <= 1" @click="page -= 1">Previous</button>
        <span class="text-sm text-slate-600">Page {{ page }} / {{ totalPages }}</span>
        <button type="button" class="btn-secondary" :disabled="loading || page >= totalPages" @click="page += 1">Next</button>
      </div>
    </section>
  </AdminLayout>
</template>
