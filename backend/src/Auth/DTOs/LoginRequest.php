<?php

declare(strict_types=1);

namespace ParcCalanques\Auth\DTOs;

class LoginRequest
{
    public readonly string $email;
    public readonly string $password;
    public readonly bool $rememberMe;

    public function __construct(string $email, string $password, bool $rememberMe = false)
    {
        $this->email = trim(strtolower($email));
        $this->password = $password;
        $this->rememberMe = $rememberMe;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['email'] ?? '',
            $data['password'] ?? '',
            (bool)($data['remember_me'] ?? false)
        );
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->email)) {
            $errors[] = 'Email requis';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalide';
        }

        if (empty($this->password)) {
            $errors[] = 'Mot de passe requis';
        } elseif (strlen($this->password) < 6) {
            $errors[] = 'Mot de passe trop court (minimum 6 caractères)';
        }

        return $errors;
    }

    public function isValid(): bool
    {
        return empty($this->validate());
    }
}