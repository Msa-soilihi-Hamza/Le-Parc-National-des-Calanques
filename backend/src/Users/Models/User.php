<?php

declare(strict_types=1);

namespace ParcCalanques\Users\Models;

use DateTime;

class User
{
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';

    public function __construct(
        private int $id,
        private string $email,
        private string $passwordHash,
        private string $role,
        private string $firstName,
        private string $lastName,
        private bool $isActive,
        private ?DateTime $emailVerifiedAt = null,
        private ?string $rememberToken = null,
        private ?DateTime $createdAt = null,
        private ?DateTime $updatedAt = null,
        private bool $abonnement = false,
        private ?string $carteMembreNumero = null,
        private ?DateTime $carteMembreDateValidite = null,
        private bool $emailVerified = false,
        private ?string $emailVerificationToken = null,
        private ?DateTime $emailVerificationExpiresAt = null
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function getEmailVerifiedAt(): ?DateTime
    {
        return $this->emailVerifiedAt;
    }

    public function getRememberToken(): ?string
    {
        return $this->rememberToken;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function setRememberToken(?string $token): void
    {
        $this->rememberToken = $token;
    }

    public function setEmailVerifiedAt(?DateTime $verifiedAt): void
    {
        $this->emailVerifiedAt = $verifiedAt;
    }

    public function setActive(bool $active): void
    {
        $this->isActive = $active;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    public function hasAbonnement(): bool
    {
        return $this->abonnement;
    }

    public function getCarteMembreNumero(): ?string
    {
        return $this->carteMembreNumero;
    }

    public function getCarteMembreDateValidite(): ?DateTime
    {
        return $this->carteMembreDateValidite;
    }

    public function isCarteMembreValide(): bool
    {
        if (!$this->carteMembreDateValidite) {
            return false;
        }
        
        return $this->carteMembreDateValidite > new DateTime();
    }

    public function getEmailVerificationToken(): ?string
    {
        return $this->emailVerificationToken;
    }

    public function getEmailVerificationExpiresAt(): ?DateTime
    {
        return $this->emailVerificationExpiresAt;
    }

    public function isEmailVerifiedByToken(): bool
    {
        return $this->emailVerified;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'full_name' => $this->getFullName(),
            'is_active' => $this->isActive,
            'is_email_verified' => $this->isEmailVerified(),
            'email_verified_at' => $this->emailVerifiedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'abonnement' => $this->abonnement,
            'carte_membre_numero' => $this->carteMembreNumero,
            'carte_membre_date_validite' => $this->carteMembreDateValidite?->format('Y-m-d'),
            'carte_membre_valide' => $this->isCarteMembreValide(),
            'email_verified' => $this->emailVerified,
            'email_verification_token' => $this->emailVerificationToken,
            'email_verification_expires_at' => $this->emailVerificationExpiresAt?->format('Y-m-d H:i:s')
        ];
    }
}