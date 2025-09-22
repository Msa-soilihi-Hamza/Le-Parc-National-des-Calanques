<?php

declare(strict_types=1);

namespace ParcCalanques\Auth\Services;

use ParcCalanques\Auth\Models\User;
use ParcCalanques\Auth\Models\UserRepository;
use ParcCalanques\Shared\Exceptions\AuthException;
use ParcCalanques\Shared\Services\EmailService;
use ParcCalanques\Core\Request;

class AuthService
{
    private const REMEMBER_TOKEN_LENGTH = 64;
    
    public function __construct(
        private UserRepository $userRepository,
        private SessionService $sessionService,
        private ?JwtService $jwtService = null,
        private ?EmailService $emailService = null
    ) {}

    public function login(string $email, string $password, bool $remember = false): User
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            throw new AuthException(AuthException::USER_NOT_FOUND);
        }

        if (!$user->isActive()) {
            throw new AuthException(AuthException::USER_INACTIVE);
        }

        // Vérifier que l'email est vérifié
        if (!$user->isEmailVerifiedByToken()) {
            throw new AuthException('Veuillez vérifier votre email avant de vous connecter');
        }

        if (!$user->verifyPassword($password)) {
            throw new AuthException(AuthException::INVALID_CREDENTIALS);
        }

        $this->sessionService->createSession($user);

        if ($remember) {
            $this->createRememberToken($user);
        }

        return $user;
    }

    public function loginWithJwt(string $email, string $password, bool $remember = false): array
    {
        if (!$this->jwtService) {
            throw new AuthException('JWT service not available');
        }

        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new AuthException(AuthException::USER_NOT_FOUND);
        }

        if (!$user->isActive()) {
            throw new AuthException(AuthException::USER_INACTIVE);
        }

        if (!$user->verifyPassword($password)) {
            throw new AuthException(AuthException::INVALID_CREDENTIALS);
        }

        // Note: Permettre la connexion même sans email vérifié pour l'instant
        $emailVerificationRequired = !$user->isEmailVerifiedByToken();

        // Créer un remember token si demandé
        if ($remember) {
            $this->createRememberToken($user);
        }

        $tokens = $this->jwtService->generateTokenPair($user);

        return [
            'user' => $user->toArray(),
            'tokens' => $tokens,
            'email_verification_required' => $emailVerificationRequired
        ];
    }

    public function refreshJwtToken(string $refreshToken): array
    {
        if (!$this->jwtService) {
            throw new AuthException('JWT service not available');
        }

        $tokenData = $this->jwtService->refreshAccessToken($refreshToken);
        $user = $this->userRepository->findById($tokenData['user_id']);
        
        if (!$user || !$user->isActive()) {
            throw new AuthException('User not found or inactive');
        }

        $tokens = $this->jwtService->generateTokenPair($user);
        
        return [
            'user' => $user->toArray(),
            'tokens' => $tokens
        ];
    }

    public function validateJwtToken(string $token): User
    {
        if (!$this->jwtService) {
            throw new AuthException('JWT service not available');
        }

        $userId = $this->jwtService->getUserIdFromToken($token);
        $user = $this->userRepository->findById($userId);
        
        if (!$user || !$user->isActive()) {
            throw new AuthException('User not found or inactive');
        }

        return $user;
    }

    public function logout(): void
    {
        $user = $this->sessionService->getCurrentUser();
        
        if ($user) {
            $this->userRepository->updateRememberToken($user->getId(), null);
        }

        $this->sessionService->destroySession();
        $this->clearRememberCookie();
    }

    public function register(array $userData): User
    {
        // Validation du mot de passe avec regex - minimum 12 caractères
        if (isset($userData['password']) && !preg_match('/^.{12,}$/', $userData['password'])) {
            throw new AuthException('Le mot de passe doit contenir au moins 12 caractères');
        }

        if ($this->userRepository->emailExists($userData['email'])) {
            throw new AuthException('Email address already exists');
        }

        $user = $this->userRepository->create($userData);
        $this->sessionService->createSession($user);

        return $user;
    }

    public function registerWithJwt(string $nom, string $prenom, string $email, string $password): array
    {
        if (!$this->jwtService) {
            throw new AuthException('JWT service not available');
        }

        // Sanitisation supplémentaire des données
        $sanitizedData = Request::sanitize([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'password' => $password
        ], [
            'nom' => 'string',
            'prenom' => 'string',
            'email' => 'email',
            'password' => 'string'
        ]);

        // Validation stricte des noms/prénoms
        if (!$this->validateName($sanitizedData['nom'])) {
            throw new AuthException('Le nom contient des caractères non autorisés');
        }

        if (!$this->validateName($sanitizedData['prenom'])) {
            throw new AuthException('Le prénom contient des caractères non autorisés');
        }

        // Validation du mot de passe avec regex - minimum 12 caractères
        if (!preg_match('/^.{12,}$/', $sanitizedData['password'])) {
            throw new AuthException('Le mot de passe doit contenir au moins 12 caractères');
        }

        $userData = [
            'first_name' => $sanitizedData['prenom'],
            'last_name' => $sanitizedData['nom'],
            'email' => $sanitizedData['email'],
            'password' => $sanitizedData['password']
        ];

        if ($this->userRepository->emailExists($sanitizedData['email'])) {
            throw new AuthException('Email address already exists');
        }

        // Générer un token de vérification
        $verificationToken = bin2hex(random_bytes(32));
        
        // Ajouter les données de vérification
        $userData['email_verified'] = false;
        $userData['email_verification_token'] = $verificationToken;
        $userData['email_verification_expires_at'] = date('Y-m-d H:i:s', time() + (24 * 60 * 60)); // 24h
        
        $user = $this->userRepository->create($userData);
        
        // Envoyer l'email de vérification
        if ($this->emailService) {
            try {
                $emailSent = $this->emailService->sendVerificationEmail(
                    $email, 
                    $prenom . ' ' . $nom, 
                    $verificationToken
                );
                
                if (!$emailSent) {
                    error_log("Échec d'envoi de l'email de vérification pour : " . $email);
                }
            } catch (Exception $emailError) {
                error_log("Exception lors de l'envoi d'email de vérification pour $email : " . $emailError->getMessage());
                // Ne pas faire échouer l'inscription à cause d'un problème d'email
            }
        } else {
            error_log("EmailService non disponible pour l'envoi de vérification à : " . $email);
        }

        // Ne pas générer de tokens JWT tant que l'email n'est pas vérifié
        // Retourner juste les informations utilisateur
        return [
            'user' => $user->toArray(),
            'message' => 'Inscription réussie ! Veuillez vérifier votre email pour activer votre compte.',
            'email_verification_required' => true
        ];
    }

    public function getCurrentUser(): ?User
    {
        return $this->sessionService->getCurrentUser();
    }

    public function isAuthenticated(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    public function requireAuthentication(): User
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            throw new AuthException(AuthException::UNAUTHORIZED);
        }

        return $user;
    }

    public function requireRole(string $role): User
    {
        $user = $this->requireAuthentication();

        if ($user->getRole() !== $role) {
            throw new AuthException(AuthException::INSUFFICIENT_PRIVILEGES);
        }

        return $user;
    }

    public function requireAdmin(): User
    {
        return $this->requireRole(User::ROLE_ADMIN);
    }

    public function attemptRememberLogin(): ?User
    {
        $token = $_COOKIE['remember_token'] ?? null;
        
        if (!$token) {
            return null;
        }

        $user = $this->userRepository->findByRememberToken($token);
        
        if (!$user || !$user->isActive()) {
            $this->clearRememberCookie();
            return null;
        }

        $this->sessionService->createSession($user);
        $this->refreshRememberToken($user);

        return $user;
    }

    public function verifyEmail(int $userId): bool
    {
        return $this->userRepository->updateEmailVerification($userId, new \DateTime());
    }
    
    /**
     * Vérifier un email avec le token de vérification
     */
    public function verifyEmailByToken(string $token): array
    {
        $user = $this->userRepository->findByVerificationToken($token);

        if (!$user) {
            throw new AuthException('Token de vérification invalide');
        }
        
        // Vérifier si le token n'a pas expiré
        $expiresAt = $user->getEmailVerificationExpiresAt();
        if ($expiresAt && new \DateTime() > $expiresAt) {
            throw new AuthException('Token de vérification expiré');
        }
        
        // Marquer l'email comme vérifié
        $success = $this->userRepository->markEmailAsVerified($user->getId());
        
        if (!$success) {
            throw new AuthException('Erreur lors de la vérification');
        }
        
        // Envoyer l'email de bienvenue
        if ($this->emailService) {
            $this->emailService->sendWelcomeEmail(
                $user->getEmail(),
                $user->getFirstName() . ' ' . $user->getLastName()
            );
        }
        
        // Générer les tokens JWT maintenant que l'email est vérifié
        if ($this->jwtService) {
            $tokens = $this->jwtService->generateTokenPair($user);
            return [
                'user' => $user->toArray(),
                'tokens' => $tokens,
                'message' => 'Email vérifié avec succès ! Votre compte est maintenant actif.'
            ];
        }
        
        return [
            'user' => $user->toArray(),
            'message' => 'Email vérifié avec succès ! Votre compte est maintenant actif.'
        ];
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        // Validation du nouveau mot de passe avec regex - minimum 12 caractères
        if (!preg_match('/^.{12,}$/', $newPassword)) {
            throw new AuthException('Le nouveau mot de passe doit contenir au moins 12 caractères');
        }

        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            throw new AuthException(AuthException::USER_NOT_FOUND);
        }

        if (!$user->verifyPassword($currentPassword)) {
            throw new AuthException(AuthException::INVALID_CREDENTIALS);
        }

        // Get PDO connection through reflection since it's private
        $reflectionClass = new \ReflectionClass($this->userRepository);
        $pdoProperty = $reflectionClass->getProperty('pdo');
        $pdoProperty->setAccessible(true);
        $pdo = $pdoProperty->getValue($this->userRepository);
        
        $stmt = $pdo->prepare('
            UPDATE Utilisateur 
            SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP 
            WHERE id_utilisateur = :id
        ');

        return $stmt->execute([
            'password_hash' => User::hashPassword($newPassword),
            'id' => $userId
        ]);
    }

    private function createRememberToken(User $user): void
    {
        $token = bin2hex(random_bytes(self::REMEMBER_TOKEN_LENGTH));
        $hashedToken = hash('sha256', $token);
        
        $this->userRepository->updateRememberToken($user->getId(), $hashedToken);
        $this->setRememberCookie($token);
    }

    private function refreshRememberToken(User $user): void
    {
        $this->createRememberToken($user);
    }

    private function setRememberCookie(string $token): void
    {
        $expires = time() + (30 * 24 * 60 * 60); // 30 jours
        
        setcookie(
            'remember_token',
            $token,
            $expires,
            '/',
            '',
            true, // secure - only HTTPS
            true  // httpOnly - not accessible via JavaScript
        );
    }

    private function clearRememberCookie(): void
    {
        setcookie(
            'remember_token',
            '',
            time() - 3600,
            '/',
            '',
            true,
            true
        );
    }

    /**
     * Valide que le nom/prénom ne contient que des caractères autorisés
     */
    private function validateName(string $name): bool
    {
        // Autorise seulement les lettres, espaces, apostrophes et traits d'union
        // Supporte les caractères accentués français
        return preg_match('/^[a-zA-ZÀ-ÿ\s\'-]{2,50}$/u', $name) === 1;
    }
}