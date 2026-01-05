<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Initialize Router
$router = new Router();

// Define Routes
$router->group('/auth', function($router) {
    $router->post('/register', [AuthController::class, 'register']);
    $router->post('/login', [AuthController::class, 'login']);
});

// Protected Route
$router->get('/protected', function() {
    $middleware = new AuthMiddleware();
    $user = $middleware->handle();
    
    header('Content-Type: application/json');
    echo json_encode([
        'message' => 'Access granted to protected route',
        'user' => $user
    ]);
});

// Raw Query Route (Protected)
$router->post('/query', function() {
    $middleware = new AuthMiddleware();
    $middleware->handle(); // Verify token
    
    $controller = new \App\Controllers\QueryController();
    $controller->execute();
});

// Dispatch
$router->dispatch();
