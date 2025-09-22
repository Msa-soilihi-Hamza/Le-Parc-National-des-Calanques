<?php

declare(strict_types=1);

namespace ParcCalanques\Core;

class ApiResponse
{
    /**
     * Envoie une réponse JSON de succès
     */
    public static function success(array $data = [], int $status = 200): void
    {
        self::send([
            'success' => true,
            ...$data
        ], $status);
    }

    /**
     * Envoie une réponse JSON d'erreur
     */
    public static function error(string $message, int $status = 400, array $data = []): void
    {
        self::send([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $status,
                ...$data
            ]
        ], $status);
    }

    /**
     * Envoie une réponse JSON de validation échouée
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): void
    {
        self::send([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => 422,
                'validation_errors' => $errors
            ]
        ], 422);
    }

    /**
     * Envoie une réponse JSON paginée
     */
    public static function paginated(array $items, int $total, int $page, int $perPage, array $meta = []): void
    {
        $totalPages = (int) ceil($total / $perPage);
        
        self::success([
            'data' => $items,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1,
                ...$meta
            ]
        ]);
    }

    /**
     * Envoie une réponse JSON créée (201)
     */
    public static function created(array $data = [], string $message = 'Resource created successfully'): void
    {
        self::success([
            'message' => $message,
            ...$data
        ], 201);
    }

    /**
     * Envoie une réponse JSON mise à jour (200)
     */
    public static function updated(array $data = [], string $message = 'Resource updated successfully'): void
    {
        self::success([
            'message' => $message,
            ...$data
        ]);
    }

    /**
     * Envoie une réponse JSON supprimée (200)
     */
    public static function deleted(string $message = 'Resource deleted successfully'): void
    {
        self::success([
            'message' => $message
        ]);
    }

    /**
     * Envoie une réponse 404
     */
    public static function notFound(string $message = 'Resource not found'): void
    {
        self::error($message, 404);
    }

    /**
     * Envoie une réponse 401
     */
    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, 401);
    }

    /**
     * Envoie une réponse 403
     */
    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, 403);
    }

    /**
     * Envoie une réponse JSON générique
     */
    private static function send(array $data, int $status): void
    {
        // Ne pas envoyer les headers si déjà envoyés
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            
            // Headers de sécurité
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');

            // Content Security Policy pour prévenir les attaques XSS
            $csp = "default-src 'self'; " .
                   "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
                   "style-src 'self' 'unsafe-inline'; " .
                   "img-src 'self' data: https:; " .
                   "font-src 'self' https:; " .
                   "connect-src 'self' http://localhost:8000 http://localhost:3004; " .
                   "frame-ancestors 'none'; " .
                   "base-uri 'self'; " .
                   "form-action 'self'";
            header('Content-Security-Policy: ' . $csp);

            // Référence des headers de sécurité supplémentaires
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        }

        // Ajouter timestamp et environnement en développement
        if (($_ENV['APP_ENV'] ?? 'production') !== 'production') {
            $data['_meta'] = [
                'timestamp' => date('c'),
                'execution_time' => round((microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? 0)) * 1000, 2) . 'ms',
                'memory_usage' => self::formatBytes(memory_get_peak_usage(true))
            ];
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}