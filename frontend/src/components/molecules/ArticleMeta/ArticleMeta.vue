<template>
  <div class="flex items-center gap-2 text-sm text-gray-600">
    <Text as="span" size="sm" color="muted">{{ author }}</Text>
    <span class="text-gray-400">•</span>
    <DateDisplay :date="published" format="short" />
  </div>
</template>

<script setup>
import Text from '../../atoms/Text/Text.vue';
import DateDisplay from '../../atoms/DateDisplay/DateDisplay.vue';

defineProps({
  author: {
    type: String,
    required: true,
  },
  published: {
    type: String,
    required: true,
  },
});
</script>
