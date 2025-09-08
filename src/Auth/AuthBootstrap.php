<?php

declare(strict_types=1);

namespace ParcCalanques\Auth;

use Database;
use ParcCalanques\Models\UserRepository;

class AuthBootstrap
{
    private static ?AuthService $authService = null;
    private static ?UserRepository $userRepository = null;
    private static ?JwtService $jwtService = null;
    private static ?JwtMiddleware $jwtMiddleware = null;

    public static function init(): AuthService
    {
        if (self::$authService === null) {
            // Initialize database connection
            $database = new Database();
            $pdo = $database->getConnection();
            
            if (!$pdo) {
                throw new \RuntimeException('Unable to connect to database');
            }

            // Initialize repositories and services
            self::$userRepository = new UserRepository($pdo);
            $sessionManager = new SessionManager(self::$userRepository);
            self::$jwtService = new JwtService();
            self::$authService = new AuthService(self::$userRepository, $sessionManager, self::$jwtService);

            // Initialize AuthGuard
            AuthGuard::init(self::$authService);

            // Attempt remember login if not already authenticated
            if (!self::$authService->isAuthenticated()) {
                self::$authService->attemptRememberLogin();
            }
        }

        return self::$authService;
    }

    public static function getAuthService(): ?AuthService
    {
        return self::$authService;
    }

    public static function getUserRepository(): ?UserRepository
    {
        return self::$userRepository;
    }

    public static function middleware(): AuthMiddleware
    {
        if (self::$authService === null) {
            self::init();
        }

        return new AuthMiddleware(self::$authService);
    }

    public static function getJwtService(): ?JwtService
    {
        return self::$jwtService;
    }

    public static function jwtMiddleware(): JwtMiddleware
    {
        if (self::$authService === null) {
            self::init();
        }

        if (self::$jwtMiddleware === null) {
            self::$jwtMiddleware = new JwtMiddleware(self::$jwtService, self::$userRepository);
        }

        return self::$jwtMiddleware;
    }

    public static function apiController(): \ParcCalanques\Controllers\ApiController
    {
        if (self::$authService === null) {
            self::init();
        }

        return new \ParcCalanques\Controllers\ApiController(
            self::$authService,
            self::$jwtService,
            self::jwtMiddleware()
        );
    }
}