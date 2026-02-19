# Articles API

A RESTful API for managing articles built with PHP, following MVC architecture patterns.

## Project Structure

```
backend/
├── app/
│   ├── public/
│   │   └── index.php          # Entry point and route dispatcher
│   └── src/
│       ├── Controllers/        # Request handlers
│       ├── Services/           # Business logic layer
│       ├── Repositories/       # Data access layer
│       ├── Models/             # Data models
│       ├── Framework/          # Base controller class and other general framework code
│       ├── Utils/              # Utility classes (JsonStore)
│       └── data/               # JSON data storage
├── docker-compose.yml          # Docker services configuration
├── nginx.conf                  # Nginx configuration
├── PHP.Dockerfile              # PHP Docker image configuration
└── Articles_API.postman_collection.json  # Postman collection for API testing
```

## API Endpoints

### Get All Articles

```
GET /articles
```

Returns a list of all articles.

### Get Article by ID

```
GET /articles/{id}
```

Returns a specific article by its ID.

### Create Article

```
POST /articles
Content-Type: application/json

{
    "title": "Article Title",
    "author": "Author Name",
    "category": "Category",
    "published": "2025-01-15",
    "content": "Article content here"
}
```

Creates a new article and returns it with the assigned ID.

### Update Article

```
PUT /articles/{id}
Content-Type: application/json

{
    "id": 123,
    "title": "Updated Title",
    "author": "Author Name",
    "category": "Category",
    "published": "2025-01-16",
    "content": "Updated content"
}
```

Updates an existing article by ID.

### Delete Article

```
DELETE /articles/{id}
```

Deletes an article by ID.

## Getting Started

### Prerequisites

- Docker and Docker Compose

### Installation

1. Clone the repository:

```bash
cd backend
```

2. Start the Docker containers:

```bash
docker-compose up
```

3. Run composer commands:

```bash
docker-compose exec php composer [...]
```

### Running the Application

The application will be available at:

- **API**: http://localhost
- **phpMyAdmin**: http://localhost:8080

### Testing the API

Import the `Articles_API.postman_collection.json` file into Postman to test all endpoints.

## Docker Services

- **nginx**: Web server (port 80)
- **php**: PHP-FPM service
- **mysql**: MariaDB database (port 3306)
  - Username: `developer`
  - Password: `secret123`
  - Database: `developmentdb`
- **phpmyadmin**: Database management interface (port 8080)

## Data Storage

Currently, the application uses JSON file-based storage (`app/src/data/articles.json`) for demonstration purposes. In production, this should be replaced with a proper database implementation.
