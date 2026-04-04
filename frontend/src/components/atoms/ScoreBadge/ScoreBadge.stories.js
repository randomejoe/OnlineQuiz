import ScoreBadge from './ScoreBadge.vue'

export default {
  title: 'Atoms/ScoreBadge',
  component: ScoreBadge,
  tags: ['autodocs'],
}

export const Passing = { args: { percentage: 84.5 } }
export const Failing = { args: { percentage: 42.2 } }
