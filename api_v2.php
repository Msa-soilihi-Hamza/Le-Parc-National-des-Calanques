<?php

declare(strict_types=1);

// Point d'entrée API moderne avec Router
require_once 'autoload.php';

use ParcCalanques\Core\Router;
use ParcCalanques\Auth\AuthBootstrap;
use ParcCalanques\Controllers\Api\AuthApiController;
use ParcCalanques\Controllers\Api\UserApiController;
use ParcCalanques\Controllers\Api\HealthApiController;

try {
    // Initialiser l'authentification
    AuthBootstrap::init();
    
    // Créer le router
    $router = new Router();
    
    // ===== ROUTES PUBLIQUES =====
    
    // Health check
    $router->get('/api/health', function() {
        $controller = new HealthApiController();
        $controller->check();
    });
    
    // ===== ROUTES D'AUTHENTIFICATION =====
    
    $router->group('/api/auth', function(Router $router) {
        // Routes publiques d'auth
        $router->post('/login', 'ParcCalanques\\Controllers\\Api\\AuthApiController@login');
        $router->post('/refresh', 'ParcCalanques\\Controllers\\Api\\AuthApiController@refresh');
        $router->post('/validate', 'ParcCalanques\\Controllers\\Api\\AuthApiController@validateToken');
        
        // Routes protégées d'auth
        $router->get('/me', 'ParcCalanques\\Controllers\\Api\\AuthApiController@me', ['auth']);
        $router->post('/logout', 'ParcCalanques\\Controllers\\Api\\AuthApiController@logout', ['auth']);
    });
    
    // ===== ROUTES UTILISATEURS =====
    
    $router->group('/api/users', function(Router $router) {
        // Profil utilisateur
        $router->get('/profile', 'ParcCalanques\\Controllers\\Api\\UserApiController@getProfile', ['auth']);
        $router->put('/profile', 'ParcCalanques\\Controllers\\Api\\UserApiController@updateProfile', ['auth']);
        
        // Liste des utilisateurs (admin seulement)
        $router->get('/', 'ParcCalanques\\Controllers\\Api\\UserApiController@list', ['auth', 'admin']);
        $router->get('/{id}', 'ParcCalanques\\Controllers\\Api\\UserApiController@show', ['auth', 'admin']);
        $router->put('/{id}', 'ParcCalanques\\Controllers\\Api\\UserApiController@update', ['auth', 'admin']);
        $router->delete('/{id}', 'ParcCalanques\\Controllers\\Api\\UserApiController@delete', ['auth', 'admin']);
    });
    
    // ===== ROUTES MÉTIER (à ajouter progressivement) =====
    
    // $router->group('/api/zones', function(Router $router) {
    //     $router->get('/', 'ParcCalanques\\Controllers\\Api\\ZoneApiController@list');
    //     $router->get('/{id}', 'ParcCalanques\\Controllers\\Api\\ZoneApiController@show');
    // });
    
    // $router->group('/api/reservations', function(Router $router) {
    //     $router->get('/', 'ParcCalanques\\Controllers\\Api\\ReservationApiController@list', ['auth']);
    //     $router->post('/', 'ParcCalanques\\Controllers\\Api\\ReservationApiController@create', ['auth']);
    // }, ['auth']);
    
    // ===== DISPATCH =====
    
    // Extraire le chemin de la requête
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    // Supprimer les paramètres GET et normaliser le chemin
    $path = parse_url($requestUri, PHP_URL_PATH);
    
    // Supprimer le chemin de base si nécessaire
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    if ($basePath !== '/' && strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }
    
    // Supprimer /api_v2.php du chemin
    if (strpos($path, '/api_v2.php') === 0) {
        $path = substr($path, strlen('/api_v2.php'));
        if (empty($path)) {
            $path = '/api/health'; // Redirection par défaut
        }
    }
    
    // Dispatcher la requête
    $router->dispatch($requestMethod, $path);
    
} catch (\Throwable $e) {
    // Gestion globale des erreurs
    http_response_code($e->getCode() ?: 500);
    header('Content-Type: application/json; charset=utf-8');
    
    $response = [
        'success' => false,
        'error' => [
            'message' => $e->getMessage(),
            'code' => $e->getCode() ?: 500
        ]
    ];
    
    // En développement, ajouter plus de détails
    if (($_ENV['APP_ENV'] ?? 'production') !== 'production') {
        $response['error']['debug'] = [
            'type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    error_log("API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
}