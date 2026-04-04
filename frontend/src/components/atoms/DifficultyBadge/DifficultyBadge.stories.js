import DifficultyBadge from './DifficultyBadge.vue'

export default {
  title: 'Atoms/DifficultyBadge',
  component: DifficultyBadge,
  tags: ['autodocs'],
}

export const Easy = { args: { difficulty: 'easy' } }
export const Medium = { args: { difficulty: 'medium' } }
export const Hard = { args: { difficulty: 'hard' } }
