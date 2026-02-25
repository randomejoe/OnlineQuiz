import { defineStore } from 'pinia'
import { get, post } from '../utils/api'

export const useAttemptStore = defineStore('attempt', {
  state: () => ({
    activeAttemptId: null,
    activeQuiz: null,
    answers: {},
    result: null,
  }),
  getters: {
    hasActiveAttempt: (state) => Boolean(state.activeAttemptId),
    selectedOptionIdForQuestion: (state) => (questionId) => state.answers[questionId]?.option_id ?? null,
  },
  actions: {
    async startAttempt(quizId) {
      const response = await post(`/quizzes/${quizId}/attempts`, {})
      const payload = await response.json()

      if (!response.ok) {
        throw new Error(payload.error || 'Failed to start attempt')
      }

      this.activeAttemptId = payload.attempt_id
      this.activeQuiz = payload.quiz
      this.answers = {}
      this.result = null

      return payload
    },
    setAnswer(questionId, optionId, answerText) {
      this.answers[questionId] = {
        question_id: Number(questionId),
        option_id: Number.isFinite(Number(optionId)) && Number(optionId) > 0 ? Number(optionId) : null,
        answer_text: answerText,
      }
    },
    async submitAttempt() {
      if (!this.activeAttemptId) {
        throw new Error('No active attempt')
      }

      const answersPayload = Object.values(this.answers)
      const response = await post(`/attempts/${this.activeAttemptId}/submit`, { answers: answersPayload })
      const payload = await response.json()

      if (!response.ok) {
        throw new Error(payload.error || 'Failed to submit attempt')
      }

      this.result = payload

      return payload
    },
    async fetchResult(attemptId) {
      const response = await get(`/attempts/${attemptId}`)
      const payload = await response.json()

      if (!response.ok) {
        throw new Error(payload.error || 'Failed to fetch result')
      }

      this.result = payload
      return payload
    },
    clearActiveAttempt() {
      this.activeAttemptId = null
      this.activeQuiz = null
      this.answers = {}
    },
  },
})
