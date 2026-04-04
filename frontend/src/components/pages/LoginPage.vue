<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: '',
})

const loading = ref(false)
const error = ref('')

async function submit() {
  error.value = ''
  loading.value = true

  try {
    const payload = await authStore.login(form)
    if (payload.user?.role === 'admin') {
      await router.push('/admin')
    } else {
      await router.push('/quizzes')
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to login'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="page-shell pt-4 sm:pt-8">
    <article class="panel mx-auto max-w-md space-y-5 px-5 py-6 sm:px-7">
      <header>
        <h1 class="title-xl">Welcome back</h1>
        <p class="subtitle mt-1">Login to continue your quiz journey.</p>
      </header>

      <form class="space-y-3" @submit.prevent="submit">
        <label class="block text-sm font-medium text-slate-700">
          Email
          <input v-model="form.email" type="email" required autocomplete="email" class="field mt-1" />
        </label>

        <label class="block text-sm font-medium text-slate-700">
          Password
          <input
            v-model="form.password"
            type="password"
            required
            autocomplete="current-password"
            class="field mt-1"
          />
        </label>

        <p v-if="error" class="status-error">{{ error }}</p>

        <button type="submit" class="btn-primary w-full" :disabled="loading">
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
      </form>

      <p class="text-center text-sm text-slate-600">
        New here?
        <RouterLink to="/register" class="font-semibold text-blue-700 hover:text-blue-800">
          Create an account
        </RouterLink>
      </p>
    </article>
  </section>
</template>
