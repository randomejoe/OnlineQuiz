<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { post, readJsonResponse } from '../../utils/api'
import AdminLayout from '../templates/AdminLayout/AdminLayout.vue'
import QuizForm from '../organisms/QuizForm/QuizForm.vue'

const router = useRouter()

const saving = ref(false)
const error = ref('')

async function handleCreate(payload) {
  try {
    saving.value = true
    error.value = ''

    const response = await post('/quizzes', payload)
    await readJsonResponse(response, 'Failed to create quiz')

    router.push('/admin/quizzes')
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to create quiz'
  } finally {
    saving.value = false
  }
}

function cancel() {
  router.push('/admin/quizzes')
}
</script>

<template>
  <AdminLayout>
    <section class="space-y-5">
      <div>
        <h1 class="title-xl">Create Quiz</h1>
        <p class="subtitle">Build a quiz and add questions before publishing.</p>
      </div>

      <p v-if="error" class="status-error">{{ error }}</p>

      <QuizForm submit-label="Create Quiz" :loading="saving" @submit="handleCreate" @cancel="cancel" />
    </section>
  </AdminLayout>
</template>
