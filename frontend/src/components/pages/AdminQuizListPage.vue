<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { del, get, post, readJsonResponse } from '../../utils/api'
import { extractQuizzesFromJson, sanitizeImportedQuiz } from '../../utils/quizImport'
import AdminLayout from '../templates/AdminLayout/AdminLayout.vue'
import ConfirmDialog from '../molecules/ConfirmDialog/ConfirmDialog.vue'

const router = useRouter()

const loading = ref(false)
const importing = ref(false)
const error = ref('')
const success = ref('')
const quizzes = ref([])
const showDeleteDialog = ref(false)
const deleting = ref(false)
const quizToDelete = ref(null)
const deleteMessage = ref('Delete this quiz? This action cannot be undone.')
const importInput = ref(null)

async function loadQuizzes() {
  try {
    loading.value = true
    error.value = ''

    const response = await get('/quizzes?page=1&per_page=500')
    const payload = await readJsonResponse(response, 'Failed to fetch quizzes')

    quizzes.value = Array.isArray(payload.data) ? payload.data : []
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to load quizzes'
  } finally {
    loading.value = false
  }
}

function openDeleteDialog(quiz) {
  quizToDelete.value = quiz
  deleteMessage.value = `Delete "${quiz.title}"? This action cannot be undone.`
  showDeleteDialog.value = true
}

async function confirmDelete() {
  if (!quizToDelete.value) return

  try {
    deleting.value = true
    error.value = ''
    success.value = ''

    const response = await del(`/quizzes/${quizToDelete.value.id}`)
    await readJsonResponse(response, 'Failed to delete quiz')

    quizzes.value = quizzes.value.filter((quiz) => quiz.id !== quizToDelete.value.id)
    showDeleteDialog.value = false
    quizToDelete.value = null
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to delete quiz'
  } finally {
    deleting.value = false
  }
}

function goToCreate() {
  router.push('/admin/quizzes/create')
}

function goToEdit(quizId) {
  router.push(`/admin/quizzes/${quizId}/edit`)
}

function goToResults(quizId) {
  router.push(`/admin/quizzes/${quizId}/results`)
}

function openImportDialog() {
  if (importing.value) return
  importInput.value?.click()
}

async function handleImportFile(event) {
  const [file] = event.target.files || []
  event.target.value = ''

  if (!file) {
    return
  }

  try {
    importing.value = true
    error.value = ''
    success.value = ''

    const rawText = await file.text()
    let parsed
    try {
      parsed = JSON.parse(rawText)
    } catch {
      throw new Error('Invalid JSON file. Please upload a valid quiz export.')
    }

    const importedQuizzes = extractQuizzesFromJson(parsed).map(sanitizeImportedQuiz)
    if (importedQuizzes.length === 0) {
      throw new Error('No quizzes found in the JSON file.')
    }

    for (const quiz of importedQuizzes) {
      const response = await post('/quizzes', quiz)
      await readJsonResponse(response, `Failed to import "${quiz.title || 'quiz'}"`)
    }

    success.value = importedQuizzes.length === 1
      ? `Imported "${importedQuizzes[0].title}" successfully.`
      : `Imported ${importedQuizzes.length} quizzes successfully.`

    await loadQuizzes()
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to import quiz JSON'
  } finally {
    importing.value = false
  }
}

onMounted(loadQuizzes)
</script>

<template>
  <AdminLayout>
    <section class="space-y-5">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="title-xl">Quizzes</h1>
          <p class="subtitle">Manage quiz content, import quiz JSON, and review question sets.</p>
        </div>

        <div class="flex flex-wrap gap-2">
          <input ref="importInput" type="file" accept=".json,application/json" class="hidden" @change="handleImportFile" />
          <button type="button" class="btn-secondary" :disabled="importing" @click="openImportDialog">
            {{ importing ? 'Importing...' : 'Import JSON' }}
          </button>
          <button type="button" class="btn-primary" @click="goToCreate">Create Quiz</button>
        </div>
      </div>

      <p v-if="error" class="status-error">{{ error }}</p>
      <p v-if="success" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ success }}</p>

      <div v-if="loading" class="status-info">Loading quizzes...</div>

      <div v-else class="panel overflow-x-auto">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-slate-50 text-slate-600">
            <tr>
              <th class="px-4 py-3 font-medium">Title</th>
              <th class="px-4 py-3 font-medium">Subject</th>
              <th class="px-4 py-3 font-medium">Difficulty</th>
              <th class="px-4 py-3 font-medium">Questions</th>
              <th class="px-4 py-3 font-medium">Attempts</th>
              <th class="px-4 py-3 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="quizzes.length === 0">
              <td colspan="6" class="px-4 py-5 text-center text-slate-500">No quizzes found.</td>
            </tr>
            <tr v-for="quiz in quizzes" :key="quiz.id" class="border-t border-slate-200">
              <td class="px-4 py-3 text-slate-900">{{ quiz.title }}</td>
              <td class="px-4 py-3 text-slate-600">{{ quiz.subject || '-' }}</td>
              <td class="px-4 py-3 text-slate-600 capitalize">{{ quiz.difficulty }}</td>
              <td class="px-4 py-3 text-slate-600">{{ quiz.question_count ?? 0 }}</td>
              <td class="px-4 py-3 text-slate-600">{{ quiz.attempt_count ?? 0 }}</td>
              <td class="px-4 py-3">
                <div class="flex gap-2">
                  <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="goToEdit(quiz.id)">Edit</button>
                  <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="goToResults(quiz.id)">Attempts</button>
                  <button type="button" class="btn border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50" @click="openDeleteDialog(quiz)">
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <ConfirmDialog
      v-model="showDeleteDialog"
      title="Delete quiz"
      :message="deleteMessage"
      confirm-label="Delete"
      :loading="deleting"
      @confirm="confirmDelete"
    />
  </AdminLayout>
</template>
