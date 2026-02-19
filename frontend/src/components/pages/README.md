# Pages

**Specific instances of templates** with real content.

## What belongs here?

- Home page
- About page
- Product detail page
- User profile page
- Contact page

## Characteristics

- ✅ Final, concrete pages users interact with
- ✅ Combine templates with real content and data
- ✅ May fetch data from APIs
- ✅ Handle page-specific logic
- ✅ Usually route-specific

## Example

```vue
<!-- HomePage.vue - A page -->
<template>
  <MainLayout>
    <template #content>
      <HeroSection :title="heroTitle" :subtitle="heroSubtitle" />
      <ProductList :products="featuredProducts" />
    </template>
    <template #sidebar>
      <CategoryFilter />
    </template>
  </MainLayout>
</template>
```
