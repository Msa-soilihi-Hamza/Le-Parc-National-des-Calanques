<?php

declare(strict_types=1);

namespace ParcCalanques\Admin\DTOs;

class UpdateUserRequest
{
    public function __construct(
        public readonly ?string $email = null,
        public readonly ?string $password = null,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?string $role = null,
        public readonly ?bool $isActive = null,
        public readonly ?bool $abonnement = null,
        public readonly ?string $carteMembreNumero = null,
        public readonly ?string $carteMembreDateValidite = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            role: $data['role'] ?? null,
            isActive: isset($data['is_active']) ? (bool)$data['is_active'] : null,
            abonnement: isset($data['abonnement']) ? (bool)$data['abonnement'] : null,
            carteMembreNumero: $data['carte_membre_numero'] ?? null,
            carteMembreDateValidite: $data['carte_membre_date_validite'] ?? null
        );
    }

    public function validate(): array
    {
        $errors = [];

        if ($this->email !== null && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if ($this->password !== null && strlen($this->password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if ($this->firstName !== null && empty(trim($this->firstName))) {
            $errors['first_name'] = 'First name cannot be empty';
        }

        if ($this->lastName !== null && empty(trim($this->lastName))) {
            $errors['last_name'] = 'Last name cannot be empty';
        }

        if ($this->role !== null && !in_array($this->role, ['user', 'admin'])) {
            $errors['role'] = 'Role must be user or admin';
        }

        return $errors;
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->email !== null) $data['email'] = $this->email;
        if ($this->password !== null) $data['password'] = $this->password;
        if ($this->firstName !== null) $data['first_name'] = $this->firstName;
        if ($this->lastName !== null) $data['last_name'] = $this->lastName;
        if ($this->role !== null) $data['role'] = $this->role;
        if ($this->isActive !== null) $data['is_active'] = $this->isActive;
        if ($this->abonnement !== null) $data['abonnement'] = $this->abonnement;
        if ($this->carteMembreNumero !== null) $data['carte_membre_numero'] = $this->carteMembreNumero;
        if ($this->carteMembreDateValidite !== null) $data['carte_membre_date_validite'] = $this->carteMembreDateValidite;

        return $data;
    }

    public function hasData(): bool
    {
        return !empty($this->toArray());
    }
}