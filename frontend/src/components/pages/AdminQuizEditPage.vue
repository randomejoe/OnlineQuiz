<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { get, put, readJsonResponse } from '../../utils/api'
import AdminLayout from '../templates/AdminLayout/AdminLayout.vue'
import QuizForm from '../organisms/QuizForm/QuizForm.vue'

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const saving = ref(false)
const error = ref('')
const quiz = ref(null)

async function loadQuiz() {
  try {
    loading.value = true
    error.value = ''

    const response = await get(`/quizzes/${route.params.id}`)
    quiz.value = await readJsonResponse(response, 'Failed to fetch quiz')
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to load quiz'
  } finally {
    loading.value = false
  }
}

async function handleUpdate(payload) {
  try {
    saving.value = true
    error.value = ''

    const response = await put(`/quizzes/${route.params.id}`, payload)
    await readJsonResponse(response, 'Failed to update quiz')

    router.push('/admin/quizzes')
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to update quiz'
  } finally {
    saving.value = false
  }
}

function cancel() {
  router.push('/admin/quizzes')
}

onMounted(loadQuiz)
</script>

<template>
  <AdminLayout>
    <section class="space-y-5">
      <div>
        <h1 class="title-xl">Edit Quiz</h1>
        <p class="subtitle">Update quiz settings and question content.</p>
      </div>

      <p v-if="error" class="status-error">{{ error }}</p>

      <div v-if="loading" class="status-info">Loading quiz...</div>

      <QuizForm
        v-else-if="quiz"
        :initial-quiz="quiz"
        submit-label="Save Changes"
        :loading="saving"
        @submit="handleUpdate"
        @cancel="cancel"
      />
    </section>
  </AdminLayout>
</template>
