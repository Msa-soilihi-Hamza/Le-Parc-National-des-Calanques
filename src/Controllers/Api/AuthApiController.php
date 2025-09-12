<?php

declare(strict_types=1);

namespace ParcCalanques\Controllers\Api;

use ParcCalanques\Auth\AuthService;
use ParcCalanques\Auth\JwtService;
use ParcCalanques\Core\ApiResponse;
use ParcCalanques\Core\Request;
use ParcCalanques\Exceptions\AuthException;

class AuthApiController
{
    public function __construct(
        private AuthService $authService,
        private JwtService $jwtService
    ) {}

    /**
     * POST /api/auth/login
     */
    public function login(): void
    {
        $data = Request::getJsonInput();
        
        Request::validate($data, [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'remember' => 'bool'
        ]);

        try {
            $remember = (bool) ($data['remember'] ?? false);
            $result = $this->authService->loginWithJwt(
                $data['email'], 
                $data['password'], 
                $remember
            );
            
            ApiResponse::success([
                'message' => 'Login successful',
                'user' => $result['user'],
                'tokens' => $result['tokens']
            ]);

        } catch (AuthException $e) {
            ApiResponse::error($e->getMessage(), 401);
        }
    }

    /**
     * POST /api/auth/refresh
     */
    public function refresh(): void
    {
        $data = Request::getJsonInput();
        
        Request::validate($data, [
            'refresh_token' => 'required'
        ]);

        try {
            $result = $this->authService->refreshJwtToken($data['refresh_token']);
            
            ApiResponse::success([
                'message' => 'Token refreshed successfully',
                'user' => $result['user'],
                'tokens' => $result['tokens']
            ]);

        } catch (AuthException $e) {
            ApiResponse::error($e->getMessage(), 401);
        }
    }

    /**
     * GET /api/auth/me
     */
    public function me(): void
    {
        $user = Request::getAuthenticatedUser();
        
        ApiResponse::success([
            'user' => $user->toArray()
        ]);
    }

    /**
     * POST /api/auth/register
     */
    public function register(): void
    {
        $data = Request::getJsonInput();
        
        Request::validate($data, [
            'nom' => 'required|min:2|max:50',
            'prenom' => 'required|min:2|max:50',
            'email' => 'required|email',
            'password' => 'required|min:12'
        ]);

        try {
            $result = $this->authService->registerWithJwt(
                $data['nom'],
                $data['prenom'],
                $data['email'],
                $data['password']
            );
            
            // Le service retourne maintenant un format différent si l'email n'est pas vérifié
            if (isset($result['email_verification_required']) && $result['email_verification_required']) {
                ApiResponse::success([
                    'message' => $result['message'],
                    'user' => $result['user'],
                    'email_verification_required' => true
                ]);
            } else {
                ApiResponse::success([
                    'message' => 'Registration successful',
                    'user' => $result['user'],
                    'tokens' => $result['tokens']
                ]);
            }

        } catch (AuthException $e) {
            ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(): void
    {
        $user = Request::getAuthenticatedUser();
        
        // TODO: Blacklist token si nécessaire
        // $token = Request::getBearerToken();
        // $this->jwtService->blacklistToken($token);

        ApiResponse::success([
            'message' => 'Logout successful'
        ]);
    }

    /**
     * POST /api/auth/validate
     */
    public function validateToken(): void
    {
        $data = Request::getJsonInput();
        
        Request::validate($data, [
            'token' => 'required'
        ]);

        try {
            $debug = $this->jwtService->debugToken($data['token']);
            
            ApiResponse::success([
                'token_info' => $debug
            ]);

        } catch (\Exception $e) {
            ApiResponse::error($e->getMessage(), 401);
        }
    }
}