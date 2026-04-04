import { defineStore } from 'pinia'
import { del, get, post, readJsonResponse } from '../utils/api'

export const useAttemptStore = defineStore('attempt', {
  state: () => ({
    activeAttemptId: null,
    activeQuiz: null,
    answers: {},
    answerFeedbacks: {},
    result: null,
  }),
  getters: {
    hasActiveAttempt: (state) => Boolean(state.activeAttemptId),
    selectedOptionIdForQuestion: (state) => (questionId) => state.answers[questionId]?.option_id ?? null,
    answerTextForQuestion: (state) => (questionId) => state.answers[questionId]?.answer_text ?? '',
    answerFeedbackForQuestion: (state) => (questionId) => state.answerFeedbacks[questionId] ?? null,
  },
  actions: {
    async startAttempt(quizId) {
      const response = await post(`/quizzes/${quizId}/attempts`, {})
      const payload = await readJsonResponse(response, 'Failed to start attempt')

      this.activeAttemptId = payload.attempt_id
      this.activeQuiz = payload.quiz
      this.answers = {}
      this.answerFeedbacks = {}
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
    setAnswerFeedback(questionId, feedback) {
      this.answerFeedbacks[questionId] = feedback
    },
    clearAnswerFeedback(questionId) {
      delete this.answerFeedbacks[questionId]
    },
    async checkAnswer(answer) {
      if (!this.activeAttemptId) {
        throw new Error('No active attempt')
      }

      const response = await post(`/attempts/${this.activeAttemptId}/answers/check`, answer)
      const payload = await readJsonResponse(response, 'Failed to check answer')

      this.setAnswerFeedback(payload.question_id, payload)
      return payload
    },
    async submitAttempt() {
      if (!this.activeAttemptId) {
        throw new Error('No active attempt')
      }

      const answersPayload = Object.values(this.answers)
      const response = await post(`/attempts/${this.activeAttemptId}/submit`, { answers: answersPayload })
      const payload = await readJsonResponse(response, 'Failed to submit attempt')

      this.result = payload

      return payload
    },
    async fetchResult(attemptId) {
      const response = await get(`/attempts/${attemptId}`)
      const payload = await readJsonResponse(response, 'Failed to fetch result')

      this.result = payload
      return payload
    },
    async abandonAttempt() {
      if (!this.activeAttemptId) {
        return
      }

      const response = await del(`/attempts/${this.activeAttemptId}`)
      await readJsonResponse(response, 'Failed to abandon attempt')
    },
    clearActiveAttempt() {
      this.activeAttemptId = null
      this.activeQuiz = null
      this.answers = {}
      this.answerFeedbacks = {}
    },
  },
})
