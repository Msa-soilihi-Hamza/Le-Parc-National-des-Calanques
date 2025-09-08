<?php

declare(strict_types=1);

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use ParcCalanques\Models\User;
use DateTime;

class UserTest extends TestCase
{
    private User $user;
    private DateTime $testDate;

    protected function setUp(): void
    {
        $this->testDate = new DateTime('2023-01-01 10:00:00');
        
        $this->user = new User(
            id: 1,
            email: 'test@example.com',
            passwordHash: password_hash('password123', PASSWORD_DEFAULT),
            role: User::ROLE_USER,
            firstName: 'John',
            lastName: 'Doe',
            isActive: true,
            emailVerifiedAt: $this->testDate,
            rememberToken: 'remember_token_123',
            createdAt: $this->testDate,
            updatedAt: $this->testDate,
            abonnement: true,
            carteMembreNumero: 'CM123456',
            carteMembreDateValidite: new DateTime('2025-12-31')
        );
    }

    public function testUserConstantsAreDefined(): void
    {
        $this->assertEquals('user', User::ROLE_USER);
        $this->assertEquals('admin', User::ROLE_ADMIN);
    }

    public function testGetId(): void
    {
        $this->assertEquals(1, $this->user->getId());
    }

    public function testGetEmail(): void
    {
        $this->assertEquals('test@example.com', $this->user->getEmail());
    }

    public function testGetPasswordHash(): void
    {
        $hash = $this->user->getPasswordHash();
        $this->assertIsString($hash);
        $this->assertTrue(password_verify('password123', $hash));
    }

    public function testGetRole(): void
    {
        $this->assertEquals(User::ROLE_USER, $this->user->getRole());
    }

    public function testGetFirstName(): void
    {
        $this->assertEquals('John', $this->user->getFirstName());
    }

    public function testGetLastName(): void
    {
        $this->assertEquals('Doe', $this->user->getLastName());
    }

    public function testGetFullName(): void
    {
        $this->assertEquals('John Doe', $this->user->getFullName());
    }

    public function testIsActive(): void
    {
        $this->assertTrue($this->user->isActive());
    }

    public function testIsEmailVerified(): void
    {
        $this->assertTrue($this->user->isEmailVerified());
        
        // Test with unverified email
        $unverifiedUser = new User(
            id: 2,
            email: 'unverified@example.com',
            passwordHash: 'hash',
            role: User::ROLE_USER,
            firstName: 'Jane',
            lastName: 'Smith',
            isActive: true,
            emailVerifiedAt: null
        );
        
        $this->assertFalse($unverifiedUser->isEmailVerified());
    }

    public function testGetEmailVerifiedAt(): void
    {
        $this->assertEquals($this->testDate, $this->user->getEmailVerifiedAt());
    }

    public function testGetRememberToken(): void
    {
        $this->assertEquals('remember_token_123', $this->user->getRememberToken());
    }

    public function testGetCreatedAt(): void
    {
        $this->assertEquals($this->testDate, $this->user->getCreatedAt());
    }

    public function testGetUpdatedAt(): void
    {
        $this->assertEquals($this->testDate, $this->user->getUpdatedAt());
    }

    public function testIsAdmin(): void
    {
        $this->assertFalse($this->user->isAdmin());
        
        $adminUser = new User(
            id: 3,
            email: 'admin@example.com',
            passwordHash: 'hash',
            role: User::ROLE_ADMIN,
            firstName: 'Admin',
            lastName: 'User',
            isActive: true
        );
        
        $this->assertTrue($adminUser->isAdmin());
    }

    public function testIsUser(): void
    {
        $this->assertTrue($this->user->isUser());
        
        $adminUser = new User(
            id: 3,
            email: 'admin@example.com',
            passwordHash: 'hash',
            role: User::ROLE_ADMIN,
            firstName: 'Admin',
            lastName: 'User',
            isActive: true
        );
        
        $this->assertFalse($adminUser->isUser());
    }

    public function testSetRememberToken(): void
    {
        $newToken = 'new_remember_token';
        $this->user->setRememberToken($newToken);
        
        $this->assertEquals($newToken, $this->user->getRememberToken());
        
        // Test setting to null
        $this->user->setRememberToken(null);
        $this->assertNull($this->user->getRememberToken());
    }

    public function testSetEmailVerifiedAt(): void
    {
        $newDate = new DateTime('2023-06-01 15:30:00');
        $this->user->setEmailVerifiedAt($newDate);
        
        $this->assertEquals($newDate, $this->user->getEmailVerifiedAt());
        $this->assertTrue($this->user->isEmailVerified());
        
        // Test setting to null
        $this->user->setEmailVerifiedAt(null);
        $this->assertNull($this->user->getEmailVerifiedAt());
        $this->assertFalse($this->user->isEmailVerified());
    }

    public function testSetActive(): void
    {
        $this->assertTrue($this->user->isActive());
        
        $this->user->setActive(false);
        $this->assertFalse($this->user->isActive());
        
        $this->user->setActive(true);
        $this->assertTrue($this->user->isActive());
    }

    public function testVerifyPassword(): void
    {
        $this->assertTrue($this->user->verifyPassword('password123'));
        $this->assertFalse($this->user->verifyPassword('wrongpassword'));
        $this->assertFalse($this->user->verifyPassword(''));
        $this->assertFalse($this->user->verifyPassword('Password123')); // Case sensitive
    }

    public function testHashPassword(): void
    {
        $password = 'testpassword123';
        $hash = User::hashPassword($password);
        
        $this->assertIsString($hash);
        $this->assertTrue(password_verify($password, $hash));
        $this->assertFalse(password_verify('wrongpassword', $hash));
        
        // Test that hashing the same password twice produces different hashes
        $hash2 = User::hashPassword($password);
        $this->assertNotEquals($hash, $hash2);
        $this->assertTrue(password_verify($password, $hash2));
    }

    public function testHashPasswordUsesArgon2ID(): void
    {
        $password = 'testpassword';
        $hash = User::hashPassword($password);
        
        // Argon2ID hashes start with $argon2id$
        $this->assertStringStartsWith('$argon2id$', $hash);
    }

    public function testHasAbonnement(): void
    {
        $this->assertTrue($this->user->hasAbonnement());
        
        $userWithoutAbonnement = new User(
            id: 4,
            email: 'noabo@example.com',
            passwordHash: 'hash',
            role: User::ROLE_USER,
            firstName: 'No',
            lastName: 'Subscription',
            isActive: true,
            abonnement: false
        );
        
        $this->assertFalse($userWithoutAbonnement->hasAbonnement());
    }

    public function testGetCarteMembreNumero(): void
    {
        $this->assertEquals('CM123456', $this->user->getCarteMembreNumero());
    }

    public function testGetCarteMembreDateValidite(): void
    {
        $expectedDate = new DateTime('2025-12-31');
        $this->assertEquals($expectedDate, $this->user->getCarteMembreDateValidite());
    }

    public function testIsCarteMembreValide(): void
    {
        // Test with future date (valid)
        $futureDate = new DateTime('+1 year');
        $userWithValidCarte = new User(
            id: 5,
            email: 'valid@example.com',
            passwordHash: 'hash',
            role: User::ROLE_USER,
            firstName: 'Valid',
            lastName: 'Card',
            isActive: true,
            carteMembreDateValidite: $futureDate
        );
        
        $this->assertTrue($userWithValidCarte->isCarteMembreValide());
        
        // Test with past date (invalid)
        $pastDate = new DateTime('2020-01-01');
        $userWithExpiredCarte = new User(
            id: 6,
            email: 'expired@example.com',
            passwordHash: 'hash',
            role: User::ROLE_USER,
            firstName: 'Expired',
            lastName: 'Card',
            isActive: true,
            carteMembreDateValidite: $pastDate
        );
        
        $this->assertFalse($userWithExpiredCarte->isCarteMembreValide());
        
        // Test with null date (invalid)
        $userWithoutCarte = new User(
            id: 7,
            email: 'nocarte@example.com',
            passwordHash: 'hash',
            role: User::ROLE_USER,
            firstName: 'No',
            lastName: 'Card',
            isActive: true,
            carteMembreDateValidite: null
        );
        
        $this->assertFalse($userWithoutCarte->isCarteMembreValide());
    }

    public function testToArray(): void
    {
        $array = $this->user->toArray();
        
        $expectedKeys = [
            'id', 'email', 'role', 'first_name', 'last_name', 'full_name',
            'is_active', 'is_email_verified', 'email_verified_at',
            'created_at', 'updated_at', 'abonnement', 'carte_membre_numero',
            'carte_membre_date_validite', 'carte_membre_valide'
        ];
        
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $array);
        }
        
        $this->assertEquals(1, $array['id']);
        $this->assertEquals('test@example.com', $array['email']);
        $this->assertEquals('user', $array['role']);
        $this->assertEquals('John', $array['first_name']);
        $this->assertEquals('Doe', $array['last_name']);
        $this->assertEquals('John Doe', $array['full_name']);
        $this->assertTrue($array['is_active']);
        $this->assertTrue($array['is_email_verified']);
        $this->assertEquals('2023-01-01 10:00:00', $array['email_verified_at']);
        $this->assertEquals('2023-01-01 10:00:00', $array['created_at']);
        $this->assertEquals('2023-01-01 10:00:00', $array['updated_at']);
        $this->assertTrue($array['abonnement']);
        $this->assertEquals('CM123456', $array['carte_membre_numero']);
        $this->assertEquals('2025-12-31', $array['carte_membre_date_validite']);
        $this->assertTrue($array['carte_membre_valide']);
        
        // Test that password hash is not included in array
        $this->assertArrayNotHasKey('password_hash', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function testToArrayWithNullValues(): void
    {
        $minimalUser = new User(
            id: 8,
            email: 'minimal@example.com',
            passwordHash: 'hash',
            role: User::ROLE_USER,
            firstName: 'Min',
            lastName: 'User',
            isActive: true
        );
        
        $array = $minimalUser->toArray();
        
        $this->assertNull($array['email_verified_at']);
        $this->assertNull($array['created_at']);
        $this->assertNull($array['updated_at']);
        $this->assertFalse($array['abonnement']);
        $this->assertNull($array['carte_membre_numero']);
        $this->assertNull($array['carte_membre_date_validite']);
        $this->assertFalse($array['carte_membre_valide']);
        $this->assertFalse($array['is_email_verified']);
    }

    public function testRoleCheckingUserRole(): void
    {
        $user = new User(
            id: 1,
            email: 'test@example.com',
            passwordHash: password_hash('password123', PASSWORD_DEFAULT),
            role: User::ROLE_USER,
            firstName: 'Test',
            lastName: 'User',
            isActive: true
        );
        
        $this->assertTrue($user->isUser());
        $this->assertFalse($user->isAdmin());
        $this->assertEquals(User::ROLE_USER, $user->getRole());
    }

    public function testRoleCheckingAdminRole(): void
    {
        $user = new User(
            id: 1,
            email: 'admin@example.com',
            passwordHash: password_hash('password123', PASSWORD_DEFAULT),
            role: User::ROLE_ADMIN,
            firstName: 'Admin',
            lastName: 'User',
            isActive: true
        );
        
        $this->assertFalse($user->isUser());
        $this->assertTrue($user->isAdmin());
        $this->assertEquals(User::ROLE_ADMIN, $user->getRole());
    }

    public function testPasswordVerificationCorrect(): void
    {
        $this->assertTrue($this->user->verifyPassword('password123'));
    }

    public function testPasswordVerificationIncorrect(): void
    {
        $this->assertFalse($this->user->verifyPassword('wrongpassword'));
        $this->assertFalse($this->user->verifyPassword(''));
        $this->assertFalse($this->user->verifyPassword('Password123'));
    }

    public function testUserCreationWithMinimalData(): void
    {
        $minimalUser = new User(
            id: 999,
            email: 'minimal@test.com',
            passwordHash: password_hash('test', PASSWORD_DEFAULT),
            role: User::ROLE_USER,
            firstName: 'Test',
            lastName: 'User',
            isActive: true
        );
        
        $this->assertEquals(999, $minimalUser->getId());
        $this->assertEquals('minimal@test.com', $minimalUser->getEmail());
        $this->assertEquals(User::ROLE_USER, $minimalUser->getRole());
        $this->assertEquals('Test', $minimalUser->getFirstName());
        $this->assertEquals('User', $minimalUser->getLastName());
        $this->assertTrue($minimalUser->isActive());
        $this->assertNull($minimalUser->getEmailVerifiedAt());
        $this->assertNull($minimalUser->getRememberToken());
        $this->assertNull($minimalUser->getCreatedAt());
        $this->assertNull($minimalUser->getUpdatedAt());
        $this->assertFalse($minimalUser->hasAbonnement());
        $this->assertNull($minimalUser->getCarteMembreNumero());
        $this->assertNull($minimalUser->getCarteMembreDateValidite());
        $this->assertFalse($minimalUser->isCarteMembreValide());
    }
}