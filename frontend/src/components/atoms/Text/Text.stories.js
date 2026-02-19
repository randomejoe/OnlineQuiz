import Text from './Text.vue';

export default {
  title: 'Atoms/Text',
  component: Text,
  tags: ['autodocs'],
  argTypes: {
    as: {
      control: { type: 'select' },
      options: ['p', 'span', 'div', 'label'],
    },
    size: {
      control: { type: 'select' },
      options: ['xs', 'sm', 'base', 'lg'],
    },
    weight: {
      control: { type: 'select' },
      options: ['normal', 'medium', 'semibold', 'bold'],
    },
    color: {
      control: { type: 'select' },
      options: ['default', 'muted', 'primary', 'secondary'],
    },
  },
};

export const Default = {
  args: {
    children: 'This is default text',
  },
  render: (args) => ({
    components: { Text },
    setup() {
      return { args };
    },
    template: '<Text v-bind="args">This is default text</Text>',
  }),
};

export const Sizes = {
  render: () => ({
    components: { Text },
    template: `
      <div class="space-y-2">
        <Text size="xs">Extra small text</Text>
        <Text size="sm">Small text</Text>
        <Text size="base">Base text</Text>
        <Text size="lg">Large text</Text>
      </div>
    `,
  }),
};

export const Colors = {
  render: () => ({
    components: { Text },
    template: `
      <div class="space-y-2">
        <Text color="default">Default color text</Text>
        <Text color="muted">Muted color text</Text>
        <Text color="primary">Primary color text</Text>
        <Text color="secondary">Secondary color text</Text>
      </div>
    `,
  }),
};
