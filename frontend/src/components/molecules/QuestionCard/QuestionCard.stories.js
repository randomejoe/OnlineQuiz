import QuestionCard from './QuestionCard.vue'

export default {
  title: 'Molecules/QuestionCard',
  component: QuestionCard,
  tags: ['autodocs'],
}

const sampleQuestion = {
  id: 1,
  question_text: 'Which HTML tag is used for the largest heading?',
  options: [
    { id: 1, option_text: '<h1>' },
    { id: 2, option_text: '<h6>' },
    { id: 3, option_text: '<header>' },
  ],
}

export const Default = {
  args: {
    question: sampleQuestion,
    selectedOptionId: null,
  },
}

export const Selected = {
  args: {
    question: sampleQuestion,
    selectedOptionId: 1,
  },
}
