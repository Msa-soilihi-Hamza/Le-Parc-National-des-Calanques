<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use ParcCalanques\Models\User;
use DateTime;

/**
 * Helper class for common test utilities and data factories
 */
class TestHelper
{
    /**
     * Create a test user with default or custom data
     */
    public static function createTestUser(array $overrides = []): User
    {
        $defaults = [
            'id' => 1,
            'email' => 'test@example.com',
            'passwordHash' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => User::ROLE_USER,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'isActive' => true,
            'emailVerifiedAt' => new DateTime('2023-01-01 10:00:00'),
            'rememberToken' => null,
            'createdAt' => new DateTime('2023-01-01 10:00:00'),
            'updatedAt' => new DateTime('2023-01-01 10:00:00'),
            'abonnement' => false,
            'carteMembreNumero' => null,
            'carteMembreDateValidite' => null
        ];

        $data = array_merge($defaults, $overrides);

        return new User(
            id: $data['id'],
            email: $data['email'],
            passwordHash: $data['passwordHash'],
            role: $data['role'],
            firstName: $data['firstName'],
            lastName: $data['lastName'],
            isActive: $data['isActive'],
            emailVerifiedAt: $data['emailVerifiedAt'],
            rememberToken: $data['rememberToken'],
            createdAt: $data['createdAt'],
            updatedAt: $data['updatedAt'],
            abonnement: $data['abonnement'],
            carteMembreNumero: $data['carteMembreNumero'],
            carteMembreDateValidite: $data['carteMembreDateValidite']
        );
    }

    /**
     * Create an admin test user
     */
    public static function createAdminUser(array $overrides = []): User
    {
        $adminDefaults = [
            'id' => 2,
            'email' => 'admin@example.com',
            'role' => User::ROLE_ADMIN,
            'firstName' => 'Admin',
            'lastName' => 'User'
        ];

        return self::createTestUser(array_merge($adminDefaults, $overrides));
    }

    /**
     * Create an inactive test user
     */
    public static function createInactiveUser(array $overrides = []): User
    {
        $inactiveDefaults = [
            'id' => 3,
            'email' => 'inactive@example.com',
            'isActive' => false,
            'firstName' => 'Inactive',
            'lastName' => 'User'
        ];

        return self::createTestUser(array_merge($inactiveDefaults, $overrides));
    }

    /**
     * Create a user with unverified email
     */
    public static function createUnverifiedUser(array $overrides = []): User
    {
        $unverifiedDefaults = [
            'id' => 4,
            'email' => 'unverified@example.com',
            'emailVerifiedAt' => null,
            'firstName' => 'Unverified',
            'lastName' => 'User'
        ];

        return self::createTestUser(array_merge($unverifiedDefaults, $overrides));
    }

    /**
     * Generate a valid JWT payload for testing
     */
    public static function generateJwtPayload(User $user, string $tokenType = 'access', int $expiresIn = 3600): array
    {
        $now = time();
        
        $basePayload = [
            'iss' => 'test-issuer.com',
            'aud' => 'test-issuer.com',
            'iat' => $now,
            'exp' => $now + $expiresIn,
            'sub' => (string) $user->getId(),
            'user_id' => $user->getId(),
            'token_type' => $tokenType
        ];

        if ($tokenType === 'access') {
            $basePayload = array_merge($basePayload, [
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'is_active' => $user->isActive()
            ]);
        }

        return $basePayload;
    }

    /**
     * Generate test registration data
     */
    public static function generateRegistrationData(array $overrides = []): array
    {
        $defaults = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test.registration@example.com',
            'password' => 'TestPassword123',
            'password_confirmation' => 'TestPassword123',
            'terms' => '1'
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Generate invalid registration data for testing validation
     */
    public static function generateInvalidRegistrationData(string $type = 'all'): array
    {
        $invalidData = [
            'empty_first_name' => [
                'first_name' => '',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'password' => 'TestPassword123',
                'password_confirmation' => 'TestPassword123',
                'terms' => '1'
            ],
            'short_first_name' => [
                'first_name' => 'T',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'password' => 'TestPassword123',
                'password_confirmation' => 'TestPassword123',
                'terms' => '1'
            ],
            'invalid_email' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'invalid-email',
                'password' => 'TestPassword123',
                'password_confirmation' => 'TestPassword123',
                'terms' => '1'
            ],
            'weak_password' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'password' => '123',
                'password_confirmation' => '123',
                'terms' => '1'
            ],
            'mismatched_passwords' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'password' => 'TestPassword123',
                'password_confirmation' => 'DifferentPassword123',
                'terms' => '1'
            ],
            'no_terms' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'password' => 'TestPassword123',
                'password_confirmation' => 'TestPassword123'
                // terms omitted
            ]
        ];

        if ($type === 'all') {
            return $invalidData;
        }

        return $invalidData[$type] ?? [];
    }

    /**
     * Mock $_SERVER superglobal for HTTP requests
     */
    public static function mockHttpRequest(string $method = 'GET', array $headers = [], string $uri = '/'): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_HOST'] = 'localhost';

        // Set headers
        foreach ($headers as $name => $value) {
            $headerName = 'HTTP_' . str_replace('-', '_', strtoupper($name));
            $_SERVER[$headerName] = $value;
        }
    }

    /**
     * Mock JWT Authorization header
     */
    public static function mockJwtAuthHeader(string $token): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }

    /**
     * Clean up superglobals after tests
     */
    public static function cleanupSuperglobals(): void
    {
        $_SERVER = [];
        $_POST = [];
        $_GET = [];
        $_COOKIE = [];
        $_SESSION = [];
    }

    /**
     * Create a temporary database for testing
     */
    public static function createTestDatabase(): \PDO
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        // Create test tables
        $pdo->exec("
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

        return $pdo;
    }

    /**
     * Assert that an array contains expected JWT token structure
     */
    public static function assertValidTokenStructure(array $tokens, TestCase $testCase): void
    {
        $testCase->assertArrayHasKey('access_token', $tokens);
        $testCase->assertArrayHasKey('refresh_token', $tokens);
        $testCase->assertArrayHasKey('token_type', $tokens);
        $testCase->assertArrayHasKey('expires_in', $tokens);
        $testCase->assertArrayHasKey('expires_at', $tokens);
        
        $testCase->assertIsString($tokens['access_token']);
        $testCase->assertIsString($tokens['refresh_token']);
        $testCase->assertEquals('Bearer', $tokens['token_type']);
        $testCase->assertIsInt($tokens['expires_in']);
        $testCase->assertIsInt($tokens['expires_at']);
        $testCase->assertNotEmpty($tokens['access_token']);
        $testCase->assertNotEmpty($tokens['refresh_token']);
    }

    /**
     * Assert that an array contains expected user data structure
     */
    public static function assertValidUserArray(array $userData, TestCase $testCase): void
    {
        $expectedKeys = [
            'id', 'email', 'role', 'first_name', 'last_name', 'full_name',
            'is_active', 'is_email_verified', 'email_verified_at',
            'created_at', 'updated_at', 'abonnement', 'carte_membre_numero',
            'carte_membre_date_validite', 'carte_membre_valide'
        ];

        foreach ($expectedKeys as $key) {
            $testCase->assertArrayHasKey($key, $userData, "User array should contain key: {$key}");
        }

        // Assert sensitive data is not included
        $testCase->assertArrayNotHasKey('password_hash', $userData);
        $testCase->assertArrayNotHasKey('remember_token', $userData);
    }

    /**
     * Generate a range of test emails for testing
     */
    public static function generateTestEmails(int $count = 5): array
    {
        $emails = [];
        for ($i = 1; $i <= $count; $i++) {
            $emails[] = "test{$i}@example.com";
        }
        return $emails;
    }

    /**
     * Generate strong passwords for testing
     */
    public static function generateStrongPasswords(int $count = 5): array
    {
        $passwords = [];
        for ($i = 1; $i <= $count; $i++) {
            $passwords[] = "StrongPassword{$i}!";
        }
        return $passwords;
    }

    /**
     * Generate weak passwords for validation testing
     */
    public static function generateWeakPasswords(): array
    {
        return [
            'short' => '123',
            'no_uppercase' => 'lowercase123',
            'no_lowercase' => 'UPPERCASE123',
            'no_numbers' => 'NoNumbers',
            'too_short' => 'Abc1',
            'spaces' => 'Pass Word123',
            'empty' => ''
        ];
    }

    /**
     * Get current timestamp for consistent testing
     */
    public static function getTestTimestamp(): int
    {
        return strtotime('2023-06-01 12:00:00');
    }

    /**
     * Get test DateTime object
     */
    public static function getTestDateTime(): DateTime
    {
        return new DateTime('2023-06-01 12:00:00');
    }

    /**
     * Simulate file upload for testing
     */
    public static function mockFileUpload(string $filename, string $content, string $mimeType = 'text/plain'): array
    {
        return [
            'name' => $filename,
            'type' => $mimeType,
            'tmp_name' => tempnam(sys_get_temp_dir(), 'test_upload_'),
            'error' => UPLOAD_ERR_OK,
            'size' => strlen($content)
        ];
    }

    /**
     * Assert that a response matches expected JSON error format
     */
    public static function assertJsonErrorResponse(string $jsonResponse, string $expectedMessage, int $expectedCode, TestCase $testCase): void
    {
        $response = json_decode($jsonResponse, true);
        
        $testCase->assertIsArray($response);
        $testCase->assertArrayHasKey('error', $response);
        $testCase->assertArrayHasKey('message', $response);
        $testCase->assertArrayHasKey('code', $response);
        
        $testCase->assertEquals($expectedMessage, $response['message']);
        $testCase->assertEquals($expectedCode, $response['code']);
    }

    /**
     * Assert that a response matches expected JSON success format
     */
    public static function assertJsonSuccessResponse(string $jsonResponse, TestCase $testCase, array $expectedKeys = []): void
    {
        $response = json_decode($jsonResponse, true);
        
        $testCase->assertIsArray($response);
        
        foreach ($expectedKeys as $key) {
            $testCase->assertArrayHasKey($key, $response, "Response should contain key: {$key}");
        }
    }

    /**
     * Create a mock PDO statement for testing database operations
     */
    public static function createMockPDOStatement(bool $returnValue = true): \PHPUnit\Framework\MockObject\MockObject
    {
        $mockStmt = \PHPUnit\Framework\TestCase::createMock(\PDOStatement::class);
        $mockStmt->method('execute')->willReturn($returnValue);
        $mockStmt->method('fetch')->willReturn([]);
        $mockStmt->method('fetchAll')->willReturn([]);
        $mockStmt->method('rowCount')->willReturn($returnValue ? 1 : 0);
        
        return $mockStmt;
    }
}