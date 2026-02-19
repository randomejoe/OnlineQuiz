# Templates

**Page-level objects** that place components into a layout.

## What belongs here?

- Page layouts
- Grid systems
- Content structures
- Layout wrappers

## Characteristics

- ✅ Focus on page structure, not content
- ✅ Usually use placeholder content
- ✅ Define the skeleton of a page
- ✅ Show how components work together in context
- ✅ Demonstrate responsive behavior

## Example

```vue
<!-- MainLayout.vue - A template -->
<template>
  <div class="main-layout">
    <Header />
    <main class="content">
      <aside class="sidebar">
        <slot name="sidebar"></slot>
      </aside>
      <section class="main-content">
        <slot name="content"></slot>
      </section>
    </main>
    <Footer />
  </div>
</template>
```
