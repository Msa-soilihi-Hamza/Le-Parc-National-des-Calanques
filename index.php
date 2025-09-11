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
                header('Location: ' . url('/profile'));
            } else {
                header('Location: ' . url('/login'));
            }
            exit;

        case '/login':
            // Version React uniquement
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $authController->login(); // Garde la même logique de connexion
            } else {
                // Affiche la version React
                $basePath = dirname($_SERVER['SCRIPT_NAME']);
                if ($basePath === '/') {
                    $basePath = '';
                }
                $GLOBALS['basePath'] = $basePath;
                
                include 'templates/auth/login.php';
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


        case '/profile':
            // Version React uniquement
            if (!AuthGuard::check()) {
                header('Location: ' . url('/login'));
                exit;
            }
            
            $user = AuthGuard::user();
            $isLoggedIn = true;
            $userRole = $user->getRole();
            $userName = $user->getFullName();
            
            include 'templates/profile.php';
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
            <html lang='fr' data-theme='parc'>
            <head>
                <title>Page non trouvée</title>
                <meta charset='utf-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <link href='" . url('/public/css/output.css') . "' rel='stylesheet'>
            </head>
            <body class='bg-base-100 min-h-screen font-sans'>
                <div class='hero min-h-screen bg-base-200'>
                    <div class='hero-content text-center'>
                        <div class='max-w-md'>
                            <h1 class='text-9xl font-bold text-error'>404</h1>
                            <p class='text-2xl font-semibold text-base-content py-6'>Page non trouvée</p>
                            <p class='text-base-content/70 mb-8'>Désolé, la page que vous recherchez n'existe pas.</p>
                            <a href='" . url('/') . "' class='btn btn-primary'>Retour à l'accueil</a>
                        </div>
                    </div>
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
    <html lang='fr' data-theme='parc'>
    <head>
        <title>Erreur serveur</title>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <link href='" . url('/public/css/output.css') . "' rel='stylesheet'>
    </head>
    <body class='bg-base-100 min-h-screen font-sans'>
        <div class='hero min-h-screen bg-base-200'>
            <div class='hero-content text-center'>
                <div class='max-w-2xl'>
                    <h1 class='text-9xl font-bold text-error'>500</h1>
                    <p class='text-2xl font-semibold text-base-content py-6'>Erreur interne du serveur</p>
                    <p class='text-base-content/70 mb-8'>Une erreur inattendue s'est produite. Veuillez réessayer plus tard.</p>";
    
    if (ini_get('display_errors')) {
        echo "<div class='card bg-base-100 shadow-lg p-6 mb-8 text-left'>
                <h3 class='font-bold text-lg mb-4'>Détails de l'erreur :</h3>
                <p class='mb-2'><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <p><strong>Fichier:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>
              </div>";
    }
    
    echo "          <a href='" . url('/') . "' class='btn btn-primary'>Retour à l'accueil</a>
                </div>
            </div>
        </div>
    </body>
    </html>";
}