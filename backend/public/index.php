<?php

declare(strict_types=1);

// Configuration pour éviter l'affichage des erreurs PHP qui cassent le JSON
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Configuration CORS pour permettre les requêtes depuis React
header('Access-Control-Allow-Origin: http://localhost:3004');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept');
header('Access-Control-Allow-Credentials: true');

// Gérer les requêtes preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
use ParcCalanques\Sentiers\SentierBootstrap;
use ParcCalanques\Shared\Exceptions\AuthException;

try {
    // Initialize systems
    AuthBootstrap::init();
    SentierBootstrap::init();
    $apiController = AuthBootstrap::apiController();
    $sentierController = SentierBootstrap::getSentierController();

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

        case '/api/auth/verify-email':
        case '/auth/verify-email':
            if ($requestMethod === 'GET') {
                $apiController->verifyEmail();
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

        // Routes Sentiers
        case '/api/sentiers':
        case '/sentiers':
            if ($requestMethod === 'GET') {
                $sentierController->index(new \ParcCalanques\Core\Request());
            }
            break;

        case '/api/sentiers/filters':
        case '/sentiers/filters':
            if ($requestMethod === 'GET') {
                $sentierController->filters(new \ParcCalanques\Core\Request());
            }
            break;

        case '/api/sentiers/stats':
        case '/sentiers/stats':
            if ($requestMethod === 'GET') {
                $sentierController->stats(new \ParcCalanques\Core\Request());
            }
            break;

        default:
            // Gérer les routes avec paramètres (ex: /api/sentiers/{id})
            if (preg_match('#^/api/sentiers/(\d+)$#', $path, $matches) || preg_match('#^/sentiers/(\d+)$#', $path, $matches)) {
                if ($requestMethod === 'GET') {
                    $request = new \ParcCalanques\Core\Request();
                    $request->setRouteParam('id', $matches[1]);
                    $sentierController->show($request);
                    break;
                }
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