<?php

declare(strict_types=1);

namespace ParcCalanques\Auth;

use ParcCalanques\Users\Models\User;
use ParcCalanques\Users\Models\UserRepository;
use ParcCalanques\Shared\Exceptions\AuthException;

class JwtMiddleware
{
    public function __construct(
        private JwtService $jwtService,
        private UserRepository $userRepository
    ) {}

    public function authenticate(): User
    {
        $token = $this->extractToken();
        
        if (!$token) {
            $this->sendUnauthorizedResponse('Token missing');
        }

        try {
            $payload = $this->jwtService->validateToken($token);
            
            if (($payload['token_type'] ?? '') !== 'access') {
                $this->sendUnauthorizedResponse('Invalid token type');
            }

            $userId = (int) ($payload['user_id'] ?? 0);
            if (!$userId) {
                $this->sendUnauthorizedResponse('Invalid user ID in token');
            }

            $user = $this->userRepository->findById($userId);
            
            if (!$user) {
                $this->sendUnauthorizedResponse('User not found');
            }

            if (!$user->isActive()) {
                $this->sendUnauthorizedResponse('User inactive');
            }

            return $user;

        } catch (AuthException $e) {
            $this->sendUnauthorizedResponse($e->getMessage());
        }
    }

    public function requireRole(string $requiredRole): User
    {
        $user = $this->authenticate();
        
        if ($user->getRole() !== $requiredRole) {
            $this->sendForbiddenResponse('Insufficient privileges');
        }

        return $user;
    }

    public function requireAdmin(): User
    {
        return $this->requireRole(User::ROLE_ADMIN);
    }

    public function optionalAuth(): ?User
    {
        $token = $this->extractToken();
        
        if (!$token) {
            return null;
        }

        try {
            return $this->authenticate();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function can(string $permission, User $user = null): bool
    {
        $user = $user ?? $this->optionalAuth();
        
        if (!$user) {
            return false;
        }

        return match($permission) {
            'view_admin_panel' => $user->isAdmin(),
            'manage_users' => $user->isAdmin(),
            'view_profile' => true, // Tout utilisateur connecté peut voir son profil
            'edit_profile' => true, // Tout utilisateur connecté peut éditer son profil
            default => false
        };
    }

    public function requirePermission(string $permission): User
    {
        $user = $this->authenticate();
        
        if (!$this->can($permission, $user)) {
            $this->sendForbiddenResponse('Permission denied');
        }

        return $user;
    }

    private function extractToken(): ?string
    {
        // Try different ways to get the Authorization header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? 
                     $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? 
                     getallheaders()['Authorization'] ?? 
                     '';
        
        if (empty($authHeader)) {
            return null;
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        return substr($authHeader, 7);
    }

    private function sendUnauthorizedResponse(string $message = 'Unauthorized'): never
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Unauthorized',
            'message' => $message,
            'code' => 401
        ]);
        exit;
    }

    private function sendForbiddenResponse(string $message = 'Forbidden'): never
    {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Forbidden',
            'message' => $message,
            'code' => 403
        ]);
        exit;
    }

    public function handleCors(): void
    {
        // Gérer les requêtes CORS pour les API
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowedOrigins = [
            'http://localhost:3000',
            'http://localhost:8080',
            'https://parc-calanques.com'
        ];

        if (in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');

        // Répondre aux requêtes preflight OPTIONS
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    public function validateApiRequest(): void
    {
        $this->handleCors();
        
        // Vérifier que la requête est en JSON pour les API
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'];
        
        if (in_array($method, ['POST', 'PUT', 'PATCH']) && 
            !str_contains($contentType, 'application/json')) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Bad Request',
                'message' => 'Content-Type must be application/json',
                'code' => 400
            ]);
            exit;
        }
    }

    public function sendJsonResponse(array $data, int $statusCode = 200): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function sendJsonError(string $message, int $code = 400, array $details = []): never
    {
        $response = [
            'error' => $this->getErrorName($code),
            'message' => $message,
            'code' => $code
        ];

        if (!empty($details)) {
            $response['details'] = $details;
        }

        $this->sendJsonResponse($response, $code);
    }

    private function getErrorName(int $code): string
    {
        return match($code) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            409 => 'Conflict',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            default => 'Error'
        };
    }
}