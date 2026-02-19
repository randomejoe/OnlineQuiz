import DateDisplay from './DateDisplay.vue';

export default {
  title: 'Atoms/DateDisplay',
  component: DateDisplay,
  tags: ['autodocs'],
  argTypes: {
    format: {
      control: { type: 'select' },
      options: ['short', 'long', 'relative'],
    },
  },
};

export const Long = {
  args: {
    date: '2025-01-12',
    format: 'long',
  },
};

export const Short = {
  args: {
    date: '2025-01-12',
    format: 'short',
  },
};

export const Relative = {
  args: {
    date: '2025-01-12',
    format: 'relative',
  },
};

export const Recent = {
  args: {
    date: new Date().toISOString().split('T')[0],
    format: 'relative',
  },
};
