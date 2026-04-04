<template>
  <StudentLayout>
    <section class="page-shell">
      <header>
        <h1 class="title-xl">Quiz History</h1>
        <p class="subtitle">Review your past quiz attempts.</p>
      </header>

      <p v-if="error" class="status-error">{{ error }}</p>

      <div v-if="loading" class="status-info">Loading history...</div>

      <div v-else-if="attempts.length === 0" class="status-info">No attempts yet.</div>

      <div v-else class="panel overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50 text-left text-slate-600">
            <tr>
              <th class="px-4 py-3">Quiz</th>
              <th class="px-4 py-3">Date</th>
              <th class="px-4 py-3">Score</th>
              <th class="px-4 py-3">Percentage</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="attempt in attempts"
              :key="attempt.id"
              class="cursor-pointer border-t border-slate-200 hover:bg-slate-50"
              @click="router.push(`/results/${attempt.id}`)"
            >
              <td class="px-4 py-3 font-medium text-slate-800">{{ attempt.quiz_title }}</td>
              <td class="px-4 py-3 text-slate-600">{{ formatDateTime(attempt.completed_at || attempt.started_at) }}</td>
              <td class="px-4 py-3 text-slate-700">{{ attempt.score }}/{{ attempt.total_points }}</td>
              <td class="px-4 py-3">
                <ScoreBadge :percentage="Number(attempt.percentage || 0)" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="totalPages > 1" class="mt-4 flex items-center justify-between gap-3">
        <p class="text-sm text-slate-600">
          Page {{ currentPage }} of {{ totalPages }} ({{ totalItems }} attempts)
        </p>
        <div class="flex gap-2">
          <button type="button" class="btn-secondary" :disabled="loading || currentPage <= 1" @click="goToPage(currentPage - 1)">
            Previous
          </button>
          <button type="button" class="btn-secondary" :disabled="loading || currentPage >= totalPages" @click="goToPage(currentPage + 1)">
            Next
          </button>
        </div>
      </div>
    </section>
  </StudentLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { get, readJsonResponse } from '../../utils/api'
import { formatDateTime } from '../../utils/format'
import ScoreBadge from '../atoms/ScoreBadge/ScoreBadge.vue'
import StudentLayout from '../templates/StudentLayout/StudentLayout.vue'

const router = useRouter()
const loading = ref(false)
const error = ref('')
const attempts = ref([])
const currentPage = ref(1)
const totalPages = ref(1)
const totalItems = ref(0)
const pageSize = 10

async function loadHistory(page = 1) {
  try {
    loading.value = true
    error.value = ''

    const response = await get(`/users/me/attempts?page=${page}&per_page=${pageSize}`)
    const payload = await readJsonResponse(response, 'Failed to load history')

    attempts.value = Array.isArray(payload?.data)
      ? payload.data
      : (Array.isArray(payload?.items) ? payload.items : [])

    const meta = payload?.meta || payload?.pagination || {}
    currentPage.value = Number(meta.page || 1)
    totalPages.value = Number(meta.total_pages || 1)
    totalItems.value = Number(meta.total || meta.total_items || 0)
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to load history'
  } finally {
    loading.value = false
  }
}

function goToPage(page) {
  if (page < 1 || page > totalPages.value || page === currentPage.value) {
    return
  }

  loadHistory(page)
}

onMounted(() => loadHistory(1))
</script>
