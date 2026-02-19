import ArticleArchive from './ArticleArchive.vue';

const sampleArticles = [
  {
    id: 1,
    title: "How Climate Change Is Affecting Global Weather Patterns",
    author: "Jane Smith",
    category: "Environment",
    published: "2025-01-12",
    content: "Climate change has become one of the most significant environmental challenges of the 21st century. Rising global temperatures are causing major shifts in weather systems that scientists have been monitoring for decades. These changes are not limited to a specific region—they are happening worldwide and growing more noticeable each year.",
  },
  {
    id: 2,
    title: "The Rise of AI Assistants in Everyday Life",
    author: "Mark Thompson",
    category: "Technology",
    published: "2025-02-03",
    content: "Artificial intelligence assistants have transformed from simple voice-activated tools to powerful digital companions. Today, AI is integrated into smartphones, computers, cars, smart home devices, and even workplaces. These systems help users manage tasks, answer complex questions, and automate repetitive activities.",
  },
  {
    id: 3,
    title: "Healthy Eating: 10 Tips for a Balanced Diet",
    author: "Dr. Emily Chen",
    category: "Health",
    published: "2025-03-18",
    content: "Maintaining a balanced diet is essential for long-term wellness. Healthy eating doesn't require extreme restrictions or difficult routines—it's about making mindful choices that support the body's natural processes. A good diet should provide energy, strengthen the immune system, and help maintain a stable mood.",
  },
  {
    id: 4,
    title: "A Beginner's Guide to Investing in 2025",
    author: "Robert Hale",
    category: "Finance",
    published: "2025-04-09",
    content: "Investing in 2025 presents both exciting opportunities and new challenges for beginners. With digital platforms becoming more user-friendly, more people are entering the investment world than ever before. Before diving in, it's crucial to understand fundamental concepts like risk tolerance, asset allocation, and market volatility.",
  },
  {
    id: 5,
    title: "Exploring Ancient Civilizations: What We've Recently Learned",
    author: "Alicia Gomez",
    category: "History",
    published: "2025-05-22",
    content: "Recent archaeological discoveries have reshaped our understanding of ancient civilizations. Advanced technology such as LiDAR scanning and DNA analysis has allowed researchers to uncover hidden cities, trace migration patterns, and gain new insight into ancient cultures.",
  },
  {
    id: 6,
    title: "Top 10 Video Games of the Year",
    author: "Kevin Brooks",
    category: "Gaming",
    published: "2025-06-14",
    content: "The gaming world continues to evolve rapidly, delivering breathtaking experiences through improved graphics, storytelling, and gameplay mechanics. This year's top titles span a wide range of genres, ensuring something for every type of player.",
  },
];

export default {
  title: 'Templates/ArticleArchive',
  component: ArticleArchive,
  tags: ['autodocs'],
  argTypes: {
    articles: {
      control: 'object',
    },
  },
};

export const Default = {
  args: {
    articles: sampleArticles,
  },
};

export const Empty = {
  args: {
    articles: [],
  },
};

export const SingleArticle = {
  args: {
    articles: [sampleArticles[0]],
  },
};

export const ManyArticles = {
  args: {
    articles: [
      ...sampleArticles,
      {
        id: 7,
        title: "The Future of Electric Cars",
        author: "Samantha Lee",
        category: "Automotive",
        published: "2025-07-07",
        content: "Electric vehicles (EVs) are rapidly changing the automotive industry. With major improvements in battery efficiency, charging speed, and range, more consumers are making the switch from traditional gasoline-powered cars.",
      },
      {
        id: 8,
        title: "Travel Guide: Best Budget Destinations for 2025",
        author: "David Nguyen",
        category: "Travel",
        published: "2025-08-29",
        content: "Traveling doesn't have to be expensive. In 2025, several destinations offer incredible experiences at budget-friendly prices—from scenic coastlines to historic cities and vibrant cultural hubs.",
      },
      {
        id: 9,
        title: "Understanding Quantum Computing",
        author: "Dr. Lena Patel",
        category: "Science",
        published: "2025-09-11",
        content: "Quantum computing represents a groundbreaking shift in how we process information. Unlike classical computers, which use bits that represent 0 or 1, quantum computers use qubits—units that can exist in multiple states simultaneously.",
      },
    ],
  },
};
