<?php

declare(strict_types=1);

require_once 'autoload.php';

use ParcCalanques\Auth\AuthBootstrap;
use ParcCalanques\Controllers\AuthController;
use ParcCalanques\Auth\AuthGuard;

// Helper function to generate correct URLs
function url($path = '/') {
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    if ($basePath === '/') {
        return $path;
    }
    return $basePath . $path;
}

try {
    // Initialize authentication system
    $authService = AuthBootstrap::init();
    $authController = new AuthController($authService);

    // Simple routing based on REQUEST_URI
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($requestUri, PHP_URL_PATH);
    
    // Remove base path if running in subdirectory
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    if ($scriptName !== '/' && strpos($path, $scriptName) === 0) {
        $path = substr($path, strlen($scriptName));
    }

    // Remove trailing slash
    $path = rtrim($path, '/') ?: '/';

    switch ($path) {
        case '/':
            // Homepage - show different content based on auth status
            if (AuthGuard::check()) {
                header('Location: ' . url('/dashboard'));
            } else {
                header('Location: ' . url('/login'));
            }
            exit;

        case '/login':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $authController->login();
            } else {
                $authController->showLogin();
            }
            break;

        case '/register':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $authController->register();
            } else {
                $authController->showRegister();
            }
            break;

        case '/logout':
            $authController->logout();
            break;

        case '/dashboard':
            $authController->dashboard();
            break;

        case '/admin':
            $authController->adminPanel();
            break;

        case '/profile':
            $authController->profile();
            break;

        case '/api/auth/check':
            header('Content-Type: application/json');
            echo json_encode([
                'authenticated' => AuthGuard::check(),
                'user' => AuthGuard::user()?->toArray()
            ]);
            break;

        case '/api/auth/user':
            header('Content-Type: application/json');
            $user = AuthGuard::user();
            if ($user) {
                echo json_encode($user->toArray());
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Not authenticated']);
            }
            break;

        default:
            // 404 Not Found
            http_response_code(404);
            echo "<!DOCTYPE html>
            <html>
            <head>
                <title>Page non trouvée</title>
                <meta charset='utf-8'>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; margin-top: 100px; }
                    .error-container { max-width: 600px; margin: 0 auto; }
                    .error-code { font-size: 72px; color: #e74c3c; }
                    .error-message { font-size: 24px; color: #333; margin: 20px 0; }
                    .back-link { color: #3498db; text-decoration: none; }
                </style>
            </head>
            <body>
                <div class='error-container'>
                    <div class='error-code'>404</div>
                    <div class='error-message'>Page non trouvée</div>
                    <a href='/' class='back-link'>Retour à l'accueil</a>
                </div>
            </body>
            </html>";
            break;
    }

} catch (Exception $e) {
    // Error handling
    error_log('Application error: ' . $e->getMessage());
    
    http_response_code(500);
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Erreur serveur</title>
        <meta charset='utf-8'>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; margin-top: 100px; }
            .error-container { max-width: 600px; margin: 0 auto; }
            .error-code { font-size: 72px; color: #e74c3c; }
            .error-message { font-size: 24px; color: #333; margin: 20px 0; }
            .error-details { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: left; }
            .back-link { color: #3498db; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class='error-container'>
            <div class='error-code'>500</div>
            <div class='error-message'>Erreur interne du serveur</div>";
    
    if (ini_get('display_errors')) {
        echo "<div class='error-details'>
                <strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "<br>
                <strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "
              </div>";
    }
    
    echo "    <a href='/' class='back-link'>Retour à l'accueil</a>
        </div>
    </body>
    </html>";
}