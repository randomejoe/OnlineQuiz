import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'
import { pinia } from './stores'
import { configureApiClient } from './utils/api'

const app = createApp(App)

app.use(pinia)
app.use(router)

configureApiClient({
  handleUnauthorized: async () => {
    const authStore = useAuthStore(pinia)
    authStore.clearSession()
  },
})

app.mount('#app')
