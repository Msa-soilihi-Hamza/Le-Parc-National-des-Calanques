<?php

declare(strict_types=1);

namespace ParcCalanques\Auth;

use ParcCalanques\Models\User;
use ParcCalanques\Models\UserRepository;
use ParcCalanques\Exceptions\AuthException;

class AuthService
{
    private const REMEMBER_TOKEN_LENGTH = 64;
    
    public function __construct(
        private UserRepository $userRepository,
        private SessionManager $sessionManager,
        private ?JwtService $jwtService = null
    ) {}

    public function login(string $email, string $password, bool $remember = false): User
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            throw new AuthException(AuthException::USER_NOT_FOUND);
        }

        if (!$user->isActive()) {
            throw new AuthException(AuthException::USER_INACTIVE);
        }

        if (!$user->verifyPassword($password)) {
            throw new AuthException(AuthException::INVALID_CREDENTIALS);
        }

        $this->sessionManager->createSession($user);

        if ($remember) {
            $this->createRememberToken($user);
        }

        return $user;
    }

    public function loginWithJwt(string $email, string $password, bool $remember = false): array
    {
        if (!$this->jwtService) {
            throw new AuthException('JWT service not available');
        }

        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            throw new AuthException(AuthException::USER_NOT_FOUND);
        }

        if (!$user->isActive()) {
            throw new AuthException(AuthException::USER_INACTIVE);
        }

        if (!$user->verifyPassword($password)) {
            throw new AuthException(AuthException::INVALID_CREDENTIALS);
        }

        // Créer un remember token si demandé
        if ($remember) {
            $this->createRememberToken($user);
        }

        $tokens = $this->jwtService->generateTokenPair($user);
        
        return [
            'user' => $user->toArray(),
            'tokens' => $tokens
        ];
    }

    public function refreshJwtToken(string $refreshToken): array
    {
        if (!$this->jwtService) {
            throw new AuthException('JWT service not available');
        }

        $tokenData = $this->jwtService->refreshAccessToken($refreshToken);
        $user = $this->userRepository->findById($tokenData['user_id']);
        
        if (!$user || !$user->isActive()) {
            throw new AuthException('User not found or inactive');
        }

        $tokens = $this->jwtService->generateTokenPair($user);
        
        return [
            'user' => $user->toArray(),
            'tokens' => $tokens
        ];
    }

    public function validateJwtToken(string $token): User
    {
        if (!$this->jwtService) {
            throw new AuthException('JWT service not available');
        }

        $userId = $this->jwtService->getUserIdFromToken($token);
        $user = $this->userRepository->findById($userId);
        
        if (!$user || !$user->isActive()) {
            throw new AuthException('User not found or inactive');
        }

        return $user;
    }

    public function logout(): void
    {
        $user = $this->sessionManager->getCurrentUser();
        
        if ($user) {
            $this->userRepository->updateRememberToken($user->getId(), null);
        }

        $this->sessionManager->destroySession();
        $this->clearRememberCookie();
    }

    public function register(array $userData): User
    {
        if ($this->userRepository->emailExists($userData['email'])) {
            throw new AuthException('Email address already exists');
        }

        $user = $this->userRepository->create($userData);
        $this->sessionManager->createSession($user);

        return $user;
    }

    public function getCurrentUser(): ?User
    {
        return $this->sessionManager->getCurrentUser();
    }

    public function isAuthenticated(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    public function requireAuthentication(): User
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            throw new AuthException(AuthException::UNAUTHORIZED);
        }

        return $user;
    }

    public function requireRole(string $role): User
    {
        $user = $this->requireAuthentication();

        if ($user->getRole() !== $role) {
            throw new AuthException(AuthException::INSUFFICIENT_PRIVILEGES);
        }

        return $user;
    }

    public function requireAdmin(): User
    {
        return $this->requireRole(User::ROLE_ADMIN);
    }

    public function attemptRememberLogin(): ?User
    {
        $token = $_COOKIE['remember_token'] ?? null;
        
        if (!$token) {
            return null;
        }

        $user = $this->userRepository->findByRememberToken($token);
        
        if (!$user || !$user->isActive()) {
            $this->clearRememberCookie();
            return null;
        }

        $this->sessionManager->createSession($user);
        $this->refreshRememberToken($user);

        return $user;
    }

    public function verifyEmail(int $userId): bool
    {
        return $this->userRepository->updateEmailVerification($userId, new \DateTime());
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            throw new AuthException(AuthException::USER_NOT_FOUND);
        }

        if (!$user->verifyPassword($currentPassword)) {
            throw new AuthException(AuthException::INVALID_CREDENTIALS);
        }

        // Get PDO connection through reflection since it's private
        $reflectionClass = new \ReflectionClass($this->userRepository);
        $pdoProperty = $reflectionClass->getProperty('pdo');
        $pdoProperty->setAccessible(true);
        $pdo = $pdoProperty->getValue($this->userRepository);
        
        $stmt = $pdo->prepare('
            UPDATE Utilisateur 
            SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP 
            WHERE id_utilisateur = :id
        ');

        return $stmt->execute([
            'password_hash' => User::hashPassword($newPassword),
            'id' => $userId
        ]);
    }

    private function createRememberToken(User $user): void
    {
        $token = bin2hex(random_bytes(self::REMEMBER_TOKEN_LENGTH));
        $hashedToken = hash('sha256', $token);
        
        $this->userRepository->updateRememberToken($user->getId(), $hashedToken);
        $this->setRememberCookie($token);
    }

    private function refreshRememberToken(User $user): void
    {
        $this->createRememberToken($user);
    }

    private function setRememberCookie(string $token): void
    {
        $expires = time() + (30 * 24 * 60 * 60); // 30 jours
        
        setcookie(
            'remember_token',
            $token,
            $expires,
            '/',
            '',
            true, // secure - only HTTPS
            true  // httpOnly - not accessible via JavaScript
        );
    }

    private function clearRememberCookie(): void
    {
        setcookie(
            'remember_token',
            '',
            time() - 3600,
            '/',
            '',
            true,
            true
        );
    }
}