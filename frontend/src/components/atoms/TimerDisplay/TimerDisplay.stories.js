import TimerDisplay from './TimerDisplay.vue'

export default {
  title: 'Atoms/TimerDisplay',
  component: TimerDisplay,
  tags: ['autodocs'],
}

export const Default = {
  args: {
    secondsRemaining: 305,
  },
}

export const Critical = {
  args: {
    secondsRemaining: 45,
  },
}
