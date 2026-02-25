<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  name: '',
  email: '',
  password: '',
})

const loading = ref(false)
const error = ref('')

async function submit() {
  error.value = ''
  loading.value = true

  try {
    const payload = await authStore.register(form)
    if (payload.user?.role === 'admin') {
      await router.push('/admin')
    } else {
      await router.push('/quizzes')
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unable to register'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="auth-page">
    <h1>Register</h1>
    <form @submit.prevent="submit">
      <label>
        Name
        <input v-model="form.name" type="text" required autocomplete="name" />
      </label>

      <label>
        Email
        <input v-model="form.email" type="email" required autocomplete="email" />
      </label>

      <label>        Password
        <input v-model="form.password" type="password" required minlength="8" />
      </label>


      <p v-if="error" class="error">{{ error }}</p>

      <button type="submit" :disabled="loading">
        {{ loading ? 'Creating account...' : 'Register' }}
      </button>
    </form>
  </section>
</template>

<style scoped>
.auth-page {
  max-width: 420px;
  margin: 2rem auto;
}

form {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

input,
button,
select {
  padding: 0.6rem;
  border-radius: 0.4rem;
  border: 1px solid #cbd5e1;
}

button {
  background: #2563eb;
  color: #fff;
  border: 0;
  cursor: pointer;
}

.error {
  color: #dc2626;
}
</style>
