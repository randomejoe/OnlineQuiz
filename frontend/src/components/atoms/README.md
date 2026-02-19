# Atoms

**Basic building blocks** that cannot be broken down further without losing their meaning.

## What belongs here?

- Buttons
- Inputs
- Labels
- Icons
- Headings (h1, h2, etc.)
- Links
- Images
- Text elements

## Characteristics

- ✅ Highly reusable
- ✅ Minimal dependencies
- ✅ Single, focused purpose
- ✅ No business logic
- ✅ Styling and basic interactivity only

## Example

```vue
<!-- Button.vue - An atom -->
<template>
  <button :class="['btn', `btn-${variant}`]" @click="$emit('click')">
    <slot></slot>
  </button>
</template>
```
