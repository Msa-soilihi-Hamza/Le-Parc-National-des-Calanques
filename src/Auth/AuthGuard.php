<?php

declare(strict_types=1);

namespace ParcCalanques\Auth;

use ParcCalanques\Models\User;

class AuthGuard
{
    private static ?AuthService $authService = null;
    private static ?AuthMiddleware $middleware = null;

    public static function init(AuthService $authService): void
    {
        self::$authService = $authService;
        self::$middleware = new AuthMiddleware($authService);
    }

    public static function user(): ?User
    {
        return self::$authService?->getCurrentUser();
    }

    public static function check(): bool
    {
        return self::$authService?->isAuthenticated() ?? false;
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function id(): ?int
    {
        return self::user()?->getId();
    }

    public static function role(): ?string
    {
        return self::user()?->getRole();
    }

    public static function isAdmin(): bool
    {
        return self::user()?->isAdmin() ?? false;
    }

    public static function isUser(): bool
    {
        return self::user()?->isUser() ?? false;
    }

    public static function hasRole(string $role): bool
    {
        return self::role() === $role;
    }

    public static function can(string $permission, ...$args): bool
    {
        $user = self::user();
        
        if (!$user) {
            return false;
        }

        return match($permission) {
            'view_admin_panel' => $user->isAdmin(),
            'manage_users' => $user->isAdmin(),
            'view_user_profile' => $user->isUser() || $user->isAdmin(),
            'edit_own_profile' => true,
            'edit_user_profile' => $user->isAdmin() || ($args[0] ?? null) === $user->getId(),
            'delete_user' => $user->isAdmin(),
            default => false
        };
    }

    public static function cannot(string $permission, ...$args): bool
    {
        return !self::can($permission, ...$args);
    }

    public static function require(): User
    {
        if (!self::$middleware) {
            throw new \RuntimeException('AuthGuard not initialized. Call AuthGuard::init() first.');
        }
        
        return self::$middleware->requireAuthentication();
    }

    public static function requireRole(string $role): User
    {
        if (!self::$middleware) {
            throw new \RuntimeException('AuthGuard not initialized. Call AuthGuard::init() first.');
        }
        
        return self::$middleware->requireRole($role);
    }

    public static function requireAdmin(): User
    {
        if (!self::$middleware) {
            throw new \RuntimeException('AuthGuard not initialized. Call AuthGuard::init() first.');
        }
        
        return self::$middleware->requireAdmin();
    }

    public static function requireGuest(): void
    {
        if (!self::$middleware) {
            throw new \RuntimeException('AuthGuard not initialized. Call AuthGuard::init() first.');
        }
        
        self::$middleware->requireGuest();
    }

    public static function middleware(): AuthMiddleware
    {
        if (!self::$middleware) {
            throw new \RuntimeException('AuthGuard not initialized. Call AuthGuard::init() first.');
        }
        
        return self::$middleware;
    }

    public static function attempt(string $email, string $password, bool $remember = false): bool
    {
        if (!self::$authService) {
            return false;
        }

        try {
            self::$authService->login($email, $password, $remember);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function login(User $user): void
    {
        if (self::$authService) {
            // Créer une session pour l'utilisateur donné
            $sessionManager = new \ReflectionProperty(self::$authService, 'sessionManager');
            $sessionManager->setAccessible(true);
            $sessionManager->getValue(self::$authService)->createSession($user);
        }
    }

    public static function logout(): void
    {
        self::$authService?->logout();
    }

    public static function getAuthService(): ?AuthService
    {
        return self::$authService;
    }
}