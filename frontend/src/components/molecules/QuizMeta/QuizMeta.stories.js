import QuizMeta from './QuizMeta.vue'

export default {
  title: 'Molecules/QuizMeta',
  component: QuizMeta,
  tags: ['autodocs'],
}

export const Default = {
  args: {
    subject: 'Web Development',
    difficulty: 'medium',
    timeLimitMinutes: 15,
    questionCount: 12,
  },
}
