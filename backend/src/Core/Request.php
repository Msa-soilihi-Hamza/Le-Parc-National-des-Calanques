<?php

declare(strict_types=1);

namespace ParcCalanques\Core;

use ParcCalanques\Models\User;
use ParcCalanques\Auth\AuthBootstrap;
use ParcCalanques\Core\ApiResponse;

class Request
{
    private static ?array $jsonInput = null;
    private static ?User $authenticatedUser = null;
    private array $routeParams = [];
    private array $queryParams = [];

    public function __construct()
    {
        $this->queryParams = $_GET;
    }

    /**
     * Définit un paramètre de route
     */
    public function setRouteParam(string $key, $value): void
    {
        $this->routeParams[$key] = $value;
    }

    /**
     * Récupère un paramètre de route
     */
    public function getRouteParam(string $key, $default = null)
    {
        return $this->routeParams[$key] ?? $default;
    }

    /**
     * Récupère un paramètre de query string
     */
    public function getQueryParam(string $key, $default = null)
    {
        return $this->queryParams[$key] ?? $default;
    }

    /**
     * Récupère les données JSON du body de la requête
     */
    public static function getJsonInput(): array
    {
        if (self::$jsonInput === null) {
            $input = file_get_contents('php://input');
            self::$jsonInput = json_decode($input, true) ?? [];
        }
        
        return self::$jsonInput;
    }

    /**
     * Récupère une valeur des données JSON
     */
    public static function json(string $key, $default = null)
    {
        return self::getJsonInput()[$key] ?? $default;
    }

    /**
     * Récupère une valeur des paramètres GET
     */
    public static function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Récupère une valeur des paramètres POST
     */
    public static function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Récupère le token Bearer de l'header Authorization
     */
    public static function getBearerToken(): ?string
    {
        $header = self::getHeader('Authorization');
        
        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Récupère un header HTTP
     */
    public static function getHeader(string $name): ?string
    {
        $name = strtoupper(str_replace('-', '_', $name));
        return $_SERVER["HTTP_{$name}"] ?? null;
    }

    /**
     * Récupère la méthode HTTP
     */
    public static function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Récupère l'URL complète
     */
    public static function getUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Récupère l'IP du client
     */
    public static function getClientIp(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] 
            ?? $_SERVER['HTTP_CLIENT_IP'] 
            ?? $_SERVER['REMOTE_ADDR'] 
            ?? 'unknown';
    }

    /**
     * Récupère l'utilisateur authentifié
     */
    public static function getAuthenticatedUser(): User
    {
        if (self::$authenticatedUser === null) {
            $jwtMiddleware = AuthBootstrap::jwtMiddleware();
            self::$authenticatedUser = $jwtMiddleware->authenticate();
        }
        
        return self::$authenticatedUser;
    }

    /**
     * Vérifie si l'utilisateur est authentifié
     */
    public static function isAuthenticated(): bool
    {
        try {
            self::getAuthenticatedUser();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Vérifie si l'utilisateur a le rôle admin
     */
    public static function requireAdmin(): User
    {
        $jwtMiddleware = AuthBootstrap::jwtMiddleware();
        return $jwtMiddleware->requireAdmin();
    }

    /**
     * Valide les données selon des règles
     */
    public static function validate(array $data, array $rules): void
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $ruleList = is_string($rule) ? explode('|', $rule) : $rule;
            $value = $data[$field] ?? null;
            
            foreach ($ruleList as $singleRule) {
                $error = self::validateField($field, $value, $singleRule, $data);
                if ($error) {
                    $errors[$field] = $error;
                    break; // Premier erreur pour ce champ
                }
            }
        }

        if (!empty($errors)) {
            ApiResponse::validationError($errors);
        }
    }

    /**
     * Valide un champ selon une règle
     */
    private static function validateField(string $field, $value, string $rule, array $allData): ?string
    {
        // Extraire les paramètres de la règle (ex: min:6)
        $ruleParts = explode(':', $rule, 2);
        $ruleName = $ruleParts[0];
        $ruleParam = $ruleParts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                    return "Le champ {$field} est requis";
                }
                break;

            case 'email':
                if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "Le champ {$field} doit être un email valide";
                }
                break;

            case 'min':
                if ($value !== null) {
                    $length = is_string($value) ? strlen($value) : $value;
                    if ($length < (int)$ruleParam) {
                        return "Le champ {$field} doit avoir au minimum {$ruleParam} caractères";
                    }
                }
                break;

            case 'max':
                if ($value !== null) {
                    $length = is_string($value) ? strlen($value) : $value;
                    if ($length > (int)$ruleParam) {
                        return "Le champ {$field} doit avoir au maximum {$ruleParam} caractères";
                    }
                }
                break;

            case 'numeric':
                if ($value !== null && !is_numeric($value)) {
                    return "Le champ {$field} doit être numérique";
                }
                break;

            case 'int':
            case 'integer':
                if ($value !== null && !filter_var($value, FILTER_VALIDATE_INT)) {
                    return "Le champ {$field} doit être un entier";
                }
                break;

            case 'bool':
            case 'boolean':
                if ($value !== null && !is_bool($value) && !in_array($value, [0, 1, '0', '1', 'true', 'false'], true)) {
                    return "Le champ {$field} doit être un booléen";
                }
                break;

            case 'array':
                if ($value !== null && !is_array($value)) {
                    return "Le champ {$field} doit être un tableau";
                }
                break;

            case 'string':
                if ($value !== null && !is_string($value)) {
                    return "Le champ {$field} doit être une chaîne de caractères";
                }
                break;

            case 'in':
                $allowedValues = explode(',', $ruleParam);
                if ($value !== null && !in_array($value, $allowedValues, true)) {
                    $formatted = implode(', ', $allowedValues);
                    return "Le champ {$field} doit être l'une des valeurs suivantes: {$formatted}";
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($allData[$confirmField] ?? null)) {
                    return "Le champ {$field} ne correspond pas à sa confirmation";
                }
                break;

            case 'unique':
                // TODO: Implémenter la vérification d'unicité en base
                // Pour l'instant, on ignore cette règle
                break;

            default:
                throw new \InvalidArgumentException("Règle de validation inconnue: {$ruleName}");
        }

        return null;
    }

    /**
     * Nettoie et filtre les données d'entrée
     */
    public static function sanitize(array $data, array $rules = []): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (isset($rules[$key])) {
                $sanitized[$key] = self::sanitizeValue($value, $rules[$key]);
            } else {
                $sanitized[$key] = self::sanitizeValue($value, 'string');
            }
        }
        
        return $sanitized;
    }

    private static function sanitizeValue($value, string $type)
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'int':
            case 'integer':
                return (int) $value;
                
            case 'float':
                return (float) $value;
                
            case 'bool':
            case 'boolean':
                return (bool) $value;
                
            case 'email':
                return filter_var($value, FILTER_SANITIZE_EMAIL);
                
            case 'url':
                return filter_var($value, FILTER_SANITIZE_URL);
                
            case 'string':
            default:
                return is_string($value) ? trim(strip_tags($value)) : $value;
        }
    }
}