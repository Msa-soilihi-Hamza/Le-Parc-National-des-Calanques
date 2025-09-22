<?php

declare(strict_types=1);

namespace ParcCalanques\Sentiers;

use Database;
use ParcCalanques\Sentiers\Models\SentierRepository;
use ParcCalanques\Sentiers\Services\SentierService;
use ParcCalanques\Sentiers\Controllers\SentierController;

class SentierBootstrap
{
    private static ?SentierService $sentierService = null;
    private static ?SentierRepository $sentierRepository = null;
    private static ?SentierController $sentierController = null;

    public static function init(): SentierService
    {
        if (self::$sentierService === null) {
            // Initialize database connection
            $database = new Database();
            $pdo = $database->getConnection();
            
            if (!$pdo) {
                throw new \RuntimeException('Unable to connect to database');
            }

            // Initialize repository and services
            self::$sentierRepository = new SentierRepository($pdo);
            self::$sentierService = new SentierService(self::$sentierRepository);
        }

        return self::$sentierService;
    }

    public static function getSentierController(): SentierController
    {
        if (self::$sentierController === null) {
            $service = self::init();
            self::$sentierController = new SentierController($service);
        }

        return self::$sentierController;
    }

    public static function getSentierService(): ?SentierService
    {
        return self::$sentierService;
    }

    public static function getSentierRepository(): ?SentierRepository
    {
        return self::$sentierRepository;
    }
}


