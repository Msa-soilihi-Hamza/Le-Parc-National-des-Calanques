<?php

declare(strict_types=1);

namespace Tests\Exceptions;

use PHPUnit\Framework\TestCase;
use ParcCalanques\Exceptions\AuthException;

class AuthExceptionTest extends TestCase
{
    public function testAuthExceptionConstants(): void
    {
        $this->assertEquals('Invalid credentials', AuthException::INVALID_CREDENTIALS);
        $this->assertEquals('User not found', AuthException::USER_NOT_FOUND);
        $this->assertEquals('User account is inactive', AuthException::USER_INACTIVE);
        $this->assertEquals('Email address not verified', AuthException::EMAIL_NOT_VERIFIED);
        $this->assertEquals('Unauthorized access', AuthException::UNAUTHORIZED);
        $this->assertEquals('Insufficient privileges', AuthException::INSUFFICIENT_PRIVILEGES);
        $this->assertEquals('Session expired', AuthException::SESSION_EXPIRED);
        $this->assertEquals('Invalid token', AuthException::TOKEN_INVALID);
    }

    public function testAuthExceptionCreation(): void
    {
        $message = 'Test auth exception';
        $code = 401;
        
        $exception = new AuthException($message, $code);
        
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testAuthExceptionWithConstantMessage(): void
    {
        $exception = new AuthException(AuthException::INVALID_CREDENTIALS);
        
        $this->assertEquals('Invalid credentials', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode()); // Default code
    }

    public function testAuthExceptionWithPreviousException(): void
    {
        $previousException = new \Exception('Previous exception');
        $authException = new AuthException('Auth exception', 401, $previousException);
        
        $this->assertEquals('Auth exception', $authException->getMessage());
        $this->assertEquals(401, $authException->getCode());
        $this->assertSame($previousException, $authException->getPrevious());
    }

    /**
     * Test that all constant values are strings and non-empty
     */
    public function testAllConstantsAreValidStrings(): void
    {
        $reflection = new \ReflectionClass(AuthException::class);
        $constants = $reflection->getConstants();

        $this->assertNotEmpty($constants, 'AuthException should have constants defined');

        foreach ($constants as $constantName => $constantValue) {
            $this->assertIsString($constantValue, "Constant {$constantName} should be a string");
            $this->assertNotEmpty($constantValue, "Constant {$constantName} should not be empty");
        }
    }

    /**
     * Test that all constant values are unique
     */
    public function testAllConstantsAreUnique(): void
    {
        $reflection = new \ReflectionClass(AuthException::class);
        $constants = $reflection->getConstants();
        
        $values = array_values($constants);
        $uniqueValues = array_unique($values);
        
        $this->assertEquals(
            count($values),
            count($uniqueValues),
            'All AuthException constants should have unique values'
        );
    }

    public function testExceptionScenariosWithAppropriateHttpCodes(): void
    {
        $scenarios = [
            [AuthException::INVALID_CREDENTIALS, 401],
            [AuthException::USER_NOT_FOUND, 404],
            [AuthException::USER_INACTIVE, 403],
            [AuthException::EMAIL_NOT_VERIFIED, 403],
            [AuthException::UNAUTHORIZED, 401],
            [AuthException::INSUFFICIENT_PRIVILEGES, 403],
            [AuthException::SESSION_EXPIRED, 401],
            [AuthException::TOKEN_INVALID, 401],
        ];

        foreach ($scenarios as [$message, $expectedCode]) {
            $exception = new AuthException($message, $expectedCode);
            
            $this->assertEquals($message, $exception->getMessage());
            $this->assertEquals($expectedCode, $exception->getCode());
        }
    }
}