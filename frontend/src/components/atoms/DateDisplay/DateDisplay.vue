<template>
  <time :datetime="dateString" :class="classes">
    {{ formattedDate }}
  </time>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  date: {
    type: String,
    required: true,
  },
  format: {
    type: String,
    default: 'long',
    validator: (value) => ['short', 'long', 'relative'].includes(value),
  },
});

const dateString = computed(() => props.date);

const formattedDate = computed(() => {
  const date = new Date(props.date);
  
  if (props.format === 'short') {
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  } else if (props.format === 'relative') {
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
    if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`;
    return `${Math.floor(diffDays / 365)} years ago`;
  } else {
    return date.toLocaleDateString('en-US', { 
      weekday: 'long', 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    });
  }
});

const classes = 'text-gray-600 text-sm';
</script>
