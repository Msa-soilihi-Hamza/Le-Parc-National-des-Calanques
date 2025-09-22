<?php

declare(strict_types=1);

use ParcCalanques\Sentiers\Controllers\SentierController;

/**
 * Routes pour la gestion des sentiers
 * Toutes les routes sont préfixées par /api/sentiers
 */

return [
    // Consultation des sentiers (accessible à tous les utilisateurs connectés)
    'GET /api/sentiers' => [SentierController::class, 'index'],
    'GET /api/sentiers/filters' => [SentierController::class, 'filters'],
    'GET /api/sentiers/stats' => [SentierController::class, 'stats'],
    'GET /api/sentiers/{id}' => [SentierController::class, 'show'],

    // Routes d'administration (à implémenter plus tard)
    // 'POST /api/sentiers' => [SentierController::class, 'create'],        // Admin seulement
    // 'PUT /api/sentiers/{id}' => [SentierController::class, 'update'],    // Admin seulement
    // 'DELETE /api/sentiers/{id}' => [SentierController::class, 'delete'], // Admin seulement
];


