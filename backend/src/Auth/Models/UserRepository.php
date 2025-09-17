<?php

declare(strict_types=1);

namespace ParcCalanques\Auth\Models;

use PDO;
use DateTime;
use ParcCalanques\Shared\Exceptions\AuthException;

class UserRepository
{
    public function __construct(private PDO $pdo) {}

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('
            SELECT id_utilisateur as id, email, password_hash, role, prenom as first_name, nom as last_name, 
                   is_active, email_verified_at, remember_token, created_at, updated_at,
                   abonnement, carte_membre_numero, carte_membre_date_validite,
                   email_verified, email_verification_token, email_verification_expires_at
            FROM Utilisateur 
            WHERE email = :email
        ');
        
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare('
            SELECT id_utilisateur as id, email, password_hash, role, prenom as first_name, nom as last_name, 
                   is_active, email_verified_at, remember_token, created_at, updated_at,
                   abonnement, carte_membre_numero, carte_membre_date_validite,
                   email_verified, email_verification_token, email_verification_expires_at
            FROM Utilisateur 
            WHERE id_utilisateur = :id
        ');
        
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findByRememberToken(string $token): ?User
    {
        $stmt = $this->pdo->prepare('
            SELECT id_utilisateur as id, email, password_hash, role, prenom as first_name, nom as last_name, 
                   is_active, email_verified_at, remember_token, created_at, updated_at,
                   abonnement, carte_membre_numero, carte_membre_date_validite,
                   email_verified, email_verification_token, email_verification_expires_at
            FROM Utilisateur 
            WHERE remember_token = :token AND is_active = 1
        ');
        
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToUser($row) : null;
    }

    public function create(array $userData): User
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO Utilisateur (email, password_hash, role, prenom, nom, is_active, abonnement, 
                                   email_verified, email_verification_token, email_verification_expires_at)
            VALUES (:email, :password_hash, :role, :first_name, :last_name, :is_active, :abonnement,
                    :email_verified, :email_verification_token, :email_verification_expires_at)
        ');

        $data = [
            'email' => $userData['email'],
            'password_hash' => User::hashPassword($userData['password']),
            'role' => $userData['role'] ?? User::ROLE_USER,
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'is_active' => $userData['is_active'] ?? true,
            'abonnement' => $userData['abonnement'] ?? false,
            'email_verified' => $userData['email_verified'] ?? false,
            'email_verification_token' => $userData['email_verification_token'] ?? null,
            'email_verification_expires_at' => $userData['email_verification_expires_at'] ?? null
        ];

        if (!$stmt->execute($data)) {
            throw new AuthException('Failed to create user');
        }

        $userId = (int) $this->pdo->lastInsertId();
        $user = $this->findById($userId);

        if (!$user) {
            throw new AuthException('Failed to retrieve created user');
        }

        return $user;
    }

    public function updateRememberToken(int $userId, ?string $token): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE Utilisateur 
            SET remember_token = :token, updated_at = CURRENT_TIMESTAMP 
            WHERE id_utilisateur = :id
        ');

        return $stmt->execute([
            'token' => $token,
            'id' => $userId
        ]);
    }

    public function updateEmailVerification(int $userId, ?DateTime $verifiedAt = null): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE Utilisateur 
            SET email_verified_at = :verified_at, updated_at = CURRENT_TIMESTAMP 
            WHERE id_utilisateur = :id
        ');

        return $stmt->execute([
            'verified_at' => $verifiedAt?->format('Y-m-d H:i:s'),
            'id' => $userId
        ]);
    }

    public function updateActiveStatus(int $userId, bool $isActive): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE Utilisateur 
            SET is_active = :is_active, updated_at = CURRENT_TIMESTAMP 
            WHERE id_utilisateur = :id
        ');

        return $stmt->execute([
            'is_active' => $isActive ? 1 : 0,
            'id' => $userId
        ]);
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM Utilisateur WHERE email = :email');
        $stmt->execute(['email' => $email]);
        
        return (int) $stmt->fetchColumn() > 0;
    }

    public function getAllUsers(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare('
            SELECT id_utilisateur as id, email, password_hash, role, prenom as first_name, nom as last_name, 
                   is_active, email_verified_at, remember_token, created_at, updated_at,
                   abonnement, carte_membre_numero, carte_membre_date_validite,
                   email_verified, email_verification_token, email_verification_expires_at
            FROM Utilisateur 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ');
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $this->mapRowToUser($row);
        }
        
        return $users;
    }

    public function getUserCount(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM Utilisateur');
        return (int) $stmt->fetchColumn();
    }

    /**
     * Trouve un utilisateur par son token de vérification
     */
    public function findByVerificationToken(string $token): ?User
    {
        $stmt = $this->pdo->prepare('
            SELECT id_utilisateur as id, email, password_hash, role, prenom as first_name, nom as last_name, 
                   is_active, email_verified_at, remember_token, created_at, updated_at,
                   abonnement, carte_membre_numero, carte_membre_date_validite,
                   email_verified, email_verification_token, email_verification_expires_at
            FROM Utilisateur 
            WHERE email_verification_token = :token
        ');
        
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToUser($row) : null;
    }

    /**
     * Marque l'email d'un utilisateur comme vérifié
     */
    public function markEmailAsVerified(int $userId): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE Utilisateur 
            SET email_verified = 1, 
                email_verification_token = NULL, 
                email_verification_expires_at = NULL,
                email_verified_at = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP
            WHERE id_utilisateur = :id
        ');
        
        return $stmt->execute(['id' => $userId]);
    }

    private function mapRowToUser(array $row): User
    {
        return new User(
            id: (int) $row['id'],
            email: $row['email'],
            passwordHash: $row['password_hash'],
            role: $row['role'],
            firstName: $row['first_name'],
            lastName: $row['last_name'],
            isActive: (bool) $row['is_active'],
            emailVerifiedAt: $row['email_verified_at'] ? new DateTime($row['email_verified_at']) : null,
            rememberToken: $row['remember_token'],
            createdAt: $row['created_at'] ? new DateTime($row['created_at']) : null,
            updatedAt: $row['updated_at'] ? new DateTime($row['updated_at']) : null,
            abonnement: (bool) ($row['abonnement'] ?? false),
            carteMembreNumero: $row['carte_membre_numero'] ?? null,
            carteMembreDateValidite: $row['carte_membre_date_validite'] ? new DateTime($row['carte_membre_date_validite']) : null,
            emailVerified: (bool) ($row['email_verified'] ?? false),
            emailVerificationToken: $row['email_verification_token'] ?? null,
            emailVerificationExpiresAt: $row['email_verification_expires_at'] ? new DateTime($row['email_verification_expires_at']) : null
        );
    }
}