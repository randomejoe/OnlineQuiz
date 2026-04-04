import ProgressBar from './ProgressBar.vue'

export default {
  title: 'Atoms/ProgressBar',
  component: ProgressBar,
  tags: ['autodocs'],
}

export const Start = { args: { current: 1, total: 10 } }
export const Middle = { args: { current: 5, total: 10 } }
export const End = { args: { current: 10, total: 10 } }
