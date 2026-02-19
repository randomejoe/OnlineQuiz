<template>
  <component :is="tag" :class="classes">
    <slot></slot>
  </component>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  level: {
    type: Number,
    default: 1,
    validator: (value) => [1, 2, 3, 4, 5, 6].includes(value),
  },
  size: {
    type: String,
    default: 'auto',
    validator: (value) => ['auto', 'sm', 'md', 'lg', 'xl', '2xl', '3xl'].includes(value),
  },
});

const tag = computed(() => `h${props.level}`);

const classes = computed(() => {
  const sizeMap = {
    auto: '',
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg',
    xl: 'text-xl',
    '2xl': 'text-2xl',
    '3xl': 'text-3xl',
  };
  
  const defaultSizes = {
    1: 'text-3xl font-bold',
    2: 'text-2xl font-bold',
    3: 'text-xl font-semibold',
    4: 'text-lg font-semibold',
    5: 'text-base font-medium',
    6: 'text-sm font-medium',
  };
  
  if (props.size !== 'auto') {
    return `${sizeMap[props.size]} font-bold`;
  }
  
  return defaultSizes[props.level];
});
</script>
