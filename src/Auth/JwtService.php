<?php

declare(strict_types=1);

namespace ParcCalanques\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use ParcCalanques\Models\User;
use ParcCalanques\Exceptions\AuthException;
use DateTime;
use DateTimeImmutable;

class JwtService
{
    private const ALGORITHM = 'HS256';
    private const ACCESS_TOKEN_TTL = 3600; // 1 heure
    private const REFRESH_TOKEN_TTL = 2592000; // 30 jours
    
    private readonly string $secretKey;
    private readonly string $issuer;
    
    public function __construct(
        string $secretKey = null,
        string $issuer = 'parc-calanques.com'
    ) {
        $this->secretKey = $secretKey ?? $this->generateSecretKey();
        $this->issuer = $issuer;
    }

    public function generateTokenPair(User $user): array
    {
        $now = new DateTimeImmutable();
        
        $accessToken = $this->generateAccessToken($user, $now);
        $refreshToken = $this->generateRefreshToken($user, $now);
        
        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => self::ACCESS_TOKEN_TTL,
            'expires_at' => $now->getTimestamp() + self::ACCESS_TOKEN_TTL
        ];
    }

    public function generateAccessToken(User $user, DateTimeImmutable $now = null): string
    {
        $now = $now ?? new DateTimeImmutable();
        
        $payload = [
            'iss' => $this->issuer,
            'aud' => $this->issuer,
            'iat' => $now->getTimestamp(),
            'exp' => $now->getTimestamp() + self::ACCESS_TOKEN_TTL,
            'sub' => (string) $user->getId(),
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'is_active' => $user->isActive(),
            'token_type' => 'access'
        ];

        return JWT::encode($payload, $this->secretKey, self::ALGORITHM);
    }

    public function generateRefreshToken(User $user, DateTimeImmutable $now = null): string
    {
        $now = $now ?? new DateTimeImmutable();
        
        $payload = [
            'iss' => $this->issuer,
            'aud' => $this->issuer,
            'iat' => $now->getTimestamp(),
            'exp' => $now->getTimestamp() + self::REFRESH_TOKEN_TTL,
            'sub' => (string) $user->getId(),
            'user_id' => $user->getId(),
            'token_type' => 'refresh'
        ];

        return JWT::encode($payload, $this->secretKey, self::ALGORITHM);
    }

    public function validateToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, self::ALGORITHM));
            return (array) $decoded;
        } catch (ExpiredException $e) {
            throw new AuthException('Token has expired', 401);
        } catch (SignatureInvalidException $e) {
            throw new AuthException('Invalid token signature', 401);
        } catch (\Exception $e) {
            throw new AuthException('Invalid token: ' . $e->getMessage(), 401);
        }
    }

    public function refreshAccessToken(string $refreshToken): array
    {
        $payload = $this->validateToken($refreshToken);
        
        if (($payload['token_type'] ?? '') !== 'refresh') {
            throw new AuthException('Invalid refresh token', 401);
        }

        $userId = (int) ($payload['user_id'] ?? 0);
        if (!$userId) {
            throw new AuthException('Invalid user ID in refresh token', 401);
        }

        return [
            'user_id' => $userId,
            'payload' => $payload
        ];
    }

    public function extractBearerToken(string $authHeader = null): ?string
    {
        $authHeader = $authHeader ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
        
        if (empty($authHeader)) {
            return null;
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        return substr($authHeader, 7);
    }

    public function getUserIdFromToken(string $token): int
    {
        $payload = $this->validateToken($token);
        
        $userId = (int) ($payload['user_id'] ?? 0);
        if (!$userId) {
            throw new AuthException('Invalid user ID in token', 401);
        }

        return $userId;
    }

    public function getUserDataFromToken(string $token): array
    {
        $payload = $this->validateToken($token);
        
        if (($payload['token_type'] ?? '') !== 'access') {
            throw new AuthException('Token is not an access token', 401);
        }

        return [
            'id' => (int) ($payload['user_id'] ?? 0),
            'email' => $payload['email'] ?? '',
            'role' => $payload['role'] ?? '',
            'first_name' => $payload['first_name'] ?? '',
            'last_name' => $payload['last_name'] ?? '',
            'is_active' => (bool) ($payload['is_active'] ?? false)
        ];
    }

    public function blacklistToken(string $token): void
    {
        // Pour une implémentation complète, vous pourriez stocker les tokens blacklistés
        // dans une cache Redis ou une table de base de données
        // Pour le moment, cette méthode est un placeholder
    }

    public function isTokenBlacklisted(string $token): bool
    {
        // Vérification si le token est blacklisté
        // Pour le moment, retourne false (pas de blacklist implémentée)
        return false;
    }

    public function getTokenTtl(string $tokenType = 'access'): int
    {
        return match($tokenType) {
            'access' => self::ACCESS_TOKEN_TTL,
            'refresh' => self::REFRESH_TOKEN_TTL,
            default => self::ACCESS_TOKEN_TTL
        };
    }

    private function generateSecretKey(): string
    {
        // En production, utilisez une clé secrète sécurisée depuis une variable d'environnement
        return $_ENV['JWT_SECRET'] ?? 'your-very-secret-jwt-key-change-in-production-' . bin2hex(random_bytes(32));
    }

    public function debugToken(string $token): array
    {
        try {
            $payload = $this->validateToken($token);
            $now = time();
            
            return [
                'valid' => true,
                'payload' => $payload,
                'issued_at' => date('Y-m-d H:i:s', $payload['iat'] ?? 0),
                'expires_at' => date('Y-m-d H:i:s', $payload['exp'] ?? 0),
                'is_expired' => ($payload['exp'] ?? 0) < $now,
                'time_remaining' => max(0, ($payload['exp'] ?? 0) - $now)
            ];
        } catch (AuthException $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        }
    }
}