<?php

declare(strict_types=1);

// Test simple si paramètre test=1
if (isset($_GET['test']) && $_GET['test'] == '1') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'API is working',
        'php_version' => PHP_VERSION,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

require_once 'autoload.php';

use ParcCalanques\Auth\AuthBootstrap;
use ParcCalanques\Exceptions\AuthException;

try {
    // Initialize authentication system
    AuthBootstrap::init();
    $apiController = AuthBootstrap::apiController();

    // Get the request path and method
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    // Remove query string and base path if any
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    if ($basePath !== '/') {
        $requestUri = str_replace($basePath, '', $requestUri);
    }
    $path = strtok($requestUri, '?');
    
    // Route the API requests
    switch ($path) {
        case '/api/auth/login':
            if ($requestMethod === 'POST') {
                $apiController->login();
            }
            break;

        case '/api/auth/refresh':
            if ($requestMethod === 'POST') {
                $apiController->refresh();
            }
            break;

        case '/api/auth/me':
            if ($requestMethod === 'GET') {
                $apiController->me();
            }
            break;

        case '/api/auth/logout':
            if ($requestMethod === 'POST') {
                $apiController->logout();
            }
            break;

        case '/api/auth/validate':
            if ($requestMethod === 'POST') {
                $apiController->validateToken();
            }
            break;

        case '/api/profile':
            if (in_array($requestMethod, ['GET', 'PUT'])) {
                $apiController->profile();
            }
            break;

        case '/api/users':
            if ($requestMethod === 'GET') {
                $apiController->users();
            }
            break;

        case '/api/health':
            if ($requestMethod === 'GET') {
                $apiController->health();
            }
            break;

        default:
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Not Found',
                'message' => 'API endpoint not found',
                'code' => 404,
                'path' => $path,
                'method' => $requestMethod
            ]);
            break;
    }

} catch (AuthException $e) {
    http_response_code($e->getCode() ?: 500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Authentication Error',
        'message' => $e->getMessage(),
        'code' => $e->getCode() ?: 500
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => 'An unexpected error occurred',
        'code' => 500
    ]);
    
    // Log the error for debugging (in production)
    error_log('API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
}