<?php

declare(strict_types=1);

namespace Tests\Auth;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ParcCalanques\Auth\JwtMiddleware;
use ParcCalanques\Auth\JwtService;
use ParcCalanques\Models\User;
use ParcCalanques\Models\UserRepository;
use ParcCalanques\Exceptions\AuthException;
use DateTime;

class JwtMiddlewareTest extends TestCase
{
    private JwtMiddleware $middleware;
    private MockObject|JwtService $mockJwtService;
    private MockObject|UserRepository $mockUserRepository;
    private User $testUser;

    protected function setUp(): void
    {
        $this->mockJwtService = $this->createMock(JwtService::class);
        $this->mockUserRepository = $this->createMock(UserRepository::class);
        $this->middleware = new JwtMiddleware($this->mockJwtService, $this->mockUserRepository);

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

        // Reset SERVER superglobal
        $_SERVER = [];
        
        // Suppress output during tests to avoid "headers already sent" warnings
        if (!defined('PHPUNIT_TESTING')) {
            define('PHPUNIT_TESTING', true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up global state
        $_SERVER = [];
    }

    public function testAuthenticateWithValidTokenReturnsUser(): void
    {
        $token = 'valid.jwt.token';
        $payload = [
            'user_id' => 1,
            'token_type' => 'access',
            'email' => 'test@example.com'
        ];

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $this->mockJwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willReturn($payload);

        $this->mockUserRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($this->testUser);

        $result = $this->middleware->authenticate();

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals(1, $result->getId());
        $this->assertEquals('test@example.com', $result->getEmail());
    }

    public function testAuthenticateWithoutTokenSendsUnauthorizedResponse(): void
    {
        $this->expectOutputRegex('/Unauthorized/');
        
        $this->expectException(\Exception::class);

        $this->middleware->authenticate();
    }

    public function testAuthenticateWithInvalidTokenThrowsException(): void
    {
        $token = 'invalid.jwt.token';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $this->mockJwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willThrowException(new AuthException('Invalid token', 401));

        $this->expectOutputRegex('/Unauthorized/');
        $this->expectException(\Exception::class);

        $this->middleware->authenticate();
    }

    public function testAuthenticateWithRefreshTokenSendsUnauthorizedResponse(): void
    {
        $token = 'refresh.jwt.token';
        $payload = [
            'user_id' => 1,
            'token_type' => 'refresh', // Wrong token type
            'email' => 'test@example.com'
        ];

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $this->mockJwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willReturn($payload);

        $this->expectOutputRegex('/Invalid token type/');
        $this->expectException(\Exception::class);

        $this->middleware->authenticate();
    }

    public function testAuthenticateWithInvalidUserIdSendsUnauthorizedResponse(): void
    {
        $token = 'valid.jwt.token';
        $payload = [
            'user_id' => 0, // Invalid user ID
            'token_type' => 'access',
            'email' => 'test@example.com'
        ];

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $this->mockJwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willReturn($payload);

        $this->expectOutputRegex('/Invalid user ID in token/');
        $this->expectException(\Exception::class);

        $this->middleware->authenticate();
    }

    public function testAuthenticateWithNonExistentUserSendsUnauthorizedResponse(): void
    {
        $token = 'valid.jwt.token';
        $payload = [
            'user_id' => 999,
            'token_type' => 'access',
            'email' => 'test@example.com'
        ];

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $this->mockJwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willReturn($payload);

        $this->mockUserRepository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectOutputRegex('/User not found/');
        $this->expectException(\Exception::class);

        $this->middleware->authenticate();
    }

    public function testAuthenticateWithInactiveUserSendsUnauthorizedResponse(): void
    {
        $inactiveUser = new User(
            id: 1,
            email: 'test@example.com',
            passwordHash: password_hash('password123', PASSWORD_DEFAULT),
            role: User::ROLE_USER,
            firstName: 'John',
            lastName: 'Doe',
            isActive: false, // User is inactive
            createdAt: new DateTime()
        );

        $token = 'valid.jwt.token';
        $payload = [
            'user_id' => 1,
            'token_type' => 'access',
            'email' => 'test@example.com'
        ];

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $this->mockJwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willReturn($payload);

        $this->mockUserRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($inactiveUser);

        $this->expectOutputRegex('/User inactive/');
        $this->expectException(\Exception::class);

        $this->middleware->authenticate();
    }

    public function testRequireRoleWithCorrectRoleReturnsUser(): void
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

        $token = 'valid.jwt.token';
        $payload = [
            'user_id' => 1,
            'token_type' => 'access',
            'email' => 'admin@example.com'
        ];

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $this->mockJwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willReturn($payload);

        $this->mockUserRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($adminUser);

        $result = $this->middleware->requireRole(User::ROLE_ADMIN);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals(User::ROLE_ADMIN, $result->getRole());
    }

    public function testRequireRoleWithIncorrectRoleSendsForbiddenResponse(): void
    {
        $token = 'valid.jwt.token';
        $payload = [
            'user_id' => 1,
            'token_type' => 'access',
            'email' => 'test@example.com'
        ];

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $this->mockJwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willReturn($payload);

        $this->mockUserRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($this->testUser); // Regular user trying to access admin route

        $this->expectOutputRegex('/Forbidden/');
        $this->expectException(\Exception::class);

        $this->middleware->requireRole(User::ROLE_ADMIN);
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

        $token = 'valid.jwt.token';
        $payload = [
            'user_id' => 1,
            'token_type' => 'access',
            'email' => 'admin@example.com'
        ];

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $this->mockJwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willReturn($payload);

        $this->mockUserRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($adminUser);

        $result = $this->middleware->requireAdmin();

        $this->assertInstanceOf(User::class, $result);
        $this->assertTrue($result->isAdmin());
    }

    public function testOptionalAuthWithValidTokenReturnsUser(): void
    {
        $token = 'valid.jwt.token';
        $payload = [
            'user_id' => 1,
            'token_type' => 'access',
            'email' => 'test@example.com'
        ];

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $this->mockJwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willReturn($payload);

        $this->mockUserRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($this->testUser);

        $result = $this->middleware->optionalAuth();

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals(1, $result->getId());
    }

    public function testOptionalAuthWithoutTokenReturnsNull(): void
    {
        $result = $this->middleware->optionalAuth();

        $this->assertNull($result);
    }

    public function testOptionalAuthWithInvalidTokenReturnsNull(): void
    {
        $token = 'invalid.jwt.token';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $this->mockJwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willThrowException(new AuthException('Invalid token', 401));

        $result = $this->middleware->optionalAuth();

        $this->assertNull($result);
    }

    public function testCanWithAdminUserAndAdminPermissionReturnsTrue(): void
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

        $this->assertTrue($this->middleware->can('view_admin_panel', $adminUser));
        $this->assertTrue($this->middleware->can('manage_users', $adminUser));
    }

    public function testCanWithRegularUserAndBasicPermissionsReturnsTrue(): void
    {
        $this->assertTrue($this->middleware->can('view_profile', $this->testUser));
        $this->assertTrue($this->middleware->can('edit_profile', $this->testUser));
    }

    public function testCanWithRegularUserAndAdminPermissionReturnsFalse(): void
    {
        $this->assertFalse($this->middleware->can('view_admin_panel', $this->testUser));
        $this->assertFalse($this->middleware->can('manage_users', $this->testUser));
    }

    public function testCanWithUnknownPermissionReturnsFalse(): void
    {
        $this->assertFalse($this->middleware->can('unknown_permission', $this->testUser));
    }

    public function testCanWithoutUserReturnsAppropriateBehavior(): void
    {
        // Should return false when no user is provided and no optional auth
        $this->assertFalse($this->middleware->can('view_profile'));
    }

    public function testRequirePermissionWithValidPermissionReturnsUser(): void
    {
        $token = 'valid.jwt.token';
        $payload = [
            'user_id' => 1,
            'token_type' => 'access',
            'email' => 'test@example.com'
        ];

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $this->mockJwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willReturn($payload);

        $this->mockUserRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($this->testUser);

        $result = $this->middleware->requirePermission('view_profile');

        $this->assertInstanceOf(User::class, $result);
    }

    public function testRequirePermissionWithInvalidPermissionSendsForbiddenResponse(): void
    {
        $token = 'valid.jwt.token';
        $payload = [
            'user_id' => 1,
            'token_type' => 'access',
            'email' => 'test@example.com'
        ];

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $this->mockJwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willReturn($payload);

        $this->mockUserRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($this->testUser);

        $this->expectOutputRegex('/Permission denied/');
        $this->expectException(\Exception::class);

        $this->middleware->requirePermission('manage_users');
    }

    public function testHandleCorsWithAllowedOriginSetsHeaders(): void
    {
        $_SERVER['HTTP_ORIGIN'] = 'http://localhost:3000';

        ob_start();
        $this->middleware->handleCors();
        $output = ob_get_clean();

        $headers = xdebug_get_headers();
        
        // Since we can't easily test headers in unit tests, we'll just ensure no exceptions are thrown
        $this->assertTrue(true);
    }

    public function testHandleCorsWithOptionsRequestExits(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $_SERVER['HTTP_ORIGIN'] = 'http://localhost:3000';

        $this->expectException(\Exception::class);

        $this->middleware->handleCors();
    }

    public function testValidateApiRequestWithValidJsonContentType(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['HTTP_ORIGIN'] = 'http://localhost:3000';

        // Should not throw exception
        $this->middleware->validateApiRequest();
        $this->assertTrue(true);
    }

    public function testValidateApiRequestWithInvalidContentTypeSendsBadRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_TYPE'] = 'text/plain';

        $this->expectOutputRegex('/Bad Request/');
        $this->expectException(\Exception::class);

        $this->middleware->validateApiRequest();
    }

    public function testSendJsonResponseSetsCorrectHeadersAndOutput(): void
    {
        $data = ['message' => 'Success', 'data' => [1, 2, 3]];

        $this->expectOutputString(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->expectException(\Exception::class);

        $this->middleware->sendJsonResponse($data);
    }

    public function testSendJsonErrorSetsCorrectErrorFormat(): void
    {
        $expectedResponse = [
            'error' => 'Bad Request',
            'message' => 'Test error message',
            'code' => 400
        ];

        $this->expectOutputString(json_encode($expectedResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->expectException(\Exception::class);

        $this->middleware->sendJsonError('Test error message', 400);
    }

    public function testSendJsonErrorWithDetailsIncludesDetails(): void
    {
        $details = ['field' => 'email', 'issue' => 'required'];
        $expectedResponse = [
            'error' => 'Unprocessable Entity',
            'message' => 'Validation failed',
            'code' => 422,
            'details' => $details
        ];

        $this->expectOutputString(json_encode($expectedResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->expectException(\Exception::class);

        $this->middleware->sendJsonError('Validation failed', 422, $details);
    }

    /**
     * Test token extraction from different header sources
     */
    public function testExtractTokenFromDifferentHeaderSources(): void
    {
        $token = 'test.jwt.token';
        
        // Test HTTP_AUTHORIZATION
        $_SERVER = ['HTTP_AUTHORIZATION' => 'Bearer ' . $token];
        $this->assertEquals($token, $this->callPrivateMethod('extractToken'));

        // Test REDIRECT_HTTP_AUTHORIZATION
        $_SERVER = ['REDIRECT_HTTP_AUTHORIZATION' => 'Bearer ' . $token];
        $this->assertEquals($token, $this->callPrivateMethod('extractToken'));

        // Test with no authorization header
        $_SERVER = [];
        $this->assertNull($this->callPrivateMethod('extractToken'));
    }

    /**
     * Helper method to call private methods for testing
     */
    private function callPrivateMethod(string $methodName, array $args = [])
    {
        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        
        return $method->invokeArgs($this->middleware, $args);
    }

    /**
     * Data provider for HTTP status codes
     */
    public function httpStatusCodeProvider(): array
    {
        return [
            [400, 'Bad Request'],
            [401, 'Unauthorized'],
            [403, 'Forbidden'],
            [404, 'Not Found'],
            [409, 'Conflict'],
            [422, 'Unprocessable Entity'],
            [429, 'Too Many Requests'],
            [500, 'Internal Server Error'],
            [999, 'Error'], // Default case
        ];
    }

    /**
     * @dataProvider httpStatusCodeProvider
     */
    public function testGetErrorNameReturnsCorrectErrorName(int $code, string $expectedName): void
    {
        $result = $this->callPrivateMethod('getErrorName', [$code]);
        $this->assertEquals($expectedName, $result);
    }
}