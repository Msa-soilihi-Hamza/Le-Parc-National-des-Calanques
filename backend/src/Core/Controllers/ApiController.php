<?php

declare(strict_types=1);

namespace ParcCalanques\Core\Controllers;

use ParcCalanques\Auth\AuthService;
use ParcCalanques\Auth\JwtService;
use ParcCalanques\Auth\JwtMiddleware;
use ParcCalanques\Exceptions\AuthException;

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

        try {
            // Récupérer le paramètre remember (optionnel)
            $remember = (bool) ($input['remember'] ?? false);
            $result = $this->authService->loginWithJwt($input['email'], $input['password'], $remember);
            
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

        try {
            $result = $this->authService->registerWithJwt(
                $input['nom'],
                $input['prenom'],
                $input['email'],
                $input['password']
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
}