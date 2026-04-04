import { defineStore } from 'pinia'
import { getApiUrl, post, readJsonResponse } from '../utils/api'

const USER_KEY = 'auth_user'
let initializationPromise = null

function getStoredUser() {
  const raw = localStorage.getItem(USER_KEY)
  if (!raw) {
    return null
  }

  try {
    return JSON.parse(raw)
  } catch {
    localStorage.removeItem(USER_KEY)
    return null
  }
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: getStoredUser(),
    initialized: false,
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.user),
    isAdmin: (state) => state.user?.role === 'admin',
  },
  actions: {
    async initialize(force = false) {
      if (this.initialized && !force) {
        return this.user
      }

      if (initializationPromise && !force) {
        return initializationPromise
      }

      initializationPromise = (async () => {
        try {
          const response = await fetch(getApiUrl('/auth/me'), {
            method: 'GET',
            credentials: 'include',
            headers: { Accept: 'application/json' },
          })
          const payload = await readJsonResponse(response, 'Failed to load session')

          if (payload.user) {
            this.user = payload.user
            localStorage.setItem(USER_KEY, JSON.stringify(payload.user))
          } else {
            this.clearSession(false)
          }
        } catch {
          this.clearSession(false)
        } finally {
          this.initialized = true
          initializationPromise = null
        }

        return this.user
      })()

      return initializationPromise
    },
    async login(credentials) {
      const response = await post('/auth/login', credentials)
      const payload = await readJsonResponse(response, 'Login failed')

      this.user = payload.user
      this.initialized = true
      localStorage.setItem(USER_KEY, JSON.stringify(payload.user))

      return payload
    },
    async register(data) {
      const response = await post('/auth/register', data)
      const payload = await readJsonResponse(response, 'Registration failed')

      this.user = payload.user
      this.initialized = true
      localStorage.setItem(USER_KEY, JSON.stringify(payload.user))

      return payload
    },
    async logout(options = {}) {
      const { redirect = true } = options

      try {
        await fetch(getApiUrl('/auth/logout'), {
          method: 'POST',
          credentials: 'include',
        })
      } catch {
        // Local session cleanup still needs to happen when logout API is unavailable.
      }

      this.clearSession(redirect)
    },
    clearSession(redirect = true) {
      this.user = null
      this.initialized = true
      localStorage.removeItem(USER_KEY)

      if (redirect && window.location.pathname !== '/login') {
        window.location.assign('/login')
      }
    },
  },
})
