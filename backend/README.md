# Online Quiz API

A RESTful API for managing quizzes, questions, and attempts, built with PHP and following MVC architecture patterns.

## 🚀 Features

- **Authentication**: JWT-based authentication for students and admins.
- **Quiz Management**: Create, Read, Update, and Delete quizzes (Admin only).
- **Question Management**: Support for multiple choice, true/false, and short answer questions.
- **Attempts & Scoring**: Students can take quizzes and get immediate results with percentage-based scoring.
- **Admin Dashboard**: View statistics, manage users, and review quiz results.

## 📁 Project Structure

```text
backend/
├── app/
│   ├── public/             # Entry point (index.php) and assets
│   ├── src/                # Source code (Controllers, Services, Models, Repositories)
│   ├── vendor/             # Composer dependencies
│   └── database/           # Database migrations
├── docker-compose.yml      # Docker orchestration
├── PHP.Dockerfile          # PHP-FPM configuration
└── nginx.conf              # Nginx server configuration
```

## 🛠️ Tech Stack

- **PHP 8.2+**
- **MySQL / MariaDB**
- **FastRoute** for routing
- **Firebase/php-jwt** for authentication
- **PHPUnit** for testing

## 🚦 Getting Started

### Prerequisites

- Docker and Docker Compose
- Postman (for testing)

### Installation

1. Clone the repository
2. Set up environment variables in `backend/app/.env` (copy from `.env.example` if available)
3. Start the containers:
   ```bash
   docker-compose up -d
   ```
4. Run database migration/ensure step explicitly:
   ```bash
   cd app
   ./bin/migrate
   ```
   This applies the schema and seeds the exported sample quiz data.

## 📡 API Endpoints

### Auth
- `POST /auth/register` - Register a new user
- `POST /auth/login` - Login and receive JWT
- `GET /auth/me` - Get current user profile
- `POST /auth/logout` - Logout

### Quizzes
- `GET /quizzes` - List all quizzes
- `GET /quizzes/{id}` - Get quiz details
- `POST /quizzes` - Create a quiz (Admin)
- `PUT /quizzes/{id}` - Update a quiz (Admin)
- `DELETE /quizzes/{id}` - Delete a quiz (Admin)

### Attempts
- `POST /quizzes/{id}/attempts` - Start a quiz attempt
- `POST /attempts/{id}/submit` - Submit answers and complete attempt
- `GET /attempts/{id}` - Get attempt result
- `GET /users/me/attempts` - View your attempt history

### Admin
- `GET /admin/stats` - Overall system statistics
- `GET /admin/users` - List all users
- `GET /admin/quizzes/{id}/results` - All attempts for a specific quiz

## 🧪 Testing

Run backend migration manually when schema changes are introduced:
```bash
docker-compose exec php ./bin/migrate
```
The migration step also applies the seed data from the exported database dump.
