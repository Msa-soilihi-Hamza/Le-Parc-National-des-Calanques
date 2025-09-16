<?php

declare(strict_types=1);

namespace ParcCalanques\Auth\DTOs;

use ParcCalanques\Auth\Models\User;

class AuthResponse
{
    public readonly ?string $token;
    public readonly ?User $user;
    public readonly ?array $userData;

    public function __construct(?string $token, ?User $user)
    {
        $this->token = $token;
        $this->user = $user;
        $this->userData = $user ? $this->formatUserData($user) : null;
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'user' => $this->userData,
            'expires_in' => $this->token ? 3600 : null // 1 heure
        ];
    }

    private function formatUserData(User $user): array
    {
        return [
            'id' => $user->getId(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'is_admin' => $user->isAdmin(),
            'date_inscription' => $user->getDateInscription()?->format('Y-m-d H:i:s')
        ];
    }

    public static function success(string $token, User $user): self
    {
        return new self($token, $user);
    }

    public static function tokenOnly(string $token): self
    {
        return new self($token, null);
    }
}