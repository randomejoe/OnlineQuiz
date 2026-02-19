import Badge from './Badge.vue';

export default {
  title: 'Atoms/Badge',
  component: Badge,
  tags: ['autodocs'],
  argTypes: {
    variant: {
      control: { type: 'select' },
      options: ['default', 'primary', 'secondary', 'success', 'warning', 'danger'],
    },
    size: {
      control: { type: 'select' },
      options: ['sm', 'md', 'lg'],
    },
  },
};

export const Default = {
  args: {
    variant: 'default',
    children: 'Badge',
  },
  render: (args) => ({
    components: { Badge },
    setup() {
      return { args };
    },
    template: '<Badge v-bind="args">Badge</Badge>',
  }),
};

export const Primary = {
  args: {
    variant: 'primary',
  },
  render: (args) => ({
    components: { Badge },
    setup() {
      return { args };
    },
    template: '<Badge v-bind="args">Primary</Badge>',
  }),
};

export const AllVariants = {
  render: () => ({
    components: { Badge },
    template: `
      <div class="flex gap-2 flex-wrap">
        <Badge variant="default">Default</Badge>
        <Badge variant="primary">Primary</Badge>
        <Badge variant="secondary">Secondary</Badge>
        <Badge variant="success">Success</Badge>
        <Badge variant="warning">Warning</Badge>
        <Badge variant="danger">Danger</Badge>
      </div>
    `,
  }),
};

export const AllSizes = {
  render: () => ({
    components: { Badge },
    template: `
      <div class="flex gap-2 items-center">
        <Badge size="sm">Small</Badge>
        <Badge size="md">Medium</Badge>
        <Badge size="lg">Large</Badge>
      </div>
    `,
  }),
};
