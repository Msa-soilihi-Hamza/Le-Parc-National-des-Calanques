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
use DateTime;

class EmailVerificationTest extends TestCase
{
    private MockObject $userRepository;
    private MockObject $sessionService;
    private MockObject $jwtService;
    private MockObject $emailService;
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepository::class);
        $this->sessionService = $this->createMock(SessionService::class);
        $this->jwtService = $this->createMock(JwtService::class);
        $this->emailService = $this->createMock(EmailService::class);

        $this->authService = new AuthService(
            $this->userRepository,
            $this->sessionService,
            $this->jwtService,
            $this->emailService
        );
    }

    public function testRegisterSendsVerificationEmail(): void
    {
        $this->userRepository
            ->method('emailExists')
            ->willReturn(false);

        $user = new User(
            id: 1,
            email: 'test@example.com',
            passwordHash: 'hash',
            role: User::ROLE_USER,
            firstName: 'John',
            lastName: 'Doe',
            isActive: true,
            emailVerified: false
        );

        $this->userRepository
            ->method('create')
            ->willReturn($user);

        $this->emailService
            ->expects($this->once())
            ->method('sendVerificationEmail')
            ->willReturn(true);

        $this->jwtService
            ->method('generateTokenPair')
            ->willReturn(['access_token' => 'token', 'refresh_token' => 'refresh']);

        $result = $this->authService->registerWithJwt('Doe', 'John', 'test@example.com', 'password123456');

        $this->assertArrayHasKey('email_verification_required', $result);
        $this->assertTrue($result['email_verification_required']);
    }

    public function testVerifyEmailByTokenSuccess(): void
    {
        $user = new User(
            id: 1,
            email: 'test@example.com',
            passwordHash: 'hash',
            role: User::ROLE_USER,
            firstName: 'John',
            lastName: 'Doe',
            isActive: true,
            emailVerified: false,
            emailVerificationExpiresAt: new DateTime('+1 hour')
        );

        $this->userRepository
            ->method('findByVerificationToken')
            ->willReturn($user);

        $this->userRepository
            ->expects($this->once())
            ->method('markEmailAsVerified')
            ->willReturn(true);

        $this->emailService
            ->expects($this->once())
            ->method('sendWelcomeEmail');

        $this->jwtService
            ->method('generateTokenPair')
            ->willReturn(['access_token' => 'token', 'refresh_token' => 'refresh']);

        $result = $this->authService->verifyEmailByToken('valid_token');

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('tokens', $result);
    }

    public function testVerifyEmailByTokenInvalid(): void
    {
        $this->userRepository
            ->method('findByVerificationToken')
            ->willReturn(null);

        $this->expectException(AuthException::class);

        $this->authService->verifyEmailByToken('invalid_token');
    }

    public function testVerifyEmailByTokenExpired(): void
    {
        $user = new User(
            id: 1,
            email: 'test@example.com',
            passwordHash: 'hash',
            role: User::ROLE_USER,
            firstName: 'John',
            lastName: 'Doe',
            isActive: true,
            emailVerified: false,
            emailVerificationExpiresAt: new DateTime('-1 hour')
        );

        $this->userRepository
            ->method('findByVerificationToken')
            ->willReturn($user);

        $this->expectException(AuthException::class);

        $this->authService->verifyEmailByToken('expired_token');
    }

    public function testLoginFailsForUnverifiedEmail(): void
    {
        $unverifiedUser = new User(
            id: 1,
            email: 'test@example.com',
            passwordHash: User::hashPassword('password123456'),
            role: User::ROLE_USER,
            firstName: 'John',
            lastName: 'Doe',
            isActive: true,
            emailVerified: false
        );

        $this->userRepository
            ->method('findByEmail')
            ->willReturn($unverifiedUser);

        $this->expectException(AuthException::class);

        $this->authService->login('test@example.com', 'password123456');
    }
}
