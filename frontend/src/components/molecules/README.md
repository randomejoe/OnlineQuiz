# Molecules

**Simple groups of UI elements** that function together as a unit.

## What belongs here?

- Form fields (label + input + validation message)
- Search bars (input + button)
- Navigation items
- Card headers (title + icon + action)
- Product previews (image + title + price)
- List items with actions

## Characteristics

- ✅ Composed of atoms and/or other molecules
- ✅ Still relatively simple and reusable
- ✅ May have simple state (e.g., form field validation)
- ✅ Focused on a specific UI pattern

## Example

```vue
<!-- SearchBar.vue - A molecule -->
<template>
  <div class="search-bar">
    <Input v-model="query" placeholder="Search..." />
    <Button @click="handleSearch">Search</Button>
  </div>
</template>
```
