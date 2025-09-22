<?php

declare(strict_types=1);

namespace ParcCalanques\Core\Controllers;

use ParcCalanques\Auth\Services\AuthService;
use ParcCalanques\Auth\Services\JwtService;
use ParcCalanques\Auth\Middleware\JwtMiddleware;
use ParcCalanques\Shared\Exceptions\AuthException;
use ParcCalanques\Core\Request;

class ApiController
{
    public function __construct(
        private AuthService $authService,
        private JwtService $jwtService,
        private JwtMiddleware $jwtMiddleware
    ) {}

    public function login(): void
    {
        $this->jwtMiddleware->validateApiRequest();

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['email']) || empty($input['password'])) {
            $this->jwtMiddleware->sendJsonError('Email and password are required', 400);
        }

        // Sanitiser les données d'entrée
        $sanitizationRules = [
            'email' => 'email',
            'password' => 'string',
            'remember' => 'bool'
        ];

        $sanitizedInput = Request::sanitize($input, $sanitizationRules);

        try {
            // Récupérer le paramètre remember (optionnel)
            $remember = (bool) ($sanitizedInput['remember'] ?? false);
            $result = $this->authService->loginWithJwt($sanitizedInput['email'], $sanitizedInput['password'], $remember);
            
            $this->jwtMiddleware->sendJsonResponse([
                'success' => true,
                'message' => 'Login successful',
                'user' => $result['user'],
                'tokens' => $result['tokens']
            ]);

        } catch (AuthException $e) {
            $this->jwtMiddleware->sendJsonError($e->getMessage(), 401);
        }
    }

    public function refresh(): void
    {
        $this->jwtMiddleware->validateApiRequest();

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['refresh_token'])) {
            $this->jwtMiddleware->sendJsonError('Refresh token is required', 400);
        }

        try {
            $result = $this->authService->refreshJwtToken($input['refresh_token']);
            
            $this->jwtMiddleware->sendJsonResponse([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'user' => $result['user'],
                'tokens' => $result['tokens']
            ]);

        } catch (AuthException $e) {
            $this->jwtMiddleware->sendJsonError($e->getMessage(), 401);
        }
    }

    public function me(): void
    {
        $this->jwtMiddleware->validateApiRequest();
        $user = $this->jwtMiddleware->authenticate();

        $this->jwtMiddleware->sendJsonResponse([
            'success' => true,
            'user' => $user->toArray()
        ]);
    }

    public function logout(): void
    {
        $this->jwtMiddleware->validateApiRequest();
        $user = $this->jwtMiddleware->authenticate();

        // Dans une implémentation complète, vous pourriez blacklister le token
        // $token = $this->jwtMiddleware->extractToken();
        // $this->jwtService->blacklistToken($token);

        $this->jwtMiddleware->sendJsonResponse([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    public function register(): void
    {
        $this->jwtMiddleware->validateApiRequest();

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['nom']) || empty($input['prenom']) || empty($input['email']) || empty($input['password'])) {
            $this->jwtMiddleware->sendJsonError('Nom, prénom, email and password are required', 400);
        }

        // Sanitiser et valider les données d'entrée
        $sanitizationRules = [
            'nom' => 'string',
            'prenom' => 'string',
            'email' => 'email',
            'password' => 'string'
        ];

        $sanitizedInput = Request::sanitize($input, $sanitizationRules);

        // Validation supplémentaire des noms/prénoms
        if (!$this->validateName($sanitizedInput['nom'])) {
            $this->jwtMiddleware->sendJsonError('Le nom contient des caractères non autorisés', 400);
        }

        if (!$this->validateName($sanitizedInput['prenom'])) {
            $this->jwtMiddleware->sendJsonError('Le prénom contient des caractères non autorisés', 400);
        }

        try {
            $result = $this->authService->registerWithJwt(
                $sanitizedInput['nom'],
                $sanitizedInput['prenom'],
                $sanitizedInput['email'],
                $sanitizedInput['password']
            );
            
            $this->jwtMiddleware->sendJsonResponse([
                'success' => true,
                'message' => 'Registration successful',
                'user' => $result['user'],
                'tokens' => $result['tokens']
            ]);

        } catch (AuthException $e) {
            $this->jwtMiddleware->sendJsonError($e->getMessage(), 400);
        }
    }

    public function validateToken(): void
    {
        $this->jwtMiddleware->validateApiRequest();

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['token'])) {
            $this->jwtMiddleware->sendJsonError('Token is required', 400);
        }

        try {
            $debug = $this->jwtService->debugToken($input['token']);
            
            $this->jwtMiddleware->sendJsonResponse([
                'success' => true,
                'token_info' => $debug
            ]);

        } catch (\Exception $e) {
            $this->jwtMiddleware->sendJsonError($e->getMessage(), 401);
        }
    }

    public function profile(): void
    {
        $this->jwtMiddleware->validateApiRequest();
        $user = $this->jwtMiddleware->authenticate();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->getProfile($user);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $this->updateProfile($user);
        } else {
            $this->jwtMiddleware->sendJsonError('Method not allowed', 405);
        }
    }

    private function getProfile($user): void
    {
        $this->jwtMiddleware->sendJsonResponse([
            'success' => true,
            'user' => $user->toArray()
        ]);
    }

    private function updateProfile($user): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        // Validation basique - à améliorer selon vos besoins
        $allowedFields = ['first_name', 'last_name'];
        $updates = [];

        foreach ($allowedFields as $field) {
            if (isset($input[$field]) && !empty(trim($input[$field]))) {
                $updates[$field] = trim($input[$field]);
            }
        }

        if (empty($updates)) {
            $this->jwtMiddleware->sendJsonError('No valid fields to update', 400);
        }

        // Note: Vous devrez implémenter la méthode updateProfile dans UserRepository
        // Pour l'instant, on simule une mise à jour réussie
        $this->jwtMiddleware->sendJsonResponse([
            'success' => true,
            'message' => 'Profile updated successfully',
            'updated_fields' => array_keys($updates)
        ]);
    }

    public function users(): void
    {
        $this->jwtMiddleware->validateApiRequest();
        $user = $this->jwtMiddleware->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->listUsers();
        } else {
            $this->jwtMiddleware->sendJsonError('Method not allowed', 405);
        }
    }

    private function listUsers(): void
    {
        // Note: Vous devrez implémenter la méthode findAll dans UserRepository
        // Pour l'instant, on retourne une réponse simulée
        $this->jwtMiddleware->sendJsonResponse([
            'success' => true,
            'message' => 'Users list (admin only)',
            'users' => [
                // Liste simulée d'utilisateurs
            ]
        ]);
    }

    public function health(): void
    {
        $this->jwtMiddleware->validateApiRequest();

        $this->jwtMiddleware->sendJsonResponse([
            'status' => 'OK',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'jwt_enabled' => true
        ]);
    }

    /**
     * Vérifie l'email via token et retourne JSON
     */
    public function verifyEmail(): void
    {
        $this->jwtMiddleware->validateApiRequest();

        try {
            $token = $_GET['token'] ?? null;

            if (!$token) {
                $this->jwtMiddleware->sendJsonError('Token de vérification manquant.', 400);
                return;
            }

            $result = $this->authService->verifyEmailByToken($token);

            $this->jwtMiddleware->sendJsonResponse([
                'success' => true,
                'message' => $result['message'],
                'user' => $result['user']
            ]);

        } catch (AuthException $e) {
            $this->jwtMiddleware->sendJsonError($e->getMessage(), 400);
        } catch (\Exception $e) {
            error_log("Erreur lors de la vérification d'email : " . $e->getMessage());
            $this->jwtMiddleware->sendJsonError('Une erreur est survenue lors de la vérification. Veuillez réessayer plus tard.', 500);
        }
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