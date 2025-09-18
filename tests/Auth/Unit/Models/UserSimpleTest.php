<?php

declare(strict_types=1);

namespace Tests\Auth\Unit\Models;

use PHPUnit\Framework\TestCase;
use ParcCalanques\Auth\Models\User;

class UserSimpleTest extends TestCase
{
    public function testUserCreation(): void
    {
        $user = new User(
            id: 1,
            email: 'test@example.com',
            passwordHash: 'hashed_password',
            role: User::ROLE_USER,
            firstName: 'John',
            lastName: 'Doe',
            isActive: true
        );

        $this->assertEquals(1, $user->getId());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertTrue($user->isActive());
    }

    public function testGetFullName(): void
    {
        $user = new User(1, 'test@example.com', 'hash', 'user', 'John', 'Doe', true);
        $this->assertEquals('John Doe', $user->getFullName());
    }

    public function testRoleChecking(): void
    {
        $user = new User(1, 'user@example.com', 'hash', User::ROLE_USER, 'John', 'Doe', true);
        $admin = new User(2, 'admin@example.com', 'hash', User::ROLE_ADMIN, 'Jane', 'Admin', true);

        $this->assertTrue($user->isUser());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($admin->isUser());
        $this->assertTrue($admin->isAdmin());
    }

    public function testPasswordHashing(): void
    {
        $password = 'mySecurePassword123';
        $hashedPassword = User::hashPassword($password);

        $this->assertNotEquals($password, $hashedPassword);
        $this->assertTrue(password_verify($password, $hashedPassword));
    }

    public function testConstantsExist(): void
    {
        $this->assertEquals('user', User::ROLE_USER);
        $this->assertEquals('admin', User::ROLE_ADMIN);
    }
}
