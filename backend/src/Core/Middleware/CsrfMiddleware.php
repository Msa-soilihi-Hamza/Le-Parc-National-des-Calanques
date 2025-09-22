<?php

declare(strict_types=1);

namespace ParcCalanques\Core\Middleware;

use ParcCalanques\Core\Services\CsrfService;

class CsrfMiddleware
{
    /**
     * Routes exemptées de la protection CSRF (par exemple pour les APIs publiques)
     */
    private const EXEMPT_ROUTES = [
        '/api/health',
        '/api/auth/login',  // Première connexion
    ];

    /**
     * Valide la protection CSRF pour une requête
     */
    public static function validate(): bool
    {
        // Vérifier si la route actuelle est exemptée
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach (self::EXEMPT_ROUTES as $exemptRoute) {
            if (str_starts_with($currentPath, $exemptRoute)) {
                return true;
            }
        }

        // Valider le token CSRF
        return CsrfService::validateRequest();
    }

    /**
     * Middleware pour bloquer les requêtes sans token CSRF valide
     */
    public static function protect(): void
    {
        if (!self::validate()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'CSRF token validation failed',
                'error_code' => 'CSRF_INVALID'
            ]);
            exit;
        }
    }

    /**
     * Génère et retourne un nouveau token CSRF pour les APIs
     */
    public static function getTokenForApi(): array
    {
        $token = CsrfService::generateToken();
        return [
            'csrf_token' => $token,
            'token_name' => 'csrf_token'
        ];
    }
}