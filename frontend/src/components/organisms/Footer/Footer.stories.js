import Footer from './Footer.vue';

export default {
  title: 'Organisms/Footer',
  component: Footer,
  tags: ['autodocs'],
};

export const Default = {
  args: {
    quickLinks: [
      { name: 'Home', href: '/' },
      { name: 'Articles', href: '/articles' },
      { name: 'Categories', href: '/categories' },
      { name: 'About', href: '/about' },
    ],
    legalLinks: [
      { name: 'Privacy Policy', href: '/privacy' },
      { name: 'Terms of Service', href: '/terms' },
      { name: 'Cookie Policy', href: '/cookies' },
    ],
  },
};

export const Minimal = {
  args: {
    quickLinks: [
      { name: 'Home', href: '/' },
      { name: 'Articles', href: '/articles' },
    ],
    legalLinks: [
      { name: 'Privacy Policy', href: '/privacy' },
    ],
  },
};
