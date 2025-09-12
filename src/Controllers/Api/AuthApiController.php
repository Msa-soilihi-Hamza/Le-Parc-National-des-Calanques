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