<?php

declare(strict_types=1);

namespace Tests\Auth;

use PHPUnit\Framework\TestCase;
use ParcCalanques\Auth\JwtService;
use ParcCalanques\Models\User;
use ParcCalanques\Exceptions\AuthException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTime;
use DateTimeImmutable;

class JwtServiceTest extends TestCase
{
    private JwtService $jwtService;
    private User $testUser;
    private string $testSecretKey;

    protected function setUp(): void
    {
        $this->testSecretKey = 'test-secret-key-for-unit-tests-' . bin2hex(random_bytes(16));
        $this->jwtService = new JwtService($this->testSecretKey, 'test-issuer.com');
        
        // Create a mock user for testing
        $this->testUser = new User(
            id: 1,
            email: 'test@example.com',
            passwordHash: password_hash('password123', PASSWORD_DEFAULT),
            role: User::ROLE_USER,
            firstName: 'John',
            lastName: 'Doe',
            isActive: true,
            createdAt: new DateTime('2023-01-01 10:00:00')
        );
    }

    public function testGenerateTokenPairReturnsValidStructure(): void
    {
        $tokenPair = $this->jwtService->generateTokenPair($this->testUser);

        $this->assertIsArray($tokenPair);
        $this->assertArrayHasKey('access_token', $tokenPair);
        $this->assertArrayHasKey('refresh_token', $tokenPair);
        $this->assertArrayHasKey('token_type', $tokenPair);
        $this->assertArrayHasKey('expires_in', $tokenPair);
        $this->assertArrayHasKey('expires_at', $tokenPair);
        
        $this->assertEquals('Bearer', $tokenPair['token_type']);
        $this->assertIsString($tokenPair['access_token']);
        $this->assertIsString($tokenPair['refresh_token']);
        $this->assertIsInt($tokenPair['expires_in']);
        $this->assertIsInt($tokenPair['expires_at']);
    }

    public function testGenerateAccessTokenCreatesValidToken(): void
    {
        $now = new DateTimeImmutable('2023-06-01 12:00:00');
        $token = $this->jwtService->generateAccessToken($this->testUser, $now);
        
        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        // Decode and verify token contents
        $decoded = JWT::decode($token, new Key($this->testSecretKey, 'HS256'));
        $payload = (array) $decoded;

        $this->assertEquals('test-issuer.com', $payload['iss']);
        $this->assertEquals('test-issuer.com', $payload['aud']);
        $this->assertEquals($now->getTimestamp(), $payload['iat']);
        $this->assertEquals($now->getTimestamp() + 3600, $payload['exp']); // 1 hour
        $this->assertEquals('1', $payload['sub']);
        $this->assertEquals(1, $payload['user_id']);
        $this->assertEquals('test@example.com', $payload['email']);
        $this->assertEquals('user', $payload['role']);
        $this->assertEquals('John', $payload['first_name']);
        $this->assertEquals('Doe', $payload['last_name']);
        $this->assertTrue($payload['is_active']);
        $this->assertEquals('access', $payload['token_type']);
    }

    public function testGenerateRefreshTokenCreatesValidToken(): void
    {
        $now = new DateTimeImmutable('2023-06-01 12:00:00');
        $token = $this->jwtService->generateRefreshToken($this->testUser, $now);
        
        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        // Decode and verify token contents
        $decoded = JWT::decode($token, new Key($this->testSecretKey, 'HS256'));
        $payload = (array) $decoded;

        $this->assertEquals('test-issuer.com', $payload['iss']);
        $this->assertEquals($now->getTimestamp() + (30 * 24 * 60 * 60), $payload['exp']); // 30 days
        $this->assertEquals(1, $payload['user_id']);
        $this->assertEquals('refresh', $payload['token_type']);
    }

    public function testValidateTokenWithValidTokenReturnsPayload(): void
    {
        $token = $this->jwtService->generateAccessToken($this->testUser);
        $payload = $this->jwtService->validateToken($token);

        $this->assertIsArray($payload);
        $this->assertEquals(1, $payload['user_id']);
        $this->assertEquals('test@example.com', $payload['email']);
        $this->assertEquals('access', $payload['token_type']);
    }

    public function testValidateTokenWithInvalidTokenThrowsException(): void
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Invalid token signature');
        $this->expectExceptionCode(401);

        $this->jwtService->validateToken('invalid.token.here');
    }

    public function testValidateTokenWithExpiredTokenThrowsException(): void
    {
        // Create an expired token
        $pastTime = new DateTimeImmutable('2020-01-01 00:00:00');
        $expiredToken = $this->jwtService->generateAccessToken($this->testUser, $pastTime);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Token has expired');
        $this->expectExceptionCode(401);

        $this->jwtService->validateToken($expiredToken);
    }

    public function testValidateTokenWithMalformedTokenThrowsException(): void
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionCode(401);

        $this->jwtService->validateToken('not-a-jwt-token');
    }

    public function testRefreshAccessTokenWithValidRefreshTokenReturnsUserData(): void
    {
        $refreshToken = $this->jwtService->generateRefreshToken($this->testUser);
        $result = $this->jwtService->refreshAccessToken($refreshToken);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user_id', $result);
        $this->assertArrayHasKey('payload', $result);
        $this->assertEquals(1, $result['user_id']);
        $this->assertEquals('refresh', $result['payload']['token_type']);
    }

    public function testRefreshAccessTokenWithAccessTokenThrowsException(): void
    {
        $accessToken = $this->jwtService->generateAccessToken($this->testUser);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Invalid refresh token');
        $this->expectExceptionCode(401);

        $this->jwtService->refreshAccessToken($accessToken);
    }

    public function testRefreshAccessTokenWithInvalidTokenThrowsException(): void
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionCode(401);

        $this->jwtService->refreshAccessToken('invalid.token.here');
    }

    public function testExtractBearerTokenFromValidHeaderReturnsToken(): void
    {
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.test.token';
        $authHeader = 'Bearer ' . $token;

        $extractedToken = $this->jwtService->extractBearerToken($authHeader);

        $this->assertEquals($token, $extractedToken);
    }

    public function testExtractBearerTokenFromInvalidHeaderReturnsNull(): void
    {
        $this->assertNull($this->jwtService->extractBearerToken('Basic dGVzdA=='));
        $this->assertNull($this->jwtService->extractBearerToken('Bearer'));
        $this->assertNull($this->jwtService->extractBearerToken(''));
        $this->assertNull($this->jwtService->extractBearerToken());
    }

    public function testGetUserIdFromTokenReturnsCorrectId(): void
    {
        $token = $this->jwtService->generateAccessToken($this->testUser);
        $userId = $this->jwtService->getUserIdFromToken($token);

        $this->assertEquals(1, $userId);
    }

    public function testGetUserIdFromTokenWithInvalidUserIdThrowsException(): void
    {
        // Create token with invalid user ID
        $payload = [
            'iss' => 'test-issuer.com',
            'aud' => 'test-issuer.com',
            'iat' => time(),
            'exp' => time() + 3600,
            'user_id' => 0, // Invalid user ID
            'token_type' => 'access'
        ];
        $invalidToken = JWT::encode($payload, $this->testSecretKey, 'HS256');

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Invalid user ID in token');
        $this->expectExceptionCode(401);

        $this->jwtService->getUserIdFromToken($invalidToken);
    }

    public function testGetUserDataFromTokenReturnsCorrectData(): void
    {
        $token = $this->jwtService->generateAccessToken($this->testUser);
        $userData = $this->jwtService->getUserDataFromToken($token);

        $this->assertIsArray($userData);
        $this->assertEquals(1, $userData['id']);
        $this->assertEquals('test@example.com', $userData['email']);
        $this->assertEquals('user', $userData['role']);
        $this->assertEquals('John', $userData['first_name']);
        $this->assertEquals('Doe', $userData['last_name']);
        $this->assertTrue($userData['is_active']);
    }

    public function testGetUserDataFromRefreshTokenThrowsException(): void
    {
        $refreshToken = $this->jwtService->generateRefreshToken($this->testUser);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Token is not an access token');
        $this->expectExceptionCode(401);

        $this->jwtService->getUserDataFromToken($refreshToken);
    }

    public function testGetTokenTtlReturnsCorrectValues(): void
    {
        $this->assertEquals(3600, $this->jwtService->getTokenTtl('access'));
        $this->assertEquals(2592000, $this->jwtService->getTokenTtl('refresh'));
        $this->assertEquals(3600, $this->jwtService->getTokenTtl('invalid')); // default to access
    }

    public function testDebugTokenWithValidTokenReturnsDebugInfo(): void
    {
        $token = $this->jwtService->generateAccessToken($this->testUser);
        $debugInfo = $this->jwtService->debugToken($token);

        $this->assertIsArray($debugInfo);
        $this->assertTrue($debugInfo['valid']);
        $this->assertArrayHasKey('payload', $debugInfo);
        $this->assertArrayHasKey('issued_at', $debugInfo);
        $this->assertArrayHasKey('expires_at', $debugInfo);
        $this->assertArrayHasKey('is_expired', $debugInfo);
        $this->assertArrayHasKey('time_remaining', $debugInfo);
        $this->assertFalse($debugInfo['is_expired']);
        $this->assertGreaterThan(0, $debugInfo['time_remaining']);
    }

    public function testDebugTokenWithInvalidTokenReturnsErrorInfo(): void
    {
        $debugInfo = $this->jwtService->debugToken('invalid.token');

        $this->assertIsArray($debugInfo);
        $this->assertFalse($debugInfo['valid']);
        $this->assertArrayHasKey('error', $debugInfo);
        $this->assertArrayHasKey('code', $debugInfo);
        $this->assertEquals(401, $debugInfo['code']);
    }

    public function testDebugTokenWithExpiredTokenShowsExpiredStatus(): void
    {
        $pastTime = new DateTimeImmutable('2020-01-01 00:00:00');
        $expiredToken = $this->jwtService->generateAccessToken($this->testUser, $pastTime);
        
        $debugInfo = $this->jwtService->debugToken($expiredToken);

        $this->assertFalse($debugInfo['valid']);
        $this->assertStringContainsString('expired', strtolower($debugInfo['error']));
    }

    public function testIsTokenBlacklistedReturnsFalse(): void
    {
        $token = $this->jwtService->generateAccessToken($this->testUser);
        $this->assertFalse($this->jwtService->isTokenBlacklisted($token));
    }

    public function testBlacklistTokenDoesNotThrow(): void
    {
        $token = $this->jwtService->generateAccessToken($this->testUser);
        
        // Should not throw any exception
        $this->jwtService->blacklistToken($token);
        $this->assertTrue(true); // If we reach this point, no exception was thrown
    }

    /**
     * Test edge cases and security scenarios
     */
    public function testTokensGeneratedAtDifferentTimesAreDifferent(): void
    {
        $token1 = $this->jwtService->generateAccessToken($this->testUser);
        
        // Wait to ensure different timestamps
        usleep(1000); // 1 millisecond
        
        $token2 = $this->jwtService->generateAccessToken($this->testUser);

        $this->assertNotEquals($token1, $token2);
    }

    public function testAccessAndRefreshTokensAreDifferent(): void
    {
        $now = new DateTimeImmutable();
        $accessToken = $this->jwtService->generateAccessToken($this->testUser, $now);
        $refreshToken = $this->jwtService->generateRefreshToken($this->testUser, $now);

        $this->assertNotEquals($accessToken, $refreshToken);
    }

    public function testTokenWithDifferentSecretKeyFailsValidation(): void
    {
        $differentJwtService = new JwtService('different-secret-key');
        $token = $this->jwtService->generateAccessToken($this->testUser);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Invalid token signature');

        $differentJwtService->validateToken($token);
    }

    /**
     * Data providers for parameterized tests
     */
    public function invalidTokenProvider(): array
    {
        return [
            'empty string' => [''],
            'null' => [null],
            'malformed jwt' => ['invalid.jwt'],
            'only two parts' => ['header.payload'],
            'four parts' => ['header.payload.signature.extra'],
            'non-base64' => ['not-base64.not-base64.not-base64'],
        ];
    }

    /**
     * @dataProvider invalidTokenProvider
     */
    public function testValidateTokenWithVariousInvalidTokensThrowsException($invalidToken): void
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionCode(401);

        if ($invalidToken === null) {
            $this->expectException(\TypeError::class);
        }

        $this->jwtService->validateToken($invalidToken);
    }

    public function invalidBearerHeaderProvider(): array
    {
        return [
            'no bearer prefix' => ['token-without-bearer'],
            'wrong prefix' => ['Basic token'],
            'bearer with no token' => ['Bearer '],
            'bearer lowercase' => ['bearer token'],
            'extra spaces' => ['Bearer  token with spaces'],
        ];
    }

    /**
     * @dataProvider invalidBearerHeaderProvider
     */
    public function testExtractBearerTokenWithInvalidHeaders($header): void
    {
        $result = $this->jwtService->extractBearerToken($header);
        
        if ($header === 'Bearer  token with spaces') {
            $this->assertEquals(' token with spaces', $result);
        } else {
            $this->assertNull($result);
        }
    }
}