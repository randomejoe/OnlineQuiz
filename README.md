# Online Quiz Platform

A full-stack quiz application for creating, managing, and taking quizzes with role-based access for students and admins.

## Overview

The project is split into two parts:

- `backend/` - PHP API, routing, services, repositories, and database migrations
- `frontend/` - Vue 3 application for the student and admin interfaces

The application supports quiz creation, question management, quiz taking, score review, user administration, and JSON quiz imports.

## Features

- Authentication with student and admin roles
- Quiz creation, editing, deletion, and review
- Multiple choice, true/false, and short-answer questions
- Quiz attempts with scoring and history
- Admin user management and attempt inspection
- JSON quiz import for reusable quiz packs

## Setup

### Backend

```bash
cd backend
cp app/.env.example app/.env
docker-compose up -d
docker-compose exec php ./bin/migrate
```

### Frontend

```bash
cd frontend
npm install
npm run dev
```

## Database

The backend migration system creates the schema and seeds the exported sample data.

- Initial schema: `backend/app/database/migrations/001_create_tables.sql`
- Seed data: `backend/app/database/migrations/002_seed_quiz_data.sql`

Run the migration helper after starting the backend environment:

```bash
cd backend/app
./bin/migrate
```

## Demo Credentials

Use these sample accounts after the database seed has been applied:

- Student user: `joe@gmail.com` / `joejoejoe`
- Admin user: `admin@quiz.am` / `admin123`

## Sample Quiz JSON Files

The repository root includes ready-to-import quiz exports:

- [bio_quiz.json](./bio_quiz.json)
- [linux_quiz.json](./linux_quiz.json)
- [geography.json](./geography.json)

These can be imported from the admin quiz page using the `Import JSON` action.

## Frontend Import Format

The quiz import flow accepts:

- an array of quizzes
- `{ "quizzes": [...] }`
- `{ "quiz": { ... } }`
- a single quiz object

Each quiz should include:

- `title`
- `subject`
- `description` optional
- `difficulty` optional, one of `easy`, `medium`, or `hard`
- `time_limit_minutes` optional
- `questions`

Question types:

- `multiple_choice`
- `true_false`
- `short_answer`

See [frontend/README.md](./frontend/README.md) for the full import JSON format and examples.
