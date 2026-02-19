# Components Directory - Atomic Design Structure

This directory follows the **Atomic Design** methodology, which breaks down interfaces into five distinct levels:

## Directory Structure

### 📦 `atoms/`
**Basic building blocks** that cannot be broken down further without losing their meaning.
- Examples: Buttons, Inputs, Labels, Icons, Headings, Links
- These are the smallest, most fundamental components
- Should be highly reusable and have minimal dependencies

### 🧬 `molecules/`
**Simple groups of UI elements** that function together as a unit.
- Examples: Form fields (label + input), Search bars, Navigation items, Card headers
- Composed of atoms and/or other molecules
- Still relatively simple and reusable

### 🦠 `organisms/`
**Complex UI components** composed of groups of molecules and/or atoms.
- Examples: Headers, Footers, Forms, Sidebars, Product listings
- These are more complex and often specific to a particular section of the interface
- May have their own state management

### 📄 `templates/`
**Page-level objects** that place components into a layout.
- Examples: Page layouts, Grid systems, Content structures
- Focus on the page's underlying content structure
- Usually don't include real content, just placeholders

### 📑 `pages/`
**Specific instances of templates** with real content.
- Examples: Home page, About page, Product detail page
- These are the final, concrete pages users interact with
- Combine templates with real content and data

## Guidelines

1. **Start small**: Build atoms first, then combine them into molecules, then organisms
2. **Reusability**: Components should be reusable within their level and composable into higher levels
3. **Single Responsibility**: Each component should have one clear purpose
4. **Props over Content**: Prefer props and slots over hardcoded content for flexibility

## Example Component Organization

```
components/
├── atoms/
│   ├── Button.vue
│   ├── Input.vue
│   └── Icon.vue
├── molecules/
│   ├── SearchBar.vue (uses Input + Button atoms)
│   └── FormField.vue (uses Label + Input atoms)
├── organisms/
│   ├── Header.vue (uses SearchBar molecule + Button atoms)
│   └── ProductCard.vue (uses multiple molecules)
├── templates/
│   └── MainLayout.vue (uses Header + Footer organisms)
└── pages/
    └── HomePage.vue (uses MainLayout template with real content)
```
