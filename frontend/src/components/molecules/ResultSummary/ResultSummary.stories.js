import ResultSummary from './ResultSummary.vue'

export default {
  title: 'Molecules/ResultSummary',
  component: ResultSummary,
  tags: ['autodocs'],
}

export const Passing = {
  args: {
    percentage: 86,
    correctCount: 9,
    totalQuestions: 10,
    score: 18,
    totalPoints: 20,
    timeTakenSeconds: 520,
  },
}

export const Failing = {
  args: {
    percentage: 42,
    correctCount: 4,
    totalQuestions: 10,
    score: 8,
    totalPoints: 20,
    timeTakenSeconds: 640,
  },
}
