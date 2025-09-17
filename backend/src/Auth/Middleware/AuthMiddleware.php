<?php

declare(strict_types=1);

namespace ParcCalanques\Auth\Middleware;

use ParcCalanques\Auth\Models\User;
use ParcCalanques\Shared\Exceptions\AuthException;
use ParcCalanques\Auth\Services\AuthService;

class AuthMiddleware
{
    public function __construct(private AuthService $authService) {}

    public function requireAuthentication(): User
    {
        try {
            return $this->authService->requireAuthentication();
        } catch (AuthException $e) {
            $this->handleUnauthorized($e->getMessage());
        }
    }

    public function requireRole(string $role): User
    {
        try {
            return $this->authService->requireRole($role);
        } catch (AuthException $e) {
            $this->handleForbidden($e->getMessage());
        }
    }

    public function requireAdmin(): User
    {
        try {
            return $this->authService->requireAdmin();
        } catch (AuthException $e) {
            $this->handleForbidden($e->getMessage());
        }
    }

    public function requireGuest(): void
    {
        if ($this->authService->isAuthenticated()) {
            $this->redirectToHome();
        }
    }

    public function optionalAuth(): ?User
    {
        return $this->authService->getCurrentUser();
    }

    public function requireEmailVerified(): User
    {
        $user = $this->requireAuthentication();
        
        if (!$user->isEmailVerified()) {
            $this->handleEmailNotVerified();
        }

        return $user;
    }

    public function requireActiveUser(): User
    {
        $user = $this->requireAuthentication();
        
        if (!$user->isActive()) {
            $this->handleUserInactive();
        }

        return $user;
    }

    private function handleUnauthorized(string $message = 'Authentication required'): never
    {
        if ($this->isApiRequest()) {
            $this->sendJsonResponse(['error' => $message], 401);
        } else {
            $this->redirectToLogin();
        }
        exit;
    }

    private function handleForbidden(string $message = 'Insufficient privileges'): never
    {
        if ($this->isApiRequest()) {
            $this->sendJsonResponse(['error' => $message], 403);
        } else {
            $this->showErrorPage(403, $message);
        }
        exit;
    }

    private function handleEmailNotVerified(): never
    {
        if ($this->isApiRequest()) {
            $this->sendJsonResponse(['error' => 'Email verification required'], 403);
        } else {
            header('Location: /verify-email');
        }
        exit;
    }

    private function handleUserInactive(): never
    {
        if ($this->isApiRequest()) {
            $this->sendJsonResponse(['error' => 'Account is inactive'], 403);
        } else {
            $this->showErrorPage(403, 'Your account has been deactivated');
        }
        exit;
    }

    private function redirectToLogin(): void
    {
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
        $loginUrl = '/login';
        
        if ($currentUrl !== '/login') {
            $loginUrl .= '?redirect=' . urlencode($currentUrl);
        }

        header('Location: ' . $loginUrl);
    }

    private function redirectToHome(): void
    {
        header('Location: /');
    }

    private function isApiRequest(): bool
    {
        // Check if it's an API request based on Accept header or URL pattern
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        
        return strpos($acceptHeader, 'application/json') !== false ||
               strpos($requestUri, '/api/') !== false ||
               (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
    }

    private function sendJsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function showErrorPage(int $code, string $message): void
    {
        http_response_code($code);
        
        // Attempt to load error template
        $errorTemplate = __DIR__ . "/../../templates/errors/{$code}.php";
        if (file_exists($errorTemplate)) {
            include $errorTemplate;
        } else {
            // Fallback error page
            echo "<!DOCTYPE html>
            <html>
            <head>
                <title>Erreur {$code}</title>
                <meta charset='utf-8'>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; }
                    .error-container { max-width: 600px; margin: 0 auto; }
                    .error-code { font-size: 72px; color: #e74c3c; margin-bottom: 20px; }
                    .error-message { font-size: 24px; color: #333; margin-bottom: 30px; }
                    .back-link { color: #3498db; text-decoration: none; }
                    .back-link:hover { text-decoration: underline; }
                </style>
            </head>
            <body>
                <div class='error-container'>
                    <div class='error-code'>{$code}</div>
                    <div class='error-message'>{$message}</div>
                    <a href='/' class='back-link'>Retour à l'accueil</a>
                </div>
            </body>
            </html>";
        }
    }

    public function checkPermissions(array $allowedRoles = [], bool $requireAuth = true): ?User
    {
        $user = null;

        if ($requireAuth) {
            $user = $this->requireAuthentication();
        } else {
            $user = $this->optionalAuth();
        }

        if ($user && !empty($allowedRoles) && !in_array($user->getRole(), $allowedRoles)) {
            $this->handleForbidden('Access denied for your role');
        }

        return $user;
    }
}