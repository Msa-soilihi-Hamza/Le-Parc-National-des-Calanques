<?php

declare(strict_types=1);

namespace Tests\Auth;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ParcCalanques\Auth\AuthService;
use ParcCalanques\Auth\SessionManager;
use ParcCalanques\Auth\JwtService;
use ParcCalanques\Models\User;
use ParcCalanques\Models\UserRepository;
use ParcCalanques\Exceptions\AuthException;
use DateTime;
use PDO;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private MockObject|UserRepository $mockUserRepository;
    private MockObject|SessionManager $mockSessionManager;
    private MockObject|JwtService $mockJwtService;
    private User $testUser;

    protected function setUp(): void
    {
        $this->mockUserRepository = $this->createMock(UserRepository::class);
        $this->mockSessionManager = $this->createMock(SessionManager::class);
        $this->mockJwtService = $this->createMock(JwtService::class);
        
        $this->authService = new AuthService(
            $this->mockUserRepository,
            $this->mockSessionManager,
            $this->mockJwtService
        );

        // Create test user
        $this->testUser = new User(
            id: 1,
            email: 'test@example.com',
            passwordHash: password_hash('password123', PASSWORD_DEFAULT),
            role: User::ROLE_USER,
            firstName: 'John',
            lastName: 'Doe',
            isActive: true,
            createdAt: new DateTime()
        );

        // Reset cookies for each test
        $_COOKIE = [];
    }

    protected function tearDown(): void
    {
        $_COOKIE = [];
    }

    public function testLoginWithValidCredentialsReturnsUser(): void
    {
        $email = 'test@example.com';
        $password = 'password123';

        $this->mockUserRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($this->testUser);

        $this->mockSessionManager->expects($this->once())
            ->method('createSession')
            ->with($this->testUser);

        $result = $this->authService->login($email, $password);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($email, $result->getEmail());
    }

    public function testLoginWithRememberTokenCreatesRememberToken(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $remember = true;

        $this->mockUserRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($this->testUser);

        $this->mockSessionManager->expects($this->once())
            ->method('createSession')
            ->with($this->testUser);

        $this->mockUserRepository->expects($this->once())
            ->method('updateRememberToken')
            ->with($this->testUser->getId(), $this->isType('string'));

        $result = $this->authService->login($email, $password, $remember);

        $this->assertInstanceOf(User::class, $result);
    }

    public function testLoginWithNonExistentUserThrowsException(): void
    {
        $email = 'nonexistent@example.com';
        $password = 'password123';

        $this->mockUserRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage(AuthException::USER_NOT_FOUND);

        $this->authService->login($email, $password);
    }

    public function testLoginWithInactiveUserThrowsException(): void
    {
        $inactiveUser = new User(
            id: 1,
            email: 'inactive@example.com',
            passwordHash: password_hash('password123', PASSWORD_DEFAULT),
            role: User::ROLE_USER,
            firstName: 'John',
            lastName: 'Doe',
            isActive: false,
            createdAt: new DateTime()
        );

        $this->mockUserRepository->expects($this->once())
            ->method('findByEmail')
            ->with('inactive@example.com')
            ->willReturn($inactiveUser);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage(AuthException::USER_INACTIVE);

        $this->authService->login('inactive@example.com', 'password123');
    }

    public function testLoginWithWrongPasswordThrowsException(): void
    {
        $email = 'test@example.com';
        $wrongPassword = 'wrongpassword';

        $this->mockUserRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($this->testUser);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage(AuthException::INVALID_CREDENTIALS);

        $this->authService->login($email, $wrongPassword);
    }

    public function testLoginWithJwtReturnsTokens(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $expectedTokens = [
            'access_token' => 'access.token.here',
            'refresh_token' => 'refresh.token.here',
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ];

        $this->mockUserRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($this->testUser);

        $this->mockJwtService->expects($this->once())
            ->method('generateTokenPair')
            ->with($this->testUser)
            ->willReturn($expectedTokens);

        $result = $this->authService->loginWithJwt($email, $password);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('tokens', $result);
        $this->assertEquals($expectedTokens, $result['tokens']);
        $this->assertIsArray($result['user']);
    }

    public function testLoginWithJwtWithoutJwtServiceThrowsException(): void
    {
        $authServiceWithoutJwt = new AuthService(
            $this->mockUserRepository,
            $this->mockSessionManager
        );

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('JWT service not available');

        $authServiceWithoutJwt->loginWithJwt('test@example.com', 'password123');
    }

    public function testRefreshJwtTokenWithValidRefreshTokenReturnsNewTokens(): void
    {
        $refreshToken = 'valid.refresh.token';
        $tokenData = [
            'user_id' => 1,
            'payload' => ['user_id' => 1]
        ];
        $newTokens = [
            'access_token' => 'new.access.token',
            'refresh_token' => 'new.refresh.token',
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ];

        $this->mockJwtService->expects($this->once())
            ->method('refreshAccessToken')
            ->with($refreshToken)
            ->willReturn($tokenData);

        $this->mockUserRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($this->testUser);

        $this->mockJwtService->expects($this->once())
            ->method('generateTokenPair')
            ->with($this->testUser)
            ->willReturn($newTokens);

        $result = $this->authService->refreshJwtToken($refreshToken);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('tokens', $result);
        $this->assertEquals($newTokens, $result['tokens']);
    }

    public function testRefreshJwtTokenWithInactiveUserThrowsException(): void
    {
        $inactiveUser = new User(
            id: 1,
            email: 'inactive@example.com',
            passwordHash: password_hash('password123', PASSWORD_DEFAULT),
            role: User::ROLE_USER,
            firstName: 'John',
            lastName: 'Doe',
            isActive: false,
            createdAt: new DateTime()
        );

        $refreshToken = 'valid.refresh.token';
        $tokenData = [
            'user_id' => 1,
            'payload' => ['user_id' => 1]
        ];

        $this->mockJwtService->expects($this->once())
            ->method('refreshAccessToken')
            ->with($refreshToken)
            ->willReturn($tokenData);

        $this->mockUserRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($inactiveUser);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('User not found or inactive');

        $this->authService->refreshJwtToken($refreshToken);
    }

    public function testValidateJwtTokenWithValidTokenReturnsUser(): void
    {
        $token = 'valid.access.token';

        $this->mockJwtService->expects($this->once())
            ->method('getUserIdFromToken')
            ->with($token)
            ->willReturn(1);

        $this->mockUserRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($this->testUser);

        $result = $this->authService->validateJwtToken($token);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals(1, $result->getId());
    }

    public function testValidateJwtTokenWithNonExistentUserThrowsException(): void
    {
        $token = 'valid.access.token';

        $this->mockJwtService->expects($this->once())
            ->method('getUserIdFromToken')
            ->with($token)
            ->willReturn(999);

        $this->mockUserRepository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('User not found or inactive');

        $this->authService->validateJwtToken($token);
    }

    public function testLogoutClearsSessionAndRememberToken(): void
    {
        $this->mockSessionManager->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($this->testUser);

        $this->mockUserRepository->expects($this->once())
            ->method('updateRememberToken')
            ->with($this->testUser->getId(), null);

        $this->mockSessionManager->expects($this->once())
            ->method('destroySession');

        $this->authService->logout();

        // The method should complete without throwing exceptions
        $this->assertTrue(true);
    }

    public function testLogoutWithoutCurrentUserStillClearsSession(): void
    {
        $this->mockSessionManager->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn(null);

        $this->mockUserRepository->expects($this->never())
            ->method('updateRememberToken');

        $this->mockSessionManager->expects($this->once())
            ->method('destroySession');

        $this->authService->logout();

        $this->assertTrue(true);
    }

    public function testRegisterWithValidDataCreatesUserAndSession(): void
    {
        $userData = [
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'first_name' => 'New',
            'last_name' => 'User'
        ];

        $this->mockUserRepository->expects($this->once())
            ->method('emailExists')
            ->with($userData['email'])
            ->willReturn(false);

        $this->mockUserRepository->expects($this->once())
            ->method('create')
            ->with($userData)
            ->willReturn($this->testUser);

        $this->mockSessionManager->expects($this->once())
            ->method('createSession')
            ->with($this->testUser);

        $result = $this->authService->register($userData);

        $this->assertInstanceOf(User::class, $result);
    }

    public function testRegisterWithExistingEmailThrowsException(): void
    {
        $userData = [
            'email' => 'existing@example.com',
            'password' => 'password123',
            'first_name' => 'New',
            'last_name' => 'User'
        ];

        $this->mockUserRepository->expects($this->once())
            ->method('emailExists')
            ->with($userData['email'])
            ->willReturn(true);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Email address already exists');

        $this->authService->register($userData);
    }

    public function testGetCurrentUserReturnsSessionUser(): void
    {
        $this->mockSessionManager->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($this->testUser);

        $result = $this->authService->getCurrentUser();

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals(1, $result->getId());
    }

    public function testGetCurrentUserReturnsNullWhenNoSession(): void
    {
        $this->mockSessionManager->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn(null);

        $result = $this->authService->getCurrentUser();

        $this->assertNull($result);
    }

    public function testIsAuthenticatedReturnsTrueWhenUserExists(): void
    {
        $this->mockSessionManager->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($this->testUser);

        $this->assertTrue($this->authService->isAuthenticated());
    }

    public function testIsAuthenticatedReturnsFalseWhenNoUser(): void
    {
        $this->mockSessionManager->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn(null);

        $this->assertFalse($this->authService->isAuthenticated());
    }

    public function testRequireAuthenticationReturnsUserWhenAuthenticated(): void
    {
        $this->mockSessionManager->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($this->testUser);

        $result = $this->authService->requireAuthentication();

        $this->assertInstanceOf(User::class, $result);
    }

    public function testRequireAuthenticationThrowsExceptionWhenNotAuthenticated(): void
    {
        $this->mockSessionManager->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn(null);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage(AuthException::UNAUTHORIZED);

        $this->authService->requireAuthentication();
    }

    public function testRequireRoleWithMatchingRoleReturnsUser(): void
    {
        $adminUser = new User(
            id: 1,
            email: 'admin@example.com',
            passwordHash: password_hash('password123', PASSWORD_DEFAULT),
            role: User::ROLE_ADMIN,
            firstName: 'Admin',
            lastName: 'User',
            isActive: true,
            createdAt: new DateTime()
        );

        $this->mockSessionManager->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($adminUser);

        $result = $this->authService->requireRole(User::ROLE_ADMIN);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals(User::ROLE_ADMIN, $result->getRole());
    }

    public function testRequireRoleWithMismatchedRoleThrowsException(): void
    {
        $this->mockSessionManager->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($this->testUser);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage(AuthException::INSUFFICIENT_PRIVILEGES);

        $this->authService->requireRole(User::ROLE_ADMIN);
    }

    public function testRequireAdminCallsRequireRoleWithAdminRole(): void
    {
        $adminUser = new User(
            id: 1,
            email: 'admin@example.com',
            passwordHash: password_hash('password123', PASSWORD_DEFAULT),
            role: User::ROLE_ADMIN,
            firstName: 'Admin',
            lastName: 'User',
            isActive: true,
            createdAt: new DateTime()
        );

        $this->mockSessionManager->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($adminUser);

        $result = $this->authService->requireAdmin();

        $this->assertInstanceOf(User::class, $result);
        $this->assertTrue($result->isAdmin());
    }

    public function testAttemptRememberLoginWithValidTokenReturnsUser(): void
    {
        $rememberToken = 'valid-remember-token';
        $_COOKIE['remember_token'] = $rememberToken;

        $this->mockUserRepository->expects($this->once())
            ->method('findByRememberToken')
            ->with($this->isType('string')) // The token will be hashed
            ->willReturn($this->testUser);

        $this->mockSessionManager->expects($this->once())
            ->method('createSession')
            ->with($this->testUser);

        $this->mockUserRepository->expects($this->once())
            ->method('updateRememberToken')
            ->with($this->testUser->getId(), $this->isType('string'));

        $result = $this->authService->attemptRememberLogin();

        $this->assertInstanceOf(User::class, $result);
    }

    public function testAttemptRememberLoginWithoutTokenReturnsNull(): void
    {
        $result = $this->authService->attemptRememberLogin();

        $this->assertNull($result);
    }

    public function testAttemptRememberLoginWithInvalidTokenReturnsNull(): void
    {
        $_COOKIE['remember_token'] = 'invalid-token';

        $this->mockUserRepository->expects($this->once())
            ->method('findByRememberToken')
            ->with($this->isType('string'))
            ->willReturn(null);

        $result = $this->authService->attemptRememberLogin();

        $this->assertNull($result);
    }

    public function testAttemptRememberLoginWithInactiveUserReturnsNull(): void
    {
        $inactiveUser = new User(
            id: 1,
            email: 'inactive@example.com',
            passwordHash: password_hash('password123', PASSWORD_DEFAULT),
            role: User::ROLE_USER,
            firstName: 'John',
            lastName: 'Doe',
            isActive: false,
            createdAt: new DateTime()
        );

        $_COOKIE['remember_token'] = 'valid-token';

        $this->mockUserRepository->expects($this->once())
            ->method('findByRememberToken')
            ->with($this->isType('string'))
            ->willReturn($inactiveUser);

        $result = $this->authService->attemptRememberLogin();

        $this->assertNull($result);
    }

    public function testVerifyEmailCallsRepositoryMethod(): void
    {
        $userId = 1;
        $expectedDateTime = $this->isInstanceOf(DateTime::class);

        $this->mockUserRepository->expects($this->once())
            ->method('updateEmailVerification')
            ->with($userId, $expectedDateTime)
            ->willReturn(true);

        $result = $this->authService->verifyEmail($userId);

        $this->assertTrue($result);
    }

    public function testChangePasswordWithValidCredentialsSucceeds(): void
    {
        $userId = 1;
        $currentPassword = 'password123';
        $newPassword = 'newpassword456';

        // Mock PDO and statement
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(\PDOStatement::class);

        $this->mockUserRepository->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($this->testUser);

        $mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

        $mockStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($newPassword) {
                return isset($params['password_hash']) && 
                       isset($params['id']) &&
                       password_verify($newPassword, $params['password_hash']) &&
                       $params['id'] === 1;
            }))
            ->willReturn(true);

        // Use reflection to set the PDO mock
        $this->setPrivatePdoProperty($mockPdo);

        $result = $this->authService->changePassword($userId, $currentPassword, $newPassword);

        $this->assertTrue($result);
    }

    public function testChangePasswordWithNonExistentUserThrowsException(): void
    {
        $this->mockUserRepository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage(AuthException::USER_NOT_FOUND);

        $this->authService->changePassword(999, 'password', 'newpassword');
    }

    public function testChangePasswordWithWrongCurrentPasswordThrowsException(): void
    {
        $this->mockUserRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($this->testUser);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage(AuthException::INVALID_CREDENTIALS);

        $this->authService->changePassword(1, 'wrongpassword', 'newpassword');
    }

    /**
     * Helper method to set private PDO property using reflection
     */
    private function setPrivatePdoProperty(PDO $mockPdo): void
    {
        $reflectionClass = new \ReflectionClass($this->mockUserRepository);
        $pdoProperty = $reflectionClass->getProperty('pdo');
        $pdoProperty->setAccessible(true);
        $pdoProperty->setValue($this->mockUserRepository, $mockPdo);
    }

    /**
     * Data provider for login scenarios
     */
    public function loginScenarioProvider(): array
    {
        return [
            'valid credentials without remember' => ['test@example.com', 'password123', false, true],
            'valid credentials with remember' => ['test@example.com', 'password123', true, true],
            'invalid email' => ['wrong@example.com', 'password123', false, false],
            'invalid password' => ['test@example.com', 'wrongpassword', false, false],
            'empty email' => ['', 'password123', false, false],
            'empty password' => ['test@example.com', '', false, false],
        ];
    }

    /**
     * @dataProvider loginScenarioProvider
     */
    public function testVariousLoginScenarios(string $email, string $password, bool $remember, bool $shouldSucceed): void
    {
        if ($shouldSucceed) {
            $this->mockUserRepository->expects($this->once())
                ->method('findByEmail')
                ->with($email)
                ->willReturn($this->testUser);

            $this->mockSessionManager->expects($this->once())
                ->method('createSession')
                ->with($this->testUser);

            if ($remember) {
                $this->mockUserRepository->expects($this->once())
                    ->method('updateRememberToken')
                    ->with($this->testUser->getId(), $this->isType('string'));
            }

            $result = $this->authService->login($email, $password, $remember);
            $this->assertInstanceOf(User::class, $result);
        } else {
            if (empty($email) || empty($password)) {
                // For empty credentials, we expect a PHP error or exception
                $this->expectException(\ArgumentCountError::class);
            } else {
                if ($email === 'wrong@example.com') {
                    $this->mockUserRepository->expects($this->once())
                        ->method('findByEmail')
                        ->with($email)
                        ->willReturn(null);
                    
                    $this->expectException(AuthException::class);
                    $this->expectExceptionMessage(AuthException::USER_NOT_FOUND);
                } else {
                    $this->mockUserRepository->expects($this->once())
                        ->method('findByEmail')
                        ->with($email)
                        ->willReturn($this->testUser);
                    
                    $this->expectException(AuthException::class);
                    $this->expectExceptionMessage(AuthException::INVALID_CREDENTIALS);
                }
            }

            $this->authService->login($email, $password, $remember);
        }
    }
}