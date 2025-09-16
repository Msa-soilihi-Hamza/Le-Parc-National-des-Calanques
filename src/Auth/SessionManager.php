<?php

declare(strict_types=1);

namespace ParcCalanques\Auth;

use ParcCalanques\Models\User;
use ParcCalanques\Models\UserRepository;
use ParcCalanques\Exceptions\AuthException;

class SessionManager
{
    private const SESSION_KEY_USER_ID = 'user_id';
    private const SESSION_KEY_USER_ROLE = 'user_role';
    private const SESSION_KEY_LAST_ACTIVITY = 'last_activity';
    private const SESSION_KEY_IP_ADDRESS = 'ip_address';
    private const SESSION_KEY_USER_AGENT = 'user_agent';
    
    private const SESSION_TIMEOUT = 7200; // 2 heures
    
    public function __construct(private UserRepository $userRepository) 
    {
        $this->startSession();
    }

    public function createSession(User $user): void
    {
        $this->regenerateSession();
        
        $_SESSION[self::SESSION_KEY_USER_ID] = $user->getId();
        $_SESSION[self::SESSION_KEY_USER_ROLE] = $user->getRole();
        $_SESSION[self::SESSION_KEY_LAST_ACTIVITY] = time();
        $_SESSION[self::SESSION_KEY_IP_ADDRESS] = $this->getClientIp();
        $_SESSION[self::SESSION_KEY_USER_AGENT] = $this->getUserAgent();
    }

    public function destroySession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(
                session_name(),
                '',
                time() - 3600,
                '/',
                '',
                true,
                true
            );
        }
    }

    public function getCurrentUser(): ?User
    {
        if (!$this->isValidSession()) {
            return null;
        }

        $userId = $_SESSION[self::SESSION_KEY_USER_ID] ?? null;
        
        if (!$userId) {
            return null;
        }

        $user = $this->userRepository->findById((int) $userId);
        
        if (!$user || !$user->isActive()) {
            $this->destroySession();
            return null;
        }

        $this->updateLastActivity();
        
        return $user;
    }

    public function isAuthenticated(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    public function getUserRole(): ?string
    {
        return $_SESSION[self::SESSION_KEY_USER_ROLE] ?? null;
    }

    public function hasRole(string $role): bool
    {
        return $this->getUserRole() === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(User::ROLE_ADMIN);
    }

    public function regenerateSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public function getSessionId(): string
    {
        return session_id();
    }

    public function getLastActivity(): ?int
    {
        return $_SESSION[self::SESSION_KEY_LAST_ACTIVITY] ?? null;
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configuration sécurisée de la session (seulement si pas en CLI)
            if (php_sapi_name() !== 'cli' && !headers_sent()) {
                ini_set('session.cookie_httponly', '1');
                ini_set('session.cookie_secure', '1');
                ini_set('session.cookie_samesite', 'Strict');
                ini_set('session.use_strict_mode', '1');
                ini_set('session.gc_maxlifetime', (string) self::SESSION_TIMEOUT);
            }
            
            // Démarrer la session seulement si pas en CLI
            if (php_sapi_name() !== 'cli') {
                session_start();
            }
        }
    }

    private function isValidSession(): bool
    {
        if (empty($_SESSION[self::SESSION_KEY_USER_ID])) {
            return false;
        }

        // Vérifier l'expiration de la session
        $lastActivity = $_SESSION[self::SESSION_KEY_LAST_ACTIVITY] ?? 0;
        if (time() - $lastActivity > self::SESSION_TIMEOUT) {
            $this->destroySession();
            return false;
        }

        // Vérifier la cohérence de l'adresse IP (optionnel, peut causer des problèmes avec certains proxies)
        $sessionIp = $_SESSION[self::SESSION_KEY_IP_ADDRESS] ?? '';
        $currentIp = $this->getClientIp();
        if ($sessionIp !== $currentIp) {
            // Log suspicious activity
            error_log("Session IP mismatch: session IP {$sessionIp}, current IP {$currentIp}");
            // Uncomment next line for strict IP validation
            // $this->destroySession();
            // return false;
        }

        // Vérifier la cohérence du User-Agent
        $sessionUserAgent = $_SESSION[self::SESSION_KEY_USER_AGENT] ?? '';
        $currentUserAgent = $this->getUserAgent();
        if ($sessionUserAgent !== $currentUserAgent) {
            // Log suspicious activity
            error_log("Session User-Agent mismatch");
            // Uncomment next lines for strict User-Agent validation
            // $this->destroySession();
            // return false;
        }

        return true;
    }

    private function updateLastActivity(): void
    {
        $_SESSION[self::SESSION_KEY_LAST_ACTIVITY] = time();
    }

    private function getClientIp(): string
    {
        $ipKeys = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CF_CONNECTING_IP',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    private function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }

    public function getSessionInfo(): array
    {
        return [
            'session_id' => $this->getSessionId(),
            'user_id' => $_SESSION[self::SESSION_KEY_USER_ID] ?? null,
            'user_role' => $_SESSION[self::SESSION_KEY_USER_ROLE] ?? null,
            'last_activity' => $_SESSION[self::SESSION_KEY_LAST_ACTIVITY] ?? null,
            'ip_address' => $_SESSION[self::SESSION_KEY_IP_ADDRESS] ?? null,
            'user_agent' => $_SESSION[self::SESSION_KEY_USER_AGENT] ?? null,
            'is_authenticated' => $this->isAuthenticated(),
            'time_remaining' => max(0, self::SESSION_TIMEOUT - (time() - ($this->getLastActivity() ?? 0)))
        ];
    }
}