<template>
  <Badge :variant="categoryVariant" size="sm">
    {{ category }}
  </Badge>
</template>

<script setup>
import { computed } from 'vue';
import Badge from '../../atoms/Badge/Badge.vue';

const props = defineProps({
  category: {
    type: String,
    required: true,
  },
});

const categoryVariant = computed(() => {
  const variantMap = {
    'Technology': 'primary',
    'Environment': 'success',
    'Health': 'warning',
    'Finance': 'secondary',
    'History': 'default',
    'Gaming': 'primary',
    'Automotive': 'default',
    'Travel': 'success',
    'Science': 'secondary',
  };
  
  return variantMap[props.category] || 'default';
});
</script>
