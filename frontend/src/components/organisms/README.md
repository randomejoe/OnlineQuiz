# Organisms

**Complex UI components** composed of groups of molecules and/or atoms.

## What belongs here?

- Headers (logo + navigation + search + user menu)
- Footers
- Forms (multiple form fields + submit button)
- Sidebars
- Product listings
- Comment sections
- Data tables

## Characteristics

- ✅ Composed of molecules and/or atoms
- ✅ More complex and often specific to a section
- ✅ May have their own state management
- ✅ May include business logic
- ✅ Less reusable than molecules/atoms

## Example

```vue
<!-- Header.vue - An organism -->
<template>
  <header class="header">
    <Logo />
    <Navigation :items="navItems" />
    <SearchBar @search="handleSearch" />
    <UserMenu :user="currentUser" />
  </header>
</template>
```
