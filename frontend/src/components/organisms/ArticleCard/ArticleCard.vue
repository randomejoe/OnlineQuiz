<template>
  <article class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden">
    <div class="p-6">
      <div class="flex items-start justify-between mb-3">
        <CategoryBadge :category="article.category" />
      </div>
      
      <Heading :level="3" size="xl" class="mb-3">
        <a 
          :href="`#/articles/${article.id}`" 
          class="hover:text-blue-600 transition-colors"
          @click.prevent="$emit('click', article.id)"
        >
          {{ article.title }}
        </a>
      </Heading>
      
      <Text 
        as="p" 
        size="sm" 
        color="muted" 
        class="mb-4 line-clamp-3"
      >
        {{ truncatedContent }}
      </Text>
      
      <ArticleMeta 
        :author="article.author" 
        :published="article.published" 
      />
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue';
import Heading from '../../atoms/Heading/Heading.vue';
import Text from '../../atoms/Text/Text.vue';
import ArticleMeta from '../../molecules/ArticleMeta/ArticleMeta.vue';
import CategoryBadge from '../../molecules/CategoryBadge/CategoryBadge.vue';

const props = defineProps({
  article: {
    type: Object,
    required: true,
    validator: (value) => {
      return value.id && value.title && value.author && value.category && value.published && value.content;
    },
  },
});

defineEmits(['click']);

const truncatedContent = computed(() => {
  const maxLength = 150;
  if (props.article.content.length <= maxLength) {
    return props.article.content;
  }
  return props.article.content.substring(0, maxLength) + '...';
});
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
