<?php

declare(strict_types=1);

namespace ParcCalanques\Auth\Tests;

use PHPUnit\Framework\TestCase;
use ParcCalanques\Auth\Services\AuthService;
use ParcCalanques\Auth\Services\JwtService;
use ParcCalanques\Auth\Models\UserRepository;
use ParcCalanques\Auth\Models\User;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private UserRepository $userRepository;
    private JwtService $jwtService;

    protected function setUp(): void
    {
        // Mock des dépendances
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->jwtService = $this->createMock(JwtService::class);

        $this->authService = new AuthService(
            $this->userRepository,
            $this->jwtService
        );
    }

    public function testAuthenticateWithValidCredentials(): void
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $user = new User(1, 'Doe', 'John', $email, $hashedPassword, 'user');

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        // Act
        $result = $this->authService->authenticate($email, $password);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($email, $result->getEmail());
    }

    public function testAuthenticateWithInvalidPassword(): void
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'wrongpassword';
        $hashedPassword = password_hash('correctpassword', PASSWORD_DEFAULT);

        $user = new User(1, 'Doe', 'John', $email, $hashedPassword, 'user');

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        // Act
        $result = $this->authService->authenticate($email, $password);

        // Assert
        $this->assertNull($result);
    }

    public function testAuthenticateWithNonExistentUser(): void
    {
        // Arrange
        $email = 'nonexistent@example.com';
        $password = 'password123';

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        // Act
        $result = $this->authService->authenticate($email, $password);

        // Assert
        $this->assertNull($result);
    }

    public function testGenerateTokenForUser(): void
    {
        // Arrange
        $user = new User(1, 'Doe', 'John', 'test@example.com', 'hashedpassword', 'user');
        $expectedToken = 'generated.jwt.token';

        $this->jwtService->expects($this->once())
            ->method('generateToken')
            ->with($user)
            ->willReturn($expectedToken);

        // Act
        $token = $this->authService->generateToken($user);

        // Assert
        $this->assertEquals($expectedToken, $token);
    }
}