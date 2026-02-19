import ArticleDetail from './ArticleDetail.vue';

const sampleArticle = {
  id: 1,
  title: "How Climate Change Is Affecting Global Weather Patterns",
  author: "Jane Smith",
  category: "Environment",
  published: "2025-01-12",
  content: "Climate change has become one of the most significant environmental challenges of the 21st century. Rising global temperatures are causing major shifts in weather systems that scientists have been monitoring for decades. These changes are not limited to a specific region—they are happening worldwide and growing more noticeable each year.\n\nOne of the most visible impacts is the increase in extreme weather events. Hurricanes are becoming more intense, droughts are lasting longer, and heatwaves are occurring more frequently. Warmer oceans provide additional energy for storms, making them stronger and more destructive. Meanwhile, altered jet stream patterns are causing unusual temperature swings in areas that historically experienced stable weather.\n\nScientists warn that without significant action to reduce greenhouse gas emissions, these trends will continue to accelerate. Communities around the world must adapt by improving infrastructure, protecting vulnerable ecosystems, and preparing emergency response systems for more frequent severe weather.",
};

export default {
  title: 'Organisms/ArticleDetail',
  component: ArticleDetail,
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
      content: "Artificial intelligence assistants have transformed from simple voice-activated tools to powerful digital companions. Today, AI is integrated into smartphones, computers, cars, smart home devices, and even workplaces. These systems help users manage tasks, answer complex questions, and automate repetitive activities.\n\nAs AI models become more advanced, they are gaining the ability to understand natural language more accurately and respond with greater nuance. This has made them valuable for education, mental health support, customer service, and creative work. Many households now rely on AI to control appliances, schedule appointments, and monitor energy usage, making daily routines more efficient.\n\nHowever, the rapid growth of AI also raises ethical questions. Experts emphasize the importance of transparency, privacy protection, and responsible use. Going forward, the world must balance innovation with safeguards to ensure AI enhances human life without compromising personal rights.",
    },
  },
};

export const Health = {
  args: {
    article: {
      id: 3,
      title: "Healthy Eating: 10 Tips for a Balanced Diet",
      author: "Dr. Emily Chen",
      category: "Health",
      published: "2025-03-18",
      content: "Maintaining a balanced diet is essential for long-term wellness. Healthy eating doesn't require extreme restrictions or difficult routines—it's about making mindful choices that support the body's natural processes. A good diet should provide energy, strengthen the immune system, and help maintain a stable mood.\n\nOne of the most important steps is incorporating a variety of whole foods. Fruits, vegetables, legumes, and lean proteins offer vitamins and minerals necessary for proper functioning. Whole grains like oats, brown rice, and quinoa are better alternatives to processed carbohydrates, providing more fiber and reducing blood sugar spikes.\n\nHydration is equally essential. Drinking sufficient water supports digestion, regulates temperature, and helps maintain healthy skin. In addition, portion control and limiting added sugars make a huge difference in preventing chronic health problems such as obesity, diabetes, and heart disease. Ultimately, a balanced diet is more about consistency than perfection.",
    },
  },
};
