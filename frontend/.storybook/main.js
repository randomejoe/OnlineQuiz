import tailwindcss from "@tailwindcss/vite";

/** @type { import('@storybook/vue3-vite').StorybookConfig } */
const config = {
  stories: ["../src/**/*.stories.@(js|jsx|mjs|ts|tsx)"],
  addons: [],
  framework: "@storybook/vue3-vite",
  async viteFinal(config) {
    // Add Tailwind CSS plugin to Storybook's Vite config
    config.plugins = config.plugins || [];
    config.plugins.push(tailwindcss());
    return config;
  },
};
export default config;
