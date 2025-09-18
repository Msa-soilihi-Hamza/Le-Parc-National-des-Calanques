<?php

declare(strict_types=1);

namespace Tests\Auth\Unit\Services;

use PHPUnit\Framework\TestCase;
use ParcCalanques\Auth\Services\JwtService;
use ParcCalanques\Auth\Models\User;
use ParcCalanques\Shared\Exceptions\AuthException;

class JwtServiceTest extends TestCase
{
    private JwtService $jwtService;
    private User $testUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtService = new JwtService('test-secret-key', 'test-app');
        
        $this->testUser = new User(
            id: 1,
            email: 'test@example.com',
            passwordHash: 'hashed_password',
            role: User::ROLE_USER,
            firstName: 'John',
            lastName: 'Doe',
            isActive: true
        );
    }

    public function testGenerateTokenPair(): void
    {
        $tokens = $this->jwtService->generateTokenPair($this->testUser);

        $this->assertArrayHasKey('access_token', $tokens);
        $this->assertArrayHasKey('refresh_token', $tokens);
        $this->assertArrayHasKey('token_type', $tokens);
        $this->assertArrayHasKey('expires_in', $tokens);
        $this->assertArrayHasKey('expires_at', $tokens);

        $this->assertEquals('Bearer', $tokens['token_type']);
        $this->assertEquals(3600, $tokens['expires_in']);
        $this->assertIsString($tokens['access_token']);
        $this->assertIsString($tokens['refresh_token']);
        $this->assertIsInt($tokens['expires_at']);

        $this->assertStringContainsString('.', $tokens['access_token']);
        $this->assertCount(3, explode('.', $tokens['access_token']));
    }

    public function testGenerateAccessToken(): void
    {
        $token = $this->jwtService->generateAccessToken($this->testUser);

        $this->assertIsString($token);
        $this->assertStringContainsString('.', $token);
        $this->assertNotEmpty($token);
        
        $parts = explode('.', $token);
        $this->assertCount(3, $parts);
    }

    public function testValidateValidToken(): void
    {
        $token = $this->jwtService->generateAccessToken($this->testUser);
        $payload = $this->jwtService->validateToken($token);

        $this->assertIsArray($payload);
        $this->assertEquals($this->testUser->getId(), $payload['user_id']);
        $this->assertEquals($this->testUser->getEmail(), $payload['email']);
        $this->assertEquals('access', $payload['token_type']);
    }

    public function testGetUserIdFromToken(): void
    {
        $token = $this->jwtService->generateAccessToken($this->testUser);
        $userId = $this->jwtService->getUserIdFromToken($token);

        $this->assertEquals($this->testUser->getId(), $userId);
    }

    public function testExtractBearerToken(): void
    {
        $token = 'sample.jwt.token';
        $authHeader = 'Bearer ' . $token;

        $extractedToken = $this->jwtService->extractBearerToken($authHeader);
        $this->assertEquals($token, $extractedToken);
    }

    public function testExtractBearerTokenInvalid(): void
    {
        $invalidHeader = 'Basic username:password';
        $extractedToken = $this->jwtService->extractBearerToken($invalidHeader);
        $this->assertNull($extractedToken);
    }

    public function testGetTokenTtl(): void
    {
        $this->assertEquals(3600, $this->jwtService->getTokenTtl('access'));
        $this->assertEquals(2592000, $this->jwtService->getTokenTtl('refresh'));
        $this->assertEquals(3600, $this->jwtService->getTokenTtl('unknown'));
    }
}
