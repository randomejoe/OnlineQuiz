import ArticleMeta from './ArticleMeta.vue';

export default {
  title: 'Molecules/ArticleMeta',
  component: ArticleMeta,
  tags: ['autodocs'],
};

export const Default = {
  args: {
    author: 'Jane Smith',
    published: '2025-01-12',
  },
};

export const Recent = {
  args: {
    author: 'Mark Thompson',
    published: new Date().toISOString().split('T')[0],
  },
};

export const LongAuthorName = {
  args: {
    author: 'Dr. Emily Chen',
    published: '2025-03-18',
  },
};
