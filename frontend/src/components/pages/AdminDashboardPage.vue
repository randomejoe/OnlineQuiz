<script setup>
import { onMounted, ref } from 'vue'
import { get, readJsonResponse } from '../../utils/api'
import { formatDateOnly, formatPercentage } from '../../utils/format'
import AdminLayout from '../templates/AdminLayout/AdminLayout.vue'
import AdminStatsPanel from '../organisms/AdminStatsPanel/AdminStatsPanel.vue'

const loading = ref(false)
const error = ref('')
const stats = ref({})
const recentUsers = ref([])
const popularQuizzes = ref([])

async function loadDashboard() {
  try {
    loading.value = true
    error.value = ''

    const [statsResponse, usersResponse] = await Promise.all([
      get('/admin/stats'),
      get('/admin/users?page=1&per_page=5'),
    ])

    const statsPayload = await readJsonResponse(statsResponse, 'Failed to fetch admin stats')
    const usersPayload = await readJsonResponse(usersResponse, 'Failed to fetch recent users')

    stats.value = statsPayload
    recentUsers.value = Array.isArray(usersPayload.data) ? usersPayload.data : []
    popularQuizzes.value = Array.isArray(statsPayload.popular_quizzes) ? statsPayload.popular_quizzes : []
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to load dashboard'
  } finally {
    loading.value = false
  }
}

onMounted(loadDashboard)
</script>

<template>
  <AdminLayout>
    <section class="space-y-6">
      <div>
        <h1 class="title-xl">Dashboard</h1>
        <p class="subtitle">Overview of platform activity and performance.</p>
      </div>

      <p v-if="error" class="status-error">{{ error }}</p>

      <div v-if="loading" class="status-info">Loading dashboard data...</div>

      <template v-else>
        <AdminStatsPanel :stats="stats" />

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
          <article class="panel overflow-hidden">
            <header class="border-b border-slate-200 px-4 py-3">
              <h2 class="text-base font-semibold text-slate-900">Recent Users</h2>
            </header>
            <div class="overflow-x-auto">
              <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                  <tr>
                    <th class="px-4 py-2 font-medium">Name</th>
                    <th class="px-4 py-2 font-medium">Email</th>
                    <th class="px-4 py-2 font-medium">Joined</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="recentUsers.length === 0">
                    <td colspan="3" class="px-4 py-4 text-center text-slate-500">No users found.</td>
                  </tr>
                  <tr v-for="user in recentUsers" :key="user.id" class="border-t border-slate-200">
                    <td class="px-4 py-2 text-slate-800">{{ user.name }}</td>
                    <td class="px-4 py-2 text-slate-600">{{ user.email }}</td>
                    <td class="px-4 py-2 text-slate-600">{{ formatDateOnly(user.created_at) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </article>

          <article class="panel overflow-hidden">
            <header class="border-b border-slate-200 px-4 py-3">
              <h2 class="text-base font-semibold text-slate-900">Popular Quizzes</h2>
            </header>
            <div class="overflow-x-auto">
              <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                  <tr>
                    <th class="px-4 py-2 font-medium">Title</th>
                    <th class="px-4 py-2 font-medium">Attempts</th>
                    <th class="px-4 py-2 font-medium">Avg Score</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="popularQuizzes.length === 0">
                    <td colspan="3" class="px-4 py-4 text-center text-slate-500">No attempts yet.</td>
                  </tr>
                  <tr v-for="quiz in popularQuizzes" :key="quiz.id" class="border-t border-slate-200">
                    <td class="px-4 py-2 text-slate-800">{{ quiz.title }}</td>
                    <td class="px-4 py-2 text-slate-600">{{ quiz.attempt_count }}</td>
                    <td class="px-4 py-2 text-slate-600">{{ formatPercentage(quiz.avg_percentage) }}%</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </article>
        </div>
      </template>
    </section>
  </AdminLayout>
</template>
