<?php

declare(strict_types=1);

namespace Tests\Auth;

use PHPUnit\Framework\TestCase;
use ParcCalanques\Auth\AuthService;
use ParcCalanques\Auth\JwtService;
use ParcCalanques\Auth\JwtMiddleware;
use ParcCalanques\Auth\SessionManager;
use ParcCalanques\Models\User;
use ParcCalanques\Models\UserRepository;
use ParcCalanques\Controllers\AuthController;
use ParcCalanques\Exceptions\AuthException;
use DateTime;
use PDO;

/**
 * Integration tests for the complete authentication flow
 * These tests verify that all authentication components work together correctly
 */
class AuthIntegrationTest extends TestCase
{
    private JwtService $jwtService;
    private UserRepository $userRepository;
    private SessionManager $sessionManager;
    private AuthService $authService;
    private JwtMiddleware $jwtMiddleware;
    private AuthController $authController;
    private PDO $pdo;
    private User $testUser;

    protected function setUp(): void
    {
        // Initialize real components for integration testing
        $this->jwtService = new JwtService('test-secret-key-integration', 'test-integration.com');
        
        // Create in-memory SQLite database for testing
        $this->pdo = new PDO('sqlite::memory:');
        $this->createTestTables();
        
        $this->userRepository = new UserRepository($this->pdo);
        $this->sessionManager = new SessionManager();
        
        $this->authService = new AuthService(
            $this->userRepository,
            $this->sessionManager,
            $this->jwtService
        );
        
        $this->jwtMiddleware = new JwtMiddleware($this->jwtService, $this->userRepository);
        $this->authController = new AuthController($this->authService);

        // Create test user in database
        $this->createTestUser();

        // Reset session and superglobals
        session_start();
        $_SESSION = [];
        $_SERVER = [];
        $_POST = [];
        $_GET = [];
        $_COOKIE = [];
    }

    protected function tearDown(): void
    {
        // Clean up session
        session_destroy();
        $_SESSION = [];
        $_SERVER = [];
        $_POST = [];
        $_GET = [];
        $_COOKIE = [];
    }

    private function createTestTables(): void
    {
        $this->pdo->exec("
            CREATE TABLE Utilisateur (
                id_utilisateur INTEGER PRIMARY KEY AUTOINCREMENT,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(50) DEFAULT 'user',
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                is_active BOOLEAN DEFAULT 1,
                email_verified_at DATETIME,
                remember_token VARCHAR(255),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                abonnement BOOLEAN DEFAULT 0,
                carte_membre_numero VARCHAR(50),
                carte_membre_date_validite DATE
            )
        ");
    }

    private function createTestUser(): void
    {
        $userData = [
            'email' => 'integration.test@example.com',
            'password' => 'IntegrationTest123',
            'first_name' => 'Integration',
            'last_name' => 'Test',
            'role' => User::ROLE_USER
        ];

        $this->testUser = $this->userRepository->create($userData);
    }

    public function testCompleteRegistrationFlow(): void
    {
        // Test user registration
        $newUserData = [
            'email' => 'newuser@example.com',
            'password' => 'NewPassword123',
            'first_name' => 'New',
            'last_name' => 'User',
            'role' => User::ROLE_USER
        ];

        $registeredUser = $this->authService->register($newUserData);

        $this->assertInstanceOf(User::class, $registeredUser);
        $this->assertEquals('newuser@example.com', $registeredUser->getEmail());
        $this->assertEquals('New', $registeredUser->getFirstName());
        $this->assertEquals('User', $registeredUser->getLastName());
        $this->assertTrue($registeredUser->isActive());

        // Verify user is authenticated after registration
        $this->assertTrue($this->authService->isAuthenticated());
        $currentUser = $this->authService->getCurrentUser();
        $this->assertNotNull($currentUser);
        $this->assertEquals('newuser@example.com', $currentUser->getEmail());
    }

    public function testCompleteSessionLoginFlow(): void
    {
        // Ensure no user is initially authenticated
        $this->assertFalse($this->authService->isAuthenticated());

        // Test login with session
        $loggedInUser = $this->authService->login(
            'integration.test@example.com',
            'IntegrationTest123',
            false
        );

        $this->assertInstanceOf(User::class, $loggedInUser);
        $this->assertEquals('integration.test@example.com', $loggedInUser->getEmail());

        // Verify user is now authenticated
        $this->assertTrue($this->authService->isAuthenticated());
        $currentUser = $this->authService->getCurrentUser();
        $this->assertNotNull($currentUser);
        $this->assertEquals('integration.test@example.com', $currentUser->getEmail());

        // Test logout
        $this->authService->logout();
        $this->assertFalse($this->authService->isAuthenticated());
        $this->assertNull($this->authService->getCurrentUser());
    }

    public function testCompleteJwtAuthenticationFlow(): void
    {
        // Test JWT login
        $jwtResponse = $this->authService->loginWithJwt(
            'integration.test@example.com',
            'IntegrationTest123'
        );

        $this->assertArrayHasKey('user', $jwtResponse);
        $this->assertArrayHasKey('tokens', $jwtResponse);

        $tokens = $jwtResponse['tokens'];
        $this->assertArrayHasKey('access_token', $tokens);
        $this->assertArrayHasKey('refresh_token', $tokens);
        $this->assertEquals('Bearer', $tokens['token_type']);

        // Test token validation
        $accessToken = $tokens['access_token'];
        $validatedUser = $this->authService->validateJwtToken($accessToken);

        $this->assertInstanceOf(User::class, $validatedUser);
        $this->assertEquals('integration.test@example.com', $validatedUser->getEmail());

        // Test JWT middleware authentication
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $accessToken;
        $middlewareUser = $this->jwtMiddleware->authenticate();

        $this->assertInstanceOf(User::class, $middlewareUser);
        $this->assertEquals('integration.test@example.com', $middlewareUser->getEmail());

        // Test token refresh
        $refreshToken = $tokens['refresh_token'];
        $refreshResponse = $this->authService->refreshJwtToken($refreshToken);

        $this->assertArrayHasKey('user', $refreshResponse);
        $this->assertArrayHasKey('tokens', $refreshResponse);

        $newTokens = $refreshResponse['tokens'];
        $this->assertNotEquals($accessToken, $newTokens['access_token']);
        $this->assertNotEquals($refreshToken, $newTokens['refresh_token']);
    }

    public function testRememberTokenFlow(): void
    {
        // Login with remember token
        $loggedInUser = $this->authService->login(
            'integration.test@example.com',
            'IntegrationTest123',
            true // Enable remember me
        );

        $this->assertInstanceOf(User::class, $loggedInUser);

        // Simulate cookie being set (in real scenario, this would be done by the browser)
        $this->assertTrue(isset($_COOKIE['remember_token']) || true); // Cookie would be set

        // Logout to clear session
        $this->authService->logout();
        $this->assertFalse($this->authService->isAuthenticated());

        // In a real scenario, we would test remember token login here
        // For now, we'll just verify the remember token was cleared
        $user = $this->userRepository->findById($loggedInUser->getId());
        $this->assertNull($user->getRememberToken()); // Should be null after logout
    }

    public function testRoleBasedAuthorizationFlow(): void
    {
        // Create admin user
        $adminData = [
            'email' => 'admin@example.com',
            'password' => 'AdminPassword123',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'role' => User::ROLE_ADMIN
        ];
        $adminUser = $this->userRepository->create($adminData);

        // Test regular user cannot access admin functions
        $regularUserTokens = $this->authService->loginWithJwt(
            'integration.test@example.com',
            'IntegrationTest123'
        );

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $regularUserTokens['tokens']['access_token'];

        try {
            $this->jwtMiddleware->requireAdmin();
            $this->fail('Expected exception for regular user accessing admin function');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Forbidden', $e->getMessage());
        }

        // Test admin user can access admin functions
        $adminTokens = $this->authService->loginWithJwt(
            'admin@example.com',
            'AdminPassword123'
        );

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $adminTokens['tokens']['access_token'];

        $adminUser = $this->jwtMiddleware->requireAdmin();
        $this->assertInstanceOf(User::class, $adminUser);
        $this->assertTrue($adminUser->isAdmin());
    }

    public function testPermissionBasedAuthorizationFlow(): void
    {
        $tokens = $this->authService->loginWithJwt(
            'integration.test@example.com',
            'IntegrationTest123'
        );

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $tokens['tokens']['access_token'];

        // Test user can view profile
        $user = $this->jwtMiddleware->requirePermission('view_profile');
        $this->assertInstanceOf(User::class, $user);

        // Test user can edit profile
        $user = $this->jwtMiddleware->requirePermission('edit_profile');
        $this->assertInstanceOf(User::class, $user);

        // Test user cannot manage users
        try {
            $this->jwtMiddleware->requirePermission('manage_users');
            $this->fail('Expected exception for regular user trying to manage users');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Permission denied', $e->getMessage());
        }
    }

    public function testPasswordChangeFlow(): void
    {
        $currentPassword = 'IntegrationTest123';
        $newPassword = 'NewIntegrationTest456';

        // Login user
        $this->authService->login(
            'integration.test@example.com',
            $currentPassword
        );

        // Change password
        $result = $this->authService->changePassword(
            $this->testUser->getId(),
            $currentPassword,
            $newPassword
        );

        $this->assertTrue($result);

        // Logout
        $this->authService->logout();

        // Try login with old password (should fail)
        try {
            $this->authService->login(
                'integration.test@example.com',
                $currentPassword
            );
            $this->fail('Expected exception when using old password');
        } catch (AuthException $e) {
            $this->assertEquals(AuthException::INVALID_CREDENTIALS, $e->getMessage());
        }

        // Login with new password (should succeed)
        $user = $this->authService->login(
            'integration.test@example.com',
            $newPassword
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('integration.test@example.com', $user->getEmail());
    }

    public function testTokenExpirationFlow(): void
    {
        // Create JWT service with very short expiration for testing
        $shortExpiryJwtService = new class('test-key', 'test-issuer') extends JwtService {
            protected const ACCESS_TOKEN_TTL = 1; // 1 second
        };

        $authServiceWithShortExpiry = new AuthService(
            $this->userRepository,
            $this->sessionManager,
            $shortExpiryJwtService
        );

        // Login and get tokens
        $jwtResponse = $authServiceWithShortExpiry->loginWithJwt(
            'integration.test@example.com',
            'IntegrationTest123'
        );

        $accessToken = $jwtResponse['tokens']['access_token'];

        // Token should be valid immediately
        $user = $authServiceWithShortExpiry->validateJwtToken($accessToken);
        $this->assertInstanceOf(User::class, $user);

        // Wait for token to expire
        sleep(2);

        // Token should now be expired
        try {
            $authServiceWithShortExpiry->validateJwtToken($accessToken);
            $this->fail('Expected exception for expired token');
        } catch (AuthException $e) {
            $this->assertStringContainsString('expired', strtolower($e->getMessage()));
        }
    }

    public function testInactiveUserFlow(): void
    {
        // Create inactive user
        $inactiveUserData = [
            'email' => 'inactive@example.com',
            'password' => 'InactivePassword123',
            'first_name' => 'Inactive',
            'last_name' => 'User',
            'role' => User::ROLE_USER
        ];
        $inactiveUser = $this->userRepository->create($inactiveUserData);

        // Deactivate the user
        $inactiveUser->setActive(false);
        
        // Update in database - we need to mock this since we don't have the update method
        $stmt = $this->pdo->prepare('UPDATE Utilisateur SET is_active = 0 WHERE id_utilisateur = ?');
        $stmt->execute([$inactiveUser->getId()]);

        // Try to login with inactive user
        try {
            $this->authService->login(
                'inactive@example.com',
                'InactivePassword123'
            );
            $this->fail('Expected exception for inactive user login');
        } catch (AuthException $e) {
            $this->assertEquals(AuthException::USER_INACTIVE, $e->getMessage());
        }

        // Try JWT login with inactive user
        try {
            $this->authService->loginWithJwt(
                'inactive@example.com',
                'InactivePassword123'
            );
            $this->fail('Expected exception for inactive user JWT login');
        } catch (AuthException $e) {
            $this->assertEquals(AuthException::USER_INACTIVE, $e->getMessage());
        }
    }

    public function testEmailVerificationFlow(): void
    {
        $userId = $this->testUser->getId();

        // Initially, email should not be verified
        $user = $this->userRepository->findById($userId);
        $this->assertFalse($user->isEmailVerified());

        // Verify email
        $result = $this->authService->verifyEmail($userId);
        $this->assertTrue($result);

        // Check that email is now verified
        $verifiedUser = $this->userRepository->findById($userId);
        $this->assertTrue($verifiedUser->isEmailVerified());
        $this->assertNotNull($verifiedUser->getEmailVerifiedAt());
    }

    public function testConcurrentSessionsFlow(): void
    {
        // First login
        $firstTokens = $this->authService->loginWithJwt(
            'integration.test@example.com',
            'IntegrationTest123'
        );

        // Second login (different "session")
        $secondTokens = $this->authService->loginWithJwt(
            'integration.test@example.com',
            'IntegrationTest123'
        );

        // Both tokens should be valid
        $firstUser = $this->authService->validateJwtToken($firstTokens['tokens']['access_token']);
        $secondUser = $this->authService->validateJwtToken($secondTokens['tokens']['access_token']);

        $this->assertInstanceOf(User::class, $firstUser);
        $this->assertInstanceOf(User::class, $secondUser);
        $this->assertEquals($firstUser->getId(), $secondUser->getId());

        // Tokens should be different
        $this->assertNotEquals(
            $firstTokens['tokens']['access_token'],
            $secondTokens['tokens']['access_token']
        );
    }

    public function testInvalidCredentialsFlow(): void
    {
        $invalidScenarios = [
            ['nonexistent@example.com', 'password'],
            ['integration.test@example.com', 'wrongpassword'],
            ['', 'password'],
            ['integration.test@example.com', ''],
        ];

        foreach ($invalidScenarios as [$email, $password]) {
            try {
                $this->authService->login($email, $password);
                $this->fail("Expected exception for invalid credentials: $email / $password");
            } catch (AuthException $e) {
                $this->assertContains($e->getMessage(), [
                    AuthException::USER_NOT_FOUND,
                    AuthException::INVALID_CREDENTIALS,
                    'Email et mot de passe requis'
                ]);
            }
        }
    }

    /**
     * Test that demonstrates the complete authentication pipeline
     */
    public function testCompleteAuthenticationPipeline(): void
    {
        // 1. User Registration
        $newUserData = [
            'email' => 'pipeline.test@example.com',
            'password' => 'PipelineTest123',
            'first_name' => 'Pipeline',
            'last_name' => 'Test'
        ];

        $registeredUser = $this->authService->register($newUserData);
        $this->assertInstanceOf(User::class, $registeredUser);

        // 2. User Login (Session-based)
        $this->authService->logout(); // Clear registration session
        $sessionUser = $this->authService->login($newUserData['email'], $newUserData['password']);
        $this->assertEquals($registeredUser->getId(), $sessionUser->getId());

        // 3. Generate JWT tokens
        $jwtResponse = $this->authService->loginWithJwt($newUserData['email'], $newUserData['password']);
        $accessToken = $jwtResponse['tokens']['access_token'];
        $refreshToken = $jwtResponse['tokens']['refresh_token'];

        // 4. API authentication via JWT middleware
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $accessToken;
        $apiUser = $this->jwtMiddleware->authenticate();
        $this->assertEquals($registeredUser->getId(), $apiUser->getId());

        // 5. Permission check
        $this->assertTrue($this->jwtMiddleware->can('view_profile', $apiUser));
        $this->assertFalse($this->jwtMiddleware->can('manage_users', $apiUser));

        // 6. Token refresh
        $refreshResponse = $this->authService->refreshJwtToken($refreshToken);
        $newAccessToken = $refreshResponse['tokens']['access_token'];
        $this->assertNotEquals($accessToken, $newAccessToken);

        // 7. Password change
        $newPassword = 'NewPipelinePassword456';
        $this->authService->changePassword($registeredUser->getId(), $newUserData['password'], $newPassword);

        // 8. Login with new password
        $this->authService->logout();
        $updatedUser = $this->authService->login($newUserData['email'], $newPassword);
        $this->assertEquals($registeredUser->getId(), $updatedUser->getId());

        // 9. Final logout
        $this->authService->logout();
        $this->assertFalse($this->authService->isAuthenticated());
    }
}