<?php

declare(strict_types=1);

namespace ParcCalanques\Admin\DTOs;

class CreateUserRequest
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $role = 'user',
        public readonly bool $isActive = true,
        public readonly bool $abonnement = false,
        public readonly ?string $carteMembreNumero = null,
        public readonly ?string $carteMembreDateValidite = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? throw new \InvalidArgumentException('Email is required'),
            password: $data['password'] ?? throw new \InvalidArgumentException('Password is required'),
            firstName: $data['first_name'] ?? throw new \InvalidArgumentException('First name is required'),
            lastName: $data['last_name'] ?? throw new \InvalidArgumentException('Last name is required'),
            role: $data['role'] ?? 'user',
            isActive: $data['is_active'] ?? true,
            abonnement: $data['abonnement'] ?? false,
            carteMembreNumero: $data['carte_membre_numero'] ?? null,
            carteMembreDateValidite: $data['carte_membre_date_validite'] ?? null
        );
    }

    public function validate(): array
    {
        $errors = [];

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (strlen($this->password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if (empty(trim($this->firstName))) {
            $errors['first_name'] = 'First name is required';
        }

        if (empty(trim($this->lastName))) {
            $errors['last_name'] = 'Last name is required';
        }

        if (!in_array($this->role, ['user', 'admin'])) {
            $errors['role'] = 'Role must be user or admin';
        }

        return $errors;
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'role' => $this->role,
            'is_active' => $this->isActive,
            'abonnement' => $this->abonnement,
            'carte_membre_numero' => $this->carteMembreNumero,
            'carte_membre_date_validite' => $this->carteMembreDateValidite
        ];
    }
}