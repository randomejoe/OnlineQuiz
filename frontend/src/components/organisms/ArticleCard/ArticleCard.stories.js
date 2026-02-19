import ArticleCard from './ArticleCard.vue';

const sampleArticle = {
  id: 1,
  title: "How Climate Change Is Affecting Global Weather Patterns",
  author: "Jane Smith",
  category: "Environment",
  published: "2025-01-12",
  content: "Climate change has become one of the most significant environmental challenges of the 21st century. Rising global temperatures are causing major shifts in weather systems that scientists have been monitoring for decades. These changes are not limited to a specific region—they are happening worldwide and growing more noticeable each year.",
};

export default {
  title: 'Organisms/ArticleCard',
  component: ArticleCard,
  tags: ['autodocs'],
  argTypes: {
    article: {
      control: 'object',
    },
  },
};

export const Default = {
  args: {
    article: sampleArticle,
  },
};

export const Technology = {
  args: {
    article: {
      id: 2,
      title: "The Rise of AI Assistants in Everyday Life",
      author: "Mark Thompson",
      category: "Technology",
      published: "2025-02-03",
      content: "Artificial intelligence assistants have transformed from simple voice-activated tools to powerful digital companions. Today, AI is integrated into smartphones, computers, cars, smart home devices, and even workplaces.",
    },
  },
};

export const LongContent = {
  args: {
    article: {
      id: 3,
      title: "Healthy Eating: 10 Tips for a Balanced Diet",
      author: "Dr. Emily Chen",
      category: "Health",
      published: "2025-03-18",
      content: "Maintaining a balanced diet is essential for long-term wellness. Healthy eating doesn't require extreme restrictions or difficult routines—it's about making mindful choices that support the body's natural processes. A good diet should provide energy, strengthen the immune system, and help maintain a stable mood. One of the most important steps is incorporating a variety of whole foods. Fruits, vegetables, legumes, and lean proteins offer vitamins and minerals necessary for proper functioning.",
    },
  },
};

export const Grid = {
  render: () => ({
    components: { ArticleCard },
    setup() {
      const articles = [
        {
          id: 1,
          title: "How Climate Change Is Affecting Global Weather Patterns",
          author: "Jane Smith",
          category: "Environment",
          published: "2025-01-12",
          content: "Climate change has become one of the most significant environmental challenges of the 21st century.",
        },
        {
          id: 2,
          title: "The Rise of AI Assistants in Everyday Life",
          author: "Mark Thompson",
          category: "Technology",
          published: "2025-02-03",
          content: "Artificial intelligence assistants have transformed from simple voice-activated tools to powerful digital companions.",
        },
        {
          id: 3,
          title: "Healthy Eating: 10 Tips for a Balanced Diet",
          author: "Dr. Emily Chen",
          category: "Health",
          published: "2025-03-18",
          content: "Maintaining a balanced diet is essential for long-term wellness.",
        },
      ];
      return { articles };
    },
    template: `
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <ArticleCard v-for="article in articles" :key="article.id" :article="article" />
      </div>
    `,
  }),
};
