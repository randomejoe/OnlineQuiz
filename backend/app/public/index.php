<?php

require __DIR__ . '/../vendor/autoload.php';

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

$dispatcher = simpleDispatcher(function (RouteCollector $r) {
	// Routes are registered by parallel tasks; keep this file minimal for now.
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
	case FastRoute\Dispatcher::NOT_FOUND:
		http_response_code(404);
		echo 'Not Found';
		break;
	case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
		http_response_code(405);
		echo 'Method Not Allowed';
		break;
	case FastRoute\Dispatcher::FOUND:
		$handler = $routeInfo[1];
		$class = $handler[0];
		$method = $handler[1];
		$vars = $routeInfo[2];

		$controller = new $class();
		$controller->$method($vars);
		break;
}
