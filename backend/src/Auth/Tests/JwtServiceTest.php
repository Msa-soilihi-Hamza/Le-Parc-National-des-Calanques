<?php

declare(strict_types=1);

namespace ParcCalanques\Auth\Tests;

use PHPUnit\Framework\TestCase;
use ParcCalanques\Auth\Services\JwtService;
use ParcCalanques\Auth\Models\User;

class JwtServiceTest extends TestCase
{
    private JwtService $jwtService;

    protected function setUp(): void
    {
        // Utiliser une clé secrète de test
        $_ENV['JWT_SECRET'] = 'test-secret-key-for-testing-purposes-only';
        $this->jwtService = new JwtService();
    }

    public function testGenerateToken(): void
    {
        // Arrange
        $user = new User(1, 'Doe', 'John', 'test@example.com', 'hashedpassword', 'user');

        // Act
        $token = $this->jwtService->generateToken($user);

        // Assert
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('.', $token); // JWT contient des points
    }

    public function testValidateTokenWithValidToken(): void
    {
        // Arrange
        $user = new User(1, 'Doe', 'John', 'test@example.com', 'hashedpassword', 'user');
        $token = $this->jwtService->generateToken($user);

        // Act
        $payload = $this->jwtService->validateToken($token);

        // Assert
        $this->assertIsArray($payload);
        $this->assertArrayHasKey('user_id', $payload);
        $this->assertArrayHasKey('email', $payload);
        $this->assertEquals(1, $payload['user_id']);
        $this->assertEquals('test@example.com', $payload['email']);
    }

    public function testValidateTokenWithInvalidToken(): void
    {
        // Arrange
        $invalidToken = 'invalid.jwt.token';

        // Act
        $payload = $this->jwtService->validateToken($invalidToken);

        // Assert
        $this->assertNull($payload);
    }

    public function testValidateTokenWithExpiredToken(): void
    {
        // Arrange - créer un token avec expiration très courte
        $user = new User(1, 'Doe', 'John', 'test@example.com', 'hashedpassword', 'user');

        // Simuler un token expiré en modifiant temporairement l'expiration
        $expiredToken = $this->jwtService->generateTokenWithExpiration($user, -3600); // Expiré depuis 1h

        // Act
        $payload = $this->jwtService->validateToken($expiredToken);

        // Assert
        $this->assertNull($payload);
    }

    public function testRefreshToken(): void
    {
        // Arrange
        $user = new User(1, 'Doe', 'John', 'test@example.com', 'hashedpassword', 'user');
        $originalToken = $this->jwtService->generateToken($user);

        // Act
        $newToken = $this->jwtService->refreshToken($originalToken);

        // Assert
        $this->assertIsString($newToken);
        $this->assertNotEquals($originalToken, $newToken);

        // Vérifier que le nouveau token est valide
        $payload = $this->jwtService->validateToken($newToken);
        $this->assertIsArray($payload);
        $this->assertEquals(1, $payload['user_id']);
    }
}