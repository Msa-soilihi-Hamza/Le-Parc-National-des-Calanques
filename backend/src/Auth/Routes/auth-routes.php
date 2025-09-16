<?php

declare(strict_types=1);

use ParcCalanques\Auth\Controllers\AuthController;
use ParcCalanques\Auth\Controllers\JwtController;

/**
 * Routes d'authentification
 * Toutes les routes sont prefixées par /api/auth
 */

return [
    // Authentification de base
    'POST /api/auth/login' => [AuthController::class, 'login'],
    'POST /api/auth/logout' => [AuthController::class, 'logout'],
    'POST /api/auth/register' => [AuthController::class, 'register'],
    'GET /api/auth/profile' => [AuthController::class, 'profile'],
    'PUT /api/auth/profile' => [AuthController::class, 'updateProfile'],
    'POST /api/auth/change-password' => [AuthController::class, 'changePassword'],

    // Gestion JWT
    'POST /api/auth/refresh' => [JwtController::class, 'refresh'],
    'POST /api/auth/validate' => [JwtController::class, 'validate'],

    // Récupération de mot de passe
    'POST /api/auth/forgot-password' => [AuthController::class, 'forgotPassword'],
    'POST /api/auth/reset-password' => [AuthController::class, 'resetPassword'],

    // Vérification compte
    'POST /api/auth/verify-email' => [AuthController::class, 'verifyEmail'],
    'POST /api/auth/resend-verification' => [AuthController::class, 'resendVerification']
];