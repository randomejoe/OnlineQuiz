import { defineStore } from 'pinia'
import { get, readJsonResponse } from '../utils/api'

export const useQuizStore = defineStore('quiz', {
  state: () => ({
    quizzes: [],
    activeQuiz: null,
    filters: {
      search: '',
      subject: '',
      difficulty: '',
    },
    pagination: {
      page: 1,
      perPage: 10,
      total: 0,
    },
  }),
  actions: {
    async fetchQuizzes(filters = {}) {
      this.filters = { ...this.filters, ...filters }

      const params = new URLSearchParams()
      if (this.filters.search) params.append('search', this.filters.search)
      if (this.filters.subject) params.append('subject', this.filters.subject)
      if (this.filters.difficulty) params.append('difficulty', this.filters.difficulty)
      params.append('page', String(this.pagination.page))
      params.append('per_page', String(this.pagination.perPage))

      const endpoint = `/quizzes?${params.toString()}`
      const response = await get(endpoint)
      const payload = await readJsonResponse(response, 'Failed to fetch quizzes')

      if (Array.isArray(payload.data)) {
        this.quizzes = payload.data
        this.pagination.total = Number(payload.meta?.total || payload.data.length || 0)
      } else if (Array.isArray(payload.items)) {
        this.quizzes = payload.items
        this.pagination.total = Number(payload.total || payload.items.length || 0)
      } else {
        this.quizzes = Array.isArray(payload) ? payload : []
        this.pagination.total = this.quizzes.length
      }

      return this.quizzes
    },
    async fetchQuiz(id) {
      const response = await get(`/quizzes/${id}`)
      const payload = await readJsonResponse(response, 'Failed to fetch quiz')

      this.activeQuiz = payload
      return payload
    },
  },
})
