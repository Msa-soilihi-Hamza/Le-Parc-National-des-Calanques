<?php

declare(strict_types=1);

namespace ParcCalanques\Core\Services;

class CsrfService
{
    private const TOKEN_NAME = 'csrf_token';
    private const TOKEN_LENGTH = 32;

    /**
     * Génère un nouveau token CSRF
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::TOKEN_NAME] = $token;

        return $token;
    }

    /**
     * Valide un token CSRF
     */
    public static function validateToken(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }

        $isValid = hash_equals($_SESSION[self::TOKEN_NAME], $token);

        // Régénérer le token après validation pour sécurité
        if ($isValid) {
            unset($_SESSION[self::TOKEN_NAME]);
        }

        return $isValid;
    }

    /**
     * Récupère le token CSRF actuel
     */
    public static function getToken(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION[self::TOKEN_NAME] ?? null;
    }

    /**
     * Middleware pour valider les requêtes avec CSRF
     */
    public static function validateRequest(): bool
    {
        // Exemples de méthodes qui nécessitent la protection CSRF
        $protectedMethods = ['POST', 'PUT', 'DELETE', 'PATCH'];

        if (!in_array($_SERVER['REQUEST_METHOD'], $protectedMethods)) {
            return true; // GET et OPTIONS n'ont pas besoin de CSRF
        }

        // Pour les requêtes API JSON, chercher le token dans les headers
        $token = null;

        // 1. Vérifier dans les headers
        if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        // 2. Vérifier dans le body JSON pour les API
        elseif (isset($_SERVER['CONTENT_TYPE']) &&
                str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {
            $input = json_decode(file_get_contents('php://input'), true);
            $token = $input['csrf_token'] ?? null;
        }
        // 3. Vérifier dans POST pour les formulaires classiques
        elseif (isset($_POST[self::TOKEN_NAME])) {
            $token = $_POST[self::TOKEN_NAME];
        }

        return $token ? self::validateToken($token) : false;
    }

    /**
     * Génère le HTML pour un champ input hidden avec le token CSRF
     */
    public static function getHiddenInput(): string
    {
        $token = self::getToken() ?: self::generateToken();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }
}