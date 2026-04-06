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

## Quiz Import JSON

The admin quiz list includes an `Import JSON` action that accepts either:

- an array of quizzes
- `{ "quizzes": [...] }`
- `{ "quiz": { ... } }`
- a single quiz object

Each imported quiz is normalized before being sent to the API. The importer keeps the following quiz fields:

- `title` required
- `subject` required
- `description` optional
- `difficulty` optional, one of `easy`, `medium`, or `hard`
- `time_limit_minutes` optional, defaults to `10`
- `questions` optional array

Supported question shapes:

- `multiple_choice`
  - `question_text` required
  - `points` optional, defaults to `1`
  - `options` array with at least one item marked `is_correct: true`
- `true_false`
  - `question_text` required
  - `options` should contain `True` and `False`
  - one option must be marked correct
- `short_answer`
  - `question_text` required
  - `correct_answer` accepted
  - if `correct_answer` is missing, the importer uses the first non-empty option text

Accepted aliases during import:

- `questionText` for `question_text`
- `timeLimitMinutes` for `time_limit_minutes`
- `text` for `option_text`

Example:

```json
{
  "quizzes": [
    {
      "title": "Intro to HTML",
      "description": "Basics of HTML",
      "subject": "Web",
      "difficulty": "medium",
      "time_limit_minutes": 15,
      "questions": [
        {
          "type": "multiple_choice",
          "question_text": "Pick the right tag",
          "points": 3,
          "options": [
            { "option_text": "div", "is_correct": false },
            { "option_text": "h1", "is_correct": true }
          ]
        },
        {
          "type": "true_false",
          "question_text": "HTML is a markup language",
          "options": [
            { "option_text": "True", "is_correct": true },
            { "option_text": "False", "is_correct": false }
          ]
        },
        {
          "type": "short_answer",
          "question_text": "Name the largest heading tag",
          "correct_answer": "h1"
        }
      ]
    }
  ]
}
```

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
