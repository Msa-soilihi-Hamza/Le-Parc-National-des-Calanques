<?php

declare(strict_types=1);

// Configuration pour éviter l'affichage des erreurs PHP qui cassent le JSON
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Gestionnaire d'erreurs personnalisé
set_error_handler(function($severity, $message, $file, $line) {
    error_log("PHP Error: $message in $file on line $line");
    return true;
});

// Test simple si paramètre test=1
if (isset($_GET['test']) && $_GET['test'] == '1') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'Backend API is working',
        'php_version' => PHP_VERSION,
        'timestamp' => date('Y-m-d H:i:s'),
        'structure' => 'New Backend Structure'
    ]);
    exit;
}

// Charger l'autoloader et bootstrap depuis la racine du projet
require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../config/bootstrap.php';

use ParcCalanques\Auth\AuthBootstrap;
use ParcCalanques\Shared\Exceptions\AuthException;
use ParcCalanques\Admin\Controllers\UserController;
use ParcCalanques\Admin\Services\UserManagementService;
use ParcCalanques\Admin\Middleware\AdminMiddleware;
use ParcCalanques\Auth\Models\UserRepository;
use ParcCalanques\Auth\Services\JwtService;

require_once __DIR__ . '/../config/database.php';

function handleAdminRoutes($path, $method) {

    try {
        $database = new Database();
        $pdo = $database->getConnection();
        $userRepository = new UserRepository($pdo);
        $userManagementService = new UserManagementService($userRepository);
        $adminMiddleware = new AdminMiddleware();
        $userController = new UserController($userManagementService, $adminMiddleware);
        $jwtService = new JwtService();

        $getCurrentUser = function() use ($jwtService, $userRepository) {
            $token = null;
            $headers = getallheaders();

            if (isset($headers['Authorization'])) {
                $authHeader = $headers['Authorization'];
                if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                    $token = $matches[1];
                }
            }

            if (!$token) {
                return null;
            }

            try {
                $payload = $jwtService->validateToken($token);
                return $userRepository->findById($payload['user_id']);
            } catch (Exception $e) {
                return null;
            }
        };

        $currentUser = $getCurrentUser();

        // Router les différentes routes admin
        $normalizedPath = str_replace(['/api/admin', '/admin'], '', $path);

        switch (true) {
            case $normalizedPath === '/users' && $method === 'GET':
                $response = $userController->index($currentUser);
                http_response_code($response['success'] ? 200 : 400);
                echo json_encode($response);
                break;

            case preg_match('#^/users/(\d+)$#', $normalizedPath, $matches) && $method === 'GET':
                $response = $userController->show((int)$matches[1], $currentUser);
                http_response_code($response['success'] ? 200 : ($response['message'] === 'User not found' ? 404 : 400));
                echo json_encode($response);
                break;

            case $normalizedPath === '/users' && $method === 'POST':
                $response = $userController->create($currentUser);
                http_response_code($response['success'] ? 201 : 400);
                echo json_encode($response);
                break;

            case preg_match('#^/users/(\d+)$#', $normalizedPath, $matches) && $method === 'PUT':
                $response = $userController->update((int)$matches[1], $currentUser);
                http_response_code($response['success'] ? 200 : ($response['message'] === 'User not found' ? 404 : 400));
                echo json_encode($response);
                break;

            case preg_match('#^/users/(\d+)$#', $normalizedPath, $matches) && $method === 'DELETE':
                $response = $userController->delete((int)$matches[1], $currentUser);
                http_response_code($response['success'] ? 200 : ($response['message'] === 'User not found' ? 404 : 400));
                echo json_encode($response);
                break;

            case preg_match('#^/users/(\d+)/activate$#', $normalizedPath, $matches) && $method === 'PATCH':
                $response = $userController->activate((int)$matches[1], $currentUser);
                http_response_code($response['success'] ? 200 : ($response['message'] === 'User not found' ? 404 : 400));
                echo json_encode($response);
                break;

            case preg_match('#^/users/(\d+)/deactivate$#', $normalizedPath, $matches) && $method === 'PATCH':
                $response = $userController->deactivate((int)$matches[1], $currentUser);
                http_response_code($response['success'] ? 200 : ($response['message'] === 'User not found' ? 404 : 400));
                echo json_encode($response);
                break;

            default:
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode([
                    'error' => 'Admin route not found',
                    'path' => $path,
                    'method' => $method
                ]);
                break;
        }

    } catch (Exception $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error: ' . $e->getMessage(),
            'data' => null
        ]);
    }
}

try {
    // Initialize authentication system
    AuthBootstrap::init();
    $apiController = AuthBootstrap::apiController();

    // Get the request path and method
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    // Remove query string and base path
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    if ($basePath !== '/') {
        $requestUri = str_replace($basePath, '', $requestUri);
    }
    $path = strtok($requestUri, '?');

    // Nettoyer le path pour extraire la vraie route API
    if (strpos($path, '/backend/public/index.php') !== false) {
        $path = str_replace('/backend/public/index.php', '', $path);
    }

    // Supprimer /index.php si présent
    if (strpos($path, '/index.php') !== false) {
        $path = str_replace('/index.php', '', $path);
    }

    // Si pas de path ou path vide, défaut vers /api
    if (empty($path) || $path === '/') {
        $path = '/api';
    }

    // Route the API requests - Support both /api/route and /route for compatibility
    switch ($path) {
        case '/api/auth/login':
        case '/auth/login':
            if ($requestMethod === 'POST') {
                $apiController->login();
            }
            break;

        case '/api/auth/refresh':
        case '/auth/refresh':
            if ($requestMethod === 'POST') {
                $apiController->refresh();
            }
            break;

        case '/api/auth/me':
        case '/auth/me':
            if ($requestMethod === 'GET') {
                $apiController->me();
            }
            break;

        case '/api/auth/logout':
        case '/auth/logout':
            if ($requestMethod === 'POST') {
                $apiController->logout();
            }
            break;

        case '/api/auth/register':
        case '/auth/register':
            if ($requestMethod === 'POST') {
                $apiController->register();
            }
            break;

        case '/api/auth/validate':
        case '/auth/validate':
            if ($requestMethod === 'POST') {
                $apiController->validateToken();
            }
            break;

        case '/api/profile':
        case '/profile':
            if (in_array($requestMethod, ['GET', 'PUT'])) {
                $apiController->profile();
            }
            break;

        case '/api/users':
        case '/users':
            if ($requestMethod === 'GET') {
                $apiController->users();
            }
            break;

        case '/api/health':
        case '/health':
            if ($requestMethod === 'GET') {
                $apiController->health();
            }
            break;

        default:
            // Check for admin routes
            if (preg_match('#^/api/admin(/.*)?$#', $path, $matches) || preg_match('#^/admin(/.*)?$#', $path, $matches)) {
                handleAdminRoutes($path, $requestMethod);
                break;
            }
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Not Found',
                'message' => 'Backend API endpoint not found',
                'code' => 404,
                'path' => $path,
                'method' => $requestMethod,
                'info' => 'Using new backend structure'
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

    // Log the error
    error_log('Backend API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
}