<?php
require __DIR__ . '/../vendor/autoload.php';
if (class_exists(\Dotenv\Dotenv::class)) {
	$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
	$dotenv->load();
}

// Keep API responses JSON-clean: log PHP warnings/errors instead of printing HTML into response bodies.
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('html_errors', '0');
ini_set('error_log', '/proc/self/fd/2');

if (strlen((string)($_ENV['JWT_SECRET'] ?? '')) < 32) {
	header('Content-Type: application/json; charset=utf-8');
	http_response_code(500);
	echo json_encode(['error' => 'Server misconfiguration']);
	error_log('JWT_SECRET is missing or too weak (minimum 32 characters).');
	exit;
}

/**
 * This is the central route handler of the application.
 * It uses FastRoute to map URLs to controller methods.
 *
 * See the documentation for FastRoute for more information: https://github.com/nikic/FastRoute
 */

// CORS headers for localhost requests
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1|::1)(:\\d+)?$/', $origin)) {
	header('Access-Control-Allow-Origin: ' . $origin);
	// Specifies which HTTP methods are allowed when accessing the resource from the origin
	header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
	// Specifies which HTTP headers can be used when making the actual request
	header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
	// Allows cookies and authentication credentials to be sent with cross-origin requests
	header('Access-Control-Allow-Credentials: true');
	// Specifies how long (in seconds) the browser can cache the preflight response (24 hours)
	header('Access-Control-Max-Age: 86400');
}

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(200);
	exit;
}

use FastRoute\RouteCollector;
use App\Exceptions\HttpException;
use App\Framework\Request;
use App\Framework\RequestContext;
use function FastRoute\simpleDispatcher;

/**
 * Define the routes for the application.
 */
$dispatcher = simpleDispatcher(function (RouteCollector $r) {
	// Auth routes (public)
	$r->addRoute('POST', '/auth/register', ['App\Controllers\AuthController', 'register', 'public']);
	$r->addRoute('POST', '/auth/login', ['App\Controllers\AuthController', 'login', 'public']);
	$r->addRoute('GET', '/auth/me', ['App\Controllers\AuthController', 'me', 'auth']);
	$r->addRoute('POST', '/auth/logout', ['App\Controllers\AuthController', 'logout', 'public']);

	// Quiz routes
	$r->addRoute('GET', '/quizzes', ['App\Controllers\QuizController', 'getAll', 'auth']);
	$r->addRoute('GET', '/quizzes/{id:\\d+}', ['App\Controllers\QuizController', 'get', 'auth']);
	$r->addRoute('POST', '/quizzes', ['App\Controllers\QuizController', 'create', 'admin']);
	$r->addRoute('PUT', '/quizzes/{id:\\d+}', ['App\Controllers\QuizController', 'update', 'admin']);
	$r->addRoute('DELETE', '/quizzes/{id:\\d+}', ['App\Controllers\QuizController', 'delete', 'admin']);

	// Question routes
	$r->addRoute('POST', '/quizzes/{id:\\d+}/questions', ['App\Controllers\QuestionController', 'create', 'admin']);
	$r->addRoute('PUT', '/questions/{id:\\d+}', ['App\Controllers\QuestionController', 'update', 'admin']);
	$r->addRoute('DELETE', '/questions/{id:\\d+}', ['App\Controllers\QuestionController', 'delete', 'admin']);

	// Attempt routes
	$r->addRoute('POST', '/quizzes/{id:\\d+}/attempts', ['App\Controllers\AttemptController', 'start', 'auth']);
	$r->addRoute('POST', '/attempts/{id:\\d+}/answers/check', ['App\Controllers\AttemptController', 'checkAnswer', 'auth']);
	$r->addRoute('POST', '/attempts/{id:\\d+}/submit', ['App\Controllers\AttemptController', 'submit', 'auth']);
	$r->addRoute('DELETE', '/attempts/{id:\\d+}', ['App\Controllers\AttemptController', 'abandon', 'auth']);
	$r->addRoute('GET', '/attempts/{id:\\d+}', ['App\Controllers\AttemptController', 'getResult', 'auth']);
	$r->addRoute('GET', '/users/me/attempts', ['App\Controllers\AttemptController', 'getHistory', 'auth']);

	// Admin routes (placeholders)
	$r->addRoute('GET', '/admin/stats', ['App\Controllers\AdminController', 'getStats', 'admin']);
	$r->addRoute('GET', '/admin/users', ['App\Controllers\AdminController', 'getUsers', 'admin']);
	$r->addRoute('GET', '/admin/users/{id:\\d+}/attempts', ['App\Controllers\AdminController', 'getUserAttempts', 'admin']);
	$r->addRoute('DELETE', '/admin/users/{id:\\d+}', ['App\Controllers\AdminController', 'deleteUser', 'admin']);
	$r->addRoute('GET', '/admin/quizzes/{id:\\d+}/results', ['App\Controllers\AdminController', 'getQuizResults', 'admin']);
});


/**
 * Get the request method and URI from the server variables and invoke the dispatcher.
 */
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'], '?');
RequestContext::set(Request::capture());
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

/**
 * Switch on the dispatcher result and call the appropriate controller method if found.
 */
switch ($routeInfo[0]) {
	// Handle not found routes
	case FastRoute\Dispatcher::NOT_FOUND:
		header('Content-Type: application/json; charset=utf-8');
		http_response_code(404);
		echo json_encode(['error' => 'Not Found']);
		break;
	// Handle routes that were invoked with the wrong HTTP method
	case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
		header('Content-Type: application/json; charset=utf-8');
		http_response_code(405);
		echo json_encode(['error' => 'Method Not Allowed']);
		break;
	// Handle found routes
	case FastRoute\Dispatcher::FOUND:
		try {
			$handler = $routeInfo[1];
			$class = $handler[0];
			$method = $handler[1];
			$access = $handler[2] ?? 'auth';
			$vars = $routeInfo[2];

			if ($access === 'auth' || $access === 'admin') {
				\App\Middleware\JwtMiddleware::handle();
			}

			if ($access === 'admin') {
				\App\Middleware\RoleMiddleware::requireAdmin();
			}

			$controller = new $class();
			$controller->$method($vars);
		} catch (HttpException $e) {
			header('Content-Type: application/json; charset=utf-8');
			http_response_code($e->getStatusCode());
			echo json_encode(['error' => $e->getMessage()]);
		} catch (\Throwable $e) {
			error_log((string)$e);
			header('Content-Type: application/json; charset=utf-8');
			http_response_code(500);
			echo json_encode(['error' => 'Internal server error']);
		}
		break;
}
