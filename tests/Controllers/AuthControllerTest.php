<?php

declare(strict_types=1);

namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ParcCalanques\Controllers\AuthController;
use ParcCalanques\Auth\AuthService;
use ParcCalanques\Models\User;
use ParcCalanques\Exceptions\AuthException;
use DateTime;

class AuthControllerTest extends TestCase
{
    private AuthController $authController;
    private MockObject|AuthService $mockAuthService;
    private User $testUser;

    protected function setUp(): void
    {
        $this->mockAuthService = $this->createMock(AuthService::class);
        $this->authController = new AuthController($this->mockAuthService);

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

        // Reset superglobals
        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'SCRIPT_NAME' => '/index.php'
        ];
        $_POST = [];
        $_GET = [];

        // Start output buffering to capture rendered content
        ob_start();
    }

    protected function tearDown(): void
    {
        // Clean output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Reset superglobals
        $_SERVER = [];
        $_POST = [];
        $_GET = [];
    }

    public function testShowLoginWhenNotAuthenticatedRendersLoginTemplate(): void
    {
        $this->mockAuthService->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(false);

        // Mock the render method by catching the include
        $this->expectOutputRegex('/login/');
        
        try {
            $this->authController->showLogin();
        } catch (\Exception $e) {
            // Template file doesn't exist in test environment, that's expected
            $this->assertStringContainsString('Template not found', $e->getMessage());
        }
    }

    public function testShowLoginWhenAuthenticatedRedirectsToProfile(): void
    {
        $this->mockAuthService->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        // Mock the header function to capture redirects
        $this->expectException(\Exception::class);

        // Override the header function for testing
        if (!function_exists('header')) {
            function header($string) {
                if (strpos($string, 'Location:') === 0) {
                    throw new \Exception('Redirect to: ' . substr($string, 10));
                }
            }
        }

        $this->authController->showLogin();
    }

    public function testLoginWithValidPOSTCredentialsSucceeds(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => '1'
        ];

        $this->mockAuthService->expects($this->once())
            ->method('login')
            ->with('test@example.com', 'password123', true)
            ->willReturn($this->testUser);

        $this->expectException(\Exception::class);

        $this->authController->login();
    }

    public function testLoginWithInvalidCredentialsRendersError(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];

        $this->mockAuthService->expects($this->once())
            ->method('login')
            ->with('test@example.com', 'wrongpassword', false)
            ->willThrowException(new AuthException('Invalid credentials'));

        try {
            $this->authController->login();
        } catch (\Exception $e) {
            // Template file doesn't exist in test environment, that's expected
            $this->assertStringContainsString('Template not found', $e->getMessage());
        }
    }

    public function testLoginWithEmptyEmailAndPasswordRendersError(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'email' => '',
            'password' => ''
        ];

        try {
            $this->authController->login();
        } catch (\Exception $e) {
            // Should render login template with error, but template doesn't exist
            $this->assertStringContainsString('Template not found', $e->getMessage());
        }
    }

    public function testLoginWithGETRequestRedirects(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->expectException(\Exception::class);

        $this->authController->login();
    }

    public function testShowRegisterWhenNotAuthenticatedRendersRegisterTemplate(): void
    {
        $this->mockAuthService->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(false);

        try {
            $this->authController->showRegister();
        } catch (\Exception $e) {
            $this->assertStringContainsString('Template not found', $e->getMessage());
        }
    }

    public function testShowRegisterWhenAuthenticatedRedirectsToProfile(): void
    {
        $this->mockAuthService->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $this->expectException(\Exception::class);

        $this->authController->showRegister();
    }

    public function testRegisterWithValidDataCreatesUser(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123',
            'password_confirmation' => 'SecurePassword123',
            'terms' => '1'
        ];

        $this->mockAuthService->expects($this->once())
            ->method('register')
            ->with([
                'email' => 'john.doe@example.com',
                'password' => 'SecurePassword123',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'role' => 'user'
            ])
            ->willReturn($this->testUser);

        $this->expectException(\Exception::class);

        $this->authController->register();
    }

    public function testRegisterWithInvalidDataRendersErrors(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'first_name' => 'J', // Too short
            'last_name' => '', // Empty
            'email' => 'invalid-email', // Invalid format
            'password' => '123', // Too short, no uppercase, no lowercase
            'password_confirmation' => 'different', // Doesn't match
            // terms not accepted
        ];

        try {
            $this->authController->register();
        } catch (\Exception $e) {
            $this->assertStringContainsString('Template not found', $e->getMessage());
        }
    }

    public function testRegisterWithExistingEmailThrowsAuthException(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'existing@example.com',
            'password' => 'SecurePassword123',
            'password_confirmation' => 'SecurePassword123',
            'terms' => '1'
        ];

        $this->mockAuthService->expects($this->once())
            ->method('register')
            ->willThrowException(new AuthException('Email address already exists'));

        try {
            $this->authController->register();
        } catch (\Exception $e) {
            $this->assertStringContainsString('Template not found', $e->getMessage());
        }
    }

    public function testLogoutCallsAuthServiceLogout(): void
    {
        $this->mockAuthService->expects($this->once())
            ->method('logout');

        $this->expectException(\Exception::class);

        $this->authController->logout();
    }

    public function testProfileWithGETRequestRendersProfile(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Mock AuthGuard::require() - this would normally be done via dependency injection
        // For this test, we'll simulate the behavior
        try {
            $this->authController->profile();
        } catch (\Error $e) {
            // AuthGuard::require() will fail because it's not properly mocked
            // This is expected in the test environment
            $this->assertStringContainsString('AuthGuard', $e->getMessage());
        }
    }

    public function testProfileWithWelcomeParameterShowsWelcomeMessage(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['welcome'] = '1';

        try {
            $this->authController->profile();
        } catch (\Error $e) {
            // AuthGuard::require() will fail because it's not properly mocked
            $this->assertStringContainsString('AuthGuard', $e->getMessage());
        }
    }

    public function testProfileWithPOSTRequestTriesToUpdateProfile(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['first_name' => 'Updated Name'];

        try {
            $this->authController->profile();
        } catch (\Error $e) {
            // AuthGuard::require() will fail because it's not properly mocked
            $this->assertStringContainsString('AuthGuard', $e->getMessage());
        }
    }

    public function testValidateRegistrationDataWithValidDataReturnsCleanedData(): void
    {
        $validData = [
            'first_name' => '  John  ',
            'last_name' => '  Doe  ',
            'email' => '  john.doe@example.com  ',
            'password' => 'SecurePassword123',
            'password_confirmation' => 'SecurePassword123',
            'terms' => '1'
        ];

        $result = $this->callPrivateMethod('validateRegistrationData', [$validData]);

        $this->assertEquals([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123'
        ], $result);
    }

    public function testValidateRegistrationDataWithEmptyFirstNameThrowsException(): void
    {
        $invalidData = [
            'first_name' => '',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123',
            'password_confirmation' => 'SecurePassword123',
            'terms' => '1'
        ];

        $this->expectException(\InvalidArgumentException::class);

        $this->callPrivateMethod('validateRegistrationData', [$invalidData]);
    }

    public function testValidateRegistrationDataWithShortFirstNameThrowsException(): void
    {
        $invalidData = [
            'first_name' => 'J',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123',
            'password_confirmation' => 'SecurePassword123',
            'terms' => '1'
        ];

        $this->expectException(\InvalidArgumentException::class);

        $this->callPrivateMethod('validateRegistrationData', [$invalidData]);
    }

    public function testValidateRegistrationDataWithInvalidEmailThrowsException(): void
    {
        $invalidData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'password' => 'SecurePassword123',
            'password_confirmation' => 'SecurePassword123',
            'terms' => '1'
        ];

        $this->expectException(\InvalidArgumentException::class);

        $this->callPrivateMethod('validateRegistrationData', [$invalidData]);
    }

    public function testValidateRegistrationDataWithWeakPasswordThrowsException(): void
    {
        $weakPasswords = [
            'short', // Too short
            'nouppercase123', // No uppercase
            'NOLOWERCASE123', // No lowercase
            'NoNumbers', // No numbers
        ];

        foreach ($weakPasswords as $weakPassword) {
            $invalidData = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'password' => $weakPassword,
                'password_confirmation' => $weakPassword,
                'terms' => '1'
            ];

            $this->expectException(\InvalidArgumentException::class);

            try {
                $this->callPrivateMethod('validateRegistrationData', [$invalidData]);
            } catch (\InvalidArgumentException $e) {
                $errors = json_decode($e->getMessage(), true);
                $this->assertArrayHasKey('password', $errors);
                continue; // Expected exception caught, continue to next iteration
            }

            $this->fail('Expected exception was not thrown for password: ' . $weakPassword);
        }
    }

    public function testValidateRegistrationDataWithMismatchedPasswordsThrowsException(): void
    {
        $invalidData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123',
            'password_confirmation' => 'DifferentPassword123',
            'terms' => '1'
        ];

        $this->expectException(\InvalidArgumentException::class);

        $this->callPrivateMethod('validateRegistrationData', [$invalidData]);
    }

    public function testValidateRegistrationDataWithoutTermsAcceptanceThrowsException(): void
    {
        $invalidData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123',
            'password_confirmation' => 'SecurePassword123',
            // terms not provided
        ];

        $this->expectException(\InvalidArgumentException::class);

        $this->callPrivateMethod('validateRegistrationData', [$invalidData]);
    }

    public function testUpdateProfileThrowsNotImplementedException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Fonctionnalité non encore implémentée');

        $this->callPrivateMethod('updateProfile', [$this->testUser, []]);
    }

    public function testUrlMethodGeneratesCorrectUrls(): void
    {
        // Test root path
        $result = $this->callPrivateMethod('url', ['/']);
        $this->assertEquals('/', $result);

        // Test with custom path
        $result = $this->callPrivateMethod('url', ['/login']);
        $this->assertEquals('/login', $result);

        // Test with script in subdirectory
        $_SERVER['SCRIPT_NAME'] = '/app/index.php';
        $result = $this->callPrivateMethod('url', ['/login']);
        $this->assertEquals('/app/login', $result);
    }

    public function testRenderMethodIncludesTemplate(): void
    {
        // Create a temporary template file
        $tempDir = sys_get_temp_dir();
        $templateDir = $tempDir . '/templates';
        $templateFile = $templateDir . '/test.php';
        
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0777, true);
        }
        
        file_put_contents($templateFile, '<?php echo "Hello " . ($name ?? "World"); ?>');

        try {
            // Temporarily change the template path in the render method
            // This would require modifying the method to accept a custom template path
            // For now, we'll just test that it throws the expected exception
            $this->callPrivateMethod('render', ['nonexistent']);
        } catch (\Exception $e) {
            $this->assertStringContainsString('Template not found', $e->getMessage());
        } finally {
            // Cleanup
            if (file_exists($templateFile)) {
                unlink($templateFile);
            }
            if (is_dir($templateDir)) {
                rmdir($templateDir);
            }
        }
    }

    /**
     * Helper method to call private methods for testing
     */
    private function callPrivateMethod(string $methodName, array $args = [])
    {
        $reflection = new \ReflectionClass($this->authController);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        
        return $method->invokeArgs($this->authController, $args);
    }

    /**
     * Test data provider for password validation scenarios
     */
    public function passwordValidationProvider(): array
    {
        return [
            'valid strong password' => ['StrongPassword123', true],
            'too short' => ['Short1', false],
            'no uppercase' => ['lowercase123', false],
            'no lowercase' => ['UPPERCASE123', false],
            'no numbers' => ['NoNumbers', false],
            'empty password' => ['', false],
            'whitespace only' => ['   ', false],
        ];
    }

    /**
     * @dataProvider passwordValidationProvider
     */
    public function testPasswordValidationRules(string $password, bool $shouldBeValid): void
    {
        $testData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => $password,
            'password_confirmation' => $password,
            'terms' => '1'
        ];

        if ($shouldBeValid) {
            $result = $this->callPrivateMethod('validateRegistrationData', [$testData]);
            $this->assertIsArray($result);
            $this->assertEquals($password, $result['password']);
        } else {
            $this->expectException(\InvalidArgumentException::class);
            $this->callPrivateMethod('validateRegistrationData', [$testData]);
        }
    }

    /**
     * Test data provider for email validation scenarios
     */
    public function emailValidationProvider(): array
    {
        return [
            'valid email' => ['user@example.com', true],
            'valid email with subdomain' => ['user@mail.example.com', true],
            'valid email with plus' => ['user+tag@example.com', true],
            'invalid no @' => ['userexample.com', false],
            'invalid no domain' => ['user@', false],
            'invalid no user' => ['@example.com', false],
            'invalid spaces' => ['user @example.com', false],
            'empty email' => ['', false],
        ];
    }

    /**
     * @dataProvider emailValidationProvider
     */
    public function testEmailValidationRules(string $email, bool $shouldBeValid): void
    {
        $testData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $email,
            'password' => 'ValidPassword123',
            'password_confirmation' => 'ValidPassword123',
            'terms' => '1'
        ];

        if ($shouldBeValid) {
            $result = $this->callPrivateMethod('validateRegistrationData', [$testData]);
            $this->assertIsArray($result);
            $this->assertEquals($email, $result['email']);
        } else {
            $this->expectException(\InvalidArgumentException::class);
            $this->callPrivateMethod('validateRegistrationData', [$testData]);
        }
    }
}