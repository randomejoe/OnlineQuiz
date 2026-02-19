<template>
  <article class="max-w-4xl mx-auto">
    <header class="mb-6">
      <div class="mb-4">
        <CategoryBadge :category="article.category" />
      </div>
      
      <Heading :level="1" size="3xl" class="mb-4">
        {{ article.title }}
      </Heading>
      
      <ArticleMeta 
        :author="article.author" 
        :published="article.published" 
        class="mb-6"
      />
    </header>
    
    <div class="prose prose-lg max-w-none">
      <Text 
        as="div" 
        size="base" 
        color="default"
        class="whitespace-pre-line leading-relaxed"
      >
        {{ article.content }}
      </Text>
    </div>
  </article>
</template>

<script setup>
import Heading from '../../atoms/Heading/Heading.vue';
import Text from '../../atoms/Text/Text.vue';
import ArticleMeta from '../../molecules/ArticleMeta/ArticleMeta.vue';
import CategoryBadge from '../../molecules/CategoryBadge/CategoryBadge.vue';

defineProps({
  article: {
    type: Object,
    required: true,
    validator: (value) => {
      return value.id && value.title && value.author && value.category && value.published && value.content;
    },
  },
});
</script>

<style scoped>
.prose {
  color: #374151;
}

.prose p {
  margin-bottom: 1.25em;
}
</style>
