<?php

declare(strict_types=1);

namespace ParcCalanques\Auth\Controllers;

use ParcCalanques\Auth\Services\JwtService;
use ParcCalanques\Auth\DTOs\AuthResponse;
use ParcCalanques\Core\Request;
use ParcCalanques\Core\ApiResponse;

class JwtController
{
    private JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function refresh(Request $request): ApiResponse
    {
        try {
            $token = $request->getBearerToken();

            if (!$token) {
                return ApiResponse::error('Token manquant', 401);
            }

            $newToken = $this->jwtService->refreshToken($token);

            if (!$newToken) {
                return ApiResponse::error('Token invalide ou expiré', 401);
            }

            $response = new AuthResponse($newToken, null);
            return ApiResponse::success($response->toArray());

        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors du refresh du token: ' . $e->getMessage(), 500);
        }
    }

    public function validate(Request $request): ApiResponse
    {
        try {
            $token = $request->getBearerToken();

            if (!$token) {
                return ApiResponse::error('Token manquant', 401);
            }

            $payload = $this->jwtService->validateToken($token);

            if (!$payload) {
                return ApiResponse::error('Token invalide', 401);
            }

            return ApiResponse::success(['valid' => true, 'payload' => $payload]);

        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors de la validation: ' . $e->getMessage(), 500);
        }
    }
}