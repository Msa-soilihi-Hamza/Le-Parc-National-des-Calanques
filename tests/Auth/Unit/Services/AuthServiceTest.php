<?php

declare(strict_types=1);

namespace Tests\Auth\Unit\Services;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ParcCalanques\Auth\Services\AuthService;
use ParcCalanques\Auth\Services\SessionService;
use ParcCalanques\Auth\Services\JwtService;
use ParcCalanques\Auth\Models\UserRepository;
use ParcCalanques\Auth\Models\User;
use ParcCalanques\Shared\Exceptions\AuthException;
use ParcCalanques\Shared\Services\EmailService;

class AuthServiceTest extends TestCase
{
    private MockObject $userRepository;
    private MockObject $sessionService;
    private AuthService $authService;
    private User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepository::class);
        $this->sessionService = $this->createMock(SessionService::class);
        $jwtService = $this->createMock(JwtService::class);
        $emailService = $this->createMock(EmailService::class);

        $this->authService = new AuthService(
            $this->userRepository,
            $this->sessionService,
            $jwtService,
            $emailService
        );

        $this->testUser = new User(
            id: 1,
            email: 'test@example.com',
            passwordHash: User::hashPassword('password123456'),
            role: User::ROLE_USER,
            firstName: 'John',
            lastName: 'Doe',
            isActive: true,
            emailVerified: true
        );
    }

    public function testLoginSuccess(): void
    {
        $this->userRepository
            ->method('findByEmail')
            ->with('test@example.com')
            ->willReturn($this->testUser);

        $this->sessionService
            ->expects($this->once())
            ->method('createSession')
            ->with($this->testUser);

        $result = $this->authService->login('test@example.com', 'password123456');

        $this->assertSame($this->testUser, $result);
    }

    public function testLoginUserNotFound(): void
    {
        $this->userRepository
            ->method('findByEmail')
            ->willReturn(null);

        $this->expectException(AuthException::class);

        $this->authService->login('inexistant@example.com', 'password123456');
    }

    public function testLoginInvalidPassword(): void
    {
        $this->userRepository
            ->method('findByEmail')
            ->willReturn($this->testUser);

        $this->expectException(AuthException::class);

        $this->authService->login('test@example.com', 'mauvais_mot_de_passe');
    }

    public function testRegisterSuccess(): void
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'test@example.com',
            'password' => 'password123456'
        ];

        $this->userRepository
            ->method('emailExists')
            ->willReturn(false);

        $this->userRepository
            ->expects($this->once())
            ->method('create')
            ->with($userData)
            ->willReturn($this->testUser);

        $this->sessionService
            ->expects($this->once())
            ->method('createSession')
            ->with($this->testUser);

        $result = $this->authService->register($userData);

        $this->assertSame($this->testUser, $result);
    }

    public function testRegisterPasswordTooShort(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'court'
        ];

        $this->expectException(AuthException::class);

        $this->authService->register($userData);
    }

    public function testIsAuthenticated(): void
    {
        $this->sessionService
            ->method('getCurrentUser')
            ->willReturn($this->testUser);

        $this->assertTrue($this->authService->isAuthenticated());
    }

    public function testIsNotAuthenticated(): void
    {
        $this->sessionService
            ->method('getCurrentUser')
            ->willReturn(null);

        $this->assertFalse($this->authService->isAuthenticated());
    }
}
