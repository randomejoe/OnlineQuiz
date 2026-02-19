<template>
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <div class="flex items-center">
          <Heading :level="1" size="xl" class="text-blue-600">
            <a href="/" class="hover:text-blue-700 transition-colors">
              Article Archive
            </a>
          </Heading>
        </div>

        <nav class="hidden md:flex space-x-8">
          <a
            v-for="link in navigationLinks"
            :key="link.name"
            :href="link.href"
            class="text-gray-700 hover:text-blue-600 transition-colors font-medium"
          >
            {{ link.name }}
          </a>
        </nav>

        <div class="md:hidden">
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="text-gray-700 hover:text-blue-600 transition-colors"
            aria-label="Toggle menu"
          >
            <svg
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                v-if="!mobileMenuOpen"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
              />
              <path
                v-else
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile menu -->
      <div v-if="mobileMenuOpen" class="md:hidden pb-4">
        <nav class="flex flex-col space-y-2">
          <a
            v-for="link in navigationLinks"
            :key="link.name"
            :href="link.href"
            class="text-gray-700 hover:text-blue-600 transition-colors px-2 py-1"
          >
            {{ link.name }}
          </a>
        </nav>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from "vue";
import Heading from "../../atoms/Heading/Heading.vue";

defineProps({
  navigationLinks: {
    type: Array,
    default: () => [
      { name: "Home", href: "/" },
      { name: "Articles", href: "/articles" },
      { name: "About", href: "/about" },
      { name: "Contact", href: "/contact" },
    ],
  },
});

const mobileMenuOpen = ref(false);
</script>
