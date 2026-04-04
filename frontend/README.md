# Frontend

Vue 3 frontend for the Online Quiz platform, built with Vite, Tailwind CSS, Pinia, Vue Router, and Storybook.

## Stack

- Vue 3
- Vite
- Pinia
- Vue Router
- Tailwind CSS v4
- Storybook

## Project Structure

```text
src/
├── assets/           # Global styles
├── components/       # Pages, layouts, and reusable UI
├── config.js         # Runtime configuration
├── router/           # Route definitions and guards
├── stores/           # Pinia stores
├── utils/            # API and shared helpers
└── main.js           # App bootstrap
```

## Available Scripts

```sh
npm run dev
npm run build
npm run preview
npm run storybook
npm run build-storybook
```

## Configuration

Create a `frontend/.env` file if you need to override the backend API domain:

```env
VITE_API_DOMAIN=http://localhost
```

## API Helpers

Use `frontend/src/utils/api.js` for all backend requests. It provides:

- `get`
- `post`
- `put`
- `del`
- `readJsonResponse`

## App Areas

- Student quiz browsing and taking
- Attempt history and results
- Admin dashboard, quiz management, and user management

## Component Organization

- `atoms` for small primitives
- `molecules` for composed UI blocks
- `organisms` for page sections
- `templates` for route shells
- `pages` for route-level views

## Development Notes

- Prefer shared helpers in `utils/` and `stores/` before adding local page logic.
- Keep UI copy and component names aligned with the quiz domain.
- Storybook stories should live next to the component they document.
