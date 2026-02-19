import CategoryBadge from './CategoryBadge.vue';

export default {
  title: 'Molecules/CategoryBadge',
  component: CategoryBadge,
  tags: ['autodocs'],
};

export const Technology = {
  args: {
    category: 'Technology',
  },
};

export const Environment = {
  args: {
    category: 'Environment',
  },
};

export const Health = {
  args: {
    category: 'Health',
  },
};

export const AllCategories = {
  render: () => ({
    components: { CategoryBadge },
    template: `
      <div class="flex gap-2 flex-wrap">
        <CategoryBadge category="Technology" />
        <CategoryBadge category="Environment" />
        <CategoryBadge category="Health" />
        <CategoryBadge category="Finance" />
        <CategoryBadge category="History" />
        <CategoryBadge category="Gaming" />
        <CategoryBadge category="Automotive" />
        <CategoryBadge category="Travel" />
        <CategoryBadge category="Science" />
      </div>
    `,
  }),
};
