<template>
  <component :is="tag" :class="classes">
    <slot></slot>
  </component>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  as: {
    type: String,
    default: 'p',
    validator: (value) => ['p', 'span', 'div', 'label'].includes(value),
  },
  size: {
    type: String,
    default: 'base',
    validator: (value) => ['xs', 'sm', 'base', 'lg'].includes(value),
  },
  weight: {
    type: String,
    default: 'normal',
    validator: (value) => ['normal', 'medium', 'semibold', 'bold'].includes(value),
  },
  color: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'muted', 'primary', 'secondary'].includes(value),
  },
});

const tag = computed(() => props.as);

const classes = computed(() => {
  const sizes = {
    xs: 'text-xs',
    sm: 'text-sm',
    base: 'text-base',
    lg: 'text-lg',
  };
  
  const weights = {
    normal: 'font-normal',
    medium: 'font-medium',
    semibold: 'font-semibold',
    bold: 'font-bold',
  };
  
  const colors = {
    default: 'text-gray-900',
    muted: 'text-gray-600',
    primary: 'text-blue-600',
    secondary: 'text-purple-600',
  };
  
  return `${sizes[props.size]} ${weights[props.weight]} ${colors[props.color]}`;
});
</script>
