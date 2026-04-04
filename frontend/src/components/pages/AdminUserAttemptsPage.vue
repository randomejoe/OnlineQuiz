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
const user = ref(null)
const attempts = ref([])

const page = ref(1)
const perPage = 10
const total = ref(0)
const totalPages = ref(1)

async function loadAttempts() {
  try {
    loading.value = true
    error.value = ''

    const userId = Number(route.params.id)
    const response = await get(`/admin/users/${userId}/attempts?page=${page.value}&per_page=${perPage}`)
    const payload = await readJsonResponse(response, 'Failed to load user attempt history')

    user.value = payload.user || null
    attempts.value = Array.isArray(payload.data)
      ? payload.data
      : (Array.isArray(payload.attempts) ? payload.attempts : [])
    total.value = Number(payload.meta?.total || attempts.value.length)
    totalPages.value = Math.max(1, Math.ceil(total.value / perPage))
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to load user attempt history'
  } finally {
    loading.value = false
  }
}

watch(page, loadAttempts)
onMounted(loadAttempts)
</script>

<template>
  <AdminLayout>
    <section class="space-y-5">
      <div>
        <h1 class="title-xl">User Attempt History</h1>
        <p class="subtitle">
          {{
            user
              ? `User: ${user.name} (${user.email}) · ID ${user.id}`
              : 'Attempts for this user'
          }}
        </p>
      </div>

      <p v-if="error" class="status-error">{{ error }}</p>

      <div v-if="loading" class="status-info">Loading user attempts...</div>

      <div v-else class="panel overflow-x-auto">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-slate-50 text-slate-600">
            <tr>
              <th class="px-4 py-3 font-medium">Attempt ID</th>
              <th class="px-4 py-3 font-medium">Quiz</th>
              <th class="px-4 py-3 font-medium">Subject</th>
              <th class="px-4 py-3 font-medium">Score</th>
              <th class="px-4 py-3 font-medium">Percentage</th>
              <th class="px-4 py-3 font-medium">Submitted</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="attempts.length === 0">
              <td colspan="6" class="px-4 py-5 text-center text-slate-500">No attempts found for this user.</td>
            </tr>
            <tr v-for="attempt in attempts" :key="attempt.attempt_id" class="border-t border-slate-200">
              <td class="px-4 py-3 text-slate-900">
                <button type="button" class="font-medium text-blue-700 hover:underline" @click="router.push(`/results/${attempt.attempt_id}`)">
                  #{{ attempt.attempt_id }}
                </button>
              </td>
              <td class="px-4 py-3 text-slate-900">{{ attempt.quiz_title || '-' }}</td>
              <td class="px-4 py-3 text-slate-600">{{ attempt.subject || '-' }}</td>
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
