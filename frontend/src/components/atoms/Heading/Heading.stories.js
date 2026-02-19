/**
 * STORYBOOK STORIES FILE
 *
 * What is Storybook?
 * Storybook is a tool that helps you build and test UI components in isolation.
 * Think of it as a "component library" where you can see all your components
 * and test them with different props/inputs without running your entire app.
 *
 * What does this file do?
 * This file creates "stories" for the Heading component. Each story is like
 * a different example or use case of the Heading component. When you open
 * Storybook, you'll see these stories in the sidebar and can interact with
 * them to see how the component looks and behaves.
 *
 * How to use this file:
 * 1. Run your Storybook server (usually `npm run storybook`)
 * 2. Open the browser to see the Storybook interface
 * 3. Navigate to "Atoms/Heading" in the sidebar
 * 4. Click on different stories (H1, H2, AllLevels) to see them
 * 5. Use the "Controls" panel to change props and see the component update live
 */

import Heading from "./Heading.vue";

/**
 * DEFAULT EXPORT - Component Configuration
 *
 * This is the main configuration object that tells Storybook:
 * - Where to show this component in the sidebar (title: 'Atoms/Heading')
 * - Which component to use (component: Heading)
 * - What controls to show in the Controls panel (argTypes)
 *
 * The 'tags: ['autodocs']' tells Storybook to automatically generate
 * documentation for this component based on the component's props.
 */
export default {
  // This creates the path in Storybook's sidebar: Atoms > Heading
  title: "Atoms/Heading",

  // The Vue component we're creating stories for
  component: Heading,

  // Automatically generates documentation from the component
  tags: ["autodocs"],

  /**
   * ARGTYPES - Control Panel Configuration
   *
   * This defines what controls appear in Storybook's "Controls" panel.
   * When you select a story, you can use these controls to change the
   * component's props in real-time and see how it responds.
   *
   * For example, the 'level' control lets you pick from 1-6 to change
   * which heading level (h1, h2, etc.) is rendered.
   */
  argTypes: {
    // Creates a dropdown selector for heading levels 1-6
    level: {
      control: { type: "select" },
      options: [1, 2, 3, 4, 5, 6],
    },
    // Creates a dropdown selector for text sizes
    size: {
      control: { type: "select" },
      options: ["auto", "sm", "md", "lg", "xl", "2xl", "3xl"],
    },
  },
};

/**
 * STORY 1: H1
 *
 * This creates a story called "H1" that shows the Heading component
 * as a level 1 heading (h1 tag).
 *
 * How it works:
 * - 'args' defines the default props for this story
 * - 'render' is a function that returns a Vue component configuration
 * - The template uses v-bind="args" to pass all args as props to Heading
 *
 * In Storybook, you'll see this as "H1" in the stories list.
 */
export const H1 = {
  // Default props/arguments for this story
  args: {
    level: 1,
    children: "Heading 1",
  },
  // This function tells Storybook how to render the component
  render: (args) => ({
    components: { Heading },
    setup() {
      return { args };
    },
    // v-bind="args" passes all properties from args as props to Heading
    template: '<Heading v-bind="args">Heading 1</Heading>',
  }),
};

/**
 * STORY 2: H2
 *
 * Similar to H1, but shows a level 2 heading (h2 tag).
 * Notice that we don't need to specify 'children' in args here
 * because we're putting the text directly in the template.
 */
export const H2 = {
  args: {
    level: 2,
  },
  render: (args) => ({
    components: { Heading },
    setup() {
      return { args };
    },
    template: '<Heading v-bind="args">Heading 2</Heading>',
  }),
};

/**
 * STORY 3: AllLevels
 *
 * This story demonstrates all heading levels (1-6) at once.
 * This is useful for comparing how different heading levels look
 * side-by-side.
 *
 * Note: This story doesn't use 'args' because we're manually
 * specifying each Heading component with its own props.
 *
 * The 'space-y-4' class adds vertical spacing between the headings
 * (this is a Tailwind CSS utility class).
 */
export const AllLevels = {
  render: () => ({
    components: { Heading },
    template: `
      <div class="space-y-4">
        <Heading :level="1">Heading 1</Heading>
        <Heading :level="2">Heading 2</Heading>
        <Heading :level="3">Heading 3</Heading>
        <Heading :level="4">Heading 4</Heading>
        <Heading :level="5">Heading 5</Heading>
        <Heading :level="6">Heading 6</Heading>
      </div>
    `,
  }),
};
