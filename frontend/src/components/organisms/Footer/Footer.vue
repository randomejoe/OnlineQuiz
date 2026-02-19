<template>
  <footer class="bg-gray-50 border-t border-gray-200 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Brand section -->
        <div class="col-span-1 md:col-span-2">
          <Heading :level="3" size="lg" class="mb-4">
            Article Archive
          </Heading>
          <Text as="p" size="sm" color="muted" class="mb-4">
            Your source for quality articles on technology, science, health, and more.
          </Text>
          <div class="flex space-x-4">
            <a 
              v-for="social in socialLinks" 
              :key="social.name"
              :href="social.href" 
              class="text-gray-600 hover:text-blue-600 transition-colors"
              :aria-label="social.name"
            >
              <component :is="social.icon" class="h-5 w-5" />
            </a>
          </div>
        </div>
        
        <!-- Quick Links -->
        <div>
          <Heading :level="4" size="md" class="mb-4">
            Quick Links
          </Heading>
          <ul class="space-y-2">
            <li v-for="link in quickLinks" :key="link.name">
              <a 
                :href="link.href" 
                class="text-gray-600 hover:text-blue-600 transition-colors text-sm"
              >
                {{ link.name }}
              </a>
            </li>
          </ul>
        </div>
        
        <!-- Legal -->
        <div>
          <Heading :level="4" size="md" class="mb-4">
            Legal
          </Heading>
          <ul class="space-y-2">
            <li v-for="link in legalLinks" :key="link.name">
              <a 
                :href="link.href" 
                class="text-gray-600 hover:text-blue-600 transition-colors text-sm"
              >
                {{ link.name }}
              </a>
            </li>
          </ul>
        </div>
      </div>
      
      <div class="mt-8 pt-8 border-t border-gray-200">
        <Text as="p" size="sm" color="muted" class="text-center">
          © {{ currentYear }} Article Archive. All rights reserved.
        </Text>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue';
import Heading from '../../atoms/Heading/Heading.vue';
import Text from '../../atoms/Text/Text.vue';

defineProps({
  quickLinks: {
    type: Array,
    default: () => [
      { name: 'Home', href: '/' },
      { name: 'Articles', href: '/articles' },
      { name: 'Categories', href: '/categories' },
      { name: 'About', href: '/about' },
    ],
  },
  legalLinks: {
    type: Array,
    default: () => [
      { name: 'Privacy Policy', href: '/privacy' },
      { name: 'Terms of Service', href: '/terms' },
      { name: 'Cookie Policy', href: '/cookies' },
    ],
  },
  socialLinks: {
    type: Array,
    default: () => [],
  },
});

const currentYear = computed(() => new Date().getFullYear());
</script>
